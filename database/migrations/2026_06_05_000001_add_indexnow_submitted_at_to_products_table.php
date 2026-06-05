<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('indexnow_submitted_at')->nullable()->after('sitemapped_at');
            $table->index('indexnow_submitted_at', 'idx_products_indexnow_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_indexnow_submitted_at');
            $table->dropColumn('indexnow_submitted_at');
        });
    }
};
