<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionResource;
use App\Http\Resources\BidResource;
use App\Models\Auction;
use App\Services\AuctionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuctionController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctionService
    ) {
    }

    public function show(Request $request, Auction $auction): AuctionResource|JsonResponse
    {
        if (! $auction->fantasyLeague->memberships()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return new AuctionResource($auction);
    }

    public function bids(Request $request, Auction $auction): AnonymousResourceCollection|JsonResponse
    {
        $membership = $auction->fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $bids = $auction->bids()
            ->where('fantasy_team_id', $membership->fantasyTeam->id)
            ->with('player')
            ->latest('placed_at')
            ->get();

        return BidResource::collection($bids);
    }

    public function close(Request $request, Auction $auction): JsonResponse
    {
        $membership = $auction->fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership || ! $membership->isManager()) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $auction = $this->auctionService->close($auction);

        return response()->json([
            'message' => 'Auction closed successfully.',
            'data' => new AuctionResource($auction),
            'bids' => BidResource::collection($auction->bids),
        ]);
    }
}
