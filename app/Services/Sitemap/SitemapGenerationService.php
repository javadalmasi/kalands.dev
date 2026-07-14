<?php

namespace App\Services\Sitemap;

use App\Jobs\Sitemap\SitemapBuildJob;
use App\Models\Product;
use App\Models\SitemapRunLog;
use App\Models\SitemapShard;
use App\Repositories\SettingsRepository;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Dynamic, Yoast-style sitemap generator.
 *
 * No files are written to disk. A build only computes lightweight shard
 * metadata (keyset boundaries) into the `sitemap_shards` table; the XML for the
 * index and each product sub-sitemap is rendered on demand from those
 * boundaries and cached. Every shard read is an indexed
 * `id BETWEEN first AND last` range scan — never a slow OFFSET — so it scales to
 * millions of products.
 *
 * Rebuilds are generation-based: a new generation is planned alongside the live
 * one, then the active-generation pointer flips atomically with zero downtime,
 * and old generations are pruned.
 */
class SitemapGenerationService
{
    private const RUNNING_KEY = 'sitemap:running';

    private const STOP_KEY = 'sitemap:stop';

    private const GENERATION_KEY = 'sitemap.active_generation';

    private const COUNTS_KEY = 'sitemap.cached_counts';

    private const CACHE_TAG = 'sitemap.xml';

