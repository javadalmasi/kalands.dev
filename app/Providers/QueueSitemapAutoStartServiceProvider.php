<?php

namespace App\Providers;

use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class QueueSitemapAutoStartServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        static $lastCountsRefreshAt = null;

        Queue::looping(function (): void {
            if (app()->runningInConsole() === false) {
                return;
            }

            try {
                $service = app(SitemapGenerationService::class);

                if ($lastCountsRefreshAt === null || $lastCountsRefreshAt->diffInMinutes(now()) >= 10) {
                    $service->refreshCachedCounts();
                    $lastCountsRefreshAt = now();
                }

                if (! $service->shouldStartAutomatically()) {
                    return;
                }

                $run = $service->start(true);
                if ($run) {
                    Log::info("SitemapGenerator: Auto-started from queue worker loop ({$run->run_id}).");
                }
            } catch (\Throwable $exception) {
                Log::warning('SitemapGenerator: auto-start check failed in queue loop: '.$exception->getMessage());
            }
        });
    }
}
