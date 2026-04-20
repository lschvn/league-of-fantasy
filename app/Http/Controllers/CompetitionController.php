<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompetitionResource;
use App\Http\Resources\GameMatchResource;
use App\Http\Resources\PlayerStatResource;
use App\Http\Resources\WeekResource;
use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Week;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompetitionController extends Controller
{
    /**
     * List available real-world competitions.
     *
     * @unauthenticated
     */
    public function index(): AnonymousResourceCollection
    {
        $competitions = Competition::query()
            ->withCount(['teams', 'weeks'])
            ->latest()
            ->get();

        return CompetitionResource::collection($competitions);
    }

    /**
     * Show one competition with teams and scheduled weeks.
     *
     * @unauthenticated
     */
    public function show(Competition $competition): CompetitionResource
    {
        $competition->load(['teams.players', 'weeks']);

        return new CompetitionResource($competition);
    }

    /**
     * List the weeks for a competition.
     *
     * @unauthenticated
     */
    public function weeks(Competition $competition): AnonymousResourceCollection
    {
        $weeks = $competition->weeks()
            ->withCount('matches')
            ->orderBy('number')
            ->get();

        return WeekResource::collection($weeks);
    }

    /**
     * List the matches scheduled for a competition week.
     *
     * @unauthenticated
     */
    public function matches(Week $week): AnonymousResourceCollection
    {
        $matches = $week->matches()
            ->with('teams')
            ->get();

        return GameMatchResource::collection($matches);
    }

    /**
     * Show one match and its teams.
     *
     * @unauthenticated
     */
    public function showMatch(GameMatch $match): GameMatchResource
    {
        $match->load('teams');

        return new GameMatchResource($match);
    }

    /**
     * List player statistics recorded for a match.
     *
     * @unauthenticated
     */
    public function playerStats(GameMatch $match): AnonymousResourceCollection
    {
        $stats = $match->playerStats()
            ->with('player')
            ->get();

        return PlayerStatResource::collection($stats);
    }
}
