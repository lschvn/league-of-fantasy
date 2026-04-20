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
use Illuminate\Support\Carbon;

class FantasyTeamController extends Controller
{
    public function show(Request $request, FantasyTeam $team): JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return $this->forbiddenResponse();
        }

        $team->load(['membership', 'activeRosterSlots.player']);

        return $this->successResponse(
            'fantasy team fetched successfully.',
            new FantasyTeamResource($team)
        );
    }

    public function roster(Request $request, FantasyTeam $team): JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return $this->forbiddenResponse();
        }

        $roster = $team->rosterSlots()->with('player')->latest('acquired_at')->get();

        return $this->successResponse(
            'fantasy team roster fetched successfully.',
            RosterSlotResource::collection($roster)
        );
    }

    public function release(Request $request, FantasyTeam $team, RosterSlot $rosterSlot): JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return $this->forbiddenResponse();
        }

        if ($rosterSlot->fantasy_team_id !== $team->id) {
            return $this->unprocessableResponse('roster slot does not belong to the fantasy team.');
        }

        if ($rosterSlot->status !== 'active') {
            return $this->unprocessableResponse('roster slot is not active.');
        }

        // prevent releases when the player is already locked into a lineup
        $inLockedLineup = $rosterSlot->lineupSlots()
            ->whereHas('lineup', fn ($q) => $q->whereNotNull('locked_at'))
            ->exists();

        if ($inLockedLineup) {
            return $this->unprocessableResponse('cannot release a player already used in a locked lineup.');
        }

        $rosterSlot->update([
            'status' => 'released',
            'released_at' => Carbon::now(),
        ]);

        return $this->successResponse(
            'player released from roster successfully.',
            new RosterSlotResource($rosterSlot->load('player'))
        );
    }

    public function showLineup(Request $request, FantasyTeam $team, Week $week): JsonResponse
    {
        if (! $this->isOwner($request, $team)) {
            return $this->forbiddenResponse();
        }

        $lineup = $team->lineups()
            ->where('week_id', $week->id)
            ->with('slots.rosterSlot.player')
            ->first();

        if (! $lineup) {
            return $this->notFoundResponse('lineup not found.');
        }

        return $this->successResponse(
            'lineup fetched successfully.',
            new LineupResource($lineup)
        );
    }

    private function isOwner(Request $request, FantasyTeam $team): bool
    {
        return $team->membership->user_id === $request->user()->id;
    }
}
