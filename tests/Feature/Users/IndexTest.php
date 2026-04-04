<?php

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admins can see user list with pending requests info', function () {
    $admin = createAdmin();

    $user1 = User::factory()->create(['name' => 'User One']);
    $user2 = User::factory()->create(['name' => 'User Two']);

    VacationRequest::factory()->create([
        'user_id' => $user1->id,
        'status' => 'pending',
    ]);

    VacationRequest::factory()->create([
        'user_id' => $user2->id,
        'status' => 'approved',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('users.index'));

    $response->assertStatus(200);
    $response->assertInertia(function ($page) use ($user1, $user2) {
        $page->component('Users/Index');

        $users = $page->toArray()['props']['users'];
        $u1 = collect($users)->firstWhere('id', $user1->id);
        $u2 = collect($users)->firstWhere('id', $user2->id);

        expect($u1)->toHaveKey('has_pending_requests', true);
        expect($u2)->toHaveKey('has_pending_requests', false);

        return $page;
    });
});

test('admins can see the user management page', function () {
    $admin = createAdmin();

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Users/Index'));
});

test('admins can see the user management page with vacation days information', function () {
    $admin = createAdmin();
    $user = User::factory()->create(['vacation_days_yearly' => 20]);
    VacationRequest::factory()->create([
        'user_id' => $user->id,
        'total_days' => 5,
        'status' => 'approved',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->startOfYear()->addDays(4),
    ]);

    $response = $this->actingAs($admin)->get(route('users.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Users/Index')
        ->where('users', function ($users) use ($user) {
            $u = collect($users)->firstWhere('id', $user->id);

            return $u &&
                $u['vacation_days_yearly'] === 20 &&
                $u['vacation_days_used'] === 5 &&
                $u['vacation_days_remaining'] === 15;
        })
    );
});

test('managers can see the user management page', function () {
    $manager = createManager();

    $response = $this->actingAs($manager)->get(route('users.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Users/Index'));
});

test('staff cannot see the user management page', function () {
    $staff = User::factory()->create();

    $response = $this->actingAs($staff)->get(route('users.index'));

    $response->assertStatus(403);
});
