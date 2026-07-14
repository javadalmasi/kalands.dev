<?php

namespace App\Jobs\Sitemap;

use App\Models\SitemapRunLog;
use App\Models\SitemapShard;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Plans the shard metadata for one sitemap generation.
 *
 * This is intentionally lightweight: it does NOT render XML. Each pass computes
 * the keyset boundaries for a batch of shards (via the service), then either
 * re-dispatches itself for the next batch or, when the catalog is exhausted,
 * activates the new generation — flipping the live pointer atomically and
 * pruning old generations.
 *
 * Concurrency is guarded by the `sitemap:running` cache lock, so this job is
 * intentionally NOT ShouldBeUnique (that would block the self-redispatch chain).
 */
class SitemapBuildJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public int $timeout;

    public function __construct(
        public readonly string $runId,
        public readonly int $generation,
        public readonly ?string $cursor = null,
        public readonly int $nextShardIndex = 1,
        public readonly int $processed = 0,
    ) {
        $this->tries = (int) config('sitemap.job_tries', 3);
        $this->timeout = (int) config('sitemap.job_timeout', 280);
        $this->onQueue(config('sitemap.queue', 'default'));
    }

    public function handle(SitemapGenerationService $service): void
    {
        if ($service->stopRequested()) {
            $this->abort($service, 'فرآیند توسط مدیر متوقف شد');

            return;
        }

        $result = $service->planPass($this->generation, $this->cursor, $this->nextShardIndex);

        $processed = $this->processed + $result['processed'];

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update(['processed_products' => $processed]);

        if (! $result['exhausted']) {
            $this->continueWith(
                $service,
                $result['cursor'],
                $this->nextShardIndex + $result['planned'],
                $processed,
            );

            return;
        }

        $this->finish($service, $processed);
    }

    private function continueWith(SitemapGenerationService $service, ?string $cursor, int $nextShardIndex, int $processed): void
    {
        self::dispatch($this->runId, $this->generation, $cursor, $nextShardIndex, $processed);
    }

    private function finish(SitemapGenerationService $service, int $processed): void
    {
        // Flip the live pointer to the freshly planned generation and prune the rest.
        $service->activateGeneration($this->generation);

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'status' => 'completed',
                'processed_products' => $processed,
                'completed_at' => now(),
            ]);

        $service->clearRunning();
        $service->clearStop();
        $service->refreshCounts();

        Log::info("Sitemap build completed: run {$this->runId} (generation {$this->generation}, {$processed} products).");
    }

    private function abort(SitemapGenerationService $service, string $reason): void
    {
        // Discard the half-planned generation; the live one is untouched.
        SitemapShard::query()->where('generation', $this->generation)->delete();

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->where('status', 'running')
            ->update([
                'status' => 'failed',
                'error_message' => $reason,
                'completed_at' => now(),
            ]);

        $service->clearRunning();
        $service->clearStop();
    }

    public function failed(Throwable $exception): void
    {
        SitemapShard::query()->where('generation', $this->generation)->delete();

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

        app(SitemapGenerationService::class)->clearRunning();

        Log::error("Sitemap build failed: run {$this->runId}: {$exception->getMessage()}");
    }
}
