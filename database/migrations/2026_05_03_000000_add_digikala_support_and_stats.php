<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliate_links', 'store')) {
                $table->string('store', 20)->default('basalam')->after('id');
            }
        });

        Schema::create('affiliate_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('store', 20)->index();
            $table->unsignedInteger('clicks')->default(0);
            $table->unique(['date', 'store']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_daily_stats');
        Schema::table('affiliate_links', function (Blueprint $table) {
            $table->dropColumn('store');
        });
    }
};
