<?php

use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeExcelImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

function employeeImportWorkbook(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'employee-import-').'.xlsx';
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([
        [
            'fullname', 'sex', 'email', 'password', 'contact_no',
            'emp_status', 'item_code', 'position_name', 'salary_grade',
            'assignment_name', 'div_sec_unit', 'date_of_birth', 'tin_no',
            'date_appointment', 'date_last_promotion', 'civil_service', 'education',
        ],
        ...$rows,
    ]);
    (new Xlsx($spreadsheet))->save($path);
    $spreadsheet->disconnectWorksheets();

    return $path;
}

function prepareEmployeeImportSqliteSchema(): void
{
    // The legacy schema references indexed (rather than unique) name columns.
    // MySQL accepts that schema, while SQLite requires unique parent keys.
    DB::statement('CREATE UNIQUE INDEX test_positions_name_unique ON positions (name)');
    DB::statement('CREATE UNIQUE INDEX test_assignments_name_unique ON assignments (name)');
    DB::statement('CREATE UNIQUE INDEX test_div_sec_units_name_unique ON div_sec_units (name)');
}

test('it imports all rows and preserves an existing password when the cell is blank', function () {
    prepareEmployeeImportSqliteSchema();

    $path = employeeImportWorkbook([
        [
            'DOE, JANE A', 'Female', 'jane@example.test', 'OriginalPass1!', '09170000001',
            'Regular', 'ITEM-001', 'Accountant', '12', 'Main Office', 'Finance',
            '1990-01-15', '123-456', '2020-02-01', '', 'Professional', 'BS Accountancy',
        ],
        [
            'SMITH, JOHN B', 'Male', 'john@example.test', 'SecondPass1!', '09170000002',
            'Regular', 'ITEM-002', 'Engineer', '15', 'Field Office', 'Engineering',
            '1988-06-10', '', '2019-03-01', '', 'Professional', 'BS Engineering',
        ],
    ]);

    $progress = [];
    $result = app(EmployeeExcelImporter::class)->import($path, function (array $state) use (&$progress) {
        $progress = $state;
    });

    expect($result)->toMatchArray([
        'total' => 2,
        'processed' => 2,
        'created' => 2,
        'updated' => 0,
        'failed' => 0,
    ])->and($progress['processed'])->toBe(2)
        ->and(Employee::count())->toBe(2)
        ->and(Hash::check('OriginalPass1!', User::where('email', 'jane@example.test')->value('password')))->toBeTrue();

    @unlink($path);

    $updatePath = employeeImportWorkbook([
        [
            'DOE, JANE A', 'Female', 'jane@example.test', 'DoNotReplace1!', '09170000001',
            'Regular', 'ITEM-001', 'Senior Accountant', '14', 'Main Office', 'Finance',
            '1990-01-15', '123-456', '2020-02-01', '', 'Professional', 'BS Accountancy',
        ],
    ]);

    $updatedResult = app(EmployeeExcelImporter::class)->import($updatePath, fn (array $state) => null);

    expect($updatedResult['updated'])->toBe(1)
        ->and($updatedResult['failed'])->toBe(0)
        ->and(Hash::check('OriginalPass1!', User::where('email', 'jane@example.test')->value('password')))->toBeTrue();

    @unlink($updatePath);
});

test('duplicate placeholder emails and contacts create distinct employees', function () {
    prepareEmployeeImportSqliteSchema();

    $path = employeeImportWorkbook([
        [
            'DOE, JANE A', 'Female', 'sample@example.test', '', '123456789',
            'Regular', 'ITEM-101', 'Accountant', '12', 'Main Office', 'Finance',
            '', '', '', '', '', '',
        ],
        [
            'SMITH, JOHN B', 'Male', 'sample@example.test', '', '123456789',
            'Regular', 'ITEM-102', 'Engineer', '15', 'Field Office', 'Engineering',
            '', '', '', '', '', '',
        ],
    ]);

    $firstChunk = app(EmployeeExcelImporter::class)->importChunk($path, 0, 1);
    $secondChunk = app(EmployeeExcelImporter::class)->importChunk($path, 1, 1);

    expect($firstChunk['created'])->toBe(1)
        ->and($firstChunk['processed'])->toBe(1)
        ->and($secondChunk['created'])->toBe(1)
        ->and($secondChunk['processed'])->toBe(1)
        ->and($firstChunk['failed'])->toBe(0)
        ->and($secondChunk['failed'])->toBe(0)
        ->and(Employee::count())->toBe(2)
        ->and(Employee::distinct()->count('email'))->toBe(2)
        ->and(Employee::distinct()->count('contact_no'))->toBe(2);

    $secondImport = app(EmployeeExcelImporter::class)->import($path, fn (array $state) => null);

    expect($secondImport['created'])->toBe(0)
        ->and($secondImport['updated'])->toBe(2)
        ->and($secondImport['failed'])->toBe(0)
        ->and(Employee::count())->toBe(2);

    @unlink($path);
});
