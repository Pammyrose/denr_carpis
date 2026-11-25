<?php

use App\Models\AssignmentPlace;
use App\Models\Employee;
use App\Models\OrgUnit;
use App\Models\Position;
use App\Models\User;
use App\Services\OrganizationalScope;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createScopedEmployee(string $email, string $assignment, string $unit, string $contact): Employee
{
    AssignmentPlace::firstOrCreate([
        'name' => $assignment,
    ], [
        'region' => 'Cordillera Administrative Region',
        'address' => 'N/A',
    ]);

    OrgUnit::firstOrCreate([
        'name' => $unit,
    ], [
        'org_code' => strtoupper(str_replace([' ', '.'], '_', $unit)),
    ]);

    Schema::disableForeignKeyConstraints();

    try {
        return Employee::create([
            'first_name' => 'Scope',
            'last_name' => 'Tester',
            'sex' => 'Male',
            'email' => $email,
            'contact_no' => $contact,
            'emp_status' => 'Regular',
            'assignment_name' => $assignment,
            'div_sec_unit' => $unit,
        ]);
    } finally {
        Schema::enableForeignKeyConstraints();
    }
}

test('a PENRO admin sees only its PENRO and subordinate CENRO employees', function () {
    $admin = User::factory()->create([
        'email' => 'abra-admin@example.com',
        'is_admin' => 2,
    ]);
    createScopedEmployee($admin->email, 'PENRO Abra', 'PENRO Abra', '09170000001');
    createScopedEmployee('bangued@example.com', 'CENRO Bangued', 'CENRO Bangued', '09170000002');
    createScopedEmployee('ifugao@example.com', 'PENRO Ifugao', 'PENRO Ifugao', '09170000003');
    createScopedEmployee('regional@example.com', 'Regional Office', 'OFFICE OF THE REGIONAL DIRECTOR', '09170000004');
    AssignmentPlace::firstOrCreate(['name' => 'CENRO Lagangilang'], [
        'region' => 'Cordillera Administrative Region',
        'address' => 'N/A',
    ]);

    $employees = app(OrganizationalScope::class)
        ->applyToEmployees(Employee::query(), $admin)
        ->pluck('email');

    expect($employees)->toContain('abra-admin@example.com', 'bangued@example.com')
        ->not->toContain('ifugao@example.com', 'regional@example.com');

    $this->actingAs($admin)
        ->get(route('employee.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('employee/Index')
            ->has('employees', 2)
            ->where('assignments', ['CENRO Bangued', 'CENRO Lagangilang', 'PENRO Abra']));
});

test('an admin sees only positions within its office hierarchy', function () {
    $admin = User::factory()->create([
        'email' => 'position-admin@example.com',
        'is_admin' => 2,
    ]);
    createScopedEmployee($admin->email, 'PENRO Abra', 'PENRO Abra', '09170000011');

    Position::create(['item_code' => 'ABRA-1', 'org_code' => 'PENRO_ABRA', 'name' => 'Abra Position', 'status' => 'Vacant']);
    Position::create(['item_code' => 'BANGUED-1', 'org_code' => 'CENRO_BANGUED', 'name' => 'Bangued Position', 'status' => 'Vacant']);
    Position::create(['item_code' => 'IFUGAO-1', 'org_code' => 'PENRO_IFUGAO', 'name' => 'Ifugao Position', 'status' => 'Vacant']);
    Position::create(['item_code' => 'LAGANGILANG-1', 'org_code' => '', 'name' => 'Lagangilang Position', 'desc' => 'CENRO Lagangilang', 'status' => 'Vacant']);
    Position::create(['item_code' => 'IFUGAO-DESC-1', 'org_code' => '', 'name' => 'Outside Position', 'desc' => 'PENRO Ifugao', 'status' => 'Vacant']);

    $this->actingAs($admin)
        ->get(route('positions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('positions/Index')
            ->has('positions', 3)
            ->where('positions.0.item_code', 'ABRA-1')
            ->where('positions.1.item_code', 'BANGUED-1')
            ->where('positions.2.item_code', 'LAGANGILANG-1'));
});

test('a super admin remains unrestricted', function () {
    $superAdmin = User::factory()->create(['is_admin' => 1]);

    expect(app(OrganizationalScope::class)->allowedAssignments($superAdmin))->toBeNull();
});

test('an admin without an employee assignment sees no organization records', function () {
    $admin = User::factory()->create(['is_admin' => 2]);

    expect(app(OrganizationalScope::class)
        ->applyToEmployees(Employee::query(), $admin)
        ->count())->toBe(0);
});
