<?php

namespace App\Services\Slider;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HomeItemsPayloadStorage
{
    public function publish(array $slider, array $bannersCategories, ?string $oldPayloadUrl = null): string
    {
        $publicDir = public_path('assets/home-items');
        if (! File::isDirectory($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }

        if (is_string($oldPayloadUrl) && str_starts_with($oldPayloadUrl, '/assets/home-items/homeitems-')) {
            $oldFile = public_path(ltrim($oldPayloadUrl, '/'));
            if (File::exists($oldFile)) {
                File::delete($oldFile);
            }
        }

        $filename = 'homeitems-'.Str::lower(Str::random(16)).'.json';
        $relativePath = 'assets/home-items/'.$filename;

        $normalize = function (array $item): array {
            foreach (['image'] as $key) {
                if (! empty($item[$key]) && ! str_starts_with((string) $item[$key], 'http://') && ! str_starts_with((string) $item[$key], 'https://')) {
                    $item[$key] = '/'.ltrim((string) $item[$key], '/');
                }
            }

            return $item;
        };

        $desktopSlides = array_values(array_map($normalize, array_filter((array) ($slider['desktop_slides'] ?? []), fn (array $slide) => (bool) ($slide['is_active'] ?? true))));
        $mobileSlides = array_values(array_map($normalize, array_filter((array) ($slider['mobile_slides'] ?? []), fn (array $slide) => (bool) ($slide['is_active'] ?? true))));

        $banners = array_map($normalize, (array) ($bannersCategories['banners'] ?? []));
        $categoriesTop = (array) ($bannersCategories['categories_top'] ?? []);
        $categoriesBottom = (array) ($bannersCategories['categories_bottom'] ?? []);

        $json = [
            'updated_at' => now()->toIso8601String(),
            'slider' => [
                'module' => 'home_main_banners',
                'config' => (array) ($slider['config'] ?? []),
                'desktop_config' => (array) ($slider['desktop_config'] ?? []),
                'mobile_config' => (array) ($slider['mobile_config'] ?? []),
                'desktop_slides' => $desktopSlides,
                'mobile_slides' => $mobileSlides,
            ],
            'banners_categories' => [
                'banners' => $banners,
                'categories_top' => array_merge($categoriesTop, [
                    'items' => array_map($normalize, (array) ($categoriesTop['items'] ?? [])),
                ]),
                'categories_bottom' => array_merge($categoriesBottom, [
                    'items' => array_map($normalize, (array) ($categoriesBottom['items'] ?? [])),
                ]),
            ],
            // Legacy-compatible keys for existing blades
            'desktop_slides' => $desktopSlides,
            'mobile_slides' => $mobileSlides,
            'desktop_config' => (array) ($slider['desktop_config'] ?? []),
            'mobile_config' => (array) ($slider['mobile_config'] ?? []),
            'desktop' => [
                'banners' => $banners,
                'categories' => array_map($normalize, (array) ($bannersCategories['categories'] ?? [])),
            ],
            'mobile' => [
                'banners' => $banners,
                'categories' => array_map($normalize, (array) ($bannersCategories['categories'] ?? [])),
            ],
            'categories_top' => array_merge($categoriesTop, [
                'items' => array_map($normalize, (array) ($categoriesTop['items'] ?? [])),
            ]),
            'categories_bottom' => array_merge($categoriesBottom, [
                'items' => array_map($normalize, (array) ($categoriesBottom['items'] ?? [])),
            ]),
        ];

        File::put(public_path($relativePath), json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return '/'.$relativePath;
    }
}
