<?php

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Middleware\ShareInertiaData;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Access & Visibility
|--------------------------------------------------------------------------
*/

test('users can view the vacation management page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->withoutMiddleware([ShareInertiaData::class])
        ->get(route('vacations.index'))
        ->assertSuccessful();
});

test('managers can view the vacation approvals page', function () {
    $manager = User::factory()->manager()->create();
    $this->actingAs($manager)
        ->withoutMiddleware([ShareInertiaData::class])
        ->get(route('vacations.approvals'))
        ->assertSuccessful();
});

test('non-managers cannot view the vacation approvals page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->withoutMiddleware([ShareInertiaData::class])
        ->get(route('vacations.approvals'))
        ->assertForbidden();
});

test('users can view the vacation calendar', function () {
    $user = User::factory()->create();
    $vacation = VacationRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'approved',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(2)->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('vacations.calendar'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Vacations/Calendar')
            ->has('vacations', 1)
            ->where('vacations.0.user_name', $user->name)
        );
});

/*
|--------------------------------------------------------------------------
| Shared Data/Counts
|--------------------------------------------------------------------------
*/

test('the pending vacation requests count is shared with inertia', function () {
    $user = User::factory()->create();

    // Create 2 pending and 1 approved vacation requests for the user
    VacationRequest::factory()->count(2)->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);
    VacationRequest::factory()->create([
        'user_id' => $user->id,
        'status' => 'approved',
    ]);

    // Create a pending vacation request for another user
    VacationRequest::factory()->create([
        'status' => 'pending',
    ]);

    $this->actingAs($user)
        ->get(route('vacations.calendar'))
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_vacation_requests_count', 2)
        );
});

test('the pending vacation requests count is zero if no pending requests', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('vacations.calendar'))
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_vacation_requests_count', 0)
        );
});

test('managers can see the total pending approvals count', function () {
    $manager = User::factory()->manager()->create();
    $user = User::factory()->create();

    // Create 3 pending requests in total
    VacationRequest::factory()->count(2)->create([
        'user_id' => $user->id,
        'status' => 'pending',
    ]);
    VacationRequest::factory()->create([
        'user_id' => $manager->id,
        'status' => 'pending',
    ]);
    $this->actingAs($manager)
        ->get(route('vacations.calendar'))
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_approvals_count', 3)
        );
});

test('the pending approvals count is zero for managers if no pending requests', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->get(route('vacations.calendar'))
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_approvals_count', 0)
        );
});

test('non-managers do not see the pending approvals count', function () {
    $user = User::factory()->create();
    VacationRequest::factory()->create(['status' => 'pending']);

    $this->actingAs($user)
        ->get(route('vacations.calendar'))
        ->assertInertia(fn ($page) => $page
            ->where('auth.user.pending_approvals_count', 0)
        );
});
