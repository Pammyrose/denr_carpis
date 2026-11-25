<?php

namespace App\Services;

use App\Models\AssignmentPlace;
use App\Models\Employee;
use App\Models\OrgUnit;
use App\Models\Position;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use RuntimeException;
use Throwable;

class EmployeeExcelImporter
{
    private const CHUNK_SIZE = 25;

    private const IMPORT_BCRYPT_ROUNDS = 10;

    private array $positionsByItemCode = [];

    private array $assignmentsByName = [];

    private array $orgUnitsByName = [];

    private array $employeesByEmail = [];

    private array $employeesByContact = [];

    private array $employeesByName = [];

    private array $usersByEmail = [];

    private array $emailFrequencies = [];

    private array $contactFrequencies = [];

    public function importChunk(string $path, int $offset, int $limit = 50): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();

        if ($rows === []) {
            throw new RuntimeException('The spreadsheet is empty.');
        }

        $header = array_shift($rows);
        $columnIndexes = $this->mapColumns($header);
        $dataRows = [];

        foreach ($rows as $rowIndex => $row) {
            if ($this->rowHasData($row)) {
                $dataRows[] = ['number' => $rowIndex + 2, 'values' => $row];
            }
        }

        $this->prepareIdentityFrequencies($dataRows, $columnIndexes);
        $this->loadLookupCaches();

        $result = [
            'total' => count($dataRows),
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach (array_slice($dataRows, $offset, $limit) as $row) {
            try {
                $outcome = DB::transaction(
                    fn () => $this->processRow($row['values'], $row['number'], $columnIndexes),
                    3
                );
                $result[$outcome]++;
            } catch (Throwable $exception) {
                $result['failed']++;
                $result['errors'][] = "Row {$row['number']}: {$exception->getMessage()}";
                $this->loadLookupCaches();
            }

            $result['processed']++;
        }

        return $result;
    }

    public function import(string $path, callable $onProgress): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if ($rows === []) {
            throw new RuntimeException('The spreadsheet is empty.');
        }

        $header = array_shift($rows);
        $columnIndexes = $this->mapColumns($header);
        $dataRows = [];

        foreach ($rows as $rowIndex => $row) {
            if (! $this->rowHasData($row)) {
                continue;
            }

            $dataRows[] = [
                'number' => $rowIndex + 2,
                'values' => $row,
            ];
        }

        $this->prepareIdentityFrequencies($dataRows, $columnIndexes);
        $this->loadLookupCaches();

        $result = [
            'total' => count($dataRows),
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $onProgress($result);

        foreach (array_chunk($dataRows, self::CHUNK_SIZE) as $chunk) {
            foreach ($chunk as $row) {
                try {
                    $outcome = DB::transaction(
                        fn () => $this->processRow($row['values'], $row['number'], $columnIndexes),
                        3
                    );
                    $result[$outcome]++;
                } catch (Throwable $exception) {
                    $result['failed']++;
                    if (count($result['errors']) < 200) {
                        $result['errors'][] = "Row {$row['number']}: {$exception->getMessage()}";
                    }

                    // A rolled-back row may have changed in-memory model caches.
                    $this->loadLookupCaches();
                }

                $result['processed']++;
            }

            $onProgress($result);
        }

        return $result;
    }

