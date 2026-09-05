<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $authKey = 'secret-key';

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create([
            'is_active' => true,
            'dashboard_authkey' => $this->authKey,
            'dashboard_authkey_expires_at' => now()->addHour(),
        ]);
        $role = Role::query()->where('name', 'super_admin')->first()
            ?? Role::query()->create(['name' => 'super_admin', 'label' => 'Super Admin']);
        $this->admin->roles()->attach($role);

        session(['admin_dashboard_auth_key' => $this->authKey]);
    }

    public function test_admin_can_view_queue_settings()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/queues");

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
        $response->assertSessionHas('message', 'تنظیمات صف ذخیره شد.');
    }
}
