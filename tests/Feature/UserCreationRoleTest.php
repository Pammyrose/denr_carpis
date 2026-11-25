<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('an admin-created account is always assigned the regular user role', function () {
    $admin = User::factory()->create(['is_admin' => 2]);

    $this->actingAs($admin)->post(route('users.store'), [
        'full_name' => 'Created User',
        'email' => 'created@example.com',
        'is_admin' => 1,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'created@example.com',
        'is_admin' => 0,
    ]);
});

test('the selected role must be valid when a super admin creates a user', function () {
    $superAdmin = User::factory()->create(['is_admin' => 1]);

    $this->actingAs($superAdmin)->post(route('users.store'), [
        'full_name' => 'Invalid Role User',
        'email' => 'invalid-role@example.com',
        'is_admin' => 99,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('is_admin');

    $this->assertDatabaseMissing('users', [
        'email' => 'invalid-role@example.com',
    ]);
});

test('an admin cannot change a user role through the update endpoint', function () {
    $admin = User::factory()->create([
        'is_admin' => 2,
        'office_scope' => 'Regional Office',
    ]);
    $user = User::factory()->create([
        'email' => 'regional-user@example.com',
        'is_admin' => 0,
    ]);

    $employee = Employee::create([
        'first_name' => 'Regional',
        'last_name' => 'User',
        'sex' => 'Male',
        'email' => $user->email,
        'contact_no' => '09170000099',
        'emp_status' => 'Regular',
        'assignment_name' => 'Regional Office',
    ]);

    $this->actingAs($admin)->put(route('users.update', $user), [
        'full_name' => 'User, Updated Regional',
        'email' => 'updated-regional-user@example.com',
        'is_admin' => 1,
        'password' => '',
        'password_confirmation' => '',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'updated-regional-user@example.com',
        'is_admin' => 0,
    ]);
    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'email' => 'updated-regional-user@example.com',
        'first_name' => 'Updated',
        'middle_name' => 'Regional',
        'last_name' => 'User',
    ]);
});

test('a super admin can update a user full name and linked employee email', function () {
    $superAdmin = User::factory()->create(['is_admin' => 1]);
    $user = User::factory()->create([
        'email' => 'old-email@example.com',
        'is_admin' => 0,
    ]);

    $employee = Employee::create([
        'first_name' => 'Old',
        'last_name' => 'Name',
        'sex' => 'Female',
        'email' => $user->email,
        'contact_no' => '09170000100',
        'emp_status' => 'Regular',
        'assignment_name' => 'Regional Office',
    ]);

    $this->actingAs($superAdmin)->put(route('users.update', $user), [
        'full_name' => 'Name, Updated Full',
        'email' => 'updated-email@example.com',
        'is_admin' => 0,
        'password' => '',
        'password_confirmation' => '',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'updated-email@example.com',
    ]);
    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'email' => 'updated-email@example.com',
        'first_name' => 'Updated',
        'middle_name' => 'Full',
        'last_name' => 'Name',
    ]);
});

test('an Admin must have a Regional Office or PENRO scope', function () {
    $superAdmin = User::factory()->create(['is_admin' => 1]);

    $this->actingAs($superAdmin)->post(route('users.store'), [
        'full_name' => 'Scoped Admin',
        'email' => 'scoped-admin@example.com',
        'is_admin' => 2,
        'office_scope' => 'PENRO Abra',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'scoped-admin@example.com',
        'is_admin' => 2,
        'office_scope' => 'PENRO Abra',
    ]);

    $this->actingAs($superAdmin)->post(route('users.store'), [
        'full_name' => 'Unscoped Admin',
        'email' => 'unscoped-admin@example.com',
        'is_admin' => 2,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('office_scope');
});

test('role values use the super admin admin and user labels', function () {
    expect(User::factory()->make(['is_admin' => 1])->role)->toBe('Super Admin')
        ->and(User::factory()->make(['is_admin' => 2])->role)->toBe('Admin')
        ->and(User::factory()->make(['is_admin' => 0])->role)->toBe('User');
});

test('a role can still be updated on a legacy users table without a name column', function () {
    $superAdmin = User::factory()->create(['is_admin' => 1]);
    $user = User::factory()->create(['is_admin' => 0]);

    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('name');
    });

    $this->actingAs($superAdmin)->put(route('users.update', $user), [
        'full_name' => 'Legacy User',
        'email' => $user->email,
        'is_admin' => 2,
        'office_scope' => 'Regional Office',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => $user->email,
        'is_admin' => 2,
        'office_scope' => 'Regional Office',
    ]);
});
