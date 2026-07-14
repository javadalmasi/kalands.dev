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
            if (! isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $module;
        }

        return $grouped;
    }

    public function helpManifest(string $moduleKey): ?string
    {
        // Try markdown first
        $mdPath = resource_path("modules/help/{$moduleKey}.md");
        if (File::exists($mdPath)) {
            return File::get($mdPath);
        }

        // Fallback to JSON for backward compatibility
        $jsonPath = resource_path("modules/help/{$moduleKey}.json");
        if (File::exists($jsonPath)) {
            $json = json_decode(File::get($jsonPath), true);
            // Convert JSON structure to markdown format
            if (isset($json['help']['sections'])) {
                return $this->convertJsonToMarkdown($json, $moduleKey);
            }
        }

        return null;
    }

    private function convertJsonToMarkdown(array $data, string $moduleKey): string
    {
        $markdown = '# '.($data['help']['title'] ?? 'راهنمای ماژول')."\n\n";

        foreach ($data['help']['sections'] ?? [] as $section) {
            if (isset($section['heading'])) {
                $markdown .= '## '.$section['heading']."\n\n";
            }

            if ($section['type'] === 'text') {
                $markdown .= $section['content']."\n\n";
            } elseif ($section['type'] === 'code') {
                $markdown .= "```\n".$section['content']."\n```\n\n";
            } elseif ($section['type'] === 'tip') {
                $markdown .= '> **💡 نکته:** '.$section['content']."\n\n";
            } elseif ($section['type'] === 'warning') {
                $markdown .= '> **⚠️ هشدار:** '.$section['content']."\n\n";
            } elseif ($section['type'] === 'table' && isset($section['data'])) {
                $markdown .= '| '.implode(' | ', $section['data']['headers'])." |\n";
                $markdown .= '|'.str_repeat(' --- |', count($section['data']['headers']))."\n";
                foreach ($section['data']['rows'] as $row) {
                    $markdown .= '| '.implode(' | ', $row)." |\n";
                }
                $markdown .= "\n";
            }
        }

        return $markdown;
    }

    public function categoryLabels(): array
    {
        return [
            'communication' => 'ارتباطات',
            'content' => 'مدیریت محتوا',
            'data' => 'داده‌ها و فایل‌ها',
            'technical' => 'فنی و بهینه‌سازی',
        ];
    }

    public function categoryIcons(): array
    {
        return [
            'communication' => 'mail_outline',
            'content' => 'inventory_2',
            'data' => 'folder_open',
            'technical' => 'settings',
        ];
    }
}
