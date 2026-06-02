<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            $table->dropColumn(['error_message', 'cached_at', 'expires_at']);
            $table->string('slug', 64)->nullable()->unique()->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            $table->text('error_message')->nullable();
            $table->timestamp('cached_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->dropColumn('slug');
        });
    }
};
