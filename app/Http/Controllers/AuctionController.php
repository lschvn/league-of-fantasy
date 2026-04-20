<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuctionResource;
use App\Http\Resources\BidResource;
use App\Models\Auction;
use App\Services\AuctionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctionService
    ) {}

    public function show(Request $request, Auction $auction): JsonResponse
    {
        if (! $auction->fantasyLeague->memberships()->where('user_id', $request->user()->id)->exists()) {
            return $this->forbiddenResponse();
        }

        return $this->successResponse(
            'auction fetched successfully.',
            new AuctionResource($auction->load('week'))
        );
    }

    public function bids(Request $request, Auction $auction): JsonResponse
    {
        $membership = $auction->fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership) {
            return $this->forbiddenResponse();
        }

        $bids = $auction->bids()
            ->where('fantasy_team_id', $membership->fantasyTeam->id)
            ->with('player')
            ->latest('placed_at')
            ->get();

        return $this->successResponse(
            'auction bids fetched successfully.',
            BidResource::collection($bids)
        );
    }

    public function close(Request $request, Auction $auction): JsonResponse
    {
        $membership = $auction->fantasyLeague->memberships()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $membership || ! $membership->isManager()) {
            return $this->forbiddenResponse();
        }

        $auction = $this->auctionService->close($auction);

        return $this->successResponse('auction closed successfully.', [
            'auction' => new AuctionResource($auction->load('week')),
            'bids' => BidResource::collection($auction->bids()->with('player')->get()),
        ]);
    }
}
