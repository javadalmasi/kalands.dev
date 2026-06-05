<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('index_now_run_logs', function (Blueprint $table) {
            $table->id();
            $table->string('run_id')->unique();
            $table->unsignedTinyInteger('hour');
            $table->enum('engine', ['bing', 'yandex']);
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_queued')->default(0);
            $table->unsignedInteger('total_submitted')->default(0);
            $table->unsignedInteger('total_failed')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['hour', 'engine']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('index_now_run_logs');
    }
};
