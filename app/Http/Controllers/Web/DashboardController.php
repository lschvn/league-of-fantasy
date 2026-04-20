<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function index(): View
    {
        try {
            $user = $this->fetchCurrentUser($this->apiClient);
            $memberships = collect(data_get($user, 'memberships', []))
                ->map(function (array $membership): array {
                    $league = $this->apiData($this->apiClient->fantasyLeague((int) data_get($membership, 'fantasy_league_id')));
                    $membership['fantasy_league'] = $league;

                    return $membership;
                })
                ->values()
                ->all();
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        $teams = collect($memberships)
            ->filter(fn (array $membership) => is_array(data_get($membership, 'fantasy_team')))
            ->map(function (array $membership): array {
                return [
                    ...data_get($membership, 'fantasy_team', []),
                    'fantasy_league' => data_get($membership, 'fantasy_league'),
                    'membership' => $membership,
                ];
            })
            ->values()
            ->all();

        return view('pages.dashboard.index', [
            'memberships' => $memberships,
            'teams' => $teams,
            'user' => $user,
        ]);
    }
}
