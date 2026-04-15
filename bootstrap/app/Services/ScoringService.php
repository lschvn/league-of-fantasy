<?php

namespace App\Services;

use App\Models\FantasyTeam;
use App\Models\FantasyTeamScore;
use App\Models\PlayerStat;
use App\Models\Week;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ScoringService
{
    // calculate fantasy points for all teams in a given week
    public function scoreWeek(Week $week): void
    {
        DB::transaction(function () use ($week): void {
            // total fantasy points per player for this week
            $playerPoints = PlayerStat::query()
                ->whereHas('match', fn ($q) => $q->where('week_id', $week->id))
                ->selectRaw('player_id, SUM(fantasy_points) as total_points')
                ->groupBy('player_id')
                ->pluck('total_points', 'player_id');

            // all teams in competitions that match this week
            $teams = FantasyTeam::query()
                ->whereHas('membership.fantasyLeague', fn ($q) => $q->where('competition_id', $week->competition_id))
                ->with([
                    'membership.fantasyLeague',
                    'lineups' => fn ($q) => $q->where('week_id', $week->id)->with('slots.rosterSlot.player'),
                ])
                ->get();

            $scores = [];

            foreach ($teams as $team) {
                $lineup = $team->lineups->first();
                $points = 0.0;

                if ($lineup) {
                    foreach ($lineup->slots as $slot) {
                        $score = (float) ($playerPoints[$slot->rosterSlot->player_id] ?? 0);

                        // captain gets double points
                        if ($slot->is_captain) {
                            $score *= 2;
                        }

                        $points += $score;
                    }
                }

                $scores[] = FantasyTeamScore::updateOrCreate(
                    ['fantasy_team_id' => $team->id, 'week_id' => $week->id],
                    ['points' => $points, 'calculated_at' => Carbon::now()]
                );
            }

            // rank teams by points descending
            usort($scores, fn (FantasyTeamScore $a, FantasyTeamScore $b) => $b->points <=> $a->points);

            foreach ($scores as $i => $score) {
                $score->update(['rank' => $i + 1]);
            }
        });
    }
}
