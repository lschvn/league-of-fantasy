<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FantasyTeamController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function show(string $team): View
    {
        $teamId = (int) $team;

        try {
            $teamData = $this->apiData($this->apiClient->fantasyTeam($teamId));
            $roster = $this->apiData($this->apiClient->fantasyTeamRoster($teamId));
            $user = $this->fetchCurrentUser($this->apiClient);
            $membership = $this->findMembershipByTeam(
                data_get($user, 'memberships', []),
                $teamId,
                (int) data_get($teamData, 'membership_id')
            );
            $league = $membership
                ? $this->apiData($this->apiClient->fantasyLeague((int) data_get($membership, 'fantasy_league_id')))
                : null;
            $weeks = $league
                ? $this->apiData($this->apiClient->competitionWeeks((int) data_get($league, 'competition_id')))
                : [];
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        if (! is_array($membership) || ! is_array($league)) {
            abort(404);
        }

        $lockedRosterSlotIds = collect($weeks)
            ->flatMap(function (array $week) use ($teamId) {
                try {
                    $lineup = $this->apiData($this->apiClient->fantasyTeamLineup($teamId, (int) data_get($week, 'id')));
                } catch (ApiException $exception) {
                    if ($exception->status === 404) {
                        return [];
                    }

                    $this->handleApiException($exception);
                }

                if (! filled(data_get($lineup, 'locked_at'))) {
                    return [];
                }

                return collect(data_get($lineup, 'slots', []))
                    ->pluck('roster_slot_id')
                    ->all();
            })
            ->map(fn (mixed $slotId) => (int) $slotId)
            ->unique()
            ->values()
            ->all();

        $upcomingWeeks = collect($weeks)
            ->filter(fn (array $week) => ! filled(data_get($week, 'end_at')) || Carbon::parse(data_get($week, 'end_at'))->isFuture())
            ->values()
            ->all();

        if ($upcomingWeeks === []) {
            $upcomingWeeks = $weeks;
        }

        $tab = request()->string('tab')->toString();
        $activeTab = in_array($tab, ['roster', 'lineups'], true) ? $tab : 'roster';

        return view('pages.teams.show', [
            'activeTab' => $activeTab,
            'league' => $league,
            'lockedRosterSlotIds' => $lockedRosterSlotIds,
            'roster' => $roster,
            'team' => $teamData,
            'weeks' => $upcomingWeeks,
        ]);
    }
}
