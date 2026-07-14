<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var string[] */
    private array $tables = [
        'analytics_alert_logs',
        'analytics_alerts',
        'analytics_funnel_daily',
        'analytics_funnels',
        'analytics_daily_stats',
        'analytics_live_visitors',
        'analytics_sessions',
        'analytics_events',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // جداول analytics به طور دائم حذف شده‌اند و قابل بازگشت نیستند.
    }
};
