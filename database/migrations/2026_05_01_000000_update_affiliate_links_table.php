<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            if (!Schema::hasColumn('affiliate_links', 'click_count')) {
                $table->unsignedInteger('click_count')->default(0)->after('link');
            }
        });

        // Change status to string to avoid enum issues
        Schema::table('affiliate_links', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->change();
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            $table->dropColumn('click_count');
        });
    }
};
