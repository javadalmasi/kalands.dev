<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 80)->index();
            $table->string('visitor_hash', 80)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('event_type', 40)->index();
            $table->string('goal_key', 80)->nullable()->index();
            $table->string('goal_label', 120)->nullable();
            $table->string('product_id', 120)->nullable()->index();
            $table->string('category_key', 160)->nullable()->index();
            $table->string('seller_id', 120)->nullable()->index();
            $table->string('search_term', 190)->nullable()->index();
            $table->string('url', 1000)->nullable();
            $table->string('path', 500)->nullable()->index();
            $table->string('title', 500)->nullable();
            $table->string('referrer_url', 1000)->nullable();
            $table->string('referrer_host', 190)->nullable()->index();
            $table->string('referrer_type', 40)->nullable()->index();
            $table->string('country_code', 8)->nullable()->index();
            $table->string('country_name', 120)->nullable();
            $table->string('device_type', 40)->nullable()->index();
            $table->string('browser', 80)->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('ip_hash', 80)->nullable()->index();
            $table->string('user_agent_hash', 80)->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('analytics_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('metric', 60)->index();
            $table->string('dimension_key', 190)->default('__all__');
            $table->string('dimension_label', 255)->nullable();
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamps();
            $table->unique(['date', 'metric', 'dimension_key'], 'analytics_daily_unique');
        });

        Schema::create('analytics_live_visitors', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 80)->unique();
            $table->string('visitor_hash', 80)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('url', 1000)->nullable();
            $table->string('path', 500)->nullable()->index();
            $table->string('title', 500)->nullable();
            $table->string('product_id', 120)->nullable()->index();
            $table->string('country_code', 8)->nullable()->index();
            $table->string('country_name', 120)->nullable();
            $table->string('device_type', 40)->nullable()->index();
            $table->timestamp('first_seen_at')->index();
            $table->timestamp('last_seen_at')->index();
            $table->timestamps();
        });

        $this->createAnalyticsPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_live_visitors');
        Schema::dropIfExists('analytics_daily_stats');
        Schema::dropIfExists('analytics_events');
    }

    private function createAnalyticsPermissions(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $now = now();
        $actions = [
            'view' => 'مشاهده آنالیزور',
            'create' => 'ایجاد آنالیزور',
            'edit' => 'ویرایش آنالیزور',
            'delete' => 'حذف آنالیزور',
            'full' => 'مدیریت کامل آنالیزور',
            'check' => 'بررسی آنالیزور',
        ];

        foreach ($actions as $action => $label) {
            DB::table('permissions')->updateOrInsert(
                ['name' => "analytics.{$action}"],
                [
                    'label' => $label,
                    'module' => 'آنالیزور',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('role_permission')) {
            return;
        }

        $superAdminId = DB::table('roles')->where('name', 'super_admin')->value('id');
        if (!$superAdminId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->where('name', 'like', 'analytics.%')
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permission')->updateOrInsert([
                'role_id' => $superAdminId,
                'permission_id' => $permissionId,
            ]);
        }
    }
};
