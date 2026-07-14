<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapEndpointTest extends TestCase
{
    use RefreshDatabase;

    private SitemapGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'sync', 'app.url' => 'https://example.test', 'sitemap.urls_per_shard' => 2, 'sitemap.cache_store' => 'array']);
        Cache::flush();

        $this->service = app(SitemapGenerationService::class);
        $this->service->setMode('auto');
        $this->service->clearRunning();

        Product::query()->insert(collect(range(1, 5))->map(fn ($i) => [
            'id' => (string) $i,
            'store' => 'digikala',
            'title' => 'Product '.$i,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }

    public function test_index_endpoint_returns_xml_sitemap_index(): void
    {
        $this->service->start();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<sitemapindex', false);
        $response->assertSee('https://example.test/product-sitemap1.xml', false);
    }

    public function test_shard_endpoint_returns_url_set(): void
    {
        $this->service->start();

        $response = $this->get('/product-sitemap1.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<urlset', false);
        $response->assertSee('https://example.test/product/1/', false);
    }

    public function test_missing_shard_returns_404(): void
    {
        $this->service->start();

        $this->get('/product-sitemap999.xml')->assertNotFound();
    }

    public function test_index_returns_404_before_any_build(): void
    {
        $this->get('/sitemap.xml')->assertNotFound();
    }

    public function test_shard_route_rejects_non_numeric(): void
    {
        $this->service->start();

        // `/product-sitemapabc.xml` does not match the numeric route constraint.
        $this->get('/product-sitemapabc.xml')->assertNotFound();
    }
}
