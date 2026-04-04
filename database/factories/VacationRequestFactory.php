<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VacationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VacationRequest>
 */
class VacationRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDays(fake()->numberBetween(1, 30));
        $end = (clone $start)->addDays(fake()->numberBetween(1, 5));

        return [
            'user_id' => User::factory(),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'total_days' => 3, // Simplification for factory
            'status' => 'pending',
            'reason' => fake()->sentence(),
            'substitute_id' => User::factory(),
        ];
    }
}
