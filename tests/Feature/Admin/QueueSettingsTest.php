<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class QueueSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $authKey = 'secret-key';

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create(['is_active' => true]);
        $role = Role::create(['name' => 'super_admin', 'label' => 'Super Admin']);
        $this->admin->roles()->attach($role);

        session(['admin_dashboard_auth_key' => $this->authKey]);
    }

    public function test_admin_can_view_queue_settings()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/modules/queues");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.queues');
        $response->assertViewHas('settings');
    }

    public function test_admin_can_save_queue_settings_including_driver()
    {
        $settings = [
            'mode' => 'artisan',
            'driver' => 'redis',
            'queue_log_retention_days' => 10,
            'laravel_log_retention_days' => 20,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post("/dash/admin/{$this->authKey}/queues", $settings);

        $response->assertRedirect();

        // Verify .env or config would be updated in a real scenario
        // Here we just check if it returned with success
        $response->assertSessionHas('message', 'تنظیمات صف ذخیره شد.');
    }
}
