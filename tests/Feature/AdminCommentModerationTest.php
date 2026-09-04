<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Comment;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommentModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_comment_and_activity_log_is_written(): void
    {
        $admin = Admin::query()->create([
            'full_name' => 'Super Admin',
            'username' => 'superadmin',
            'email_address' => 'admin@example.com',
            'password_hash' => 'hashed',
            'password_salt' => 'salt',
            'is_active' => true,
            'dashboard_authkey' => 'ADMINKEY123',
            'dashboard_authkey_expires_at' => now()->addHour(),
            'theme_preference' => 'light',
        ]);
        $admin->syncRoles([Role::query()->where('name', 'super_admin')->valueOrFail('id')]);

        $user = User::query()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password_hash' => 'hashed',
            'password_salt' => 'salt',
            'is_active' => true,
            'theme_preference' => 'light',
        ]);

        $product = Product::query()->create([
            'id' => 'test-product-1',
            'title' => 'Test Product',
        ]);

        $comment = Comment::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'content' => 'Pending comment',
            'status' => Comment::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession([
                'dashboard.admin.authkey' => 'ADMINKEY123',
                '2fa_checked_admin' => true,
            ])
            ->post(route('dash.admin.comments.status', [
                'authkey' => 'ADMINKEY123',
                'comment' => $comment->id,
                'status' => Comment::STATUS_APPROVED,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas((new Comment)->getTable(), [
            'id' => $comment->id,
            'status' => Comment::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'admin.comment.status.update',
            'actor_id' => $admin->id,
        ]);
    }

    public function test_admin_can_delete_comment_and_activity_log_is_written(): void
    {
        $admin = Admin::query()->create([
            'full_name' => 'Super Admin',
            'username' => 'superadmin2',
            'email_address' => 'admin2@example.com',
            'password_hash' => 'hashed',
            'password_salt' => 'salt',
            'is_active' => true,
            'dashboard_authkey' => 'ADMINKEY456',
            'dashboard_authkey_expires_at' => now()->addHour(),
            'theme_preference' => 'light',
        ]);
        $admin->syncRoles([Role::query()->where('name', 'super_admin')->valueOrFail('id')]);

        $user = User::query()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'user2@example.com',
            'password_hash' => 'hashed',
            'password_salt' => 'salt',
            'is_active' => true,
            'theme_preference' => 'light',
        ]);

        $product = Product::query()->create([
            'id' => 'test-product-2',
            'title' => 'Test Product 2',
        ]);

        $comment = Comment::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'content' => 'Comment to delete',
            'status' => Comment::STATUS_PENDING,
        ]);

        $this->actingAs($admin, 'admin')
            ->withSession([
                'dashboard.admin.authkey' => 'ADMINKEY456',
                '2fa_checked_admin' => true,
            ])
            ->delete(route('dash.admin.comments.delete', [
                'authkey' => 'ADMINKEY456',
                'comment' => $comment->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseMissing((new Comment)->getTable(), [
            'id' => $comment->id,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'admin.comment.delete',
            'actor_id' => $admin->id,
        ]);
    }
}
