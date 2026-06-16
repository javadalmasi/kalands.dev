<?php

namespace App\Console\Commands;

use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate {--force : Force full rebuild}';

    protected $description = 'Start or ensure the continuous product sitemap generator is running';

    public function handle(SitemapGenerationService $sitemapGenerationService): int
    {
        $sitemapGenerationService->refreshCachedCountsIfDue(10);

        if (Cache::get('sitemap:running')) {
            $this->info('Continuous sitemap generation is already running.');
            return Command::SUCCESS;
        }
        
        $force = $this->option('force');
        
        if ($force) {
            $this->warn('Starting FULL REBUILD - this will create a new sitemap version');
        }

        if (!$force && !$sitemapGenerationService->shouldStartAutomatically()) {
            $this->warn('Continuous sitemap generation is not eligible to start now.');
            return Command::SUCCESS;
        }

        $run = $sitemapGenerationService->start($force);

        if (!$run) {
            $this->warn('Sitemap generation was not enqueued.');
            return Command::SUCCESS;
        }

        $rebuildType = $run->rebuild_type === 'full' ? 'FULL REBUILD' : 'INCREMENTAL';
        $this->info("Sitemap generation started: {$rebuildType} (run: {$run->run_id}, version: {$run->version})");

        return Command::SUCCESS;
    }
}
