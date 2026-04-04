<?php

use App\Models\Team;
use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest user has no shared auth data', function () {
    $this->get('/')
        ->assertInertia(fn ($page) => $page
            ->where('auth.user', null)
        );
});

test('regular user sees their pending vacation requests count and zero pending approvals', function () {
    $user = User::factory()->create();

    // Own pending requests
    VacationRequest::factory()->count(2)->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    // Own approved request (should not be counted)
    VacationRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'approved',
    ]);

    // Another user's pending request (should not be counted for approvals if not manager/admin)
    VacationRequest::factory()->create([
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_vacation_requests_count', 2)
            ->where('auth.user.pending_approvals_count', 0)
        );
});

test('manager sees their own pending requests and total pending approvals count', function () {
    $manager = User::factory()->create();
    $managerTeam = Team::factory()->create([
        'user_id' => $manager->id,
        'name' => 'Manager',
        'personal_team' => false,
    ]);
    $manager->teams()->attach($managerTeam, ['role' => 'admin']);
    $manager->switchTeam($managerTeam);

    // Ensure isManager() returns true
    expect($manager->isManager())->toBeTrue();

    // Manager's own pending requests
    VacationRequest::factory()->count(1)->create([
        'user_id' => $manager->id,
        'status' => 'pending',
    ]);

    // Other users' pending requests
    VacationRequest::factory()->count(3)->create([
        'status' => 'pending',
    ]);

    // Total pending = 1 (manager) + 3 (others) = 4

    $this->actingAs($manager)
        ->get('/')
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_vacation_requests_count', 1)
            ->where('auth.user.pending_approvals_count', 4)
        );
});

test('admin sees their own pending requests and total pending approvals count', function () {
    $admin = User::factory()->create();
    $adminTeam = Team::factory()->create([
        'user_id' => $admin->id,
        'name' => 'Admin',
        'personal_team' => false,
    ]);
    $admin->teams()->attach($adminTeam, ['role' => 'admin']);
    $admin->switchTeam($adminTeam);

    // Ensure isAdmin() returns true
    expect($admin->isAdmin())->toBeTrue();

    // Admin's own pending requests
    VacationRequest::factory()->count(1)->create([
        'user_id' => $admin->id,
        'status' => 'pending',
    ]);

    // Other users' pending requests
    VacationRequest::factory()->count(2)->create([
        'status' => 'pending',
    ]);

    // Total pending = 1 (admin) + 2 (others) = 3

    $this->actingAs($admin)
        ->get('/')
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_vacation_requests_count', 1)
            ->where('auth.user.pending_approvals_count', 3)
        );
});
