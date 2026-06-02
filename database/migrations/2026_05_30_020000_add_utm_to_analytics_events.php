<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->string('utm_source', 120)->nullable()->index()->after('search_term');
            $table->string('utm_medium', 120)->nullable()->index()->after('utm_source');
            $table->string('utm_campaign', 120)->nullable()->index()->after('utm_medium');
            $table->string('utm_term', 120)->nullable()->index()->after('utm_campaign');
            $table->string('utm_content', 120)->nullable()->index()->after('utm_term');
            $table->string('search_engine', 80)->nullable()->index()->after('utm_content');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events', function (Blueprint $table) {
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'search_engine']);
        });
    }
};
