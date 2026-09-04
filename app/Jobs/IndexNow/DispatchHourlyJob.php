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

class DispatchHourlyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public int $tries = 1;

    public function __construct(
        private int $hour,
        private ?int $overrideLimit = null,
    ) {}

    public function handle(IndexNowService $service): void
    {
        $engines = ['bing', 'yandex'];

        foreach ($engines as $engine) {
            if (! $service->isEnabled($engine)) {
                continue;
            }

            $key = $service->getVerificationKey($engine);
            if (empty($key)) {
                continue;
            }

            $limit = $this->overrideLimit ?? $service->getProductsForHour($engine, $this->hour);
            if ($limit <= 0) {
                continue;
            }

            $productIds = Product::query()
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('indexnow_submitted_at')
                        ->orWhereColumn('updated_at', '>', 'indexnow_submitted_at');
                })
                ->limit($limit)
                ->pluck('id');

            if ($productIds->isEmpty()) {
                continue;
            }

            $runId = now()->format('Ymd_His')."_{$engine}_h{$this->hour}";

            IndexNowRunLog::create([
                'run_id' => $runId,
                'hour' => $this->hour,
                'engine' => $engine,
                'status' => 'running',
                'total_queued' => $productIds->count(),
                'started_at' => now(),
            ]);

            $ids = $productIds->values()->all();
            $chunks = array_chunk($ids, 5000);

            foreach ($chunks as $chunk) {
                SubmitBatchJob::dispatch($chunk, $engine, $runId, $this->hour);
            }
        }
    }
}
