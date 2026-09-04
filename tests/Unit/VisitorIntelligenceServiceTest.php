<?php

namespace Tests\Unit;

use App\Repositories\SettingsRepository;
use App\Services\VisitorIntelligenceService;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class VisitorIntelligenceServiceTest extends TestCase
{
    public function test_get_config_returns_defaults_when_empty()
    {
        $settings = Mockery::mock(SettingsRepository::class);
        $settings->shouldReceive('get')->once()->andReturn([
            'robots_pattern' => 'default',
            'trusted_asns' => [],
        ]);

        Cache::shouldReceive('rememberForever')
            ->once()
            ->with('visitor_intelligence:config', Mockery::type('Closure'))
            ->andReturnUsing(function ($key, $callback) {
                return $callback();
            });

        $service = new VisitorIntelligenceService($settings);
        $config = $service->getConfig();

        $this->assertEquals('default', $config['robots_pattern']);
        $this->assertEquals([], $config['trusted_asns']);
    }

    public function test_save_config_clears_cache()
    {
        $settings = Mockery::mock(SettingsRepository::class);
        $settings->shouldReceive('set')->once();

        Cache::shouldReceive('forget')->once()->with('visitor_intelligence:config');

        $service = new VisitorIntelligenceService($settings);
        $service->saveConfig(['robots_pattern' => 'test', 'trusted_asns' => [123]]);
        $this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
