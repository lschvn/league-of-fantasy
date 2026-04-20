<?php

namespace App\Http\Controllers;

use App\Http\Resources\FantasyTeamResource;
use App\Http\Resources\LineupResource;
use App\Http\Resources\RosterSlotResource;
use App\Models\FantasyTeam;
use App\Models\RosterSlot;
use App\Models\Week;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class FantasyTeamController extends Controller
{
    public function show(Request $request, FantasyTeam $team): FantasyTeamResource|JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $team->load(['membership', 'activeRosterSlots.player']);

        return new FantasyTeamResource($team);
    }

    public function roster(Request $request, FantasyTeam $team): AnonymousResourceCollection|JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $roster = $team->rosterSlots()->with('player')->latest('acquired_at')->get();

        return RosterSlotResource::collection($roster);
    }

    public function release(Request $request, FantasyTeam $team, RosterSlot $rosterSlot): JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if ($rosterSlot->fantasy_team_id !== $team->id) {
            return response()->json(['message' => 'Roster slot does not belong to fantasy team.'], 422);
        }

        if ($rosterSlot->status !== 'active') {
            return response()->json(['message' => 'Roster slot is not active.'], 422);
        }

        // can't release a player used in a locked lineup
        $inLockedLineup = $rosterSlot->lineupSlots()
            ->whereHas('lineup', fn ($q) => $q->whereNotNull('locked_at'))
            ->exists();

        if ($inLockedLineup) {
            return response()->json(['message' => 'Cannot release a player already used in a locked lineup.'], 422);
        }

        $rosterSlot->update([
            'status' => 'released',
            'released_at' => Carbon::now(),
        ]);

        return response()->json(['message' => 'Player released from roster.']);
    }

    public function showLineup(Request $request, FantasyTeam $team, Week $week): LineupResource|JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $lineup = $team->lineups()
            ->where('week_id', $week->id)
            ->with('slots.rosterSlot.player')
            ->first();

        if (! $lineup) {
            return response()->json(['message' => 'Lineup not found.'], 404);
        }

        return new LineupResource($lineup);
    }

    private function isOwner(Request $request, FantasyTeam $team): bool
    {
        return $team->membership->user_id === $request->user()->id;
    }
}
