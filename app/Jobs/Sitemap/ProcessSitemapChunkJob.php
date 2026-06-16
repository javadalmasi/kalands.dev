<?php

namespace App\Jobs\Sitemap;

use App\Models\Product;
use App\Models\SitemapGroup;
use App\Models\SitemapRunLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Sitemap\SitemapGenerationService;

class ProcessSitemapChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 5;
    public array $backoff = [10, 30, 60, 120];

    public const int CHUNK_SIZE = 1000;
    public const int URLS_PER_GROUP = 50000;
    private const int BATCH_SIZE = 1000;

    public function __construct(
        protected string $runId,
        protected string $version,
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

        if ($products->isEmpty()) {
            $this->handleEmptyProducts($sitemapService);
            return;
        }
        
        $currentGroup = $sitemapService->getIncompleteGroup($this->version);
        
        if (!$currentGroup || !$currentGroup->hasSpace()) {
            if ($currentGroup) {
                $this->finalizeGroup($sitemapService, $currentGroup);
            }
            
            $newGroupIndex = $sitemapService->getNextGroupIndex($this->version);
            $currentGroup = $sitemapService->createNewGroup($this->version, $newGroupIndex);
        }
        
        $urls = [];
        $maxLastMod = null;
        $productIds = [];
        $availableSpace = $currentGroup->remainingCapacity();
        $productsToProcess = $products->take($availableSpace);

        foreach ($productsToProcess as $product) {
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
        
        $this->appendToGroupFile($currentGroup, $urls);

        $now = now();
        $lastProductId = $productsToProcess->last()->id;
        
        if ($currentGroup->first_product_id === null) {
            $currentGroup->first_product_id = $productsToProcess->first()->id;
        }
        $currentGroup->last_product_id = $lastProductId;
        $currentGroup->url_count += count($urls);
        $currentGroup->save();

        collect($productIds)
            ->chunk(self::BATCH_SIZE)
            ->each(function ($ids) use ($now) {
                Product::query()
                    ->whereIn('id', $ids)
                    ->update(['sitemapped_at' => $now]);
            });

        $processedCount = count($productIds);

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'processed_products' => DB::raw('processed_products + '.(int) $processedCount),
            ]);
        
        if ($currentGroup->url_count >= self::URLS_PER_GROUP) {
            $this->finalizeGroup($sitemapService, $currentGroup);
        }
        
        $hasMoreProducts = $products->count() >= self::CHUNK_SIZE;
        
        if ($hasMoreProducts) {
            self::dispatch(
                $this->runId,
                $this->version,
                lastId: $lastProductId,
                force: $this->force,
                store: $this->store,
                separateStores: $this->separateStores,
                chunkIndex: $this->chunkIndex + 1,
            )->delay(now()->addSeconds($sitemapService->getDelaySecondsForNextBatch()));
        } else {
            if ($currentGroup && !$currentGroup->is_complete) {
                $this->finalizeGroup($sitemapService, $currentGroup);
            }
            
            $this->handleCompletion($sitemapService);
        }
    }

    private function appendToGroupFile(SitemapGroup $group, array $newUrls): void
    {
        $path = public_path("sitemaps/{$group->filename}");
        
        $existingUrls = [];
        if (file_exists($path)) {
            $existingUrls = $this->parseGzipUrls($path);
        }
        
        $allUrls = array_merge($existingUrls, $newUrls);
        
        $this->writeGzipFile($path, $allUrls);
    }
    
    private function parseGzipUrls(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }
        
        $content = gzdecode(file_get_contents($path));
        if ($content === false) {
            return [];
        }
        
        $sxml = @simplexml_load_string($content);
        if ($sxml === false) {
            return [];
        }
        
        $urls = [];
        foreach ($sxml->url as $url) {
            $urls[] = [
                'loc' => (string) $url->loc,
                'lastmod' => (string) $url->lastmod,
            ];
        }
        
        return $urls;
    }
    
    private function writeGzipFile(string $path, array $urls): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            $xml .= '<lastmod>' . $url['lastmod'] . '</lastmod>';
            $xml .= '<changefreq>daily</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($path, gzencode($xml, 9));
    }
    
    private function finalizeGroup(SitemapGenerationService $sitemapService, SitemapGroup $group): void
    {
        $group->is_complete = true;
        $group->completed_at = now();
        $group->save();
        
        $sitemapService->rebuildSitemapIndexForVersion($this->version);
        
        $this->info("Group {$group->group_index} finalized with {$group->url_count} URLs");
    }
    
    private function handleEmptyProducts(SitemapGenerationService $sitemapService): void
    {
        $currentGroup = $sitemapService->getIncompleteGroup($this->version);
        if ($currentGroup && $currentGroup->url_count > 0) {
            $this->finalizeGroup($sitemapService, $currentGroup);
        }
        
        if ($this->store === 'dk' && $this->separateStores) {
            self::dispatch(
                $this->runId,
                $this->version,
                lastId: null,
                force: $this->force,
                store: 'bs',
                separateStores: true,
                chunkIndex: 0,
            );
        } elseif ($this->store === 'bs' && $this->separateStores) {
            self::dispatch(
                $this->runId,
                $this->version,
                lastId: null,
                force: $this->force,
                store: 'other',
                separateStores: true,
                chunkIndex: 0,
            );
        } else {
            $this->handleCompletion($sitemapService);
        }
    }
    
    private function handleCompletion(SitemapGenerationService $sitemapService): void
    {
        $run = SitemapRunLog::where('run_id', $this->runId)->first();
        
        if ($run && $run->rebuild_type === 'full') {
            $sitemapService->deactivateOldVersionGroups($this->version);
            $sitemapService->cleanupOldVersionFiles($this->version);
            $sitemapService->setLastFullRebuildAt(now());
        }
        
        FinalizeSitemapJob::dispatch(
            $this->runId,
            force: $this->force,
            separateStores: $this->separateStores,
        );
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
