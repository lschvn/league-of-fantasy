<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\FantasyTeam;
use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bid>
 */
class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'fantasy_team_id' => FantasyTeam::factory(),
            'player_id' => Player::factory(),
            'amount' => fake()->randomFloat(2, 1, 30),
            'status' => 'pending',
            'placed_at' => now(),
        ];
    }
}
