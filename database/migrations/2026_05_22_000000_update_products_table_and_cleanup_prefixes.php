<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop foreign keys referencing products.id
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        // 2. Modify related tables product_id column to string(120)
        Schema::table('comments', function (Blueprint $table) {
            $table->string('product_id', 120)->change();
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->string('product_id', 120)->change();
        });
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->string('product_id', 120)->change();
        });

        // 3. Modify products table
        Schema::table('products', function (Blueprint $table) {
            $table->string('id', 120)->change();

            $table->enum('store', ['digikala', 'basalam'])->default('digikala')->after('id');
            $table->boolean('is_active')->default(true)->after('title');
            $table->json('api_status')->nullable()->after('is_active');
            $table->timestamp('last_checked_at')->nullable()->after('api_status');
        });

        // 4. Update data and remove 55140 prefix
        // Update products
        DB::table('products')->where('id', 'like', '55140%')->update([
            'store' => 'basalam',
            'id' => DB::raw("SUBSTRING(id, 6)")
        ]);

        // Update related tables
        $tables = ['comments', 'likes', 'bookmarks', 'affiliate_links'];
        foreach ($tables as $table) {
            DB::table($table)->where('product_id', 'like', '55140%')->update([
                'product_id' => DB::raw("SUBSTRING(product_id, 6)")
            ]);
        }

        // 5. Re-add foreign keys
        Schema::table('comments', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
        Schema::table('bookmarks', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['store', 'is_active', 'api_status', 'last_checked_at']);
        });
    }
};
