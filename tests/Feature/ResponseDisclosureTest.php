<?php

namespace Tests\Feature;

use App\Services\Communication\ChannelSettingsResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Mechanisms\HandleRequests\EndpointResolver;
use RuntimeException;
use Tests\TestCase;

class ResponseDisclosureTest extends TestCase
{
    use RefreshDatabase;

    public function test_obsolete_livewire_asset_proxy_is_not_registered(): void
    {
        $this->assertNull(config('livewire.asset_url'));
        $this->assertNull(collect(app('router')->getRoutes()->getRoutes())
            ->first(fn ($route) => $route->getName() === 'livewire.asset'));

        $this->get('/api/assets/js')->assertNotFound();
        $this->assertFileExists(public_path('vendor/livewire/livewire.min.js'));
        $scriptResponse = $this->get(EndpointResolver::scriptPath(minified: (bool) ! config('app.debug')));
        $scriptResponse->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertHeader('Cache-Control', 'public, max-age=31536000');
        $this->get(EndpointResolver::scriptPath(minified: (bool) ! config('app.debug')), [
            'If-Modified-Since' => $scriptResponse->headers->get('Last-Modified'),
        ])->assertStatus(304);
        $this->get(EndpointResolver::mapPath())->assertNotFound();
        $this->get(EndpointResolver::mapPath(csp: true))->assertNotFound();
        $this->assertFileDoesNotExist(public_path('vendor/livewire/livewire.min.js.map'));
        $this->assertStringNotContainsString('<?php', $this->get(EndpointResolver::scriptPath(minified: true))->getContent());
        $this->assertSame('/api/services/update', config('livewire.update_route_path'));
        $this->assertFileDoesNotExist(public_path('vendor/livewire/app-core.js'));
        $this->assertFileDoesNotExist(public_path('vendor/livewire/app-core.min.js'));
        $this->assertFileDoesNotExist(public_path('vendor/livewire/livewire.min.js.map'));
        $this->assertFileDoesNotExist(public_path('vendor/livewire/livewire.csp.min.js.map'));
        $this->assertFileDoesNotExist(public_path('vendor/livewire/livewire.esm.js.map'));
        $this->assertFileDoesNotExist(public_path('vendor/livewire/livewire.csp.esm.js.map'));
    }

    public function test_mail_test_failure_does_not_return_exception_details(): void
    {
        $this->withoutMiddleware();
        config(['mail.default' => 'smtp']);
        $this->mock(ChannelSettingsResolver::class, function ($mock): void {
            $mock->shouldReceive('applyMailConfig')
                ->once()
                ->andThrow(new RuntimeException('private SMTP host and stack trace'));
        });

        $response = $this->postJson('/dash/admin/test-key/mail-config/test', [
            'to' => 'recipient@example.test',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('ok', false)
            ->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('exception');
        $response->assertDontSee('private SMTP host and stack trace');
    }

    public function test_sms_test_failure_does_not_return_upstream_response_body(): void
    {
        $this->withoutMiddleware();
        config(['services.melipayamak.key' => 'test-token']);
        $this->mock(ChannelSettingsResolver::class, function ($mock): void {
            $mock->shouldReceive('resolveSms')->once()->andReturn([
                'endpoint' => 'https://sms.example.test/send',
                'api_token' => 'test-token',
                'sender_number' => null,
            ]);
        });

        $upstreamBody = str_repeat('upstream implementation details ', 1000);
        Http::fake([
            'https://sms.example.test/*' => Http::response($upstreamBody, 500),
        ]);

        $response = $this->postJson('/dash/admin/test-key/sms-config/test', [
            'to' => '09121234567',
            'message' => 'test',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('ok', false)
            ->assertJsonMissingPath('trace')
            ->assertJsonMissingPath('exception');
        $response->assertDontSee('upstream implementation details');
    }
}
