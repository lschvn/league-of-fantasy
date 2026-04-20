<?php

namespace Database\Factories;

use App\Models\Lineup;
use App\Models\LineupSlot;
use App\Models\RosterSlot;
use App\Support\FantasyLineup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LineupSlot>
 */
class LineupSlotFactory extends Factory
{
    protected $model = LineupSlot::class;

    public function definition(): array
    {
        return [
            'lineup_id' => Lineup::factory(),
            'roster_slot_id' => RosterSlot::factory(),
            'position' => fake()->randomElement(FantasyLineup::REQUIRED_POSITIONS),
            'is_captain' => false,
        ];
    }
}
