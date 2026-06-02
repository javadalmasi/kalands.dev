<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_events', 'device_brand')) {
                $table->string('device_brand', 80)->nullable()->index()->after('device_type');
            }
            if (!Schema::hasColumn('analytics_events', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('platform');
            }
            if (!Schema::hasColumn('analytics_events', 'user_agent')) {
                $table->string('user_agent', 500)->nullable()->after('ip_hash');
            }
            if (!Schema::hasColumn('analytics_events', 'error_source')) {
                $table->string('error_source', 500)->nullable()->after('user_agent_hash');
            }
            if (!Schema::hasColumn('analytics_events', 'error_line')) {
                $table->unsignedInteger('error_line')->nullable()->after('error_source');
            }
            if (!Schema::hasColumn('analytics_events', 'error_message')) {
                $table->text('error_message')->nullable()->after('error_line');
            }
        });

        Schema::table('analytics_live_visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_live_visitors', 'device_brand')) {
                $table->string('device_brand', 80)->nullable()->index()->after('device_type');
            }
            if (!Schema::hasColumn('analytics_live_visitors', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('device_brand');
            }
            if (!Schema::hasColumn('analytics_live_visitors', 'user_agent')) {
                $table->string('user_agent', 500)->nullable()->after('ip_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('analytics_live_visitors', function (Blueprint $table) {
            foreach (['device_brand', 'ip_address', 'user_agent'] as $column) {
                if (Schema::hasColumn('analytics_live_visitors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('analytics_events', function (Blueprint $table) {
            foreach (['device_brand', 'ip_address', 'user_agent', 'error_source', 'error_line', 'error_message'] as $column) {
                if (Schema::hasColumn('analytics_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
