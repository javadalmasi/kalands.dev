<?php

namespace App\Console\Commands;

use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Console\Command;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate {--force : Rebuild now even if not yet due}';

    protected $description = 'Plan the product sitemap shards (rebuild), or auto-rebuild when due';

    public function handle(SitemapGenerationService $service): int
    {
        if ($service->isRunning()) {
            $this->warn('A sitemap build is already running.');

            return self::SUCCESS;
        }

        $run = $this->option('force') ? $service->start() : $service->startAuto();

        if (! $run) {
            $this->line('No sitemap build was started (module off, not due, or already running).');

            return self::SUCCESS;
        }

        $this->info("Sitemap build queued: run {$run->run_id} (generation ".($run->meta['generation'] ?? '?').').');

        return self::SUCCESS;
    }
}
