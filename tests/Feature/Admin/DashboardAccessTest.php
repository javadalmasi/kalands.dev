<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get('/dash/admin/any-auth-key');
        $response->assertRedirect('/auth/login');
    }

    public function test_admin_can_access_dashboard_with_correct_permission()
    {
        $authKey = 'test-auth-key';
        $admin = Admin::factory()->create([
            'is_active' => true,
            'dashboard_authkey' => $authKey,
            'dashboard_authkey_expires_at' => now()->addHour(),
        ]);
        $role = Role::create(['name' => 'manager', 'label' => 'Manager']);
        $permission = Permission::where('name', 'dashboard.view')->first()
                      ?? Permission::create(['name' => 'dashboard.view', 'label' => 'View Dashboard', 'module' => 'dashboard']);

        $role->permissions()->attach($permission);
        $admin->roles()->attach($role);

        session(['admin_dashboard_auth_key' => $authKey]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/dash/admin/{$authKey}");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.index');
    }

    public function test_admin_without_permission_cannot_access_dashboard()
    {
        $authKey = 'test-auth-key';
        $admin = Admin::factory()->create([
            'is_active' => true,
            'dashboard_authkey' => $authKey,
            'dashboard_authkey_expires_at' => now()->addHour(),
        ]);
        session(['admin_dashboard_auth_key' => $authKey]);

        $response = $this->actingAs($admin, 'admin')
            ->get("/dash/admin/{$authKey}");

        $response->assertStatus(403);
    }
}
