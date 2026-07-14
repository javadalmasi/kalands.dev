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

        $this->admin = Admin::factory()->create(['is_active' => true]);
        $role = Role::create(['name' => 'super_admin', 'label' => 'Super Admin']);
        $this->admin->roles()->attach($role);

        session(['admin_dashboard_auth_key' => $this->authKey]);
    }

    public function test_admin_can_view_modules_list()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/modules");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.modules');
        $response->assertViewHas('modules');
    }

    public function test_admin_can_access_email_settings_module()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/modules/email_settings");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.email-settings');
    }

    public function test_admin_can_access_sms_settings_module()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/modules/sms_settings");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.sms-settings');
    }

    public function test_admin_cannot_access_non_existent_module()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/modules/invalid_module");

        $response->assertStatus(404);
    }
}
