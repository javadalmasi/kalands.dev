<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('module_name', 120)->index();
            $table->string('title', 180);
            $table->boolean('status')->default(true);
            $table->json('config_json')->nullable();
            $table->timestamps();
        });

        Schema::create('slider_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slider_id')->constrained('sliders')->cascadeOnDelete();
            $table->string('image', 500)->nullable();
            $table->string('title', 180)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->string('button_text', 120)->nullable();
            $table->string('button_link', 500)->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('slide_type', 40)->default('image');
            $table->json('meta_json')->nullable();
            $table->timestamps();
        });

        $legacy = DB::table('system_configs')->where('config_key', 'home.slider')->first();
        if (! $legacy) {
            return;
        }

        $raw = $legacy->config_value;
        $decoded = is_string($raw) ? json_decode($raw, true) : (array) $raw;
        if (! is_array($decoded)) {
            return;
        }

        $sliderId = DB::table('sliders')->insertGetId([
            'module_name' => 'home_main_banners',
            'title' => 'Home Main Banners',
            'status' => (bool) ($decoded['enabled'] ?? true),
            'config_json' => json_encode([
                'pagination' => 'bullets',
                'effect' => 'slide',
                'direction' => 'horizontal',
                'navigation' => true,
                'mousewheel' => false,
                'keyboard' => true,
                'draggable' => true,
                'loop' => true,
                'centeredSlides' => false,
                'autoplay' => true,
                'autoplayDelay' => 3000,
                'speed' => 600,
                'spaceBetween' => 0,
                'breakpoints' => [
                    'mobile' => ['spaceBetween' => 0, 'slidesPerView' => 1],
                    'tablet' => ['spaceBetween' => 0, 'slidesPerView' => 1],
                    'desktop' => ['spaceBetween' => 0, 'slidesPerView' => 1],
                ],
            ], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (array_values($decoded['main_slides'] ?? []) as $index => $item) {
            if (empty($item['image'] ?? null)) {
                continue;
            }

            DB::table('slider_items')->insert([
                'slider_id' => $sliderId,
                'image' => (string) ($item['image'] ?? ''),
                'title' => (string) ($item['title'] ?? ''),
                'subtitle' => (string) ($item['alt'] ?? ''),
                'button_text' => null,
                'button_link' => (string) ($item['link'] ?? '#'),
                'sort_order' => $index,
                'is_active' => (bool) ($item['enabled'] ?? true),
                'slide_type' => 'image',
                'meta_json' => json_encode(['alt' => (string) ($item['alt'] ?? '')], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('slider_items');
        Schema::dropIfExists('sliders');
    }
};
