<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('digikala_category_id');
            $table->unsignedBigInteger('source_category_id')->comment('ID of Basalam or SnappShop category');
            $table->float('confidence')->default(0);
            $table->boolean('is_manual')->default(false);
            $table->timestamps();

            $table->foreign('digikala_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('source_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->index(['digikala_category_id', 'source_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_mappings');
    }
};
