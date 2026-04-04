<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;

uses(RefreshDatabase::class);

test('new users are added to the staff team upon registration', function () {
    // Seed the Staff team first
    $admin = User::factory()->create();
    $staffTeam = Team::forceCreate([
        'user_id' => $admin->id,
        'name' => 'Staff',
        'personal_team' => false,
    ]);

    $response = $this->post('/register', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
    ]);

    $this->assertAuthenticated();
    $user = auth()->user();

    // Verify user is a member of the Staff team
    expect($user->belongsToTeam($staffTeam))->toBeTrue();
    expect($user->currentTeam->id)->toBe($staffTeam->id);
})->skip(function () {
    return ! Features::enabled(Features::registration());
}, 'Registration support is not enabled.');
