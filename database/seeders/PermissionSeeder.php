<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Auth\PasswordHashService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing to ensure clean state as requested
        Schema::disableForeignKeyConstraints();
        DB::table('admins')->truncate(); // Explicitly requested to clear all admins
        DB::table('model_has_roles')->truncate();
        DB::table('role_permission')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        $modules = [
            'users' => 'کاربران',
            'admins' => 'ادمین‌ها',
            'roles' => 'نقش‌ها و دسترسی‌ها',
            'products' => 'محصولات',
            'comments' => 'نظرات',
            'tickets' => 'تیکت‌ها',
            'faq' => 'سوالات متداول',
            'affiliate' => 'افیلیت',
            'contact' => 'تماس با ما',
            'file_manager' => 'مدیریت فایل',
            'home_items' => 'مدیریت صفحه اصلی',
            'email_templates' => 'تمپلیت ایمیل',
            'queues' => 'مدیریت صف',
            'communication' => 'ارتباطات',
            'dashboard' => 'داشبورد',
            'analytics' => 'آنالیزور',
            'geoip' => 'بروزرسانی GeoIP',
            'robots' => 'فایل Robots.txt',
            'search' => 'جستجوی هوشمند',
            'megamenu' => 'مدیریت مگا منو',
            'error_pages' => 'مدیریت صفحات خطا',
            'cache_management' => 'مدیریت کش',
            'object_cache' => 'مدیریت Object Cache',
            'artisan_commands' => 'دستورات Artisan',
            'sitemap' => 'مدیریت سایت مپ',
            'indexnow' => 'ایندکس‌سازی (IndexNow)',
        ];

        $actions = [
            'view' => 'مشاهده',
            'create' => 'ایجاد',
            'edit' => 'ویرایش',
            'delete' => 'حذف',
            'full' => 'مدیریت کامل',
            'check' => 'بررسی وب‌سرویس',
        ];

        foreach ($modules as $moduleKey => $moduleLabel) {
            foreach ($actions as $actionKey => $actionLabel) {
                Permission::updateOrCreate(
                    ['name' => "{$moduleKey}.{$actionKey}"],
                    [
                        'label' => "{$actionLabel} {$moduleLabel}",
                        'module' => $moduleLabel,
                    ]
                );
            }
        }

        // Super Admin
        $superAdmin = Role::updateOrCreate(['name' => 'super_admin'], ['label' => 'مدیر کل سیستم']);
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // Content Manager
        $contentManager = Role::updateOrCreate(['name' => 'content_manager'], ['label' => 'مدیر محتوا']);
        $contentManager->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'users.view',
                'products.view', 'products.edit',
                'comments.view', 'comments.edit', 'comments.delete',
                'tickets.view', 'tickets.edit',
                'faq.view', 'faq.create', 'faq.edit', 'faq.delete',
                'home_items.view', 'home_items.edit',
                'email_templates.view', 'email_templates.edit',
                'contact.view', 'contact.edit',
                'megamenu.view', 'megamenu.edit',
                'error_pages.view', 'error_pages.edit',
                'sitemap.view',
            ])->pluck('id')
        );

        // User Manager
        $userManager = Role::updateOrCreate(['name' => 'user_manager'], ['label' => 'مدیر کاربران']);
        $userManager->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'admins.view',
                'tickets.view', 'tickets.edit', 'tickets.full',
                'roles.view',
            ])->pluck('id')
        );

        // System Manager
        $systemManager = Role::updateOrCreate(['name' => 'system_manager'], ['label' => 'مدیر سیستم']);
        $systemManager->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'users.view',
                'queues.view', 'queues.full',
                'communication.view', 'communication.edit', 'communication.full',
                'affiliate.view', 'affiliate.edit', 'affiliate.full',
                'file_manager.view', 'file_manager.full',
                'sitemap.view',
            ])->pluck('id')
        );

        // Create Fresh Super Admin as requested
        $passwordService = app(PasswordHashService::class);
        $auth = $passwordService->make('AdminPassword123');

        $admin = Admin::create([
            'full_name' => 'مدیر کل سیستم',
            'username' => 'admin',
            'email_address' => 'admin@kalands.ir',
            'password_hash' => $auth['hash'],
            'password_salt' => $auth['salt'],
            'is_active' => true,
            'dashboard_authkey' => 'ADMIN_MASTER_KEY',
            'dashboard_authkey_expires_at' => now()->addYears(10),
        ]);

        $admin->syncRoles([$superAdmin->id]);
    }
}
