<?php

namespace Database\Factories;

use App\Models\FantasyLeague;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Membership>
 */
class MembershipFactory extends Factory
{
    protected $model = Membership::class;

    public function definition(): array
    {
        return [
            'fantasy_league_id' => FantasyLeague::factory(),
            'user_id' => User::factory(),
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'owner',
        ]);
    }
}
