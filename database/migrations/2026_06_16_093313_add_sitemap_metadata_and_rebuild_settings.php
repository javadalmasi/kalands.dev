<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_groups', function (Blueprint $table) {
            $table->id();
            $table->string('version')->index();
            $table->unsignedInteger('group_index')->index();
            $table->string('filename');
            $table->unsignedInteger('url_count')->default(0);
            $table->string('first_product_id', 120)->nullable();
            $table->string('last_product_id', 120)->nullable();
            $table->boolean('is_complete')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unique(['version', 'group_index']);
            $table->index(['is_active', 'is_complete']);
        });

        Schema::table('sitemap_run_logs', function (Blueprint $table) {
            $table->string('version')->nullable()->after('run_id');
            $table->string('rebuild_type')->default('incremental')->after('force_mode');
            $table->timestamp('last_full_rebuild_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_groups');

        Schema::table('sitemap_run_logs', function (Blueprint $table) {
            $table->dropColumn(['version', 'rebuild_type', 'last_full_rebuild_at']);
        });
    }
};
