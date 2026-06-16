<?php

namespace App\Services\Sitemap;

use App\Jobs\Sitemap\ProcessSitemapChunkJob;
use App\Models\Product;
use App\Models\SitemapGroup;
use App\Models\SitemapRunLog;
use App\Repositories\SettingsRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapGenerationService
{
    private const string MODE_AUTO = 'auto';

    private const string MODE_OFF = 'off';
    
    private const int URLS_PER_GROUP = 50000;

    public function __construct(
        private SettingsRepository $settings,
    ) {}
    
    public function getPeriodicRebuildEnabled(): bool
    {
        return (bool) $this->settings->get('sitemap.periodic_rebuild_enabled', false);
    }
    
    public function setPeriodicRebuildEnabled(bool $enabled): void
    {
        $this->settings->set('sitemap.periodic_rebuild_enabled', $enabled);
    }
    
    public function getPeriodicRebuildDays(): int
    {
        return max(30, min(90, (int) $this->settings->get('sitemap.periodic_rebuild_days', 75)));
    }
    
    public function setPeriodicRebuildDays(int $days): void
    {
        $this->settings->set('sitemap.periodic_rebuild_days', max(30, min(90, $days)));
    }
    
    public function getLastFullRebuildAt(): ?Carbon
    {
        $timestamp = $this->settings->get('sitemap.last_full_rebuild_at');
        return $timestamp ? Carbon::parse($timestamp) : null;
    }
    
    public function setLastFullRebuildAt(?Carbon $date): void
    {
        $this->settings->set('sitemap.last_full_rebuild_at', $date?->toIso8601String());
    }
    
    public function shouldDoFullRebuild(): bool
    {
        if (!$this->getPeriodicRebuildEnabled()) {
            return false;
        }
        
        $lastRebuild = $this->getLastFullRebuildAt();
        if (!$lastRebuild) {
            return true;
        }
        
        $daysSinceRebuild = $lastRebuild->diffInDays(now());
        return $daysSinceRebuild >= $this->getPeriodicRebuildDays();
    }
    
    public function getCurrentVersion(): string
    {
        return $this->settings->get('sitemap.current_version', 'v1');
    }
    
    public function setCurrentVersion(string $version): void
    {
        $this->settings->set('sitemap.current_version', $version);
    }
    
    public function generateNewVersion(): string
    {
        return 'v' . now()->format('YmdHis');
    }
    
    public function getIncompleteGroup(?string $version = null): ?SitemapGroup
    {
        $version ??= $this->getCurrentVersion();
        
        return SitemapGroup::query()
            ->where('version', $version)
            ->where('is_active', true)
            ->where('is_complete', false)
            ->orderBy('group_index', 'desc')
            ->first();
    }
    
    public function getNextGroupIndex(?string $version = null): int
    {
        $version ??= $this->getCurrentVersion();
        
        $maxIndex = SitemapGroup::query()
            ->where('version', $version)
            ->max('group_index');
            
        return $maxIndex !== null ? $maxIndex + 1 : 0;
    }
    
    public function createNewGroup(string $version, int $groupIndex): SitemapGroup
    {
        $filename = "sitemap-{$version}-g{$groupIndex}.xml.gz";
        
        return SitemapGroup::create([
            'version' => $version,
            'group_index' => $groupIndex,
            'filename' => $filename,
            'url_count' => 0,
            'is_complete' => false,
            'is_active' => true,
            'created_at' => now(),
        ]);
    }

    public function getMode(): string
    {
        $mode = (string) $this->settings->get('sitemap.mode', '');
        if (in_array($mode, [self::MODE_AUTO, self::MODE_OFF], true)) {
            return $mode;
        }

        $executionEnabled = (bool) $this->settings->get('sitemap.execution_enabled', true);

        if (! $executionEnabled) {
            return self::MODE_OFF;
        }

        return self::MODE_AUTO;
    }

    public function setMode(string $mode): void
    {
        if (! in_array($mode, [self::MODE_AUTO, self::MODE_OFF], true)) {
            $mode = self::MODE_AUTO;
        }

        $this->settings->set('sitemap.mode', $mode);
        $this->settings->set('sitemap.execution_enabled', $mode !== self::MODE_OFF);
        $this->settings->set('sitemap.auto_enabled', $mode === self::MODE_AUTO);
    }

    public function isAutoEnabled(): bool
    {
        return $this->getMode() === self::MODE_AUTO;
    }

    public function isExecutionEnabled(): bool
    {
        return $this->getMode() !== self::MODE_OFF;
    }

    public function setExecutionEnabled(bool $enabled): void
    {
        $this->setMode($enabled ? self::MODE_AUTO : self::MODE_OFF);
    }

    public function setAutoEnabled(bool $enabled): void
    {
        $this->setMode($enabled ? self::MODE_AUTO : self::MODE_OFF);
    }

    public function getHourlyRates(): array
    {
        $default = array_fill(0, 24, 2);
        for ($h = 1; $h <= 6; $h++) {
            $default[$h] = 8;
        }

        $rates = $this->settings->get('sitemap.hourly_rates', $default);
        if (! is_array($rates) || count($rates) !== 24) {
            return $default;
        }

        return array_values(array_map(
            fn ($rate) => max(0, min(10, (int) $rate)),
            $rates,
        ));
    }

    public function setHourlyRates(array $rates): void
    {
        $normalized = [];
        for ($h = 0; $h < 24; $h++) {
            $normalized[$h] = max(0, min(10, (int) ($rates[$h] ?? 0)));
        }

        $this->settings->set('sitemap.hourly_rates', $normalized);
    }

    public function getMaxBatchesPerHour(): int
    {
        return max(1, min(3600, (int) $this->settings->get('sitemap.max_batches_per_hour', 60)));
    }

    public function setMaxBatchesPerHour(int $value): void
    {
        $this->settings->set('sitemap.max_batches_per_hour', max(1, min(3600, $value)));
    }

    public function getCurrentRate(?int $hour = null): int
    {
        $hour ??= (int) now()->format('G');

        return $this->getHourlyRates()[$hour] ?? 0;
    }

    public function getBatchesForHour(?int $hour = null): int
    {
        $rate = $this->getCurrentRate($hour);
        if ($rate <= 0) {
            return 0;
        }

        return max(1, (int) floor($this->getMaxBatchesPerHour() * ($rate / 10)));
    }

    public function getDelaySecondsForNextBatch(?int $hour = null): int
    {
        $batches = $this->getBatchesForHour($hour);
        if ($batches <= 0) {
            return 3600;
        }

        return max(1, (int) ceil(3600 / $batches));
    }

    public function shouldStartAutomatically(): bool
    {
        return $this->getMode() === self::MODE_AUTO
            && ! Cache::get('sitemap:running')
            && $this->getCurrentRate() > 0
            && $this->getCachedCounts()['active_products'] > 0;
    }

    public function start(bool $force = false): ?SitemapRunLog
    {
        if ($this->getMode() === self::MODE_OFF || Cache::get('sitemap:running')) {
            return null;
        }

        $separateStores = (bool) $this->settings->get('sitemap.separate_stores', false);
        
        $rebuildType = 'incremental';
        $version = $this->getCurrentVersion();
        
        if ($force || $this->shouldDoFullRebuild()) {
            $rebuildType = 'full';
            $version = $this->generateNewVersion();
            $this->setCurrentVersion($version);
        }
        
        $runId = now()->format('Ymd_His');

        $run = SitemapRunLog::query()->create([
            'run_id' => $runId,
            'version' => $version,
            'status' => 'running',
            'force_mode' => $force,
            'rebuild_type' => $rebuildType,
            'started_at' => now(),
            'total_products' => ($rebuildType === 'full') ? $this->activeProductsCount() : $this->pendingProductsCount(),
            'meta' => [
                'trigger' => 'queue_auto_continuous',
                'batch_size' => ProcessSitemapChunkJob::CHUNK_SIZE,
                'urls_per_gzip' => ProcessSitemapChunkJob::URLS_PER_GZIP,
            ],
        ]);

        Cache::put('sitemap:running', now()->toIso8601String(), 86400);

        ProcessSitemapChunkJob::dispatch(
            $runId,
            $version,
            lastId: null,
            force: ($rebuildType === 'full'),
            store: $separateStores ? 'dk' : '',
            separateStores: $separateStores,
            chunkIndex: 0,
        );

        return $run;
    }

    public function activeProductsCount(): int
    {
        return Product::query()->where('is_active', true)->count();
    }

    public function pendingProductsCount(): int
    {
        return Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('sitemapped_at')
                    ->orWhereColumn('updated_at', '>', 'sitemapped_at');
            })
            ->count();
    }

    public function getCachedCounts(): array
    {
        $cached = $this->settings->get('sitemap.cached_counts', []);
        if (
            is_array($cached)
            && array_key_exists('active_products', $cached)
            && array_key_exists('pending_products', $cached)
            && array_key_exists('updated_at', $cached)
        ) {
            return [
                'active_products' => (int) $cached['active_products'],
                'pending_products' => (int) $cached['pending_products'],
                'updated_at' => (string) $cached['updated_at'],
            ];
        }

        return $this->refreshCachedCounts();
    }

    public function refreshCachedCounts(): array
    {
        $payload = [
            'active_products' => $this->activeProductsCount(),
            'pending_products' => $this->pendingProductsCount(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->settings->set('sitemap.cached_counts', $payload);

        return $payload;
    }

    public function refreshCachedCountsIfDue(int $intervalMinutes = 10): ?array
    {
        $cached = $this->settings->get('sitemap.cached_counts', []);
        $updatedAt = isset($cached['updated_at']) ? Carbon::parse((string) $cached['updated_at']) : null;

        if ($updatedAt && $updatedAt->diffInMinutes(now()) < $intervalMinutes) {
            return null;
        }

        return $this->refreshCachedCounts();
    }

    public function getTailGzip(): ?array
    {
        $tail = $this->settings->get('sitemap.tail_gzip');
        return is_array($tail) ? $tail : null;
    }

    public function setTailGzip(?array $data): void
    {
        $this->settings->set('sitemap.tail_gzip', $data);
    }

    public function clearTailGzip(): void
    {
        $this->settings->set('sitemap.tail_gzip', null);
    }

    public function rebuildSitemapIndex(): void
    {
        $appUrl = rtrim(config('app.url'), '/');
        $files = glob(public_path('sitemaps/sitemap-*.xml.gz'));
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

    public function parseGzipProductIds(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [];
        }

        $content = gzdecode(file_get_contents($filePath));
        if ($content === false) {
            return [];
        }

        $sxml = @simplexml_load_string($content);
        if ($sxml === false) {
            return [];
        }

        $ids = [];

        foreach ($sxml->url as $url) {
            $loc = (string) $url->loc;
            if (preg_match('#/product/(\d+)#', $loc, $m)) {
                $ids[] = (int) $m[1];
            }
        }

        return $ids;
    }

    public function productToUrlData(Product $product): array
    {
        $slug = str_slug_persian($product->title ?? '');
        $lastMod = ($product->updated_at ?? $product->created_at)
            ->setTimezone('UTC')
            ->format('Y-m-d\TH:i:sP');

        return [
            'loc' => config('app.url') . '/product/' . $product->id . ($slug ? '/' . $slug : ''),
            'lastmod' => $lastMod,
        ];
    }

    public function writeGzipFromUrls(string $path, array $urls): void
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
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, gzencode($xml, 9));
    }
    
    public function deactivateOldVersionGroups(string $currentVersion): void
    {
        SitemapGroup::query()
            ->where('version', '!=', $currentVersion)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
    
    public function cleanupOldVersionFiles(string $currentVersion): void
    {
        $oldGroups = SitemapGroup::query()
            ->where('version', '!=', $currentVersion)
            ->where('is_active', false)
            ->get();
            
        foreach ($oldGroups as $group) {
            $path = public_path("sitemaps/{$group->filename}");
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        
        SitemapGroup::query()
            ->where('version', '!=', $currentVersion)
            ->delete();
    }
    
    public function rebuildSitemapIndexForVersion(string $version): void
    {
        $appUrl = rtrim(config('app.url'), '/');
        
        $groups = SitemapGroup::query()
            ->where('version', $version)
            ->where('is_active', true)
            ->where('is_complete', true)
            ->orderBy('group_index')
            ->get();
            
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($groups as $group) {
            $loc = "{$appUrl}/sitemaps/{$group->filename}";
            $lastmod = ($group->completed_at ?? $group->created_at)->setTimezone('UTC')->format('Y-m-d\TH:i:sP');
            
            $xml .= '<sitemap>';
            $xml .= '<loc>' . htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>';
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
            $xml .= '</sitemap>';
        }
        
        $xml .= '</sitemapindex>';
        
        file_put_contents(public_path('sitemap.xml'), $xml);
    }

    public function reset(): ?SitemapRunLog
    {
        foreach (glob(public_path('sitemaps/sitemap-*.xml.gz')) as $f) @unlink($f);
        foreach (glob(storage_path('app/sitemap_chunks/*.json')) as $f) @unlink($f);

        $indexFiles = [
            public_path('sitemap.xml'),
            public_path('sitemap-digikala.xml'),
            public_path('sitemap-basalam.xml'),
            public_path('sitemap-other.xml'),
        ];
        foreach ($indexFiles as $p) {
            if (file_exists($p)) @unlink($p);
        }

        Cache::forget('sitemap:running');

        DB::table('jobs')
            ->where('queue', (string) config('queue.sitemap_queue', 'default'))
            ->where(function ($q) {
                $q->where('payload', 'like', '%Sitemap%');
            })
            ->delete();

        SitemapRunLog::query()
            ->where('status', 'running')
            ->update([
                'status' => 'failed',
                'error_message' => 'بازنشانی توسط مدیر',
                'completed_at' => now(),
            ]);

        $this->refreshCachedCounts();
        $this->clearTailGzip();

        return $this->start();
    }
}
