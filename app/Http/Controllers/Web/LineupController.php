<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class LineupController extends Controller
{
    private const POSITIONS = ['TOP', 'JGL', 'MID', 'ADC', 'SUP', 'FLEX_1', 'FLEX_2'];

    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function edit(string $team, string $week): View
    {
        $teamId = (int) $team;
        $weekId = (int) $week;

        try {
            $teamData = $this->apiData($this->apiClient->fantasyTeam($teamId));
            $roster = collect($this->apiData($this->apiClient->fantasyTeamRoster($teamId)))
                ->filter(fn (array $slot) => data_get($slot, 'status') === 'active')
                ->values()
                ->all();
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

        $weekData = collect($weeks)->first(fn (array $item) => (int) data_get($item, 'id') === $weekId);

        if (! is_array($membership) || ! is_array($league) || ! is_array($weekData)) {
            abort(404);
        }

        $lineup = null;

        try {
            $lineup = $this->apiData($this->apiClient->fantasyTeamLineup($teamId, $weekId));
        } catch (ApiException $exception) {
            if ($exception->status !== 404) {
                $this->handleApiException($exception);
            }
        }

        $selectedSlots = collect(data_get($lineup, 'slots', []))
            ->mapWithKeys(fn (array $slot) => [
                data_get($slot, 'position') => (int) data_get($slot, 'roster_slot_id'),
            ])
            ->all();

        $captain = collect(data_get($lineup, 'slots', []))
            ->first(fn (array $slot) => (bool) data_get($slot, 'is_captain') === true);

        $isLocked = filled(data_get($lineup, 'locked_at'))
            || (filled(data_get($weekData, 'lineup_lock_at'))
                && Carbon::parse(data_get($weekData, 'lineup_lock_at'))->isPast());

        return view('pages.lineups.edit', [
            'captainPosition' => data_get($captain, 'position'),
            'isLocked' => $isLocked,
            'league' => $league,
            'lineup' => $lineup,
            'positions' => self::POSITIONS,
            'roster' => $roster,
            'selectedSlots' => $selectedSlots,
            'team' => $teamData,
            'week' => $weekData,
        ]);
    }

    public function store(Request $request, string $team): RedirectResponse
    {
        $teamId = (int) $team;
        $weekId = (int) $request->input('week_id');

        $slots = collect(self::POSITIONS)
            ->map(fn (string $position) => [
                'roster_slot_id' => (int) $request->input("slots.{$position}.roster_slot_id"),
                'position' => $position,
                'is_captain' => $request->input('captain') === $position,
            ])
            ->all();

        try {
            $this->apiClient->submitLineup($teamId, [
                'week_id' => $weekId,
                'slots' => $slots,
            ]);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return $this->redirectBackWithApiErrors($request, $exception);
            }

            $this->handleApiException($exception);
        }

        return redirect()
            ->route('lineups.edit', ['team' => $teamId, 'week' => $weekId])
            ->with('success', 'Lineup submitted successfully.');
    }
}