    private function processRow(array $row, int $rowNumber, array $columns): string
    {
        $fullName = $this->cell($row, $columns, 'fullname') ?: "Unknown {$rowNumber}";
        [$firstName, $middleName, $lastName, $suffix] = $this->splitFullName($fullName);

        $rawEmail = strtolower($this->cell($row, $columns, 'email'));
        $rawContactNo = $this->cell($row, $columns, 'contact_no');
        $nameKey = $this->employeeNameKey($firstName, $middleName, $lastName, $suffix);
        $employeeByName = $this->employeesByName[$nameKey] ?? null;

        $sexRaw = strtoupper($this->cell($row, $columns, 'sex') ?: 'F');
        $sex = in_array($sexRaw, ['M', 'MALE', '1'], true) ? 'Male' : 'Female';

        $itemCode = $this->cell($row, $columns, 'item_code') ?: "NO-CODE-{$rowNumber}";
        $identityHash = substr(sha1($nameKey.'|'.$itemCode.'|'.$rowNumber), 0, 20);

        $emailIsDuplicated = $rawEmail !== ''
            && ($this->emailFrequencies[$this->key($rawEmail)] ?? 0) > 1;
        $contactIsDuplicated = $rawContactNo !== ''
            && ($this->contactFrequencies[$this->key($rawContactNo)] ?? 0) > 1;

        if ($rawEmail === '') {
            $email = $employeeByName?->email ?: "import-{$identityHash}@auto.local";
        } elseif ($emailIsDuplicated && (! $employeeByName || $this->key($employeeByName->email) !== $this->key($rawEmail))) {
            $email = "import-{$identityHash}@auto.local";
        } else {
            $email = $rawEmail;
        }

        if ($rawContactNo === '') {
            $contactNo = $employeeByName?->contact_no ?: "AUTO-{$identityHash}";
        } elseif ($contactIsDuplicated && (! $employeeByName || $this->key((string) $employeeByName->contact_no) !== $this->key($rawContactNo))) {
            $contactNo = "AUTO-{$identityHash}";
        } else {
            $contactNo = $rawContactNo;
        }

        $positionKey = $this->key($itemCode);
        $positionData = [
            'name' => $this->cell($row, $columns, 'position_name') ?: "{$itemCode} Position",
            'salary_grade' => $this->nullable($this->cell($row, $columns, 'salary_grade')),
            'status' => 'Occupied',
        ];

        $position = $this->positionsByItemCode[$positionKey] ?? null;
        if ($position && $position->name !== $positionData['name']) {
            // Employee records reference position names. Create the renamed
            // position first so existing foreign-key references remain valid.
            $position = Position::firstOrNew(['name' => $positionData['name']]);
            $position->fill(['item_code' => $itemCode] + $positionData);
            $position->save();
            $this->positionsByItemCode[$positionKey] = $position;
        } elseif ($position) {
            $position->fill($positionData);
            if ($position->isDirty()) {
                $position->save();
            }
        } else {
            $position = Position::create(['item_code' => $itemCode] + $positionData);
            $this->positionsByItemCode[$positionKey] = $position;
        }

        $assignmentName = $this->cell($row, $columns, 'assignment_name') ?: 'UNASSIGNED';
        $assignmentKey = $this->key($assignmentName);
        $assignment = $this->assignmentsByName[$assignmentKey] ?? null;
        if (! $assignment) {
            $assignment = AssignmentPlace::create([
                'name' => $assignmentName,
                'region' => 'Unspecified',
            ]);
            $this->assignmentsByName[$assignmentKey] = $assignment;
        }

        $orgUnitName = $this->cell($row, $columns, 'div_sec_unit') ?: 'UNASSIGNED';
        $orgUnitKey = $this->key($orgUnitName);
        $orgUnit = $this->orgUnitsByName[$orgUnitKey] ?? null;
        if (! $orgUnit) {
            $orgUnit = OrgUnit::create([
                'org_code' => 'IMP-'.strtoupper(substr(sha1($orgUnitName), 0, 10)),
                'name' => $orgUnitName,
            ]);
            $this->orgUnitsByName[$orgUnitKey] = $orgUnit;
        }

        $employeeData = [
            'first_name' => $firstName,
            'middle_name' => $this->nullable($middleName),
            'last_name' => $lastName,
            'suffix' => $this->nullable($suffix),
            'sex' => $sex,
            'email' => $email,
            'contact_no' => $contactNo,
            'emp_status' => $this->cell($row, $columns, 'emp_status') ?: 'Regular',
            'position_name' => $position->name,
            'assignment_name' => $assignment->name,
            'div_sec_unit' => $orgUnit->name,
        ];

        $otherInfoData = [
            'date_of_birth' => $this->excelDateToYmd($this->rawCell($row, $columns, 'date_of_birth')),
            'tin_no' => $this->nullable($this->cell($row, $columns, 'tin_no')),
            'date_appointment' => $this->excelDateToYmd($this->rawCell($row, $columns, 'date_appointment')),
            'date_last_promotion' => $this->excelDateToYmd($this->rawCell($row, $columns, 'date_last_promotion')),
            'civil_service' => $this->nullable($this->cell($row, $columns, 'civil_service')),
            'education' => $this->nullable($this->cell($row, $columns, 'education')),
        ];

        $emailKey = $this->key($email);
        $employee = $employeeByName ?: ($this->employeesByEmail[$emailKey] ?? null);
        if (! $employee && $contactNo !== '') {
            $employee = $this->employeesByContact[$this->key($contactNo)] ?? null;
        }

        $rawPassword = $this->cell($row, $columns, 'password');
        $user = $employee
            ? ($this->usersByEmail[$this->key($employee->email)] ?? null)
            : ($this->usersByEmail[$emailKey] ?? null);

        if ($employee) {
            $oldEmailKey = $this->key($employee->email);
            $oldContactKey = $this->key((string) $employee->contact_no);
            $oldNameKey = $this->employeeNameKey(
                $employee->first_name,
                $employee->middle_name,
                $employee->last_name,
                $employee->suffix
            );

            $employee->fill($employeeData);
            if ($employee->isDirty()) {
                $employee->save();
            }

            $otherInfo = $employee->otherInfo;
            if ($otherInfo) {
                $otherInfo->fill($otherInfoData);
                if ($otherInfo->isDirty()) {
                    $otherInfo->save();
                }
            } else {
                $otherInfo = $employee->otherInfo()->create($otherInfoData);
                $employee->setRelation('otherInfo', $otherInfo);
            }

            if ($user) {
                $userData = [];
                if ($user->email !== $email) {
                    $userData['email'] = $email;
                }
                if ($userData !== []) {
                    $user->update($userData);
                }
            } else {
                $user = User::create([
                    'email' => $email,
                    'password' => $this->hashImportedPassword($rawPassword),
                    'is_admin' => false,
                ]);
            }

            unset($this->employeesByEmail[$oldEmailKey]);
            if ($oldContactKey !== '') {
                unset($this->employeesByContact[$oldContactKey]);
            }
            unset($this->usersByEmail[$oldEmailKey]);
            unset($this->employeesByName[$oldNameKey]);
            $this->employeesByEmail[$emailKey] = $employee;
            $this->employeesByName[$nameKey] = $employee;
            if ($contactNo !== '') {
                $this->employeesByContact[$this->key($contactNo)] = $employee;
            }
            $this->usersByEmail[$emailKey] = $user;

            return 'updated';
        }

        // Existing account passwords are never reset by a bulk import.
        if (! $user) {
            $user = User::create([
                'email' => $email,
                'password' => $this->hashImportedPassword($rawPassword),
                'is_admin' => false,
            ]);
        }

        $employee = Employee::create($employeeData);
        $employee->otherInfo()->create($otherInfoData);

        $this->employeesByEmail[$emailKey] = $employee;
        $this->employeesByName[$nameKey] = $employee;
        if ($contactNo !== '') {
            $this->employeesByContact[$this->key($contactNo)] = $employee;
        }
        $this->usersByEmail[$emailKey] = $user;

        return 'created';
    }

