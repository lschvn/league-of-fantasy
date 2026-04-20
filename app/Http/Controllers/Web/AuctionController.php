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

        $membership = $this->findMembershipByLeague(
            data_get($user, 'memberships', []),
            (int) data_get($auctionData, 'fantasy_league_id')
        );

        return view('pages.auctions.show', [
            'auction' => $auctionData,
            'bids' => $bids,
            'isOwner' => in_array((string) data_get($membership, 'role'), ['owner', 'manager'], true),
            'membership' => $membership,
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
