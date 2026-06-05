<?php

namespace App\Jobs\IndexNow;

use App\Models\IndexNowRunLog;
use App\Models\Product;
use App\Services\IndexNowService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;
    public int $tries = 3;

    /**
     * @param array<int, string> $productIds
     */
    public function __construct(
        private array $productIds,
        private string $engine,
        private string $runId,
        private int $hour,
    ) {}

    public function handle(IndexNowService $service): void
    {
        $products = Product::whereIn('id', $this->productIds)
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $urls = $products->map(fn(Product $p) => $service->buildProductUrl($p))->values()->all();

        $chunks = array_chunk($urls, 5000);
        $totalSubmitted = 0;
        $totalFailed = 0;
        $errors = [];

        foreach ($chunks as $chunk) {
            $result = $service->submitBatch($chunk, $this->engine);
            if ($result['success']) {
                $totalSubmitted += $result['submitted'];
            } else {
                $totalFailed += count($chunk);
                $errors[] = $result['error'] ?? 'Unknown error';
            }
        }

        if ($totalSubmitted > 0) {
            $now = now();
            Product::whereIn('id', $this->productIds)
                ->where('is_active', true)
                ->update(['indexnow_submitted_at' => $now]);
        }

        IndexNowRunLog::where('run_id', $this->runId)->update([
            'total_submitted' => $totalSubmitted,
            'total_failed' => $totalFailed,
            'status' => $totalFailed > 0 ? 'completed_with_errors' : 'completed',
            'completed_at' => now(),
            'meta' => ['errors' => $errors],
        ]);
    }

    public function failed(\Throwable $e): void
    {
        IndexNowRunLog::where('run_id', $this->runId)->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $e->getMessage(),
        ]);
    }
}
