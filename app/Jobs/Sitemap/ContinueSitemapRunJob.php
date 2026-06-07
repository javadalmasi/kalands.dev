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

    private const int TAIL_FILL_THRESHOLD = 10000;

    public function __construct(
        protected string $runId,
        protected ?string $lastId,
        protected bool $force,
        protected bool $separateStores,
        protected int $retryCount = 0,
    ) {
        $this->onQueue((string) config('queue.sitemap_queue', 'default'));
    }

    public function handle(SitemapGenerationService $sitemapService): void
    {
        if (! Cache::get('sitemap:running')) {
            return;
        }

        $sitemapService->refreshCachedCountsIfDue(10);

        if ($sitemapService->getCurrentRate() <= 0) {
            $this->recheck();
            return;
        }

        $tailGzip = $sitemapService->getTailGzip();

        if ($tailGzip !== null) {
            $newCount = Product::query()
                ->where('is_active', true)
                ->where('id', '>', $tailGzip['last_product_id'])
                ->count();

            if ($newCount >= self::TAIL_FILL_THRESHOLD) {
                $this->fillTailGzip($sitemapService, $tailGzip);
                return;
            }

            $this->recheck();
            return;
        }

        $hasNew = Product::query()
            ->where('is_active', true)
            ->when($this->lastId, fn ($q) => $q->where('id', '>', $this->lastId))
            ->exists();

        if ($hasNew) {
            ProcessSitemapChunkJob::dispatch(
                $this->runId,
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

    private function fillTailGzip(SitemapGenerationService $sitemapService, array $tailGzip): void
    {
        $oldPath = public_path("sitemaps/{$tailGzip['filename']}");
        $oldIds = $sitemapService->parseGzipProductIds($oldPath);

        $oldProducts = Product::query()
            ->whereIn('id', $oldIds)
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'title', 'updated_at', 'created_at']);

        $newProducts = Product::query()
            ->where('is_active', true)
            ->where('id', '>', $tailGzip['last_product_id'])
            ->orderBy('id')
            ->get(['id', 'title', 'updated_at', 'created_at']);

        @unlink($oldPath);
        $sitemapService->clearTailGzip();

        $allUrls = [];

        foreach ($oldProducts as $product) {
            $allUrls[] = $sitemapService->productToUrlData($product);
        }

        foreach ($newProducts as $product) {
            $allUrls[] = $sitemapService->productToUrlData($product);
        }

        $urlsPerGzip = ProcessSitemapChunkJob::URLS_PER_GZIP;
        $groups = array_chunk($allUrls, $urlsPerGzip);
        $newRunId = now()->format('Ymd_His');
        $newLastProductId = null;

        foreach ($groups as $gIdx => $urlBatch) {
            $gzFilename = "sitemap-{$newRunId}-g{$gIdx}.xml.gz";
            $sitemapService->writeGzipFromUrls(public_path("sitemaps/{$gzFilename}"), $urlBatch);

            $batchIds = [];
            foreach ($urlBatch as $url) {
                if (preg_match('#/product/(\d+)#', $url['loc'], $m)) {
                    $batchIds[] = (int) $m[1];
                }
            }

            Product::query()
                ->whereIn('id', $batchIds)
                ->update(['sitemapped_at' => now()]);

            if (count($urlBatch) < $urlsPerGzip) {
                $newLastProductId = !empty($batchIds) ? max($batchIds) : null;
                $sitemapService->setTailGzip([
                    'filename' => $gzFilename,
                    'run_id' => $newRunId,
                    'group' => $gIdx,
                    'url_count' => count($urlBatch),
                    'last_product_id' => $newLastProductId,
                ]);
            }
        }

        $sitemapService->rebuildSitemapIndex();
        $sitemapService->refreshCachedCountsIfDue(5);

        $this->lastId = $newLastProductId;
    }

    private function recheck(): void
    {
        self::dispatch(
            $this->runId,
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
            $nextRun = $sitemapService->start(true);
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
