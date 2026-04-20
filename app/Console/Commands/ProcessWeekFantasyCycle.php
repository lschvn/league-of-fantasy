<?php

namespace App\Console\Commands;

use App\Models\Week;
use App\Services\LineupService;
use App\Services\ScoringService;
use Illuminate\Console\Command;

class ProcessWeekFantasyCycle extends Command
{
    protected $signature = 'fantasy:process-week {week_id}';

    protected $description = 'Lock lineups and calculate weekly fantasy standings.';

    public function handle(LineupService $lineupService, ScoringService $scoringService): int
    {
        $week = Week::find($this->argument('week_id'));

        if (! $week) {
            $this->error('Week not found.');

            return self::FAILURE;
        }

        $lineupService->lockLineups($week);
        $scoringService->scoreWeek($week);

        $this->info("Week {$week->id} processed successfully.");

        return self::SUCCESS;
    }
}
