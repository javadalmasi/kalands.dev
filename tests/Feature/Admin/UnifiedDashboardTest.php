<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $authKey = 'test-auth-key-123';

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

        session([
            'dashboard.admin.authkey' => $this->authKey,
            '2fa_checked_admin' => true,
        ]);
    }

    public function test_unified_dashboard_overview_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->withSession([
                'dashboard.admin.authkey' => $this->authKey,
                '2fa_checked_admin' => true,
            ])
            ->get("/dash/admin/{$this->authKey}");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.index');
        $response->assertSee('Kalands Unified', false);
    }

    public function test_unified_navigation_and_native_routes(): void
    {
        $nativeRoutes = [
            "/dash/admin/{$this->authKey}/users",
            "/dash/admin/{$this->authKey}/admins",
            "/dash/admin/{$this->authKey}/roles",
            "/dash/admin/{$this->authKey}/products",
            "/dash/admin/{$this->authKey}/product-mappings",
            "/dash/admin/{$this->authKey}/categories",
            "/dash/admin/{$this->authKey}/comments",
            "/dash/admin/{$this->authKey}/tickets",
            "/dash/admin/{$this->authKey}/contact-us",
            "/dash/admin/{$this->authKey}/faq",
            "/dash/admin/{$this->authKey}/megamenu",
            "/dash/admin/{$this->authKey}/home-items",
            "/dash/admin/{$this->authKey}/error-pages",
            "/dash/admin/{$this->authKey}/email-settings",
            "/dash/admin/{$this->authKey}/email-templates",
            "/dash/admin/{$this->authKey}/sms-settings",
            "/dash/admin/{$this->authKey}/affiliate-settings",
            "/dash/admin/{$this->authKey}/queues",
            "/dash/admin/{$this->authKey}/object-cache",
            "/dash/admin/{$this->authKey}/cache-management",
            "/dash/admin/{$this->authKey}/geoip",
            "/dash/admin/{$this->authKey}/visitor-intelligence",
            "/dash/admin/{$this->authKey}/robots",
            "/dash/admin/{$this->authKey}/search/hub",
            "/dash/admin/{$this->authKey}/sitemap",
            "/dash/admin/{$this->authKey}/indexnow",
            "/dash/admin/{$this->authKey}/file-manager",
            "/dash/admin/{$this->authKey}/artisan-commands",
        ];

        foreach ($nativeRoutes as $routeUrl) {
            $response = $this->actingAs($this->admin, 'admin')
                ->withSession([
                    'dashboard.admin.authkey' => $this->authKey,
                    '2fa_checked_admin' => true,
                ])
                ->get($routeUrl);

            $response->assertStatus(200);
        }
    }

    public function test_unauthorized_admin_cannot_access_restricted_section(): void
    {
        $restrictedAdmin = Admin::factory()->create([
            'is_active' => true,
            'dashboard_authkey' => 'restricted-key-999',
            'dashboard_authkey_expires_at' => now()->addHour(),
        ]);

        $customRole = Role::create(['name' => 'limited_role', 'label' => 'Limited']);
        $perm = Permission::firstOrCreate(['name' => 'users.view'], ['label' => 'View Users', 'module' => 'users']);
        $customRole->permissions()->attach($perm);
        $restrictedAdmin->roles()->attach($customRole);

        $response = $this->actingAs($restrictedAdmin, 'admin')
            ->withSession([
                'dashboard.admin.authkey' => 'restricted-key-999',
                '2fa_checked_admin' => true,
            ])
            ->get("/dash/admin/restricted-key-999/queues");

        $response->assertStatus(403);
    }
}
