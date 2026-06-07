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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompressSitemapGroupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        protected string $runId,
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

        if (!$this->compressGroup()) {
            $this->continueOrFinish();
            return;
        }

        $this->updateSitemapIndex();

        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'total_chunks' => DB::raw('total_chunks + 1'),
            ]);

        $this->continueOrFinish();
    }

    private function compressGroup(): bool
    {
        $storePrefix = $this->store ? "-{$this->store}" : '';
        $pattern = public_path("sitemaps/sitemap-{$this->runId}{$storePrefix}-g{$this->groupIndex}-*.xml");
        $files = glob($pattern);
        sort($files);

        if (empty($files)) {
            $this->info("No plain XML files found for group {$this->groupIndex}");
            return false;
        }

        $allUrls = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $sxml = simplexml_load_string($content);
            if ($sxml === false) {
                $this->info("Failed to parse XML: {$file}");
                continue;
            }
            foreach ($sxml->url as $url) {
                $allUrls[] = [
                    'loc' => (string) $url->loc,
                    'lastmod' => (string) $url->lastmod,
                ];
            }
            unset($sxml);
        }

        $urlCount = count($allUrls);
        $gzFilename = "sitemap-{$this->runId}{$storePrefix}-g{$this->groupIndex}.xml.gz";
        $this->generateGzippedSitemap($gzFilename, $allUrls);

        foreach ($files as $file) {
            @unlink($file);
        }

        $metaPattern = storage_path("app/sitemap_chunks/{$this->runId}{$storePrefix}-g{$this->groupIndex}-*.json");
        foreach (glob($metaPattern) as $metaFile) {
            @unlink($metaFile);
        }

        if ($urlCount < ProcessSitemapChunkJob::URLS_PER_GZIP) {
            app(SitemapGenerationService::class)->setTailGzip([
                'filename' => $gzFilename,
                'run_id' => $this->runId,
                'group' => $this->groupIndex,
                'url_count' => $urlCount,
                'last_product_id' => $this->lastId,
            ]);
        }

        $this->info("Compressed group {$this->groupIndex}: {$urlCount} URLs → {$gzFilename}");

        return true;
    }

    private function generateGzippedSitemap(string $filename, array $urls): void
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

        $path = public_path("sitemaps/{$filename}");
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, gzencode($xml, 9));
    }

    private function updateSitemapIndex(): void
    {
        if ($this->separateStores) {
            $this->updateStoreSubIndex();
            $this->updateMainIndex();
        } else {
            $this->rebuildFlatIndex();
        }
    }

    private function rebuildFlatIndex(): void
    {
        $appUrl = rtrim(config('app.url'), '/');
        $files = glob(public_path("sitemaps/sitemap-{$this->runId}-g*.xml.gz"));
        sort($files);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($files as $file) {
            $filename = basename($file);
            $lastmod = gmdate('Y-m-d\TH:i:sP', filemtime($file));
            $loc = "{$appUrl}/sitemaps/{$filename}";

            $xml .= '<sitemap>';
            $xml .= '<loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        file_put_contents(public_path('sitemap.xml'), $xml);
    }

    private function updateStoreSubIndex(): void
    {
        $appUrl = rtrim(config('app.url'), '/');
        $storePrefix = $this->store ? "-{$this->store}" : '';
        $files = glob(public_path("sitemaps/sitemap-{$this->runId}{$storePrefix}-g*.xml.gz"));
        sort($files);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($files as $file) {
            $filename = basename($file);
            $lastmod = gmdate('Y-m-d\TH:i:sP', filemtime($file));
            $loc = "{$appUrl}/sitemaps/{$filename}";

            $xml .= '<sitemap>';
            $xml .= '<loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        $outFile = match ($this->store) {
            'dk' => 'sitemap-digikala.xml',
            'bs' => 'sitemap-basalam.xml',
            default => 'sitemap-other.xml',
        };

        file_put_contents(public_path($outFile), $xml);
    }

    private function updateMainIndex(): void
    {
        $appUrl = rtrim(config('app.url'), '/');
        $subIndexes = [];

        foreach (['sitemap-digikala.xml', 'sitemap-basalam.xml', 'sitemap-other.xml'] as $subFile) {
            if (file_exists(public_path($subFile))) {
                $subIndexes[] = $subFile;
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($subIndexes as $indexFile) {
            $filename = basename($indexFile);
            $lastmod = gmdate('Y-m-d\TH:i:sP', filemtime(public_path($indexFile)));
            $loc = "{$appUrl}/{$filename}";

            $xml .= '<sitemap>';
            $xml .= '<loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        file_put_contents(public_path('sitemap.xml'), $xml);
    }

    private function continueOrFinish(): void
    {
        if (!$this->storeExhausted) {
            $nextChunkIndex = ($this->groupIndex + 1) * ProcessSitemapChunkJob::MAX_CHUNKS_PER_GROUP;
            ProcessSitemapChunkJob::dispatch(
                $this->runId,
                lastId: $this->lastId,
                force: $this->force,
                store: $this->store,
                separateStores: $this->separateStores,
                chunkIndex: $nextChunkIndex,
            )->delay(now()->addSeconds(app(SitemapGenerationService::class)->getDelaySecondsForNextBatch()));
        } elseif ($this->store === 'dk' && $this->separateStores) {
            ProcessSitemapChunkJob::dispatch(
                $this->runId,
                lastId: null,
                force: $this->force,
                store: 'bs',
                separateStores: true,
                chunkIndex: 0,
            )->delay(now()->addSeconds(app(SitemapGenerationService::class)->getDelaySecondsForNextBatch()));
        } elseif ($this->store === 'bs' && $this->separateStores) {
            ProcessSitemapChunkJob::dispatch(
                $this->runId,
                lastId: null,
                force: $this->force,
                store: 'other',
                separateStores: true,
                chunkIndex: 0,
            )->delay(now()->addSeconds(app(SitemapGenerationService::class)->getDelaySecondsForNextBatch()));
        } else {
            $this->pruneOldLogs();
            ContinueSitemapRunJob::dispatch(
                $this->runId,
                lastId: $this->lastId,
                force: $this->force,
                separateStores: $this->separateStores,
            )->delay(now()->addMinutes(5));
        }
    }

    private function pruneOldLogs(): void
    {
        $keepIds = SitemapRunLog::query()
            ->latest('id')
            ->limit(15)
            ->pluck('id');

        if ($keepIds->isNotEmpty()) {
            SitemapRunLog::query()
                ->whereNotIn('id', $keepIds)
                ->delete();
        }

        $this->info('Pruned sitemap logs, keeping only the 15 most recent.');
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
