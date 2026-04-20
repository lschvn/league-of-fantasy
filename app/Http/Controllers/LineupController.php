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

        // only the team owner can submit a lineup
        if ($team->membership->user_id !== $request->user()->id) {
            return $this->forbiddenResponse();
        }

        $week = Week::findOrFail($request->integer('week_id'));

        // the submitted week must belong to the same competition as the league
        if ($team->membership->fantasyLeague->competition_id !== $week->competition_id) {
            return $this->unprocessableResponse('week does not match the fantasy team competition.');
        }

        try {
            $lineup = $this->lineupService->submit($team, $week, $request->input('slots'));
        } catch (RuntimeException $e) {
            return $this->unprocessableResponse($e->getMessage());
        }

        return $this->successResponse(
            'lineup submitted successfully.',
            new LineupResource($lineup->load('slots.rosterSlot.player'))
        );
    }
}
