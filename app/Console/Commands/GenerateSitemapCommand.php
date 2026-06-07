<?php

namespace App\Console\Commands;

use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Ensure the continuous product sitemap generator is running';

    public function handle(SitemapGenerationService $sitemapGenerationService): int
    {
        $sitemapGenerationService->refreshCachedCountsIfDue(10);

        if (Cache::get('sitemap:running')) {
            $this->info('Continuous sitemap generation is already running.');
            return Command::SUCCESS;
        }

        if (! $sitemapGenerationService->shouldStartAutomatically()) {
            $this->warn('Continuous sitemap generation is not eligible to start now.');
            return Command::SUCCESS;
        }

        $run = $sitemapGenerationService->start(true);

        if (!$run) {
            $this->warn('Sitemap generation was not enqueued.');
            return Command::SUCCESS;
        }

        $this->info("Continuous sitemap generation ensured (run: {$run->run_id}).");

        return Command::SUCCESS;
    }
}
