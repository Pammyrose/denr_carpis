<?php

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    URL::forceRootUrl('http://localhost');
});

test('admins can view the positions page', function () {
    DB::statement('CREATE UNIQUE INDEX position_page_positions_name_unique ON positions (name)');
    DB::statement('CREATE UNIQUE INDEX position_page_assignments_name_unique ON assignments (name)');
    DB::statement('CREATE UNIQUE INDEX position_page_div_sec_units_name_unique ON div_sec_units (name)');

    $user = User::create([
        'email' => 'positions-admin@example.com',
        'password' => Hash::make('password'),
        'is_admin' => 1,
        'email_verified_at' => now(),
    ]);
    Position::create([
        'item_code' => 'CARPIS-001',
        'name' => 'Administrative Officer',
        'desc' => 'Regional Office',
        'salary_grade' => '11',
        'status' => 'Occupied',
    ]);
    Employee::create([
        'first_name' => 'Maria',
        'middle_name' => 'Santos',
        'last_name' => 'Dela Cruz',
        'sex' => 'Female',
        'email' => 'maria@example.com',
        'contact_no' => '09171234567',
        'emp_status' => 'Regular',
        'position_name' => 'Administrative Officer',
        'assignment_name' => null,
        'div_sec_unit' => null,
    ]);

    $this->actingAs($user)
        ->get('/positions')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('positions/Index')
            ->has('positions', 1)
            ->where('positions.0.item_code', 'CARPIS-001')
            ->where('positions.0.name', 'Administrative Officer')
            ->where('positions.0.desc', 'Regional Office')
            ->where('positions.0.occupied_by.full_name', 'Dela Cruz, Maria Santos')
            ->where('positions.0.occupied_by.email', 'maria@example.com')
        );
});

test('regular users cannot view the positions page', function () {
    $user = User::create([
        'email' => 'positions-user@example.com',
        'password' => Hash::make('password'),
        'is_admin' => 0,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/positions')
        ->assertRedirect(route('profile'));
});

test('admins can add a position from the positions page', function () {
    $user = User::create([
        'email' => 'positions-creator@example.com',
        'password' => Hash::make('password'),
        'is_admin' => 1,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->from('/positions')
        ->post('/employee/position', [
            'item_code' => 'CARPIS-002',
            'name' => 'Planning Officer',
            'desc' => 'Planning and Management Division',
            'salary_grade' => '15',
        ])
        ->assertRedirect('/positions')
        ->assertSessionHas('message', 'Position created successfully');

    $this->assertDatabaseHas('positions', [
        'item_code' => 'CARPIS-002',
        'name' => 'Planning Officer',
        'desc' => 'Planning and Management Division',
        'status' => 'Vacant',
    ]);

    $this->actingAs($user)
        ->from('/positions')
        ->post('/employee/position', [
            'item_code' => 'CARPIS-003',
            'name' => 'Invalid Grade Position',
            'desc' => 'Regional Office',
            'salary_grade' => '34',
        ])
        ->assertRedirect('/positions')
        ->assertSessionHasErrors('salary_grade');

    $this->assertDatabaseMissing('positions', [
        'item_code' => 'CARPIS-003',
    ]);
});
