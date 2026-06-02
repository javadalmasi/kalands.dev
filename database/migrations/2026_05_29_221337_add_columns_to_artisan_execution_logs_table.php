<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('artisan_execution_logs', 'admin_id')) {
            return;
        }

        Schema::table('artisan_execution_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_id');
            $table->string('admin_name');
            $table->string('command');
            $table->string('label');
            $table->enum('status', ['success', 'failed']);
            $table->text('output')->nullable();
            $table->timestamp('executed_at');
        });

        Schema::table('artisan_execution_logs', function (Blueprint $table) {
            $table->foreign('admin_id')->references('id')->on('admins')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('artisan_execution_logs', 'admin_id')) {
            return;
        }

        Schema::table('artisan_execution_logs', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['admin_id', 'admin_name', 'command', 'label', 'status', 'output', 'executed_at']);
        });
    }
};
