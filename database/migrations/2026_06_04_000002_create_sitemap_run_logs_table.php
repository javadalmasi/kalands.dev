<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitemap_run_logs', function (Blueprint $table) {
            $table->id();
            $table->string('run_id')->unique();
            $table->string('status')->default('running');
            $table->unsignedBigInteger('total_products')->nullable();
            $table->unsignedBigInteger('processed_products')->default(0);
            $table->unsignedInteger('total_chunks')->default(0);
            $table->boolean('force_mode')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_run_logs');
    }
};
