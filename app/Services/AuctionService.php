<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\FantasyTeam;
use App\Models\Player;
use App\Models\RosterSlot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AuctionService
{
    public function placeBid(Auction $auction, FantasyTeam $team, Player $player, float $amount): Bid
    {
        if (! $auction->isOpen()) {
            throw new RuntimeException('Auction is not open.');
        }

        // team must belong to this auction's league
        if ($team->membership->fantasy_league_id != $auction->fantasy_league_id) {
            throw new RuntimeException('Fantasy team does not belong to this auction league.');
        }

        // player must be from the same competition
        if ($player->team->competition_id != $auction->fantasyLeague->competition_id) {
            throw new RuntimeException('Player does not belong to the league competition.');
        }

        // max 7 players on a roster
        if ($team->activeRosterSlots()->count() >= 7) {
            throw new RuntimeException('Fantasy team already has 7 active players.');
        }

        // budget left minus pending bids
        $pending = $team->bids()
            ->where('auction_id', $auction->id)
            ->where('status', 'pending')
            ->sum('amount');

        if ($amount > (float) $team->budget_remaining - (float) $pending) {
            throw new RuntimeException('Insufficient available auction budget.');
        }

        return Bid::create([
            'auction_id' => $auction->id,
            'fantasy_team_id' => $team->id,
            'player_id' => $player->id,
            'amount' => $amount,
            'status' => 'pending',
            'placed_at' => Carbon::now(),
        ])->load('player');
    }

    public function close(Auction $auction): Auction
    {
        if ($auction->status === 'closed') {
            return $auction->load('bids');
        }

        return DB::transaction(function () use ($auction): Auction {
            $auction->load(['bids.fantasyTeam.membership', 'bids.player.team', 'fantasyLeague']);

            // resolve each player's bids
            $auction->bids
                ->where('status', 'pending')
                ->groupBy('player_id')
                ->each(function (Collection $playerBids) {
                    // highest amount wins, earliest bid breaks ties
                    $sorted = $playerBids->sort(function (Bid $a, Bid $b) {
                        if ($a->amount != $b->amount) {
                            return (float) $b->amount <=> (float) $a->amount;
                        }

                        return $a->placed_at <=> $b->placed_at;
                    })->values();

                    $winner = $sorted->first();

                    if (! $winner) {
                        return;
                    }

                    $team = $winner->fantasyTeam;

                    // winner can only get the player if they have room and budget
                    $alreadyHas = RosterSlot::query()
                        ->where('fantasy_team_id', $team->id)
                        ->where('player_id', $winner->player_id)
                        ->where('status', 'active')
                        ->exists();

                    $hasRoom = $team->activeRosterSlots()->count() < 7;
                    $canAfford = (float) $team->budget_remaining >= (float) $winner->amount;

                    if ($alreadyHas || ! $hasRoom || ! $canAfford) {
                        $winner->update(['status' => 'lost']);
                    } else {
                        $winner->update(['status' => 'won']);

                        $team->activeRosterSlots()->create([
                            'player_id' => $winner->player_id,
                            'acquisition_cost' => $winner->amount,
                            'acquired_at' => Carbon::now(),
                            'status' => 'active',
                        ]);

                        $team->update([
                            'budget_remaining' => $team->budget_remaining - $winner->amount,
                        ]);
                    }

                    // everyone else loses
                    $sorted->skip(1)->each(fn (Bid $bid) => $bid->update(['status' => 'lost']));
                });

            $auction->update([
                'status' => 'closed',
                'end_at' => $auction->end_at ?: Carbon::now(),
            ]);

            return $auction->fresh(['bids.player']);
        });
    }
}
