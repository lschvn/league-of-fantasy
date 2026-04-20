<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function show(string $match): View
    {
        $matchId = (int) $match;

        try {
            $matchData = $this->apiData($this->apiClient->match($matchId));
            $stats = collect($this->apiData($this->apiClient->matchPlayerStats($matchId)))
                ->sortByDesc(fn (array $stat) => (float) data_get($stat, 'fantasy_points'))
                ->values();
            $competition = $this->resolveCompetitionForWeek(
                $this->apiClient,
                (int) data_get($matchData, 'week_id'),
                (int) data_get($matchData, 'teams.0.competition_id')
            );
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        $week = is_array($competition)
            ? $this->resolveWeekFromCompetition($competition, (int) data_get($matchData, 'week_id'))
            : null;

        if (! $competition || ! $week) {
            abort(404);
        }

        return view('pages.matches.show', [
            'competition' => $competition,
            'match' => $matchData,
            'stats' => $stats instanceof Collection ? $stats->all() : [],
            'week' => $week,
        ]);
    }
}
