<?php

namespace App\Jobs\Sitemap;

use App\Models\SitemapRunLog;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CompressSitemapGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        protected string $runId,
        protected string $version,
        protected int $groupIndex,
        protected bool $force = false,
        protected string $store = '',
        protected bool $separateStores = false,
        protected ?string $lastId = null,
        protected bool $storeExhausted = false,
    ) {
        $this->onQueue((string) config('queue.sitemap_queue', 'default'));
    }

    public function handle(): void
    {
        if ($this->shouldStop()) {
            return;
        }

        $this->info("CompressSitemapGroupJob is deprecated - groups are now managed incrementally");

        if ($this->storeExhausted) {
            if ($this->store === 'dk' && $this->separateStores) {
                ProcessSitemapChunkJob::dispatch(
                    $this->runId,
                    $this->version,
                    lastId: null,
                    force: $this->force,
                    store: 'bs',
                    separateStores: true,
                    chunkIndex: 0,
                );
            } elseif ($this->store === 'bs' && $this->separateStores) {
                ProcessSitemapChunkJob::dispatch(
                    $this->runId,
                    $this->version,
                    lastId: null,
                    force: $this->force,
                    store: 'other',
                    separateStores: true,
                    chunkIndex: 0,
                );
            } else {
                FinalizeSitemapJob::dispatch(
                    $this->runId,
                    force: $this->force,
                    separateStores: $this->separateStores,
                );
            }
        } else {
            ProcessSitemapChunkJob::dispatch(
                $this->runId,
                $this->version,
                lastId: $this->lastId,
                force: $this->force,
                store: $this->store,
                separateStores: $this->separateStores,
                chunkIndex: 0,
            )->delay(now()->addSeconds(app(SitemapGenerationService::class)->getDelaySecondsForNextBatch()));
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

        Log::error("SitemapGenerator: Compress job failed for run {$this->runId}: {$exception->getMessage()}");
    }

    private function shouldStop(): bool
    {
        if (!Cache::has("sitemap:stop:{$this->runId}")) {
            return false;
        }

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'status' => 'failed',
                'error_message' => 'فرآیند توسط مدیر متوقف شد',
                'completed_at' => now(),
            ]);

        Cache::forget("sitemap:stop:{$this->runId}");
        Cache::forget('sitemap:running');

        $this->info("Sitemap generation stopped for run {$this->runId}");

        return true;
    }

    private function info(string $message): void
    {
        Log::info("SitemapGenerator: {$message}");
    }
}
