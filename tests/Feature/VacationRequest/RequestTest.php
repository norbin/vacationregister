<?php

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Submission & Validation
|--------------------------------------------------------------------------
*/

test('users can submit a vacation request', function () {
    $user = User::factory()->create(['vacation_days_yearly' => 20]);
    $substitute = User::factory()->create();

    // Use specific dates to avoid weekend calculation issues
    $start = now()->nextWeekday();
    $end = (clone $start)->addDays(1); // 2 working days

    $this->actingAs($user)
        ->post(route('vacations.store'), [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => 'Rest and relaxation',
            'substitute_id' => $substitute->id,
        ]);

    expect(VacationRequest::count())->toBe(1);
    expect(VacationRequest::first()->substitute_id)->toBe($substitute->id);
});

test('users can submit a vacation request without a substitute', function () {
    $user = User::factory()->create(['vacation_days_yearly' => 20]);
    $start = now()->nextWeekday();
    $end = (clone $start)->addDays(1);

    $this->actingAs($user)
        ->post(route('vacations.store'), [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => 'Rest and relaxation',
        ]);

    expect(VacationRequest::count())->toBe(1);
    expect(VacationRequest::first()->substitute_id)->toBeNull();
});

test('users cannot be their own substitute', function () {
    $user = User::factory()->create(['vacation_days_yearly' => 20]);
    $start = now()->nextWeekday();
    $end = (clone $start)->addDays(1);

    $this->actingAs($user)
        ->post(route('vacations.store'), [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'substitute_id' => $user->id,
        ])
        ->assertSessionHasErrors(['substitute_id' => 'You cannot be your own substitute.']);

    expect(VacationRequest::count())->toBe(0);
});

test('users cannot submit a vacation request with insufficient days', function () {
    $user = User::factory()->create(['vacation_days_yearly' => 1]);
    $this->actingAs($user)
        ->post(route('vacations.store'), [
            'start_date' => now()->nextWeekday()->addWeeks(1)->toDateString(),
            'end_date' => now()->nextWeekday()->addWeeks(1)->addDays(5)->toDateString(),
        ])
        ->assertSessionHasErrors(['start_date' => 'You do not have enough vacation days left.']);

    expect(VacationRequest::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Update Substitute
|--------------------------------------------------------------------------
*/

test('users can update the substitute of their pending vacation request', function () {
    $user = User::factory()->create();
    $substitute = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'substitute_id' => null]);

    $this->actingAs($user)
        ->put(route('vacations.update', $request), [
            'substitute_id' => $substitute->id,
        ])
        ->assertRedirect();

    expect($request->fresh()->substitute_id)->toBe($substitute->id);
});

test('users can update the substitute of their approved vacation request', function () {
    $user = User::factory()->create();
    $substitute = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'approved', 'substitute_id' => null]);

    $this->actingAs($user)
        ->put(route('vacations.update', $request), [
            'substitute_id' => $substitute->id,
        ])
        ->assertRedirect();

    expect($request->fresh()->substitute_id)->toBe($substitute->id);
});

test('users cannot update the substitute of their rejected vacation request', function () {
    $user = User::factory()->create();
    $substitute = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'rejected', 'substitute_id' => null]);

    $this->actingAs($user)
        ->put(route('vacations.update', $request), [
            'substitute_id' => $substitute->id,
        ])
        ->assertForbidden();

    expect($request->fresh()->substitute_id)->toBeNull();
});

test('users cannot update the substitute of another user vacation request', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $substitute = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $otherUser->id, 'status' => 'pending', 'substitute_id' => null]);

    $this->actingAs($user)
        ->put(route('vacations.update', $request), [
            'substitute_id' => $substitute->id,
        ])
        ->assertForbidden();

    expect($request->fresh()->substitute_id)->toBeNull();
});

test('users cannot set themselves as substitute during update', function () {
    $user = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'pending', 'substitute_id' => null]);

    $this->actingAs($user)
        ->put(route('vacations.update', $request), [
            'substitute_id' => $user->id,
        ])
        ->assertSessionHasErrors(['substitute_id' => 'You cannot be your own substitute.']);

    expect($request->fresh()->substitute_id)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Cancellation
|--------------------------------------------------------------------------
*/

test('users can cancel their pending vacation request', function () {
    $user = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

    $this->actingAs($user)
        ->delete(route('vacations.destroy', $request))
        ->assertRedirect(route('vacations.index'));

    expect(VacationRequest::count())->toBe(0);
});

test('users cannot cancel their approved vacation request', function () {
    $user = User::factory()->create();
    $request = VacationRequest::factory()->create(['user_id' => $user->id, 'status' => 'approved']);

    $this->actingAs($user)
        ->delete(route('vacations.destroy', $request))
        ->assertForbidden();

    expect(VacationRequest::count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Redirection Logic
|--------------------------------------------------------------------------
*/

test('store method redirects back after submission', function () {
    $user = User::factory()->create(['vacation_days_yearly' => 20]);
    $start = now()->nextWeekday();
    $end = (clone $start)->addDays(1);

    $this->from(route('vacations.index'))
        ->actingAs($user)
        ->post(route('vacations.store'), [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => 'Rest',
        ])
        ->assertRedirect(route('vacations.index'));
});

test('store method redirects back if there are remaining pending requests from other users', function () {
    $user = User::factory()->create(['vacation_days_yearly' => 20]);
    VacationRequest::factory()->create(['status' => 'pending']); // Someone else's request

    $start = now()->nextWeekday();
    $end = (clone $start)->addDays(1);

    $this->from(route('vacations.index'))
        ->actingAs($user)
        ->post(route('vacations.store'), [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => 'Rest',
        ])
        ->assertRedirect(route('vacations.index'));
});
