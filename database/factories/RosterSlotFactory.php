<?php

namespace Database\Factories;

use App\Models\FantasyTeam;
use App\Models\Player;
use App\Models\RosterSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RosterSlot>
 */
class RosterSlotFactory extends Factory
{
    protected $model = RosterSlot::class;

    public function definition(): array
    {
        return [
            'fantasy_team_id' => FantasyTeam::factory(),
            'player_id' => Player::factory(),
            'acquisition_cost' => fake()->randomFloat(2, 1, 20),
            'acquired_at' => now()->subDay(),
            'released_at' => null,
            'status' => 'active',
        ];
    }
}
