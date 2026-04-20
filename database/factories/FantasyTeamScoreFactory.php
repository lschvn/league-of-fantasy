<?php

namespace Database\Factories;

use App\Models\FantasyTeam;
use App\Models\FantasyTeamScore;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FantasyTeamScore>
 */
class FantasyTeamScoreFactory extends Factory
{
    protected $model = FantasyTeamScore::class;

    public function definition(): array
    {
        return [
            'fantasy_team_id' => FantasyTeam::factory(),
            'week_id' => Week::factory(),
            'points' => fake()->randomFloat(2, 0, 150),
            'rank' => fake()->numberBetween(1, 10),
            'calculated_at' => now(),
        ];
    }
}
