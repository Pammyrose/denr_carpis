<?php

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function archiveHistoryAdmin(): User
{
    return User::factory()->create([
        'is_admin' => 1,
        'email_verified_at' => now(),
    ]);
}

function archiveHistoryEmployee(string $positionName): Employee
{
    return Employee::create([
        'first_name' => 'Archive',
        'middle_name' => 'History',
        'last_name' => 'Employee',
        'sex' => 'Female',
        'email' => 'archive-history@example.com',
        'contact_no' => '09170000999',
        'emp_status' => 'Regular',
        'position_name' => $positionName,
        'assignment_name' => 'Regional Office',
        'div_sec_unit' => 'FINANCE DIVISION',
    ]);
}

test('archiving frees the exact position and records viewable position history', function () {
    Storage::fake('local');
    $this->travelTo(CarbonImmutable::parse('2026-09-02 08:19:03', 'UTC'));
    $admin = archiveHistoryAdmin();
    $position = Position::create([
        'item_code' => 'ARCHIVE-001',
        'name' => 'Archive Test Position',
        'desc' => 'FINANCE DIVISION',
        'status' => 'Occupied',
    ]);
    Position::create([
        'item_code' => 'ARCHIVE-002',
        'name' => 'Archive Test Position',
        'desc' => 'FINANCE DIVISION',
        'status' => 'Occupied',
    ]);
    $employee = archiveHistoryEmployee($position->name);

    $this->actingAs($admin)
        ->post(route('employee.store'), ['archive_employee_id' => $employee->id])
        ->assertRedirect(route('employee.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    expect($position->fresh()->status)->toBe('Vacant');
    $this->assertDatabaseHas('employee_position_histories', [
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'item_code' => 'ARCHIVE-001',
        'position_name' => 'Archive Test Position',
        'event' => 'archived',
        'status_from' => 'Occupied',
        'status_to' => 'Vacant',
    ]);

    $archive = json_decode(Storage::disk('local')->get('archived_employees.json'), true);
    expect($archive[$employee->id]['position_record_id'])->toBe($position->id)
        ->and($archive[$employee->id]['position_item_code'])->toBe('ARCHIVE-001');

    $this->get(route('employee.archived'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('archivedEmployees', 1)
            ->where('archivedEmployees.0.id', $employee->id)
            ->has('archivedEmployees.0.position_history', 1)
            ->where('archivedEmployees.0.position_history.0.item_code', 'ARCHIVE-001')
            ->where('archivedEmployees.0.position_history.0.event', 'archived')
            ->where('archivedEmployees.0.position_history.0.recorded_at', '2026-09-02 16:19:03')
            ->where('archivedEmployees.0.archived_at', '2026-09-02 16:19:03'));
});

test('unarchiving reoccupies the recorded position and appends restoration history', function () {
    Storage::fake('local');
    $admin = archiveHistoryAdmin();
    $position = Position::create([
        'item_code' => 'RESTORE-001',
        'name' => 'Restore Test Position',
        'status' => 'Occupied',
    ]);
    $employee = archiveHistoryEmployee($position->name);

    $this->actingAs($admin)
        ->post(route('employee.store'), ['archive_employee_id' => $employee->id])
        ->assertSessionHasNoErrors();

    $this->post(route('employee.unarchive', $employee->id))
        ->assertRedirect(route('employee.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'position_name' => 'Restore Test Position',
    ]);
    expect($position->fresh()->status)->toBe('Occupied');
    $this->assertDatabaseHas('employee_position_histories', [
        'employee_id' => $employee->id,
        'position_id' => $position->id,
        'event' => 'restored',
        'status_from' => 'Vacant',
        'status_to' => 'Occupied',
    ]);
    expect(json_decode(Storage::disk('local')->get('archived_employees.json'), true))->not->toHaveKey((string) $employee->id);

    $this->get(route('employee.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('employees', 1)
            ->has('employees.0.position_history', 2)
            ->where('employees.0.position_history.0.event', 'restored')
            ->where('employees.0.position_history.1.event', 'archived'));

    $this->get(route('employee.view', $employee->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('employee.position_history', 2)
            ->where('employee.position_history.0.event', 'restored')
            ->where('employee.position_history.1.event', 'archived'));
});

test('unarchiving is blocked if the recorded position has already been filled', function () {
    Storage::fake('local');
    $admin = archiveHistoryAdmin();
    $position = Position::create([
        'item_code' => 'CONFLICT-001',
        'name' => 'Conflict Test Position',
        'status' => 'Occupied',
    ]);
    $employee = archiveHistoryEmployee($position->name);

    $this->actingAs($admin)
        ->post(route('employee.store'), ['archive_employee_id' => $employee->id])
        ->assertSessionHasNoErrors();

    $position->refresh()->update(['status' => 'Occupied']);

    $this->from(route('employee.archived'))
        ->post(route('employee.unarchive', $employee->id))
        ->assertRedirect(route('employee.archived'))
        ->assertSessionHasErrors('general');

    $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    $this->assertDatabaseMissing('employee_position_histories', [
        'employee_id' => $employee->id,
        'event' => 'restored',
    ]);
    $archive = json_decode(Storage::disk('local')->get('archived_employees.json'), true);
    expect($archive)->toHaveKey((string) $employee->id);
});

test('an archived employee can be restored into a different vacant position', function () {
    Storage::fake('local');
    $admin = archiveHistoryAdmin();
    $formerPosition = Position::create([
        'item_code' => 'FORMER-001',
        'name' => 'Former Position',
        'status' => 'Occupied',
    ]);
    $alternatePosition = Position::create([
        'item_code' => 'ALTERNATE-001',
        'name' => 'Alternate Position',
        'desc' => 'CENRO Pinukpuk',
        'status' => 'Vacant',
    ]);
    $employee = archiveHistoryEmployee($formerPosition->name);

    $this->actingAs($admin)
        ->post(route('employee.store'), ['archive_employee_id' => $employee->id])
        ->assertSessionHasNoErrors();

    // Simulate the former position being filled before this employee is restored.
    $formerPosition->refresh()->update(['status' => 'Occupied']);

    $this->post(route('employee.unarchive', $employee->id), [
        'position_record_id' => $alternatePosition->id,
    ])->assertRedirect(route('employee.index'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'position_name' => 'Alternate Position',
        'assignment_name' => 'CENRO Pinukpuk',
        'div_sec_unit' => 'CENRO Pinukpuk',
    ]);
    expect($formerPosition->fresh()->status)->toBe('Occupied')
        ->and($alternatePosition->fresh()->status)->toBe('Occupied');
    $this->assertDatabaseHas('employee_position_histories', [
        'employee_id' => $employee->id,
        'position_id' => $alternatePosition->id,
        'item_code' => 'ALTERNATE-001',
        'event' => 'restored_reassigned',
        'assignment_name' => 'CENRO Pinukpuk',
        'div_sec_unit' => 'CENRO Pinukpuk',
        'status_from' => 'Vacant',
        'status_to' => 'Occupied',
    ]);
});
