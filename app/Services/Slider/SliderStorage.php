<?php

namespace App\Services\Slider;

use App\Models\Slider;
use Illuminate\Support\Facades\Schema;

class SliderStorage
{
    public function loadByModule(string $moduleName): array
    {
        if (! Schema::hasTable('sliders') || ! Schema::hasTable('slider_items')) {
            return $this->defaultPayload($moduleName);
        }

        $slider = Slider::query()->with('items')->where('module_name', $moduleName)->first();
        if (! $slider) {
            return $this->defaultPayload($moduleName);
        }

        $mappedSlides = $slider->items
            ->map(function ($item) {
                $meta = $item->meta_json ?? [];
                $device = $meta['device'] ?? 'desktop';

                return [
                    'id' => $item->id,
                    'image' => $item->image,
                    'title' => $item->title,
                    'description' => $item->subtitle,
                    'link' => $item->button_link,
                    'button_text' => $item->button_text,
                    'sort_order' => (int) $item->sort_order,
                    'is_active' => (bool) $item->is_active,
                    'slide_type' => $item->slide_type ?: 'image',
                    'meta' => $meta,
                    'device' => in_array($device, ['desktop', 'mobile'], true) ? $device : 'desktop',
                ];
            })
            ->values();

        $desktopSlides = $mappedSlides->where('device', 'desktop')->values()->all();
        $mobileSlides = $mappedSlides->where('device', 'mobile')->values()->all();

        if (empty($desktopSlides) && empty($mobileSlides)) {
            $desktopSlides = $mappedSlides->all();
        }

        return [
            'id' => $slider->id,
            'module_name' => $slider->module_name,
            'title' => $slider->title,
            'status' => (bool) $slider->status,
            'config' => $this->mergeConfigDefaults((array) ($slider->config_json ?? [])),
            'desktop_config' => $this->deviceConfig((array) ($slider->config_json ?? []), 'desktop'),
            'mobile_config' => $this->deviceConfig((array) ($slider->config_json ?? []), 'mobile'),
            'slides' => $mappedSlides->all(),
            'desktop_slides' => $desktopSlides,
            'mobile_slides' => $mobileSlides,
        ];
    }

    public function saveModuleSlider(string $moduleName, array $payload): Slider
    {
        if (! Schema::hasTable('sliders') || ! Schema::hasTable('slider_items')) {
            throw new \RuntimeException('Slider tables are missing. Please run migrations.');
        }

        $slider = Slider::query()->firstOrNew(['module_name' => $moduleName]);
        $slider->title = (string) ($payload['title'] ?? 'Slider');
        $slider->status = (bool) ($payload['status'] ?? true);
        $config = $this->mergeConfigDefaults((array) ($payload['config'] ?? []));
        $config['desktop'] = $this->mergeConfigDefaults((array) ($payload['desktop_config'] ?? []));
        $config['mobile'] = $this->mergeConfigDefaults((array) ($payload['mobile_config'] ?? []));
        $slider->config_json = $config;
        $slider->save();

        $desktopSlides = array_map(fn (array $slide) => array_merge($slide, ['device' => 'desktop']), (array) ($payload['desktop_slides'] ?? []));
        $mobileSlides = array_map(fn (array $slide) => array_merge($slide, ['device' => 'mobile']), (array) ($payload['mobile_slides'] ?? []));
        $slides = array_values(array_filter(array_merge($desktopSlides, $mobileSlides), function (array $slide) {
            return ! empty($slide['image'] ?? null);
        }));

        $slider->items()->delete();
        foreach ($slides as $index => $slide) {
            $meta = is_array($slide['meta'] ?? null) ? $slide['meta'] : [];
            $meta['device'] = in_array(($slide['device'] ?? null), ['desktop', 'mobile'], true) ? $slide['device'] : 'desktop';

            $slider->items()->create([
                'image' => (string) ($slide['image'] ?? ''),
                'title' => (string) ($slide['title'] ?? ''),
                'subtitle' => (string) ($slide['description'] ?? ''),
                'button_text' => (string) ($slide['button_text'] ?? ''),
                'button_link' => (string) ($slide['link'] ?? '#'),
                'sort_order' => (int) ($slide['sort_order'] ?? $index),
                'is_active' => (bool) ($slide['is_active'] ?? true),
                'slide_type' => (string) ($slide['slide_type'] ?? 'image'),
                'meta_json' => $meta,
            ]);
        }

        return $slider->fresh(['items']);
    }

    private function defaultPayload(string $moduleName): array
    {
        return [
            'id' => null,
            'module_name' => $moduleName,
            'title' => 'Home Main Banners',
            'status' => true,
            'config' => $this->mergeConfigDefaults([]),
            'slides' => [],
            'desktop_slides' => [],
            'mobile_slides' => [],
            'desktop_config' => $this->mergeConfigDefaults([]),
            'mobile_config' => $this->mergeConfigDefaults([]),
        ];
    }

    private function mergeConfigDefaults(array $config): array
    {
        $defaults = [
            'pagination' => 'bullets',
            'dynamicBullets' => false,
            'direction' => 'horizontal',
            'effect' => 'slide',
            'navigation' => true,
            'mousewheel' => false,
            'keyboard' => true,
            'draggable' => true,
            'loop' => true,
            'centeredSlides' => false,
            'autoplay' => true,
            'enabled' => true,
            'autoplayDelay' => 3000,
            'speed' => 600,
            'spaceBetween' => 0,
            'rewind' => false,
            'grabCursor' => true,
            'pauseOnMouseEnter' => true,
            'breakpoints' => [
                'mobile' => ['slidesPerView' => 1, 'spaceBetween' => 0],
                'tablet' => ['slidesPerView' => 1, 'spaceBetween' => 0],
                'desktop' => ['slidesPerView' => 1, 'spaceBetween' => 0],
            ],
        ];

        return array_replace_recursive($defaults, $config);
    }

    public function setModulePayloadUrl(string $moduleName, string $payloadUrl): void
    {
        $slider = Slider::query()->where('module_name', $moduleName)->first();
        if (! $slider) {
            return;
        }

        $config = $this->mergeConfigDefaults((array) ($slider->config_json ?? []));
        $config['payload_url'] = $payloadUrl;
        $slider->config_json = $config;
        $slider->save();
    }

    private function deviceConfig(array $config, string $device): array
    {
        $current = (array) ($config[$device] ?? []);

        return $this->mergeConfigDefaults($current);
    }
}
