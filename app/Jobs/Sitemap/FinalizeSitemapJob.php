<?php

namespace App\Jobs\Sitemap;

use App\Models\SitemapRunLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FinalizeSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        protected string $runId,
        protected bool $force = false,
        protected bool $separateStores = false,
    ) {
        $this->onQueue((string) config('queue.sitemap_queue', 'default'));
    }

    public function handle(): void
    {
        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        Cache::forget('sitemap:running');

        $this->info('Sitemap generation completed.');

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

        Log::error("SitemapGenerator: Finalize job failed for run {$this->runId}: {$exception->getMessage()}");
    }

    private function info(string $message): void
    {
        Log::info("SitemapGenerator: {$message}");
    }
}
