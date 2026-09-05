<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleManagementTest extends TestCase
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

    public function test_admin_legacy_modules_url_redirects_to_dashboard_index()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/modules");

        $response->assertRedirect("/dash/admin/{$this->authKey}");
    }

    public function test_admin_can_access_native_email_settings()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/email-settings");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.email-settings');
    }

    public function test_admin_can_access_native_sms_settings()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/sms-settings");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.sms-settings');
    }

    public function test_admin_legacy_module_alias_redirects_to_native_route()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/modules/email_settings");

        $response->assertRedirect("/dash/admin/{$this->authKey}/email-settings");
    }
}
