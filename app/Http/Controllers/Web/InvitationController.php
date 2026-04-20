<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function store(Request $request, string $fantasyLeague): RedirectResponse
    {
        $leagueId = (int) $fantasyLeague;

        try {
            $response = $this->apiClient->createInvitation($leagueId, [
                'expires_at' => $this->apiDateTime($request->input('expires_at')),
                'max_uses' => $request->filled('max_uses') ? (int) $request->input('max_uses') : null,
            ]);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return $this->redirectBackWithApiErrors($request, $exception);
            }

            $this->handleApiException($exception);
        }

        $invitation = data_get($response, 'data');

        if (is_array($invitation)) {
            $this->rememberKnownInvitation($invitation);
        }

        return redirect()
            ->route('leagues.show', ['fantasyLeague' => $leagueId, 'tab' => 'overview'])
            ->with('success', 'Invitation created successfully.');
    }

    public function destroy(Request $request, string $invitation): RedirectResponse
    {
        $leagueId = (int) $request->input('fantasy_league_id');
        $invitationId = (int) $invitation;

        try {
            $this->apiClient->revokeInvitation($invitationId);
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        if ($leagueId > 0) {
            $this->forgetKnownInvitation($leagueId, $invitationId);

            return redirect()
                ->route('leagues.show', ['fantasyLeague' => $leagueId, 'tab' => 'overview'])
                ->with('success', 'Invitation revoked successfully.');
        }

        return redirect()->back()->with('success', 'Invitation revoked successfully.');
    }
}
