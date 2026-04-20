<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\View\View;

class CompetitionController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function landing(): View
    {
        try {
            $competitions = array_slice($this->apiData($this->apiClient->competitions()), 0, 6);
            $leagues = array_slice($this->apiData($this->apiClient->fantasyLeagues()), 0, 6);
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        return view('pages.landing', [
            'competitions' => $competitions,
            'leagues' => $leagues,
        ]);
    }

    public function index(): View
    {
        try {
            $competitions = $this->apiData($this->apiClient->competitions());
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        return view('pages.competitions.index', [
            'competitions' => $competitions,
        ]);
    }

    public function show(string $competition): View
    {
        try {
            $competitionData = $this->apiData($this->apiClient->competition((int) $competition));
            $weeks = $this->apiData($this->apiClient->competitionWeeks((int) $competition));
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        return view('pages.competitions.show', [
            'competition' => $competitionData,
            'weeks' => $weeks,
        ]);
    }
}
