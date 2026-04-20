<?php

namespace Database\Factories;

use App\Models\FantasyTeam;
use App\Models\Lineup;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lineup>
 */
class LineupFactory extends Factory
{
    protected $model = Lineup::class;

    public function definition(): array
    {
        return [
            'fantasy_team_id' => FantasyTeam::factory(),
            'week_id' => Week::factory(),
            'status' => 'submitted',
            'submitted_at' => now(),
            'locked_at' => null,
        ];
    }
}
