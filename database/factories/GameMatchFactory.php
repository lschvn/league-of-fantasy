<?php

namespace Database\Factories;

use App\Models\GameMatch;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameMatch>
 */
class GameMatchFactory extends Factory
{
    protected $model = GameMatch::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-2 weeks', '+2 weeks');

        return [
            'week_id' => Week::factory(),
            'status' => fake()->randomElement(['scheduled', 'completed']),
            'started_at' => $startedAt,
            'ended_at' => (clone $startedAt)->modify('+2 hours'),
        ];
    }
}
