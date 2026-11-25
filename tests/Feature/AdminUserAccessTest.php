<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('super admins use the primary users page instead of the separate admin users page', function () {
    $superAdmin = User::factory()->create(['is_admin' => 1]);

    $this->actingAs($superAdmin)
        ->get(route('users_admin.index'))
        ->assertRedirect(route('profile'));
});

test('admins can open the admin-only users page', function () {
    $admin = User::factory()->create(['is_admin' => 2]);

    $this->actingAs($admin)
        ->get(route('users_admin.index'))
        ->assertOk();
});
