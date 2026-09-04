<?php

namespace App\Services\Slider;

use App\Repositories\SettingsRepository;

class HomeCategoryBannerStorage
{
    private const SETTINGS_KEY = 'home.category_banners';

    public function __construct(private readonly SettingsRepository $settingsRepository) {}

    public function load(): array
    {
        $raw = $this->settingsRepository->get(self::SETTINGS_KEY, []);
        $defaults = [
            'title' => 'مدیریت بنرها و دسته بندی های صفحه اصلی',
            'payload_url' => null,
            'banners' => [
                ['image' => '', 'link' => '#', 'alt' => ''],
                ['image' => '', 'link' => '#', 'alt' => ''],
            ],
            'banners_enabled' => true,
            'categories_top' => [
                'enabled' => true,
                'title' => 'دسته‌بندی‌های پیشنهادی',
                'view_type' => 'grid', // grid or slider
                'grid_cols' => 6,
                'grid_rows' => 1,
                'slider_effect' => 'slide',
                'show_title' => true,
                'show_navigation' => true,
                'show_pagination' => false,
                'items' => [],
            ],
            'categories_bottom' => [
                'enabled' => false,
                'title' => 'سایر دسته‌بندی‌ها',
                'view_type' => 'grid',
                'grid_cols' => 6,
                'grid_rows' => 1,
                'slider_effect' => 'slide',
                'show_title' => true,
                'show_navigation' => true,
                'show_pagination' => false,
                'items' => [],
            ],
            'categories' => [], // Legacy
        ];

        return array_replace_recursive($defaults, is_array($raw) ? $raw : []);
    }

    public function save(array $payload): array
    {
        $state = $this->load();

        if (isset($payload['banners_enabled'])) {
            $state['banners_enabled'] = (bool) $payload['banners_enabled'];
        }

        if (isset($payload['banners'])) {
            $state['banners'] = array_slice(array_values(array_map(function (array $item) {
                return [
                    'image' => (string) ($item['image'] ?? ''),
                    'link' => (string) ($item['link'] ?? '#'),
                    'alt' => (string) ($item['alt'] ?? ''),
                ];
            }, (array) ($payload['banners'] ?? []))), 0, 2);

            while (count($state['banners']) < 2) {
                $state['banners'][] = ['image' => '', 'link' => '#', 'alt' => ''];
            }
        }

        $processCategories = function (array $items) {
            return array_values(array_filter(array_map(function (array $item) {
                if (empty($item['title'] ?? null) || empty($item['link'] ?? null) || empty($item['image'] ?? null)) {
                    return null;
                }

                return [
                    'title' => (string) ($item['title'] ?? ''),
                    'link' => (string) ($item['link'] ?? '#'),
                    'image' => (string) ($item['image'] ?? ''),
                    'alt' => (string) ($item['alt'] ?? ''),
                    'description' => (string) ($item['description'] ?? ''),
                ];
            }, $items)));
        };

        if (isset($payload['categories_top'])) {
            $state['categories_top'] = [
                'enabled' => isset($payload['categories_top']['enabled']),
                'title' => (string) ($payload['categories_top']['title'] ?? 'دسته‌بندی‌های پیشنهادی'),
                'view_type' => (string) ($payload['categories_top']['view_type'] ?? 'grid'),
                'grid_cols' => (int) ($payload['categories_top']['grid_cols'] ?? 6),
                'grid_rows' => (int) ($payload['categories_top']['grid_rows'] ?? 1),
                'slider_effect' => 'slide',
                'show_title' => isset($payload['categories_top']['show_title']),
                'show_navigation' => isset($payload['categories_top']['show_navigation']),
                'show_pagination' => isset($payload['categories_top']['show_pagination']),
                'items' => $processCategories((array) ($payload['categories_top']['items'] ?? [])),
            ];
        }

        if (isset($payload['categories_bottom'])) {
            $state['categories_bottom'] = [
                'enabled' => isset($payload['categories_bottom']['enabled']),
                'title' => (string) ($payload['categories_bottom']['title'] ?? 'سایر دسته‌بندی‌ها'),
                'view_type' => (string) ($payload['categories_bottom']['view_type'] ?? 'grid'),
                'grid_cols' => (int) ($payload['categories_bottom']['grid_cols'] ?? 6),
                'grid_rows' => (int) ($payload['categories_bottom']['grid_rows'] ?? 1),
                'slider_effect' => 'slide',
                'show_title' => isset($payload['categories_bottom']['show_title']),
                'show_navigation' => isset($payload['categories_bottom']['show_navigation']),
                'show_pagination' => isset($payload['categories_bottom']['show_pagination']),
                'items' => $processCategories((array) ($payload['categories_bottom']['items'] ?? [])),
            ];
        }

        // Keep legacy categories synced with top for backward compatibility if needed
        $state['categories'] = $state['categories_top']['items'];

        $this->settingsRepository->set(self::SETTINGS_KEY, $state);

        return $state;
    }

    public function setPayloadUrl(string $payloadUrl): void
    {
        $state = $this->load();
        $state['payload_url'] = $payloadUrl;
        $this->settingsRepository->set(self::SETTINGS_KEY, $state);
    }
}
