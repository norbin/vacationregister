<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admins can update user vacation days', function () {
    $admin = createAdmin();
    $user = User::factory()->create(['vacation_days_yearly' => 20]);

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'vacation_days_yearly' => 25,
    ]);

    $response->assertRedirect();
    expect($user->fresh()->vacation_days_yearly)->toBe(25);
});

test('managers can update user vacation days', function () {
    $manager = createManager();
    $user = User::factory()->create(['vacation_days_yearly' => 20]);

    $response = $this->actingAs($manager)->put(route('users.update', $user), [
        'vacation_days_yearly' => 25,
    ]);

    $response->assertRedirect();
    expect($user->fresh()->vacation_days_yearly)->toBe(25);
});

test('admins can add users to admin and manager teams', function () {
    $admin = createAdmin();
    $user = User::factory()->create();
    Team::where('name', 'Admin')->first() ?: Team::factory()->create(['name' => 'Admin']);
    Team::where('name', 'Manager')->first() ?: Team::factory()->create(['name' => 'Manager']);

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'vacation_days_yearly' => 20,
        'is_admin' => true,
        'is_manager' => true,
    ]);

    $response->assertRedirect();
    expect($user->fresh()->isAdmin())->toBeTrue();
    expect($user->fresh()->isManager())->toBeTrue();
});

test('admins can remove users from admin and manager teams', function () {
    $admin = createAdmin();
    $user = User::factory()->create();
    $adminTeam = Team::where('name', 'Admin')->first() ?: Team::factory()->create(['name' => 'Admin']);
    $managerTeam = Team::where('name', 'Manager')->first() ?: Team::factory()->create(['name' => 'Manager']);

    $user->teams()->attach($adminTeam, ['role' => 'admin']);
    $user->teams()->attach($managerTeam, ['role' => 'editor']);

    expect($user->fresh()->isAdmin())->toBeTrue();
    expect($user->fresh()->isManager())->toBeTrue();

    $response = $this->actingAs($admin)->put(route('users.update', $user), [
        'vacation_days_yearly' => 20,
        'is_admin' => false,
        'is_manager' => false,
    ]);

    $response->assertRedirect();
    expect($user->fresh()->isAdmin())->toBeFalse();
    expect($user->fresh()->isManager())->toBeFalse();
});
