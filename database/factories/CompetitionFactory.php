<?php

namespace Database\Factories;

use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Competition>
 */
class CompetitionFactory extends Factory
{
    protected $model = Competition::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['LEC Spring Split', 'LCS Championship', 'LCK Spring']),
            'region' => fake()->randomElement(['EMEA', 'NA', 'KR']),
            'season' => (string) fake()->numberBetween(2025, 2027),
        ];
    }
}
