<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('title');
            $table->string('store')->index(); // 'digikala' or 'basalam'
            $table->integer('product_count')->default(0);
            $table->json('vector')->nullable();
            $table->string('vector_source')->default('local'); // 'local' or 'external'
            $table->string('vector_model')->nullable(); // e.g., 'ngram' or 'gemma-4'
            $table->string('external_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
