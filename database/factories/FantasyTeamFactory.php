<?php

namespace Database\Factories;

use App\Models\FantasyTeam;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FantasyTeam>
 */
class FantasyTeamFactory extends Factory
{
    protected $model = FantasyTeam::class;

    public function definition(): array
    {
        return [
            'membership_id' => Membership::factory(),
            'name' => fake()->words(2, true),
            'budget_remaining' => fake()->randomFloat(2, 10, 100),
        ];
    }
}
