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

class ProcessSitemapChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public int $tries = 3;

    private const int CHUNK_SIZE = 50000;

    public function __construct(
        protected string $runId,
        protected ?string $lastId = null,
        protected bool $force = false,
        protected string $store = '',
        protected bool $separateStores = false,
    ) {}

    public function handle(): void
    {
        if ($this->force && $this->lastId === null && in_array($this->store, ['', 'dk'], true)) {
            $this->cleanupOldChunks();
        }

        $query = Product::query()
            ->where('is_active', true)
            ->orderBy('id');

        if ($this->store === 'dk') {
            $query->where('store', 'digikala');
        } elseif ($this->store === 'bs') {
            $query->where('store', 'basalam');
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

        if ($products->isEmpty()) {
            if ($this->store === 'dk' && $this->separateStores) {
                $this->info('Digikala products done. Starting Basalam products.');
                self::dispatch(
                    $this->runId,
                    lastId: null,
                    force: $this->force,
                    store: 'bs',
                    separateStores: true,
                );
            } else {
                $this->info('No more products to process. Finalizing sitemap.');
                FinalizeSitemapJob::dispatch($this->runId, $this->force, $this->separateStores);
            }

            return;
        }

        $storePrefix = $this->store ? "-{$this->store}" : '';
        $chunkIndex = $this->getNextChunkIndex();
        $filename = "sitemap-{$this->runId}{$storePrefix}-{$chunkIndex}.xml.gz";
        $urls = [];

        $maxLastMod = null;

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
        }

        $this->generateSitemapFile($filename, $urls);

        $now = now();
        $lastProductId = $products->last()->id;

        Product::query()
            ->whereIn('id', $products->pluck('id'))
            ->update(['sitemapped_at' => $now]);

        $processedCount = $products->count();

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'processed_products' => DB::raw('processed_products + '.(int) $processedCount),
                'total_chunks' => DB::raw('total_chunks + 1'),
            ]);

        $maxLastModStr = $maxLastMod
            ? $maxLastMod->setTimezone('UTC')->format('Y-m-d\TH:i:sP')
            : $now->setTimezone('UTC')->format('Y-m-d\TH:i:sP');

        Storage::disk('local')->put(
            "sitemap_chunks/{$this->runId}{$storePrefix}-{$chunkIndex}.json",
            json_encode([
                'filename' => $filename,
                'url_count' => count($urls),
                'last_product_id' => $lastProductId,
                'lastmod' => $maxLastModStr,
                'generated_at' => $now->toIso8601String(),
            ])
        );

        self::dispatch(
            $this->runId,
            lastId: $lastProductId,
            force: $this->force,
            store: $this->store,
            separateStores: $this->separateStores,
        );
    }

    private function getNextChunkIndex(): int
    {
        $storePrefix = $this->store ? "-{$this->store}" : '';
        $files = glob(storage_path("app/sitemap_chunks/{$this->runId}{$storePrefix}-*.json"));
        if (empty($files)) {
            return 0;
        }

        $pattern = $this->store
            ? "/{$this->runId}-{$this->store}-(\d+)\.json$/"
            : "/{$this->runId}-(\d+)\.json$/";

        $indices = array_map(function ($file) use ($pattern) {
            preg_match($pattern, $file, $m);
            return (int) ($m[1] ?? -1);
        }, $files);

        return max($indices) + 1;
    }

    private function generateSitemapFile(string $filename, array $urls): void
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

        file_put_contents($path, gzencode($xml, 9));
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

        $metaFiles = glob(storage_path('app/sitemap_chunks/*.json'));
        foreach ($metaFiles as $file) {
            @unlink($file);
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

        Log::error("SitemapGenerator: Chunk job failed for run {$this->runId}: {$exception->getMessage()}");
    }

    private function info(string $message): void
    {
        Log::info("SitemapGenerator: {$message}");
    }
}
