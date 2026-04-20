<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Week>
 */
class WeekFactory extends Factory
{
    protected $model = Week::class;

    public function definition(): array
    {
        $startAt = fake()->dateTimeBetween('-1 month', '+1 month');
        $endAt = (clone $startAt)->modify('+6 days');
        $lockAt = (clone $startAt)->modify('+1 day');

        return [
            'competition_id' => Competition::factory(),
            'number' => fake()->numberBetween(1, 18),
            'start_at' => $startAt,
            'end_at' => $endAt,
            'lineup_lock_at' => $lockAt,
        ];
    }
}
