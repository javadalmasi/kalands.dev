<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ModuleRegistry
{
    public function all(): array
    {
        return config('modules', []);
    }

    public function get(string $key): ?array
    {
        return config("modules.{$key}");
    }

    public function grouped(): array
    {
        $modules = $this->all();
        $grouped = [];

        foreach ($modules as $module) {
            $category = $module['category'] ?? 'other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $module;
        }

        return $grouped;
    }

    public function helpManifest(string $moduleKey): ?array
    {
        $helpPath = resource_path("modules/help/{$moduleKey}.json");

        if (!File::exists($helpPath)) {
            return null;
        }

        $json = File::get($helpPath);
        return json_decode($json, true);
    }

    public function categoryLabels(): array
    {
        return [
            'communication' => 'ارتباطات',
            'content' => 'مدیریت محتوا',
            'data' => 'داده‌ها و فایل‌ها',
            'technical' => 'فنی و بهینه‌سازی',
            'analytics' => 'تحلیل و آمار',
        ];
    }

    public function categoryIcons(): array
    {
        return [
            'communication' => 'mail_outline',
            'content' => 'inventory_2',
            'data' => 'folder_open',
            'technical' => 'settings',
            'analytics' => 'analytics',
        ];
    }
}
