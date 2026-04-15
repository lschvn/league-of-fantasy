<?php

namespace App\Services;

use App\Models\FantasyLeague;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LeagueMembershipService
{
    // add a regular member to a league with their fantasy team
    public function join(FantasyLeague $league, User $user, ?string $teamName = null): Membership
    {
        if ($league->memberships()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException('User is already a member of this fantasy league.');
        }

        if ($league->join_deadline?->isPast()) {
            throw new RuntimeException('Join deadline has passed.');
        }

        if ($league->status !== 'open') {
            throw new RuntimeException('Fantasy league is not open for joining.');
        }

        if ($league->memberships()->count() >= $league->max_participants) {
            throw new RuntimeException('Fantasy league is full.');
        }

        return DB::transaction(function () use ($league, $user, $teamName): Membership {
            $membership = Membership::create([
                'fantasy_league_id' => $league->id,
                'user_id' => $user->id,
                'role' => 'member',
                'status' => 'active',
                'joined_at' => Carbon::now(),
            ]);

            $membership->fantasyTeam()->create([
                'name' => $teamName ?: $user->name."'s Team",
                'budget_remaining' => $league->budget_cap,
            ]);

            return $membership->load(['user', 'fantasyTeam']);
        });
    }

    // create the league owner's membership (called on league creation)
    public function addOwner(FantasyLeague $league, User $user, ?string $teamName = null): Membership
    {
        return DB::transaction(function () use ($league, $user, $teamName): Membership {
            $membership = Membership::create([
                'fantasy_league_id' => $league->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => Carbon::now(),
            ]);

            $membership->fantasyTeam()->create([
                'name' => $teamName ?: $user->name."'s Team",
                'budget_remaining' => $league->budget_cap,
            ]);

            return $membership->load(['user', 'fantasyTeam']);
        });
    }
}
