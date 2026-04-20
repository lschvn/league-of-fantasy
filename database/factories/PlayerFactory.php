<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'nickname' => fake()->unique()->userName(),
            'role' => fake()->randomElement(['TOP', 'JGL', 'MID', 'ADC', 'SUP']),
            'status' => 'active',
        ];
    }
}
