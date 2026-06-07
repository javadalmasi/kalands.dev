<?php

namespace App\Providers;

use App\Services\IndexNowService;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class QueueSitemapAutoStartServiceProvider extends ServiceProvider
{
    private static ?\Illuminate\Support\Carbon $lastCountsRefreshAt = null;

    public function boot(): void
    {
        Queue::looping(function (): void {
            if (app()->runningInConsole() === false) {
                return;
            }

            try {
                $service = app(SitemapGenerationService::class);
                $indexNowService = app(IndexNowService::class);

                if (self::$lastCountsRefreshAt === null || self::$lastCountsRefreshAt->diffInMinutes(now()) >= 10) {
                    $service->refreshCachedCounts();
                    self::$lastCountsRefreshAt = now();
                }

                if ($service->shouldStartAutomatically()) {
                    $run = $service->start(true);
                    if ($run) {
                        Log::info("SitemapGenerator: Auto-started from queue worker loop ({$run->run_id}).");
                    }
                }

                if ($indexNowService->dispatchContinuousIfDue()) {
                    Log::info('IndexNow: Auto-dispatched current hour from queue worker loop.');
                }
            } catch (\Throwable $exception) {
                Log::warning('SitemapGenerator: auto-start check failed in queue loop: '.$exception->getMessage());
            }
        });
    }
}
