<?php

namespace Database\Seeders;

use App\Models\Competition;
use App\Models\GameMatch;
use App\Models\Player;
use App\Models\PlayerStat;
use App\Models\Team;
use App\Models\Week;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $competition = Competition::factory()->create([
            'name' => 'League of Legends EMEA Championship',
            'region' => 'EMEA',
            'season' => '2026',
        ]);

        $teams = collect([
            ['name' => 'Karmine Corp', 'tag' => 'KC'],
            ['name' => 'Fnatic', 'tag' => 'FNC'],
            ['name' => 'G2 Esports', 'tag' => 'G2'],
            ['name' => 'Team BDS', 'tag' => 'BDS'],
            ['name' => 'SK Gaming', 'tag' => 'SK'],
            ['name' => 'MAD Lions KOI', 'tag' => 'MDK'],
            ['name' => 'GIANTX', 'tag' => 'GX'],
            ['name' => 'Rogue', 'tag' => 'RGE'],
        ])->map(function (array $teamData, int $index) use ($competition) {
            $team = Team::factory()->create([
                'competition_id' => $competition->id,
                'name' => $teamData['name'],
                'tag' => $teamData['tag'],
                'logo_url' => "https://example.com/logos/{$teamData['tag']}.png",
            ]);

            foreach (['TOP', 'JGL', 'MID', 'ADC', 'SUP'] as $roleIndex => $role) {
                Player::factory()->create([
                    'team_id' => $team->id,
                    'nickname' => "{$teamData['tag']}{$role}".($index + 1),
                    'role' => $role,
                    'status' => 'active',
                ]);
            }

            return $team;
        });

        $weeks = collect([
            $this->createWeek($competition->id, 1, now()->subHours(2)),
            $this->createWeek($competition->id, 2, now()->addDays(5)),
            $this->createWeek($competition->id, 3, now()->addDays(12)),
        ]);

        $pairings = [
            [[0, 1], [2, 3], [4, 5], [6, 7]],
            [[0, 2], [1, 3], [4, 6], [5, 7]],
            [[0, 3], [1, 2], [4, 7], [5, 6]],
        ];

        $weeks->each(function (Week $week, int $weekIndex) use ($teams, $pairings) {
            foreach ($pairings[$weekIndex] as $matchIndex => [$blueIndex, $redIndex]) {
                $match = GameMatch::factory()->create([
                    'week_id' => $week->id,
                    'status' => $weekIndex === 2 ? 'scheduled' : 'completed',
                    'started_at' => $weekIndex === 2 ? null : $week->start_at->copy()->addHours($matchIndex * 3),
                    'ended_at' => $weekIndex === 2 ? null : $week->start_at->copy()->addHours(($matchIndex * 3) + 2),
                ]);

                $match->teams()->attach([
                    $teams[$blueIndex]->id => ['side' => 'blue'],
                    $teams[$redIndex]->id => ['side' => 'red'],
                ]);

                if ($weekIndex < 2) {
                    $this->seedMatchStats($match, $teams[$blueIndex], $teams[$redIndex], $weekIndex, $matchIndex);
                }
            }
        });
    }

    private function createWeek(int $competitionId, int $number, Carbon $startsAt): Week
    {
        $start = $startsAt->copy()->startOfDay();

        return Week::factory()->create([
            'competition_id' => $competitionId,
            'number' => $number,
            'start_at' => $start,
            'end_at' => $start->copy()->addDays(6),
            'lineup_lock_at' => $start->copy()->addDay(),
        ]);
    }

    private function seedMatchStats(GameMatch $match, Team $blueTeam, Team $redTeam, int $weekIndex, int $matchIndex): void
    {
        foreach ([$blueTeam, $redTeam] as $teamOffset => $team) {
            foreach ($team->players()->orderBy('id')->get() as $playerIndex => $player) {
                $kills = 2 + (($weekIndex + $matchIndex + $playerIndex + $teamOffset) % 8);
                $deaths = 1 + (($teamOffset + $playerIndex) % 5);
                $assists = 4 + (($weekIndex + $playerIndex + $matchIndex) % 10);

                PlayerStat::factory()->create([
                    'match_id' => $match->id,
                    'player_id' => $player->id,
                    'kills' => $kills,
                    'deaths' => $deaths,
                    'assists' => $assists,
                    'fantasy_points' => ($kills * 3) + ($assists * 2) - $deaths,
                ]);
            }
        }
    }
}
