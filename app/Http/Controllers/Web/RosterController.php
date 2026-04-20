<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function destroy(Request $request, string $team, string $rosterSlot): RedirectResponse
    {
        $teamId = (int) $team;

        try {
            $this->apiClient->releaseRosterSlot($teamId, (int) $rosterSlot);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return redirect()
                    ->route('teams.show', ['team' => $teamId, 'tab' => 'roster'])
                    ->with('error', $exception->getMessage());
            }

            $this->handleApiException($exception);
        }

        return redirect()
            ->route('teams.show', ['team' => $teamId, 'tab' => 'roster'])
            ->with('success', 'Roster slot released successfully.');
    }
}
