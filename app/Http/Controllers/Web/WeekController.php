<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\View\View;

class WeekController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function show(string $week): View
    {
        $weekId = (int) $week;

        try {
            $matches = $this->apiData($this->apiClient->weekMatches($weekId));
            $competitionId = (int) data_get($matches, '0.teams.0.competition_id');
            $competition = $this->resolveCompetitionForWeek(
                $this->apiClient,
                $weekId,
                $competitionId ?: null
            );
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        $weekData = is_array($competition)
            ? $this->resolveWeekFromCompetition($competition, $weekId)
            : null;

        if (! $competition || ! $weekData) {
            abort(404);
        }

        return view('pages.weeks.show', [
            'competition' => $competition,
            'matches' => $matches,
            'week' => $weekData,
        ]);
    }
}
