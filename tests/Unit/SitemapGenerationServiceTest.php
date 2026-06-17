<?php

namespace Tests\Unit;

use App\Models\SitemapGroup;
use App\Repositories\SettingsRepository;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SitemapGenerationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_incremental_run_waits_until_incomplete_group_can_reach_fifty_thousand_urls(): void
    {
        $settings = app(SettingsRepository::class);
        $settings->set('sitemap.mode', 'auto');
        $settings->set('sitemap.hourly_rates', array_fill(0, 24, 10));
        $settings->set('sitemap.current_version', 'v1');

        SitemapGroup::query()->create([
            'version' => 'v1',
            'group_index' => 0,
            'filename' => 'sitemap-v1-g0.xml.gz',
            'url_count' => 49000,
            'last_product_id' => 49000,
            'is_complete' => false,
            'is_active' => true,
            'created_at' => now(),
        ]);

        $this->insertProducts(1, 999);

        $service = app(SitemapGenerationService::class);

        Cache::forget('sitemap:running');
        $service->refreshCachedCounts();

        $this->assertSame(1000, $service->pendingProductsNeededForNextGroup('v1'));
        $this->assertFalse($service->shouldStartAutomatically());

        $this->insertProducts(1000, 1000);

        $service->refreshCachedCounts();

        $this->assertTrue($service->shouldStartAutomatically());
    }

    private function insertProducts(int $from, int $to): void
    {
        $now = now();
        $rows = [];

        for ($id = $from; $id <= $to; $id++) {
            $rows[] = [
                'id' => (string) (49000 + $id),
                'store' => 'digikala',
                'title' => "Product {$id}",
                'is_active' => true,
                'sitemapped_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 250) as $chunk) {
            DB::table('products')->insert($chunk);
        }
    }
}
