<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Simplify the sitemap schema for the rewritten streaming generator.
 *
 * The old design tracked per-shard rows in `sitemap_groups` and versioned the
 * output; the new generator derives shard state from the files on disk, so that
 * table and the related run-log columns are removed. Run logs keep a single
 * `mode` column (full | incremental).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sitemap_groups');

        Schema::table('sitemap_run_logs', function (Blueprint $table) {
            foreach (['version', 'total_chunks', 'last_full_rebuild_at', 'force_mode', 'rebuild_type'] as $column) {
                if (Schema::hasColumn('sitemap_run_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('sitemap_run_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('sitemap_run_logs', 'mode')) {
                $table->string('mode')->default('incremental')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sitemap_run_logs', function (Blueprint $table) {
            if (Schema::hasColumn('sitemap_run_logs', 'mode')) {
                $table->dropColumn('mode');
            }

            $table->string('version')->nullable()->after('run_id');
            $table->string('rebuild_type')->default('incremental');
            $table->unsignedInteger('total_chunks')->default(0);
            $table->timestamp('last_full_rebuild_at')->nullable();
            $table->boolean('force_mode')->default(false);
        });

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
    }
};
