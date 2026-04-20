<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\FantasyLeague;
use App\Models\Invitation;
use App\Models\User;
use App\Services\LeagueMembershipService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FantasyLeagueSeeder extends Seeder
{
    public function __construct(
        private readonly LeagueMembershipService $membershipService
    ) {}

    public function run(): void
    {
        $competition = Competition::query()->firstOrFail();
        $users = collect(config('demo.users'))
            ->mapWithKeys(fn (array $user, string $key) => [$key => User::where('email', $user['email'])->firstOrFail()]);

        DB::transaction(function () use ($competition, $users): void {
            $publicLeague = FantasyLeague::factory()->public()->create([
                'competition_id' => $competition->id,
                'creator_user_id' => $users['owner']->id,
                'name' => 'Review League',
                'max_participants' => 6,
                'budget_cap' => 100,
                'join_deadline' => now()->addDays(10),
                'scoring_rule_version' => 'v1',
            ]);

            $this->membershipService->addOwner($publicLeague, $users['owner'], 'Baron Hunters');
            $this->membershipService->join($publicLeague, $users['member_one'], 'Blue Side Kings');
            $this->membershipService->join($publicLeague, $users['member_two'], 'Drake Stackers');
            $this->membershipService->join($publicLeague, $users['member_three'], 'Late Game Insurance');

            $privateLeague = FantasyLeague::factory()->private()->create([
                'competition_id' => $competition->id,
                'creator_user_id' => $users['private_owner']->id,
                'name' => 'Scrim Room',
                'max_participants' => 4,
                'budget_cap' => 90,
                'join_deadline' => now()->addDays(5),
                'scoring_rule_version' => 'v1',
            ]);

            $this->membershipService->addOwner($privateLeague, $users['private_owner'], 'Quiet Macro');

            $invitation = Invitation::factory()->valid()->create([
                'fantasy_league_id' => $privateLeague->id,
                'code' => config('demo.private_invitation_code'),
                'max_uses' => 3,
                'used_count' => 0,
            ]);

            $this->membershipService->join($privateLeague, $users['private_member'], 'Vision Diff');
            $invitation->increment('used_count');
        });
    }
}
