<?php

namespace Database\Factories;

use App\Models\Auction;
use App\Models\FantasyLeague;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Auction>
 */
class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition(): array
    {
        return [
            'fantasy_league_id' => FantasyLeague::factory(),
            'week_id' => Week::factory(),
            'status' => 'scheduled',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'start_at' => now()->subDays(2),
            'end_at' => now()->subDay(),
        ]);
    }
}
