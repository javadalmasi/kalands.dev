<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_admin_can_view_users_list()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->get("/dash/admin/{$this->authKey}/users");

        $response->assertStatus(200);
        $response->assertViewIs('dash.admin.users');
        $response->assertViewHas('users');
    }

    public function test_admin_can_create_user()
    {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post("/dash/admin/{$this->authKey}/users/create", $userData);

        $response->assertRedirect("/dash/admin/{$this->authKey}/users");
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John'
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin, 'admin')
            ->delete("/dash/admin/{$this->authKey}/users/{$user->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
