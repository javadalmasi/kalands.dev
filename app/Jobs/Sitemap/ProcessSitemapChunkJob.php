<?php

namespace App\Jobs\Sitemap;

use App\Models\Product;
use App\Models\SitemapRunLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\Sitemap\SitemapGenerationService;

class ProcessSitemapChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120];

    public const int CHUNK_SIZE = 1000;

    public const int MAX_CHUNKS_PER_GROUP = 50;

    public const int URLS_PER_GZIP = self::CHUNK_SIZE * self::MAX_CHUNKS_PER_GROUP;

    private const int BATCH_SIZE = 1000;

    public function __construct(
        protected string $runId,
        protected ?string $lastId = null,
        protected bool $force = false,
        protected string $store = '',
        protected bool $separateStores = false,
        protected int $chunkIndex = 0,
    ) {
        $this->onQueue((string) config('queue.sitemap_queue', 'default'));
    }

    public function handle(SitemapGenerationService $sitemapService): void
    {
        if ($this->shouldStop()) {
            return;
        }

        if ($this->force && $this->lastId === null && in_array($this->store, ['', 'dk'], true)) {
            $this->cleanupOldChunks();
        }

        $query = Product::query()
            ->select(['id', 'title', 'updated_at', 'created_at'])
            ->where('is_active', true)
            ->orderBy('id');

        if ($this->store === 'dk') {
            $query->where('store', 'digikala');
        } elseif ($this->store === 'bs') {
            $query->where('store', 'basalam');
        } elseif ($this->store === 'other') {
            $query->whereNotIn('store', ['digikala', 'basalam']);
        }

        if ($this->force) {
            if ($this->lastId !== null) {
                $query->where('id', '>', $this->lastId);
            }
        } else {
            $query->where(function ($q) {
                $q->whereNull('sitemapped_at')
                    ->orWhereColumn('updated_at', '>', 'sitemapped_at');
            });

            if ($this->lastId !== null) {
                $query->where('id', '>', $this->lastId);
            }
        }

        $products = $query->limit(self::CHUNK_SIZE)->get();

        $groupIndex = (int) ($this->chunkIndex / self::MAX_CHUNKS_PER_GROUP);
        $chunkInGroup = $this->chunkIndex % self::MAX_CHUNKS_PER_GROUP;
        $isLastInGroup = $chunkInGroup === (self::MAX_CHUNKS_PER_GROUP - 1);

        if ($products->isEmpty()) {
            $this->handleEmptyProducts($groupIndex, $chunkInGroup);
            return;
        }

        $storePrefix = $this->store ? "-{$this->store}" : '';
        $filename = "sitemap-{$this->runId}{$storePrefix}-g{$groupIndex}-{$this->chunkIndex}.xml";
        $urls = [];

        $maxLastMod = null;
        $productIds = [];

        foreach ($products as $product) {
            $lastMod = ($product->updated_at ?? $product->created_at)
                ->setTimezone('UTC')
                ->format('Y-m-d\TH:i:sP');

            if ($maxLastMod === null || $product->updated_at > $maxLastMod) {
                $maxLastMod = $product->updated_at;
            }

            $slug = str_slug_persian($product->title ?? '');
            $urls[] = [
                'loc' => config('app.url').'/product/'.$product->id.($slug ? '/'.$slug : ''),
                'lastmod' => $lastMod,
            ];

            $productIds[] = $product->id;

            usleep(200);
        }

        $this->generatePlainSitemapFile($filename, $urls);

        $now = now();
        $lastProductId = $products->last()->id;

        collect($productIds)
            ->chunk(self::BATCH_SIZE)
            ->each(function ($ids) use ($now) {
                Product::query()
                    ->whereIn('id', $ids)
                    ->update(['sitemapped_at' => $now]);
            });

        $processedCount = count($productIds);
        $hasMoreProducts = $products->count() >= self::CHUNK_SIZE;

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'processed_products' => DB::raw('processed_products + '.(int) $processedCount),
            ]);

        $maxLastModStr = $maxLastMod
            ? $maxLastMod->setTimezone('UTC')->format('Y-m-d\TH:i:sP')
            : $now->setTimezone('UTC')->format('Y-m-d\TH:i:sP');

        Storage::disk('local')->put(
            "sitemap_chunks/{$this->runId}{$storePrefix}-g{$groupIndex}-{$this->chunkIndex}.json",
            json_encode([
                'filename' => $filename,
                'url_count' => count($urls),
                'last_product_id' => $lastProductId,
                'lastmod' => $maxLastModStr,
                'generated_at' => $now->toIso8601String(),
            ])
        );

        if ($isLastInGroup) {
            CompressSitemapGroupJob::dispatch(
                $this->runId,
                $groupIndex,
                force: $this->force,
                store: $this->store,
                separateStores: $this->separateStores,
                lastId: $lastProductId,
                storeExhausted: !$hasMoreProducts,
            )->delay(now()->addSeconds($sitemapService->getDelaySecondsForNextBatch()));
        } else {
            self::dispatch(
                $this->runId,
                lastId: $lastProductId,
                force: $this->force,
                store: $this->store,
                separateStores: $this->separateStores,
                chunkIndex: $this->chunkIndex + 1,
            )->delay(now()->addSeconds($sitemapService->getDelaySecondsForNextBatch()));
        }
    }

    private function handleEmptyProducts(int $groupIndex, int $chunkInGroup): void
    {
        if ($chunkInGroup > 0) {
            CompressSitemapGroupJob::dispatch(
                $this->runId,
                $groupIndex,
                force: $this->force,
                store: $this->store,
                separateStores: $this->separateStores,
                lastId: $this->lastId,
                storeExhausted: true,
            );
        } elseif ($this->store === 'dk' && $this->separateStores) {
            self::dispatch(
                $this->runId,
                lastId: null,
                force: $this->force,
                store: 'bs',
                separateStores: true,
                chunkIndex: 0,
            )->delay(now()->addSeconds(app(SitemapGenerationService::class)->getDelaySecondsForNextBatch()));
        } elseif ($this->store === 'bs' && $this->separateStores) {
            self::dispatch(
                $this->runId,
                lastId: null,
                force: $this->force,
                store: 'other',
                separateStores: true,
                chunkIndex: 0,
            )->delay(now()->addSeconds(app(SitemapGenerationService::class)->getDelaySecondsForNextBatch()));
        } else {
            ContinueSitemapRunJob::dispatch(
                $this->runId,
                lastId: $this->lastId,
                force: $this->force,
                separateStores: $this->separateStores,
            )->delay(now()->addMinutes(5));
        }
    }

    private function generatePlainSitemapFile(string $filename, array $urls): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>'.$this->escapeXml($url['loc']).'</loc>';
            $xml .= '<lastmod>'.$url['lastmod'].'</lastmod>';
            $xml .= '<changefreq>daily</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        $path = public_path("sitemaps/{$filename}");
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $xml);
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function cleanupOldChunks(): void
    {
        $existing = glob(public_path('sitemaps/sitemap-*.xml.gz'));
        foreach ($existing as $file) {
            @unlink($file);
        }

        $plainXml = glob(public_path('sitemaps/sitemap-*-g*-*.xml'));
        foreach ($plainXml as $file) {
            @unlink($file);
        }

        $metaFiles = glob(storage_path('app/sitemap_chunks/*.json'));
        foreach ($metaFiles as $file) {
            @unlink($file);
        }

        $indexFiles = [
            public_path('sitemap.xml'),
            public_path('sitemap-digikala.xml'),
            public_path('sitemap-basalam.xml'),
            public_path('sitemap-other.xml'),
        ];
        foreach ($indexFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        app(\App\Services\Sitemap\SitemapGenerationService::class)->clearTailGzip();
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

        Log::error("SitemapGenerator: Chunk job failed for run {$this->runId}: {$exception->getMessage()}");
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
