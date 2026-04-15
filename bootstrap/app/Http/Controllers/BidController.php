<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auction\PlaceBidRequest;
use App\Http\Resources\BidResource;
use App\Models\Auction;
use App\Models\FantasyTeam;
use App\Models\Player;
use App\Services\AuctionService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class BidController extends Controller
{
    public function __construct(
        private readonly AuctionService $auctionService
    ) {
    }

    public function store(PlaceBidRequest $request, Auction $auction): JsonResponse
    {
        $team = FantasyTeam::with('membership')->findOrFail($request->integer('fantasy_team_id'));

        // only the team owner can bid
        if ($team->membership->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $player = Player::with('team')->findOrFail($request->integer('player_id'));

        try {
            $bid = $this->auctionService->placeBid(
                $auction,
                $team,
                $player,
                (float) $request->input('amount')
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Bid placed successfully.',
            'data' => new BidResource($bid),
        ], 201);
    }
}
