<?php

namespace App\Services;

use App\Models\FantasyTeam;
use App\Models\Lineup;
use App\Models\Week;
use App\Support\FantasyLineup;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LineupService
{
    public function submit(FantasyTeam $team, Week $week, array $slots): Lineup
    {
        // can't change lineup after the lock time
        if ($week->lineup_lock_at?->isPast()) {
            throw new RuntimeException('Lineup lock has already passed.');
        }

        // must have exactly the required positions
        $positions = array_column($slots, 'position');
        sort($positions);

        $required = FantasyLineup::REQUIRED_POSITIONS;
        sort($required);

        if ($positions !== $required) {
            throw new RuntimeException('Lineup must contain exactly the required positions.');
        }

        // exactly one captain
        $captains = array_filter($slots, fn (array $slot) => $slot['is_captain'] === true);

        if (count($captains) !== 1) {
            throw new RuntimeException('Lineup must contain exactly one captain.');
        }

        // no duplicate roster slots
        $rosterSlotIds = array_column($slots, 'roster_slot_id');

        if (count(array_unique($rosterSlotIds)) !== count($rosterSlotIds)) {
            throw new RuntimeException('Each roster slot can only be used once in the lineup.');
        }

        // load and validate roster slots belong to this team
        $activeSlots = $team->activeRosterSlots()
            ->with('player')
            ->whereIn('id', $rosterSlotIds)
            ->get()
            ->keyBy('id');

        if ($activeSlots->count() !== count($rosterSlotIds)) {
            throw new RuntimeException('One or more roster slots are invalid for this fantasy team.');
        }

        // check that player roles match their lineup positions
        foreach ($slots as $slot) {
            if (FantasyLineup::isFlex($slot['position'])) {
                continue;
            }

            $role = FantasyLineup::ROLE_MAP[$slot['position']] ?? null;
            $player = $activeSlots->get($slot['roster_slot_id'])?->player;

            if (! $role || $player?->role !== $role) {
                throw new RuntimeException("Player role does not match lineup position {$slot['position']}.");
            }
        }

        return DB::transaction(function () use ($team, $week, $slots): Lineup {
            $lineup = Lineup::firstOrCreate(
                ['fantasy_team_id' => $team->id, 'week_id' => $week->id],
                ['status' => 'draft']
            );

            if ($lineup->locked_at) {
                throw new RuntimeException('Lineup is already locked.');
            }

            // replace existing slots with the new ones
            $lineup->slots()->delete();

            foreach ($slots as $slot) {
                $lineup->slots()->create([
                    'roster_slot_id' => $slot['roster_slot_id'],
                    'position' => $slot['position'],
                    'is_captain' => (bool) $slot['is_captain'],
                ]);
            }

            $lineup->update([
                'status' => 'submitted',
                'submitted_at' => Carbon::now(),
            ]);

            return $lineup->fresh(['slots.rosterSlot.player']);
        });
    }

    // lock all unlocked lineups for a given week
    public function lockLineups(Week $week): void
    {
        $week->lineups()
            ->whereNull('locked_at')
            ->update([
                'status' => 'locked',
                'locked_at' => Carbon::now(),
            ]);
    }
}
