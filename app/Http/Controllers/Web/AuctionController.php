<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function show(string $auction): View
    {
        $auctionId = (int) $auction;
        $players = [];
        $playersError = null;
        $matches = [];
        $matchesError = null;

        try {
            $auctionData = $this->apiData($this->apiClient->auction($auctionId));
            $bids = $this->apiData($this->apiClient->auctionBids($auctionId));
            $user = $this->fetchCurrentUser($this->apiClient);
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        if (is_array($auctionData)) {
            $this->rememberKnownAuction($auctionData);
        }

        try {
            $players = $this->apiData($this->apiClient->auctionPlayers($auctionId));
            $players = collect(is_array($players) ? $players : [])
                ->filter(fn (mixed $player): bool => is_array($player))
                ->sortBy(fn (array $player): string => sprintf(
                    '%s|%s|%s',
                    (string) (data_get($player, 'team.tag') ?: data_get($player, 'team.name') ?: data_get($player, 'team_id')),
                    (string) data_get($player, 'role', ''),
                    (string) data_get($player, 'nickname', '')
                ))
                ->values()
                ->all();
        } catch (ApiException $exception) {
            if (in_array($exception->status, [404, 422], true)) {
                $playersError = $exception->getMessage();
            } else {
                $this->handleApiException($exception);
            }
        }

        $auctionWeekId = (int) data_get($auctionData, 'week_id');

        if ($auctionWeekId > 0) {
            try {
                $matches = $this->apiData($this->apiClient->weekMatches($auctionWeekId));
                $matches = is_array($matches) ? $matches : [];
            } catch (ApiException $exception) {
                if (in_array($exception->status, [404, 422], true)) {
                    $matchesError = $exception->getMessage();
                    $matches = [];
                } else {
                    $this->handleApiException($exception);
                }
            }
        }

        $membership = $this->findMembershipByLeague(
            data_get($user, 'memberships', []),
            (int) data_get($auctionData, 'fantasy_league_id')
        );

        $teamsById = collect($matches)
            ->flatMap(fn (mixed $match): array => is_array($match) ? data_get($match, 'teams', []) : [])
            ->filter(fn (mixed $team): bool => is_array($team) && filled(data_get($team, 'id')))
            ->mapWithKeys(fn (array $team): array => [
                (string) data_get($team, 'id') => [
                    'name' => data_get($team, 'name'),
                    'tag' => data_get($team, 'tag'),
                ],
            ])
            ->all();

        return view('pages.auctions.show', [
            'auction' => $auctionData,
            'bids' => $bids,
            'isOwner' => in_array((string) data_get($membership, 'role'), ['owner', 'manager'], true),
            'matches' => $matches,
            'matchesError' => $matchesError,
            'membership' => $membership,
            'players' => $players,
            'playersError' => $playersError,
            'teamsById' => $teamsById,
        ]);
    }

    public function close(Request $request, string $auction): RedirectResponse
    {
        $auctionId = (int) $auction;

        try {
            $response = $this->apiClient->closeAuction($auctionId);
        } catch (ApiException $exception) {
            $this->handleApiException($exception);
        }

        $auctionData = data_get($response, 'data.auction');

        if (is_array($auctionData)) {
            $this->rememberKnownAuction($auctionData);
        }

        return redirect()
            ->route('auctions.show', ['auction' => $auctionId])
            ->with('success', 'Auction closed successfully.');
    }
}