    public function __construct(
        private readonly SettingsRepository $settings,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    public function urlsPerShard(): int
    {
        return max(1, (int) config('sitemap.urls_per_shard', 10_000));
    }

    /**
     * How many products a single build pass scans before re-dispatching. Keeps
     * each pass comfortably under the queue timeout on huge catalogs.
     */
    public function productsPerPass(): int
    {
        return max($this->urlsPerShard(), (int) config('sitemap.products_per_pass', 100_000));
    }

    public function cacheTtl(): int
    {
        return max(60, (int) config('sitemap.cache_ttl', 21_600));
    }

    public function queue(): string
    {
        return (string) config('sitemap.queue', 'default');
    }

    /**
     * The module's dedicated cache store. Defaults to `file` so a Redis
     * FLUSHALL never wipes the rendered sitemap.
     */
    private function cache(): Repository
    {
        return Cache::store(config('sitemap.cache_store', 'file'));
    }

    /*
    |--------------------------------------------------------------------------
    | Mode (auto / off)
    |--------------------------------------------------------------------------
    */

    public function getMode(): string
    {
        return $this->settings->get('sitemap.mode', 'auto') === 'off' ? 'off' : 'auto';
    }

    public function setMode(string $mode): void
    {
        $this->settings->set('sitemap.mode', $mode === 'off' ? 'off' : 'auto');
    }

    public function isAutoEnabled(): bool
    {
        return $this->getMode() === 'auto';
    }

    /*
    |--------------------------------------------------------------------------
    | Rebuild scheduling
    |--------------------------------------------------------------------------
    */

    public function rebuildIntervalHours(): int
    {
        return max(1, (int) $this->settings->get('sitemap.rebuild_interval_hours', config('sitemap.rebuild_interval_hours', 24)));
    }

    public function setRebuildIntervalHours(int $hours): void
    {
        $this->settings->set('sitemap.rebuild_interval_hours', max(1, min(720, $hours)));
    }

    public function lastBuildAt(): ?Carbon
    {
        $value = $this->settings->get('sitemap.last_build_at');

        return $value ? Carbon::parse($value) : null;
    }

    public function setLastBuildAt(Carbon $when): void
    {
        $this->settings->set('sitemap.last_build_at', $when->toIso8601String());
    }

    public function isRebuildDue(): bool
    {
        $last = $this->lastBuildAt();

        return $last === null || $last->diffInHours(now()) >= $this->rebuildIntervalHours();
    }

    /*
    |--------------------------------------------------------------------------
    | Active generation pointer
    |--------------------------------------------------------------------------
    */

    public function activeGeneration(): int
    {
        return (int) $this->settings->get(self::GENERATION_KEY, 0);
    }

    private function setActiveGeneration(int $generation): void
    {
        $this->settings->set(self::GENERATION_KEY, $generation);
    }

    /**
     * Publish a freshly-planned generation: flip the pointer, drop the render
     * cache, prune superseded generations.
     */
    public function activateGeneration(int $generation): void
    {
        $this->setActiveGeneration($generation);
        $this->flushCache();
        $this->setLastBuildAt(now());

        SitemapShard::query()->where('generation', '!=', $generation)->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Run lock & stop signal
    |--------------------------------------------------------------------------
    */

    public function isRunning(): bool
    {
        return (bool) $this->cache()->get(self::RUNNING_KEY);
    }

    public function markRunning(): void
    {
        $this->cache()->put(self::RUNNING_KEY, now()->toIso8601String(), now()->addDay());
    }

    public function clearRunning(): void
    {
        $this->cache()->forget(self::RUNNING_KEY);
    }

    public function requestStop(): void
    {
        $this->cache()->put(self::STOP_KEY, true, now()->addDay());
    }

    public function stopRequested(): bool
    {
        return (bool) $this->cache()->get(self::STOP_KEY);
    }

    public function clearStop(): void
    {
        $this->cache()->forget(self::STOP_KEY);
    }

    /*
    |--------------------------------------------------------------------------
    | Product counts (cached for the dashboard)
    |--------------------------------------------------------------------------
    */

    public function activeCount(): int
    {
        return Product::query()->where('is_active', true)->count();
    }

    /**
     * @return array{active:int, updated_at:string}
     */
    public function cachedCounts(): array
    {
        $cached = $this->settings->get(self::COUNTS_KEY);

        if (is_array($cached) && isset($cached['active'], $cached['updated_at'])) {
            return [
                'active' => (int) $cached['active'],
                'updated_at' => (string) $cached['updated_at'],
            ];
        }

        return $this->refreshCounts();
    }

    /**
     * @return array{active:int, updated_at:string}
     */
    public function refreshCounts(): array
    {
        $payload = [
            'active' => $this->activeCount(),
            'updated_at' => now()->toIso8601String(),
        ];

        $this->settings->set(self::COUNTS_KEY, $payload);

        return $payload;
    }

    /**
     * @return array{active:int, updated_at:string}|null
     */
    public function refreshCountsIfDue(int $minutes = 10): ?array
    {
        $cached = $this->settings->get(self::COUNTS_KEY);
        $updatedAt = isset($cached['updated_at']) ? Carbon::parse((string) $cached['updated_at']) : null;

        if ($updatedAt && $updatedAt->diffInMinutes(now()) < $minutes) {
            return null;
        }

        return $this->refreshCounts();
    }

    /*
    |--------------------------------------------------------------------------
    | Starting a build
    |--------------------------------------------------------------------------
    */

    public function start(): ?SitemapRunLog
    {
        if ($this->getMode() === 'off' || $this->isRunning()) {
            return null;
        }

        $this->clearStop();

        // Plan into the next generation, leaving the current live one untouched.
        $generation = $this->activeGeneration() + 1;

        // Clear any stale half-planned rows for this generation number.
        SitemapShard::query()->where('generation', $generation)->delete();

        $run = SitemapRunLog::query()->create([
            'run_id' => now()->format('Ymd_His').'_'.substr(bin2hex(random_bytes(3)), 0, 5),
            'mode' => 'rebuild',
            'status' => 'running',
            'total_products' => $this->activeCount(),
            'processed_products' => 0,
            'started_at' => now(),
            'meta' => ['generation' => $generation],
        ]);

        $this->markRunning();

        SitemapBuildJob::dispatch($run->run_id, $generation);

        return $run;
    }

    public function startAuto(): ?SitemapRunLog
    {
        if ($this->getMode() === 'off' || $this->isRunning() || ! $this->isRebuildDue()) {
            return null;
        }

        return $this->start();
    }

    /*
    |--------------------------------------------------------------------------
    | Shard planning (called by the build job, one pass at a time)
    |--------------------------------------------------------------------------
    */

    /**
     * Plan the next batch of shards for a generation, starting after the given
     * product id. Returns the last product id consumed (cursor for the next
     * pass) and whether more products remain.
     *
     * @return array{cursor:?string, exhausted:bool, planned:int, processed:int}
     */
    public function planPass(int $generation, ?string $cursor, int $shardIndexStart): array
    {
        $perShard = $this->urlsPerShard();
        $budget = $this->productsPerPass();
        $shardIndex = $shardIndexStart;
        $planned = 0;
        $processed = 0;
        $lastId = $cursor;

        while ($processed < $budget) {
            // Bind the cursor as a string so the varchar id is compared
            // lexicographically — matching ORDER BY id. An int-bound cursor makes
            // MySQL coerce the column to a number, which is inconsistent with the
            // ordering and would silently skip rows (e.g. "5987883" vs "29189691").
            $rows = Product::query()
                ->select(['id', 'updated_at', 'created_at'])
                ->where('is_active', true)
                ->when($lastId !== null, fn ($q) => $q->where('id', '>', (string) $lastId))
                ->orderBy('id')
                ->limit($perShard)
                ->get();

            if ($rows->isEmpty()) {
                return ['cursor' => $lastId, 'exhausted' => true, 'planned' => $planned, 'processed' => $processed];
            }

            $lastmod = $rows
                ->map(fn ($p) => $p->updated_at ?? $p->created_at)
                ->filter()
                ->max();

            SitemapShard::query()->create([
                'generation' => $generation,
                'shard_index' => $shardIndex,
                'first_product_id' => (string) $rows->first()->id,
                'last_product_id' => (string) $rows->last()->id,
                'url_count' => $rows->count(),
                'lastmod' => $lastmod,
            ]);

            $shardIndex++;
            $planned++;
            $processed += $rows->count();
            $lastId = (string) $rows->last()->id;

            // A short final shard means the catalog is exhausted.
            if ($rows->count() < $perShard) {
                return ['cursor' => $lastId, 'exhausted' => true, 'planned' => $planned, 'processed' => $processed];
            }
        }

        return ['cursor' => $lastId, 'exhausted' => false, 'planned' => $planned, 'processed' => $processed];
    }

    /*
    |--------------------------------------------------------------------------
    | XML rendering (on demand, cached)
    |--------------------------------------------------------------------------
    */

    public function hasSitemap(): bool
    {
        return SitemapShard::query()->where('generation', $this->activeGeneration())->exists();
    }

    /**
     * @return Collection<int, SitemapShard>
     */
    public function activeShards()
    {
        return SitemapShard::query()
            ->where('generation', $this->activeGeneration())
            ->orderBy('shard_index')
            ->get();
    }

    public function shardCount(): int
    {
        return SitemapShard::query()->where('generation', $this->activeGeneration())->count();
    }

    /**
     * Render the sitemap index (/sitemap.xml).
     */
    public function renderIndex(): string
    {
        $generation = $this->activeGeneration();

        return $this->cache()->remember(
            self::CACHE_TAG.":index:{$generation}",
            $this->cacheTtl(),
            fn () => $this->buildIndexXml(),
        );
    }

    /**
     * Render a single product sub-sitemap (/product-sitemap{n}.xml), or null if
     * the shard does not exist.
     */
    public function renderShard(int $shardIndex): ?string
    {
        $generation = $this->activeGeneration();

        return $this->cache()->remember(
            self::CACHE_TAG.":shard:{$generation}:{$shardIndex}",
            $this->cacheTtl(),
            function () use ($generation, $shardIndex): ?string {
                $shard = SitemapShard::query()
                    ->where('generation', $generation)
                    ->where('shard_index', $shardIndex)
                    ->first();

                return $shard ? $this->buildShardXml($shard) : null;
            },
        );
    }

    private function buildIndexXml(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($this->activeShards() as $shard) {
            $loc = $appUrl.'/product-sitemap'.$shard->shard_index.'.xml';
            $lastmod = ($shard->lastmod ?? now())->setTimezone('UTC')->format('Y-m-d\TH:i:sP');

            $xml .= '  <sitemap>'."\n";
            $xml .= '    <loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>'."\n";
            $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
            $xml .= '  </sitemap>'."\n";
        }

        $xml .= '</sitemapindex>'."\n";

        return $xml;
    }

    private function buildShardXml(SitemapShard $shard): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        Product::query()
            ->select(['id', 'title', 'updated_at', 'created_at'])
            ->where('is_active', true)
            // String-bind the boundaries so the range scan matches the
            // lexicographic ordering the shards were planned with.
            ->whereBetween('id', [(string) $shard->first_product_id, (string) $shard->last_product_id])
            ->orderBy('id')
            ->chunk(2_000, function ($products) use (&$xml, $appUrl): void {
                foreach ($products as $product) {
                    $slug = str_slug_persian((string) ($product->title ?? ''));
                    $loc = $appUrl.'/product/'.$product->id.($slug !== '' ? '/'.$slug : '');
                    $lastmod = ($product->updated_at ?? $product->created_at ?? now())
                        ->setTimezone('UTC')->format('Y-m-d\TH:i:sP');

                    $xml .= '  <url>'."\n";
                    $xml .= '    <loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>'."\n";
                    $xml .= '    <lastmod>'.$lastmod.'</lastmod>'."\n";
                    $xml .= '    <changefreq>weekly</changefreq>'."\n";
                    $xml .= '    <priority>0.8</priority>'."\n";
                    $xml .= '  </url>'."\n";
                }
            });

        $xml .= '</urlset>'."\n";

        return $xml;
    }

    /*
    |--------------------------------------------------------------------------
    | Cache & reset
    |--------------------------------------------------------------------------
    */

    /**
     * Drop rendered XML for every generation. Cheap: keys are namespaced by
     * generation, and stale ones expire on their own — but on a store that
     * supports flushing we clear immediately.
     */
    public function flushCache(): void
    {
        // Rendered entries are keyed by generation, so bumping the pointer makes
        // old ones unreachable. We also forget the current index eagerly so a
        // manual rebuild is reflected without waiting for TTL.
        for ($g = max(0, $this->activeGeneration() - 1); $g <= $this->activeGeneration() + 1; $g++) {
            $this->cache()->forget(self::CACHE_TAG.":index:{$g}");
        }
    }

    /**
     * Wipe all sitemap state (shards, pointer, cache, run lock).
     */
    public function reset(): void
    {
        $this->requestStop();
        $this->clearRunning();

        SitemapShard::query()->delete();
        $this->setActiveGeneration(0);
        $this->settings->forget('sitemap.last_build_at');
        $this->flushCache();
        $this->clearStop();
        $this->refreshCounts();
    }

    /**
     * @return array{shard_count:int, active_generation:int, has_sitemap:bool, total_urls:int}
     */
    public function stats(): array
    {
        $shards = $this->activeShards();

        return [
            'shard_count' => $shards->count(),
            'active_generation' => $this->activeGeneration(),
            'has_sitemap' => $shards->isNotEmpty(),
            'total_urls' => (int) $shards->sum('url_count'),
        ];
    }
}
