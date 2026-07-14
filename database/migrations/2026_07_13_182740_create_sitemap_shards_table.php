<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shard metadata for the dynamic (Yoast-style) sitemap.
 *
 * Each row describes one product sub-sitemap: the keyset boundaries of the
 * products it contains (first/last id), how many URLs it holds, and the most
 * recent product update inside it (for <lastmod>). The XML itself is never
 * stored — it is rendered on demand from these boundaries via an indexed
 * `id BETWEEN first AND last` range scan, so no slow OFFSET is ever needed.
 *
 * `generation` lets a rebuild assemble a fresh set of shards alongside the live
 * one, then flip the active-generation pointer atomically with zero downtime.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_shards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('generation');
            $table->unsignedInteger('shard_index');
            $table->string('first_product_id', 120);
            $table->string('last_product_id', 120);
            $table->unsignedInteger('url_count');
            $table->timestamp('lastmod')->nullable();
            $table->timestamps();

            $table->unique(['generation', 'shard_index']);
            $table->index('generation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_shards');
    }
};
