<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_id_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('old_product_id');
            $table->string('new_product_id');
            $table->string('store')->default('digikala');
            $table->string('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['old_product_id', 'store']);
            $table->index('new_product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_id_mappings');
    }
};