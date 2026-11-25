<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\OrgUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class OrganizationalScope
{
    public function isRestricted(?User $user): bool
    {
        return (int) ($user?->is_admin ?? 0) === 2;
    }

    public function assignedOffice(?User $user): ?string
    {
        if (! $this->isRestricted($user)) {
            return null;
        }

        $selectedOffice = $user->getAttribute('office_scope');
        if (is_string($selectedOffice) && $selectedOffice !== '') {
            return $selectedOffice;
        }

        return Employee::query()
            ->where('email', $user->email)
            ->value('assignment_name');
    }

    /** @return array<int, string> */
    public function selectableOffices(): array
    {
        return array_keys(config('organizational_scope.offices', []));
    }

    /**
     * Null means unrestricted. An empty array means restricted with no valid
     * employee office assignment, so no organization records may be returned.
     *
     * @return array<int, string>|null
     */
    public function allowedAssignments(?User $user): ?array
    {
        if (! $this->isRestricted($user)) {
            return null;
        }

        $office = $this->assignedOffice($user);
        if (! $office) {
            return [];
        }

        $officeConfig = config('organizational_scope.offices', [])[$office] ?? null;

        return $officeConfig['assignments'] ?? [$office];
    }

    /** @return array<int, string>|null */
    public function allowedOrgCodes(?User $user): ?array
    {
        $assignments = $this->allowedAssignments($user);
        if ($assignments === null) {
            return null;
        }
        if ($assignments === []) {
            return [];
        }

        $office = $this->assignedOffice($user);
        $officeConfig = config('organizational_scope.offices', [])[$office] ?? null;
        $configuredCodes = $officeConfig['org_codes'] ?? null;

        if (is_array($configuredCodes)) {
            return $configuredCodes;
        }

        return OrgUnit::query()
            ->whereIn('name', $assignments)
            ->pluck('org_code')
            ->filter()
            ->values()
            ->all();
    }

    /** @return array<int, string>|null */
    public function allowedPositionDescriptions(?User $user): ?array
    {
        $assignments = $this->allowedAssignments($user);
        if ($assignments === null || $assignments === []) {
            return $assignments;
        }

        $office = $this->assignedOffice($user);
        $officeConfig = config('organizational_scope.offices', [])[$office] ?? null;

        return $officeConfig['position_descriptions'] ?? $assignments;
    }

    public function applyToEmployees(EloquentBuilder|QueryBuilder $query, ?User $user, string $column = 'assignment_name'): EloquentBuilder|QueryBuilder
    {
        $assignments = $this->allowedAssignments($user);

        if ($assignments !== null) {
            $query->whereIn($column, $assignments);
        }

        return $query;
    }

    public function applyToUsers(EloquentBuilder $query, ?User $user): EloquentBuilder
    {
        $assignments = $this->allowedAssignments($user);

        if ($assignments !== null) {
            $query->whereHas('employee', fn (EloquentBuilder $employeeQuery) => $employeeQuery->whereIn('assignment_name', $assignments));
        }

        return $query;
    }

    public function applyToPositions(EloquentBuilder|QueryBuilder $query, ?User $user, string $column = 'org_code'): EloquentBuilder|QueryBuilder
    {
        $orgCodes = $this->allowedOrgCodes($user);
        $descriptions = $this->allowedPositionDescriptions($user);

        if ($orgCodes !== null) {
            $descriptionColumn = str_contains($column, '.')
                ? substr($column, 0, strrpos($column, '.') + 1).'desc'
                : 'desc';

            $query->where(function ($scopeQuery) use ($column, $orgCodes, $descriptionColumn, $descriptions) {
                $scopeQuery->whereIn($column, $orgCodes)
                    ->orWhereIn($descriptionColumn, $descriptions ?? []);
            });
        }

        return $query;
    }

    public function applyToAssets(EloquentBuilder|QueryBuilder $query, ?User $user, string $assignedColumn = 'assigned'): EloquentBuilder|QueryBuilder
    {
        $assignments = $this->allowedAssignments($user);

        if ($assignments !== null) {
            $query->whereIn($assignedColumn, Employee::query()->whereIn('assignment_name', $assignments)->select('id'));
        }

        return $query;
    }

    public function canAccessEmployee(?User $user, Employee $employee): bool
    {
        $assignments = $this->allowedAssignments($user);

        return $assignments === null || in_array($employee->assignment_name, $assignments, true);
    }

    public function canAccessUser(?User $viewer, User $subject): bool
    {
        $assignments = $this->allowedAssignments($viewer);

        return $assignments === null
            || ($subject->employee && in_array($subject->employee->assignment_name, $assignments, true));
    }
}
