<?php

namespace Database\Factories;

use App\Models\GameMatch;
use App\Models\Player;
use App\Models\PlayerStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlayerStat>
 */
class PlayerStatFactory extends Factory
{
    protected $model = PlayerStat::class;

    public function definition(): array
    {
        $kills = fake()->numberBetween(0, 12);
        $deaths = fake()->numberBetween(0, 8);
        $assists = fake()->numberBetween(0, 18);

        return [
            'match_id' => GameMatch::factory(),
            'player_id' => Player::factory(),
            'kills' => $kills,
            'deaths' => $deaths,
            'assists' => $assists,
            'fantasy_points' => ($kills * 3) + ($assists * 2) - $deaths,
        ];
    }
}
