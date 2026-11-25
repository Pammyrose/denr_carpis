<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('privileged users can visit the dashboard', function () {
    $user = User::factory()->create(['is_admin' => 2]);
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('vacant positions without organization codes are grouped by their stored unit description', function () {
    $user = User::factory()->create(['is_admin' => 2]);

    DB::table('positions')->insert([
        'item_code' => 'TEST-VACANT-1',
        'name' => 'Test Vacant Position',
        'desc' => 'FINANCE DIVISION',
        'status' => 'Vacant',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertInertia(fn (Assert $page) => $page
            ->where('dashboardData.vacantPositions', 1)
            ->where('dashboardData.vacantPositionsByUnit.0.name', 'FINANCE DIVISION')
            ->where('dashboardData.vacantPositionsByUnit.0.value', 1));
});
