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
    public function index(): AnonymousResourceCollection
    {
        $competitions = Competition::query()
            ->withCount(['teams', 'weeks'])
            ->latest()
            ->get();

        return CompetitionResource::collection($competitions);
    }

    public function show(Competition $competition): CompetitionResource
    {
        $competition->load(['teams.players', 'weeks']);

        return new CompetitionResource($competition);
    }

    public function weeks(Competition $competition): AnonymousResourceCollection
    {
        $weeks = $competition->weeks()
            ->withCount('matches')
            ->orderBy('number')
            ->get();

        return WeekResource::collection($weeks);
    }

    public function matches(Week $week): AnonymousResourceCollection
    {
        $matches = $week->matches()
            ->with('teams')
            ->get();

        return GameMatchResource::collection($matches);
    }

    public function showMatch(GameMatch $match): GameMatchResource
    {
        $match->load('teams');

        return new GameMatchResource($match);
    }

    public function playerStats(GameMatch $match): AnonymousResourceCollection
    {
        $stats = $match->playerStats()
            ->with('player')
            ->get();

        return PlayerStatResource::collection($stats);
    }
}
