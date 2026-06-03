<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_events', 'city')) {
                $table->string('city', 120)->nullable()->index()->after('country_name');
            }
            if (!Schema::hasColumn('analytics_events', 'region')) {
                $table->string('region', 120)->nullable()->index()->after('city');
            }
            if (!Schema::hasColumn('analytics_events', 'scroll_depth_pct')) {
                $table->unsignedTinyInteger('scroll_depth_pct')->nullable()->after('error_line');
            }
            if (!Schema::hasColumn('analytics_events', 'session_num')) {
                $table->unsignedInteger('session_num')->nullable()->index()->after('visitor_hash');
            }
            if (!Schema::hasColumn('analytics_events', 'session_duration')) {
                $table->unsignedInteger('session_duration')->nullable()->comment('seconds')->after('session_num');
            }
            if (!Schema::hasColumn('analytics_events', 'is_bounce')) {
                $table->boolean('is_bounce')->nullable()->index()->after('session_duration');
            }
            if (!Schema::hasColumn('analytics_events', 'funnel_key')) {
                $table->string('funnel_key', 80)->nullable()->index()->after('goal_key');
            }
            if (!Schema::hasColumn('analytics_events', 'funnel_step')) {
                $table->unsignedTinyInteger('funnel_step')->nullable()->after('funnel_key');
            }
            if (!Schema::hasColumn('analytics_events', 'funnel_step_name')) {
                $table->string('funnel_step_name', 120)->nullable()->after('funnel_step');
            }
        });

        Schema::table('analytics_live_visitors', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics_live_visitors', 'pageviews_count')) {
                $table->unsignedSmallInteger('pageviews_count')->default(1)->after('title');
            }
            if (!Schema::hasColumn('analytics_live_visitors', 'is_bounced')) {
                $table->boolean('is_bounced')->default(true)->after('pageviews_count');
            }
        });

        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 80)->index();
            $table->string('visitor_hash', 80)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->date('date')->index();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedSmallInteger('pageviews_count')->default(1);
            $table->boolean('is_bounce')->default(false);
            $table->string('entry_path', 500)->nullable();
            $table->string('exit_path', 500)->nullable();
            $table->string('referrer_type', 40)->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('device_type', 40)->nullable();
            $table->string('browser', 80)->nullable();
            $table->string('platform', 80)->nullable();
            $table->string('landing_source', 40)->nullable();
            $table->timestamps();
            $table->index(['date', 'is_bounce']);
            $table->index(['visitor_hash', 'started_at']);
        });

        Schema::create('analytics_funnels', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('name', 200);
            $table->json('steps');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('analytics_funnel_daily', function (Blueprint $table) {
            $table->id();
            $table->string('funnel_key', 80)->index();
            $table->date('date')->index();
            $table->unsignedTinyInteger('step');
            $table->string('step_name', 120);
            $table->unsignedInteger('entered')->default(0);
            $table->unsignedInteger('exited')->default(0);
            $table->timestamps();
            $table->unique(['funnel_key', 'date', 'step']);
        });

        Schema::create('analytics_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('metric', 60)->index();
            $table->string('condition', 20);
            $table->decimal('threshold', 20, 4);
            $table->unsignedSmallInteger('cooldown_hours')->default(24);
            $table->timestamp('last_triggered_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('analytics_alert_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('alert_id')->index();
            $table->decimal('actual_value', 20, 4);
            $table->decimal('threshold', 20, 4);
            $table->timestamp('triggered_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_alert_logs');
        Schema::dropIfExists('analytics_alerts');
        Schema::dropIfExists('analytics_funnel_daily');
        Schema::dropIfExists('analytics_funnels');
        Schema::dropIfExists('analytics_sessions');
    }
};
