<?php

namespace App\Http\Controllers\Web;

use App\Services\ApiClient;
use App\Services\ApiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function store(Request $request, string $auction): RedirectResponse
    {
        $auctionId = (int) $auction;

        try {
            $this->apiClient->placeBid($auctionId, [
                'fantasy_team_id' => (int) $request->input('fantasy_team_id'),
                'player_id' => (int) $request->input('player_id'),
                'amount' => (float) $request->input('amount'),
            ]);
        } catch (ApiException $exception) {
            if ($exception->status === 422) {
                return $this->redirectBackWithApiErrors($request, $exception);
            }

            $this->handleApiException($exception);
        }

        return redirect()
            ->route('auctions.show', ['auction' => $auctionId])
            ->with('success', 'Bid placed successfully.');
    }
}
