<?php

namespace Database\Factories;

use App\Models\FantasyLeague;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'fantasy_league_id' => FantasyLeague::factory(),
            'code' => Str::upper(fake()->unique()->bothify('??##??##')),
            'expires_at' => now()->addWeek(),
            'max_uses' => 1,
            'used_count' => 0,
            'revoked_at' => null,
        ];
    }

    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addWeek(),
            'revoked_at' => null,
        ]);
    }
}
