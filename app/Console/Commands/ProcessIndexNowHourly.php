<?php

namespace App\Console\Commands;

use App\Jobs\IndexNow\DispatchHourlyJob;
use Illuminate\Console\Command;

class ProcessIndexNowHourly extends Command
{
    protected $signature = 'indexnow:process-hourly {--hour= : Force a specific hour (0-23)}';

    protected $description = 'Dispatch IndexNow submissions for the current hour based on weights';

    public function handle(): int
    {
        $hour = $this->option('hour');

        if ($hour !== null) {
            $hour = (int) $hour;
            if ($hour < 0 || $hour > 23) {
                $this->error('Hour must be between 0 and 23.');

                return self::FAILURE;
            }
        } else {
            $hour = (int) now()->format('G');
        }

        $this->info("Dispatching IndexNow submissions for hour {$hour}...");

        DispatchHourlyJob::dispatch($hour);

        $this->info('Dispatched successfully.');

        return self::SUCCESS;
    }
}
