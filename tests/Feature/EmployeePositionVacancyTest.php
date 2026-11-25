<?php

use App\Models\AssignmentPlace;
use App\Models\Employee;
use App\Models\OrgUnit;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    URL::forceRootUrl('http://localhost');
});

function vacancyTestAdmin(): User
{
    return User::create([
        'email' => 'vacancy-admin@example.com',
        'password' => Hash::make('password'),
        'is_admin' => 1,
        'email_verified_at' => now(),
    ]);
}

test('position dropdown data exposes vacancy status for client-side filtering', function () {
    Position::create([
        'item_code' => 'VACANT-001',
        'name' => 'Vacant Position',
        'status' => 'Vacant',
    ]);
    Position::create([
        'item_code' => 'OCCUPIED-001',
        'name' => 'Occupied Position',
        'status' => 'Occupied',
    ]);

    $this->actingAs(vacancyTestAdmin())
        ->get('/employee')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('employee/Index')
            ->has('dropdowns.positions', 2)
            ->where('dropdowns.positions.0.status', 'Vacant')
            ->where('dropdowns.positions.1.status', 'Occupied')
        );
});

test('employee position filters are grouped by assignment and division', function () {
    DB::statement('CREATE UNIQUE INDEX employee_filter_positions_name_unique ON positions (name)');
    DB::statement('CREATE UNIQUE INDEX employee_filter_assignments_name_unique ON assignments (name)');
    DB::statement('CREATE UNIQUE INDEX employee_filter_div_sec_units_name_unique ON div_sec_units (name)');

    AssignmentPlace::create(['name' => 'PENRO Abra', 'region' => 'CAR']);
    AssignmentPlace::create(['name' => 'PENRO Benguet', 'region' => 'CAR']);

    OrgUnit::create(['org_code' => 'MSD-ABRA', 'name' => 'MSD-PENRO Abra']);
    OrgUnit::create(['org_code' => 'TSD-ABRA', 'name' => 'TSD-PENRO Abra']);
    OrgUnit::create(['org_code' => 'MSD-BENGUET', 'name' => 'MSD-PENRO Benguet']);

    foreach ([
        ['Accountant', 'PENRO Abra', 'MSD-PENRO Abra'],
        ['Administrative Officer', 'PENRO Abra', 'MSD-PENRO Abra'],
        ['Forester', 'PENRO Abra', 'TSD-PENRO Abra'],
        ['Engineer', 'PENRO Benguet', 'MSD-PENRO Benguet'],
    ] as $index => [$position, $assignment, $division]) {
        Position::create([
            'item_code' => 'FILTER-'.($index + 1),
            'name' => $position,
            'status' => 'Occupied',
        ]);

        Employee::create([
            'first_name' => 'Employee',
            'last_name' => (string) ($index + 1),
            'sex' => 'Other',
            'email' => "filter{$index}@example.com",
            'contact_no' => '0917000000'.$index,
            'emp_status' => 'Regular',
            'position_name' => $position,
            'assignment_name' => $assignment,
            'div_sec_unit' => $division,
        ]);
    }

    $this->actingAs(vacancyTestAdmin())
        ->get('/employee?assignment_name=PENRO%20Abra&div_sec_unit=MSD-PENRO%20Abra')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('positionOptionsByAssignment.PENRO Abra', ['Accountant', 'Administrative Officer', 'Forester'])
            ->where('positionOptionsByAssignmentAndDivision.PENRO Abra.MSD-PENRO Abra', ['Accountant', 'Administrative Officer'])
            ->where('positionOptionsByAssignmentAndDivision.PENRO Abra.TSD-PENRO Abra', ['Forester'])
            ->where('positionOptionsByAssignment.PENRO Benguet', ['Engineer'])
        );
});

test('an occupied position cannot be assigned when creating an employee', function () {
    Position::create([
        'item_code' => 'OCCUPIED-002',
        'name' => 'Already Filled Position',
        'status' => 'Occupied',
    ]);

    $this->actingAs(vacancyTestAdmin())
        ->from('/employee')
        ->post('/employee', [
            'position_name' => 'Already Filled Position',
        ])
        ->assertRedirect('/employee')
        ->assertSessionHasErrors('position_name');
});
