<?php

use App\Models\User;
use Laravel\Jetstream\Features;

test('teams can not be created', function () {
    $this->actingAs($user = User::factory()->withPersonalTeam()->create());

    $response = $this->post('/teams', [
        'name' => 'Test Team',
    ]);

    $response->assertStatus(403);
    expect($user->fresh()->ownedTeams)->toHaveCount(1);
})->skip(function () {
    return ! Features::hasTeamFeatures();
}, 'Team features are not enabled.');
