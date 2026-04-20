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
    ) {}

    public function store(PlaceBidRequest $request, Auction $auction): JsonResponse
    {
        $team = FantasyTeam::with('membership')->findOrFail($request->integer('fantasy_team_id'));

        // only the team owner can place a bid
        if ($team->membership->user_id !== $request->user()->id) {
            return $this->forbiddenResponse();
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
            return $this->unprocessableResponse($e->getMessage());
        }

        return $this->successResponse(
            'bid placed successfully.',
            new BidResource($bid->load('player')),
            201
        );
    }
}
