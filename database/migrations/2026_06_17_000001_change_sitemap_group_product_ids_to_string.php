<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sitemap_groups', function (Blueprint $table) {
            $table->string('first_product_id', 120)->nullable()->change();
            $table->string('last_product_id', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sitemap_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('first_product_id')->nullable()->change();
            $table->unsignedBigInteger('last_product_id')->nullable()->change();
        });
    }
};
