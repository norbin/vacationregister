<?php

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Middleware\ShareInertiaData;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Approval
|--------------------------------------------------------------------------
*/

test('managers can approve a vacation request', function () {
    $manager = User::factory()->manager()->create();
    $user = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->actingAs($manager)
        ->post(route('vacations.approve', $request), [
            'status' => 'approved',
            'manager_comment' => 'Have a nice time!',
        ]);

    expect($request->fresh()->status)->toBe('approved');
    expect($request->fresh()->approved_by_id)->toBe($manager->id);
});

test('non-managers cannot approve a vacation request', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->actingAs($otherUser)
        ->post(route('vacations.approve', $request), [
            'status' => 'approved',
        ])
        ->assertForbidden();

    expect($request->fresh()->status)->toBe('pending');
});

test('a manager can see their own vacation request in the approvals list', function () {
    $manager = User::factory()->manager()->create();
    $request = VacationRequest::factory()->create(['user_id' => $manager->id, 'status' => 'pending']);

    $this->actingAs($manager)
        ->withoutMiddleware([ShareInertiaData::class])
        ->get(route('vacations.approvals'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Vacations/Approvals')
            ->has('requests', 1)
            ->where('requests.0.id', $request->id)
        );
});

test('a manager can approve their own vacation request', function () {
    $manager = User::factory()->manager()->create();
    $request = VacationRequest::factory()->create(['user_id' => $manager->id, 'status' => 'pending']);

    $this->actingAs($manager)
        ->post(route('vacations.approve', $request), [
            'status' => 'approved',
            'manager_comment' => 'Self-approving',
        ])
        ->assertRedirect();

    expect($request->fresh()->status)->toBe('approved');
    expect($request->fresh()->approved_by_id)->toBe($manager->id);
});

test('a manager cannot approve another manager vacation request', function () {
    $manager1 = User::factory()->manager()->create();
    $manager2 = User::factory()->manager()->create();
    $request = VacationRequest::factory()->create(['user_id' => $manager2->id, 'status' => 'pending']);

    $this->actingAs($manager1)
        ->post(route('vacations.approve', $request), [
            'status' => 'approved',
        ])
        ->assertForbidden();

    expect($request->fresh()->status)->toBe('pending');
});

/*
|--------------------------------------------------------------------------
| Redirection Logic
|--------------------------------------------------------------------------
*/

test('approve method redirects back if there are remaining pending requests', function () {
    $manager = User::factory()->manager()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $request1 = VacationRequest::factory()->create(['user_id' => $user1->id, 'status' => 'pending']);
    VacationRequest::factory()->create(['user_id' => $user2->id, 'status' => 'pending']);

    $this->from(route('vacations.approvals'))
        ->actingAs($manager)
        ->post(route('vacations.approve', $request1), [
            'status' => 'approved',
            'manager_comment' => 'Approved',
        ])
        ->assertRedirect(route('vacations.approvals'));
});

test('approve method redirects to calendar if no remaining pending requests', function () {
    $manager = User::factory()->manager()->create();
    $user = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->actingAs($manager)
        ->post(route('vacations.approve', $request), [
            'status' => 'approved',
            'manager_comment' => 'Approved',
        ])
        ->assertRedirect(route('vacations.calendar'));
});
