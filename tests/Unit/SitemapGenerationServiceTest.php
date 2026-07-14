<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\SitemapRunLog;
use App\Models\SitemapShard;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    private SitemapGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Run the self-chaining build synchronously so a build completes in-process.
        config([
            'queue.default' => 'sync',
            'app.url' => 'https://example.test',
            'sitemap.cache_store' => 'array',
        ]);

        $this->service = app(SitemapGenerationService::class);
        $this->service->setMode('auto');
        $this->service->clearRunning();
        $this->service->clearStop();
        Cache::flush();
    }

    private function makeProducts(int $count, int $startId = 1): void
    {
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = [
                'id' => (string) ($startId + $i),
                'store' => 'digikala',
                'title' => 'Product '.($startId + $i),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Product::query()->insert($rows);
    }

    public function test_build_plans_one_shard_per_10k_urls(): void
    {
        config(['sitemap.urls_per_shard' => 2]);
        $this->makeProducts(5);

        $run = $this->service->start();
        $this->assertNotNull($run);
        $run->refresh();

        $this->assertSame('completed', $run->status);
        $this->assertSame(5, $run->processed_products);

        // 5 products, 2 per shard => 3 shards (2 + 2 + 1).
        $shards = $this->service->activeShards();
        $this->assertCount(3, $shards);
        $this->assertSame([2, 2, 1], $shards->pluck('url_count')->all());
        $this->assertSame([1, 2, 3], $shards->pluck('shard_index')->all());
    }

    public function test_keyset_covers_products_with_mixed_length_numeric_ids(): void
    {
        // Regression: numeric-string ids of differing lengths must be compared
        // lexicographically (not coerced to numbers), or keyset pagination
        // silently skips rows. "5987883" (7 digits) sorts AFTER "29189691".
        config(['sitemap.urls_per_shard' => 3]);
        $ids = ['15181726', '29189691', '47585177', '50765824', '5987883', '1935552', '9012345'];
        Product::query()->insert(collect($ids)->map(fn ($id) => [
            'id' => $id,
            'store' => 'digikala',
            'title' => 'Product '.$id,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $this->service->start();

        $covered = [];
        foreach ($this->service->activeShards() as $shard) {
            $covered = array_merge($covered, Product::query()
                ->whereBetween('id', [(string) $shard->first_product_id, (string) $shard->last_product_id])
                ->pluck('id')->map(fn ($i) => (string) $i)->all());
        }

        sort($ids);
        $covered = array_values(array_unique($covered));
        sort($covered);

        $this->assertSame($ids, $covered, 'Every product must appear in exactly one shard.');
    }

    public function test_shards_cover_all_products_via_keyset_boundaries(): void
    {
        config(['sitemap.urls_per_shard' => 2]);
        $this->makeProducts(5);
        $this->service->start();

        // Reconstruct the covered products from the stored boundaries.
        $covered = 0;
        foreach ($this->service->activeShards() as $shard) {
            $covered += Product::query()
                ->whereBetween('id', [$shard->first_product_id, $shard->last_product_id])
                ->count();
        }

        $this->assertSame(5, $covered);
    }

    public function test_index_xml_lists_every_shard(): void
    {
        config(['sitemap.urls_per_shard' => 2]);
        $this->makeProducts(5);
        $this->service->start();

        $xml = $this->service->renderIndex();

        $this->assertStringContainsString('<sitemapindex', $xml);
        $this->assertSame(3, substr_count($xml, '<sitemap>'));
        $this->assertStringContainsString('https://example.test/product-sitemap1.xml', $xml);
        $this->assertStringContainsString('https://example.test/product-sitemap3.xml', $xml);
    }

    public function test_shard_xml_contains_product_urls(): void
    {
        config(['sitemap.urls_per_shard' => 100]);
        $this->makeProducts(3);
        $this->service->start();

        $xml = $this->service->renderShard(1);

        $this->assertNotNull($xml);
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertSame(3, substr_count($xml, '<url>'));
        $this->assertStringContainsString('https://example.test/product/1/', $xml);
        $this->assertStringContainsString('<lastmod>', $xml);
    }

    public function test_render_shard_returns_null_for_missing_shard(): void
    {
        config(['sitemap.urls_per_shard' => 100]);
        $this->makeProducts(3);
        $this->service->start();

        $this->assertNull($this->service->renderShard(99));
    }

    public function test_rebuild_swaps_generation_atomically_and_prunes_old(): void
    {
        config(['sitemap.urls_per_shard' => 100]);
        $this->makeProducts(2);

        $this->service->start();
        $this->assertSame(1, $this->service->activeGeneration());
        $this->assertCount(1, $this->service->activeShards());

        // Add products and rebuild — generation advances, old rows pruned.
        $this->makeProducts(2, startId: 100);
        $this->service->clearRunning();
        $this->service->start();

        $this->assertSame(2, $this->service->activeGeneration());
        // Only the new generation's shards remain in the table.
        $this->assertSame(0, SitemapShard::query()->where('generation', 1)->count());
        $this->assertSame(4, $this->service->activeShards()->sum('url_count'));
    }

    public function test_no_sitemap_before_first_build(): void
    {
        $this->makeProducts(3);

        $this->assertFalse($this->service->hasSitemap());
        $this->assertSame(0, $this->service->activeGeneration());
    }

    public function test_does_not_start_when_mode_is_off(): void
    {
        $this->makeProducts(3);
        $this->service->setMode('off');

        $this->assertNull($this->service->start());
        $this->assertSame(0, SitemapRunLog::count());
    }

    public function test_auto_only_runs_when_due(): void
    {
        $this->makeProducts(2);

        // Never built => due.
        $first = $this->service->startAuto();
        $this->assertNotNull($first);
        $this->service->clearRunning();

        // Just built with a long interval => not due.
        $this->service->setRebuildIntervalHours(720);
        $this->assertNull($this->service->startAuto());
    }

    public function test_reset_wipes_everything(): void
    {
        config(['sitemap.urls_per_shard' => 100]);
        $this->makeProducts(3);
        $this->service->start();
        $this->assertTrue($this->service->hasSitemap());

        $this->service->reset();

        $this->assertFalse($this->service->hasSitemap());
        $this->assertSame(0, $this->service->activeGeneration());
        $this->assertSame(0, SitemapShard::query()->count());
    }

    public function test_build_spans_multiple_passes(): void
    {
        // Small pass budget forces the job to re-dispatch across shards.
        config(['sitemap.urls_per_shard' => 2, 'sitemap.products_per_pass' => 2]);
        $this->makeProducts(7);

        $run = $this->service->start();
        $run->refresh();

        $this->assertSame('completed', $run->status);
        $this->assertSame(7, $run->processed_products);
        // 7 products, 2 per shard => 4 shards (2+2+2+1).
        $this->assertCount(4, $this->service->activeShards());
    }
}
