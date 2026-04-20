<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'competition_id' => Competition::factory(),
            'name' => $name,
            'tag' => Str::upper(Str::substr(Str::slug($name, ''), 0, 4)),
            'logo_url' => fake()->imageUrl(256, 256, 'sports'),
        ];
    }
}
