<?php

namespace App\Jobs\Sitemap;

use App\Models\Product;
use App\Models\SitemapRunLog;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ContinueSitemapRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    private const int MAX_RETRIES = 12;
    private const int NEW_PRODUCTS_THRESHOLD = 10000;

    public function __construct(
        protected string $runId,
        protected string $version,
        protected ?string $lastId,
        protected bool $force,
        protected bool $separateStores,
        protected int $retryCount = 0,
    ) {
        $this->onQueue((string) config('queue.sitemap_queue', 'default'));
    }

    public function handle(SitemapGenerationService $sitemapService): void
    {
        if (!Cache::get('sitemap:running')) {
            return;
        }

        $sitemapService->refreshCachedCountsIfDue(10);

        if ($sitemapService->getCurrentRate() <= 0) {
            $this->recheck();
            return;
        }

        $incompleteGroup = $sitemapService->getIncompleteGroup($this->version);

        if ($incompleteGroup !== null) {
            $newCount = Product::query()
                ->where('is_active', true)
                ->where('id', '>', $incompleteGroup->last_product_id ?? 0)
                ->count();

            if ($newCount >= self::NEW_PRODUCTS_THRESHOLD) {
                ProcessSitemapChunkJob::dispatch(
                    $this->runId,
                    $this->version,
                    lastId: $incompleteGroup->last_product_id,
                    force: $this->force,
                    store: $this->separateStores ? 'dk' : '',
                    separateStores: $this->separateStores,
                    chunkIndex: 0,
                );
                return;
            }

            $this->recheck();
            return;
        }

        $hasNew = Product::query()
            ->where('is_active', true)
            ->whereNull('sitemapped_at')
            ->exists();

        if ($hasNew) {
            ProcessSitemapChunkJob::dispatch(
                $this->runId,
                $this->version,
                lastId: $this->lastId,
                force: $this->force,
                store: $this->separateStores ? 'dk' : '',
                separateStores: $this->separateStores,
                chunkIndex: 0,
            );
            return;
        }

        if ($this->retryCount < self::MAX_RETRIES) {
            $this->recheck();
            return;
        }

        $this->finalize($sitemapService);
    }

    private function recheck(): void
    {
        self::dispatch(
            $this->runId,
            $this->version,
            $this->lastId,
            $this->force,
            $this->separateStores,
            $this->retryCount + 1,
        )->delay(now()->addMinutes(5));
    }

    private function finalize(SitemapGenerationService $sitemapService): void
    {
        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        Cache::forget('sitemap:running');

        $sitemapService->refreshCachedCountsIfDue(10);

        if ($sitemapService->shouldStartAutomatically()) {
            $nextRun = $sitemapService->start(false);
            if ($nextRun) {
                info("ContinueSitemapRunJob: Auto-started next continuous run {$nextRun->run_id}.");
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

        Cache::forget('sitemap:running');
    }
}
