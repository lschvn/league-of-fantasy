<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\FantasyLeague;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FantasyLeague>
 */
class FantasyLeagueFactory extends Factory
{
    protected $model = FantasyLeague::class;

    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'creator_user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'visibility' => 'public',
            'status' => 'open',
            'max_participants' => 8,
            'budget_cap' => 100,
            'join_deadline' => now()->addWeek(),
            'scoring_rule_version' => 'v1',
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'public',
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'private',
        ]);
    }
}
