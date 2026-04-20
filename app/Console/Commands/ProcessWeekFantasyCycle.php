<?php

namespace App\Console\Commands;

use App\Models\Week;
use App\Services\LineupService;
use App\Services\PandaScoreService;
use App\Services\ScoringService;
use Illuminate\Console\Command;
use Throwable;

class ProcessWeekFantasyCycle extends Command
{
    protected $signature = 'fantasy:process-week {week_id}';

    protected $description = 'Lock lineups and calculate weekly fantasy standings.';

    public function handle(
        LineupService $lineupService,
        ScoringService $scoringService,
        PandaScoreService $pandaScoreService
    ): int
    {
        $week = Week::find($this->argument('week_id'));

        if (! $week) {
            $this->error('Week not found.');

            return self::FAILURE;
        }

        try {
            $syncSummary = $pandaScoreService->sync();
        } catch (Throwable $exception) {
            $this->error("PandaScore synchronization failed: {$exception->getMessage()}");

            return self::FAILURE;
        }

        $this->info(
            "PandaScore synchronized {$syncSummary['matches']} matches and {$syncSummary['players']} players."
        );

        $lineupService->lockLineups($week);
        $scoringService->scoreWeek($week);

        $this->info("Week {$week->id} processed successfully.");

        return self::SUCCESS;
    }
}