    private function loadLookupCaches(): void
    {
        $this->positionsByItemCode = Position::query()
            ->whereNotNull('item_code')
            ->get()
            ->keyBy(fn (Position $position) => $this->key((string) $position->item_code))
            ->all();

        $this->assignmentsByName = AssignmentPlace::all()
            ->keyBy(fn (AssignmentPlace $assignment) => $this->key($assignment->name))
            ->all();

        $this->orgUnitsByName = OrgUnit::all()
            ->keyBy(fn (OrgUnit $orgUnit) => $this->key($orgUnit->name))
            ->all();

        $employees = Employee::with('otherInfo')->get();
        $this->employeesByEmail = $employees
            ->keyBy(fn (Employee $employee) => $this->key($employee->email))
            ->all();
        $this->employeesByContact = $employees
            ->filter(fn (Employee $employee) => filled($employee->contact_no))
            ->keyBy(fn (Employee $employee) => $this->key($employee->contact_no))
            ->all();
        $this->employeesByName = $employees
            ->keyBy(fn (Employee $employee) => $this->employeeNameKey(
                $employee->first_name,
                $employee->middle_name,
                $employee->last_name,
                $employee->suffix
            ))
            ->all();

        $this->usersByEmail = User::all()
            ->keyBy(fn (User $user) => $this->key($user->email))
            ->all();
    }

    private function mapColumns(array $header): array
    {
        $aliases = [
            'fullname' => ['fullname', 'full name', 'name', 'employee name'],
            'sex' => ['sex', 'gender'],
            'email' => ['email', 'email address'],
            'password' => ['password', 'pwd'],
            'contact_no' => ['contact_no', 'contact', 'phone', 'mobile'],
            'emp_status' => ['emp_status', 'status'],
            'item_code' => ['item_code', 'item code', 'plantilla'],
            'position_name' => ['position_name', 'position'],
            'salary_grade' => ['salary_grade', 'sg'],
            'assignment_name' => ['assignment_name', 'assignment'],
            'div_sec_unit' => ['div_sec_unit', 'division', 'unit'],
            'date_of_birth' => ['date_of_birth', 'dob'],
            'tin_no' => ['tin_no', 'tin'],
            'date_appointment' => ['date_appointment'],
            'date_last_promotion' => ['date_last_promotion'],
            'civil_service' => ['civil_service', 'eligibility'],
            'education' => ['education', 'degree'],
        ];

        $normalizedHeader = [];
        foreach ($header as $column => $value) {
            $normalizedHeader[$column] = $this->key((string) $value);
        }

        $columns = [];
        foreach ($aliases as $field => $fieldAliases) {
            foreach ($normalizedHeader as $column => $value) {
                if (in_array($value, $fieldAliases, true)) {
                    $columns[$field] = $column;
                    break;
                }
            }
        }

        if (! isset($columns['fullname'])) {
            throw new RuntimeException('The Excel file must contain a fullname column.');
        }

        return $columns;
    }

