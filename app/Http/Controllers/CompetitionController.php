<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompetitionResource;
use App\Http\Resources\GameMatchResource;
use App\Http\Resources\PlayerStatResource;
use App\Http\Resources\WeekResource;
use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Week;
use Illuminate\Http\JsonResponse;

class CompetitionController extends Controller
{
    /**
     * list available real-world competitions.
     *
     * @unauthenticated
     */
    public function index(): JsonResponse
    {
        $competitions = Competition::query()
            ->withCount(['teams', 'weeks'])
            ->latest()
            ->get();

        return $this->successResponse(
            'competitions fetched successfully.',
            CompetitionResource::collection($competitions)
        );
    }

    /**
     * show one competition with teams and scheduled weeks.
     *
     * @unauthenticated
     */
    public function show(Competition $competition): JsonResponse
    {
        $competition->load(['teams.players', 'weeks']);

        return $this->successResponse(
            'competition fetched successfully.',
            new CompetitionResource($competition)
        );
    }

    /**
     * list the weeks for a competition.
     *
     * @unauthenticated
     */
    public function weeks(Competition $competition): JsonResponse
    {
        $weeks = $competition->weeks()
            ->withCount('matches')
            ->orderBy('number')
            ->get();

        return $this->successResponse(
            'competition weeks fetched successfully.',
            WeekResource::collection($weeks)
        );
    }

    /**
     * list the matches scheduled for a competition week.
     *
     * @unauthenticated
     */
    public function matches(Week $week): JsonResponse
    {
        $matches = $week->matches()
            ->with('teams')
            ->get();

        return $this->successResponse(
            'week matches fetched successfully.',
            GameMatchResource::collection($matches)
        );
    }

    /**
     * show one match and its teams.
     *
     * @unauthenticated
     */
    public function showMatch(GameMatch $match): JsonResponse
    {
        $match->load('teams');

        return $this->successResponse(
            'match fetched successfully.',
            new GameMatchResource($match)
        );
    }

    /**
     * list player statistics recorded for a match.
     *
     * @unauthenticated
     */
    public function playerStats(GameMatch $match): JsonResponse
    {
        $stats = $match->playerStats()
            ->with('player')
            ->get();

        return $this->successResponse(
            'player stats fetched successfully.',
            PlayerStatResource::collection($stats)
        );
    }
}
