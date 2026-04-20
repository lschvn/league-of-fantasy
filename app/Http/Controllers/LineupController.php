<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lineup\SubmitLineupRequest;
use App\Http\Resources\LineupResource;
use App\Models\FantasyTeam;
use App\Models\Week;
use App\Services\LineupService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class LineupController extends Controller
{
    public function __construct(
        private readonly LineupService $lineupService
    ) {}

    public function submit(SubmitLineupRequest $request, FantasyTeam $team): JsonResponse
    {
        $team->load('membership');

        // only the team owner can submit lineups
        if ($team->membership->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $week = Week::findOrFail($request->integer('week_id'));

        // week must be from the same competition as the team's league
        if ($team->membership->fantasyLeague->competition_id !== $week->competition_id) {
            return response()->json(['message' => 'Week does not match the fantasy team competition.'], 422);
        }

        try {
            $lineup = $this->lineupService->submit($team, $week, $request->input('slots'));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Lineup submitted successfully.',
            'data' => new LineupResource($lineup),
        ]);
    }
}
