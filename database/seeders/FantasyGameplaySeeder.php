<?php

namespace Database\Seeders;

use App\Models\Auction;
use App\Models\Competition;
use App\Models\FantasyLeague;
use App\Models\Player;
use App\Models\Week;
use App\Services\AuctionService;
use App\Services\LineupService;
use App\Services\ScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class FantasyGameplaySeeder extends Seeder
{
    public function __construct(
        private readonly AuctionService $auctionService,
        private readonly LineupService $lineupService,
        private readonly ScoringService $scoringService
    ) {}

    public function run(): void
    {
        $competition = Competition::query()->firstOrFail();
        $publicLeague = FantasyLeague::query()->where('name', 'Review League')->firstOrFail();
        $privateLeague = FantasyLeague::query()->where('name', 'Scrim Room')->firstOrFail();
        $weekOne = Week::query()->where('competition_id', $competition->id)->where('number', 1)->firstOrFail();
        $weekTwo = Week::query()->where('competition_id', $competition->id)->where('number', 2)->firstOrFail();

        $teams = $publicLeague->fantasyTeams()->with('membership.user')->orderBy('id')->get();
        $availablePlayers = Player::query()
            ->whereHas('team', fn ($query) => $query->where('competition_id', $competition->id))
            ->with('team')
            ->orderBy('id')
            ->get()
            ->groupBy('role')
            ->map(fn (Collection $players) => $players->values());

        $closedAuction = Auction::factory()->open()->create([
            'fantasy_league_id' => $publicLeague->id,
            'week_id' => $weekOne->id,
            'start_at' => now()->subHours(2),
            'end_at' => now()->addHour(),
        ]);

        $assignments = [];

        foreach ($teams as $teamIndex => $team) {
            $assignments[$team->id] = collect([
                $this->pullPlayer($availablePlayers, 'TOP'),
                $this->pullPlayer($availablePlayers, 'JGL'),
                $this->pullPlayer($availablePlayers, 'MID'),
                $this->pullPlayer($availablePlayers, 'ADC'),
                $this->pullPlayer($availablePlayers, 'SUP'),
                $this->pullPlayer($availablePlayers, 'TOP'),
                $this->pullPlayer($availablePlayers, 'MID'),
            ])->values();

            foreach ($assignments[$team->id] as $slotIndex => $player) {
                $this->auctionService->placeBid(
                    $closedAuction,
                    $team,
                    $player,
                    7 + $teamIndex + $slotIndex
                );
            }
        }

        $firstTeam = $teams->first();
        $secondTeam = $teams->skip(1)->first();
        $contestedPlayer = $assignments[$firstTeam->id]->first();

        $this->auctionService->placeBid($closedAuction, $secondTeam, $contestedPlayer, 5);
        $this->auctionService->close($closedAuction);

        foreach ($teams as $team) {
            $team->load('activeRosterSlots.player');

            $rosterByRole = $team->activeRosterSlots->groupBy(fn ($slot) => $slot->player->role);
            $lineupSlots = [
                ['roster_slot_id' => $rosterByRole['TOP'][0]->id, 'position' => 'TOP', 'is_captain' => false],
                ['roster_slot_id' => $rosterByRole['JGL'][0]->id, 'position' => 'JGL', 'is_captain' => false],
                ['roster_slot_id' => $rosterByRole['MID'][0]->id, 'position' => 'MID', 'is_captain' => true],
                ['roster_slot_id' => $rosterByRole['ADC'][0]->id, 'position' => 'ADC', 'is_captain' => false],
                ['roster_slot_id' => $rosterByRole['SUP'][0]->id, 'position' => 'SUP', 'is_captain' => false],
                ['roster_slot_id' => $rosterByRole['TOP'][1]->id, 'position' => 'FLEX_1', 'is_captain' => false],
                ['roster_slot_id' => $rosterByRole['MID'][1]->id, 'position' => 'FLEX_2', 'is_captain' => false],
            ];

            $this->lineupService->submit($team, $weekOne, $lineupSlots);
        }

        $this->lineupService->lockLineups($weekOne);
        $this->scoringService->scoreWeek($weekOne);

        $openAuction = Auction::factory()->open()->create([
            'fantasy_league_id' => $privateLeague->id,
            'week_id' => $weekTwo->id,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHours(6),
        ]);

        $privateLeagueTeams = $privateLeague->fantasyTeams()->orderBy('id')->get();
        $reservePlayers = $availablePlayers
            ->flatten()
            ->take(2)
            ->values();

        foreach ($reservePlayers as $playerIndex => $player) {
            $this->auctionService->placeBid(
                $openAuction,
                $privateLeagueTeams[$playerIndex],
                $player,
                11 + $playerIndex
            );
        }
    }

    private function pullPlayer(Collection $availablePlayers, string $role): Player
    {
        /** @var Collection<int, Player> $bucket */
        $bucket = $availablePlayers->get($role);

        /** @var Player $player */
        $player = $bucket->shift();

        $availablePlayers->put($role, $bucket);

        return $player;
    }
}