    private function prepareIdentityFrequencies(array $dataRows, array $columns): void
    {
        $this->emailFrequencies = [];
        $this->contactFrequencies = [];

        foreach ($dataRows as $row) {
            $email = $this->key($this->cell($row['values'], $columns, 'email'));
            $contact = $this->key($this->cell($row['values'], $columns, 'contact_no'));

            if ($email !== '') {
                $this->emailFrequencies[$email] = ($this->emailFrequencies[$email] ?? 0) + 1;
            }
            if ($contact !== '') {
                $this->contactFrequencies[$contact] = ($this->contactFrequencies[$contact] ?? 0) + 1;
            }
        }
    }

    private function employeeNameKey(
        ?string $firstName,
        ?string $middleName,
        ?string $lastName,
        ?string $suffix
    ): string {
        return $this->key(implode('|', [
            $firstName,
            $middleName,
            $lastName,
            $suffix,
        ]));
    }

    private function splitFullName(string $fullName): array
    {
        $fullName = preg_replace('/\s+/', ' ', trim($fullName));
        $suffix = null;
        $suffixes = ['JR', 'SR', 'II', 'III', 'IV', 'V', 'VI', 'PHD', 'MD', 'CPA', 'RN'];
        $parts = preg_split('/\s+/', $fullName) ?: [];

        if ($parts !== []) {
            $candidate = strtoupper(rtrim((string) end($parts), '.'));
            if (in_array($candidate, $suffixes, true)) {
                $suffix = $candidate;
                array_pop($parts);
                $fullName = implode(' ', $parts);
            }
        }

        if (str_contains($fullName, ',')) {
            [$lastName, $remaining] = array_map('trim', explode(',', $fullName, 2));
            $remainingParts = preg_split('/\s+/', $remaining) ?: [];

            if (count($remainingParts) <= 1) {
                return [strtoupper($remainingParts[0] ?? ''), null, strtoupper($lastName), $suffix];
            }

            if (count($remainingParts) === 2) {
                return [
                    strtoupper($remainingParts[0]),
                    strtoupper($remainingParts[1]),
                    strtoupper($lastName),
                    $suffix,
                ];
            }

            return [
                strtoupper(implode(' ', array_slice($remainingParts, 0, 2))),
                strtoupper(implode(' ', array_slice($remainingParts, 2))),
                strtoupper($lastName),
                $suffix,
            ];
        }

        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $compoundSurnames = [
            'de la cruz', 'de los santos', 'de la pena', 'de la peña',
            'de la rosa', 'de los reyes', 'del rosario', 'san juan',
            'san jose', 'santa maria', 'san pedro', 'san miguel',
            'del mundo', 'de vera', 'de guzman', 'de jesus', 'de castro',
            'de leon', 'delos reyes', 'del pilar', 'dela fuente', 'de la torre',
        ];

        $lastName = null;
        for ($length = min(3, count($parts)); $length >= 2; $length--) {
            $candidate = strtolower(implode(' ', array_slice($parts, -$length)));
            if (in_array($candidate, $compoundSurnames, true)) {
                $lastName = strtoupper(implode(' ', array_splice($parts, -$length)));
                break;
            }
        }

        $lastName ??= strtoupper((string) array_pop($parts));

        return [strtoupper(implode(' ', $parts)), null, $lastName, $suffix];
    }

    private function excelDateToYmd(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return date('Y-m-d', Date::excelToTimestamp((float) $value));
            } catch (Throwable) {
                return null;
            }
        }

        $normalized = preg_replace('/[\/.]/', '-', trim((string) $value));
        foreach (['m-d-y', 'm-d-Y', 'Y-m-d', 'd-m-Y'] as $format) {
            $date = DateTime::createFromFormat('!'.$format, $normalized);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function rawCell(array $row, array $columns, string $field): mixed
    {
        $column = $columns[$field] ?? null;

        return $column === null ? null : ($row[$column] ?? null);
    }

    private function hashImportedPassword(string $password): string
    {
        return Hash::make(
            $password !== '' ? $password : Str::random(18).'@A1',
            ['rounds' => self::IMPORT_BCRYPT_ROUNDS]
        );
    }

    private function cell(array $row, array $columns, string $field): string
    {
        return trim((string) $this->rawCell($row, $columns, $field));
    }

    private function nullable(?string $value): ?string
    {
        return $value === null || trim($value) === '' ? null : trim($value);
    }

    private function rowHasData(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return true;
            }
        }

        return false;
    }

    private function key(string $value): string
    {
        return strtolower(trim($value));
    }
}
