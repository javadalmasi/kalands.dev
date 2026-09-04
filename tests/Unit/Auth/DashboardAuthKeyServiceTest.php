<?php

namespace Tests\Unit\Auth;

use App\Models\Admin;
use App\Services\Auth\DashboardAuthKeyService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAuthKeyServiceTest extends TestCase
{
    use RefreshDatabase;

    private DashboardAuthKeyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardAuthKeyService;
    }

    public function test_it_can_issue_and_validate_key()
    {
        $admin = Admin::factory()->create();
        $key = $this->service->issue($admin, 'admin', 60);

        $this->assertNotEmpty($key);
        $this->assertEquals($key, $admin->fresh()->dashboard_authkey);
        $this->assertTrue($this->service->isValid($admin, $key));
    }

    public function test_it_returns_false_for_incorrect_key()
    {
        $admin = Admin::factory()->create(['dashboard_authkey' => 'correct-key']);
        $this->assertFalse($this->service->isValid($admin, 'wrong-key'));
    }

    public function test_it_returns_false_for_expired_key()
    {
        $admin = Admin::factory()->create([
            'dashboard_authkey' => 'some-key',
            'dashboard_authkey_expires_at' => CarbonImmutable::now()->subMinute(),
        ]);

        $this->assertFalse($this->service->isValid($admin, 'some-key'));
    }

    public function test_it_handles_string_expiration_dates()
    {
        $admin = Admin::factory()->create([
            'dashboard_authkey' => 'some-key',
        ]);

        // Manually force a string into the attribute if the model didn't cast it
        $admin->dashboard_authkey_expires_at = CarbonImmutable::now()->addHour()->toDateTimeString();

        $this->assertTrue($this->service->isValid($admin, 'some-key'));
    }
}
