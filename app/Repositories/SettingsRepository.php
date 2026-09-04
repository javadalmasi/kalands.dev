<?php

namespace App\Repositories;

use App\Models\SystemConfig;
use Illuminate\Support\Facades\Cache;

class SettingsRepository
{
    private const CACHE_PREFIX = 'system_config:';

    private const CACHE_TTL_SECONDS = 3600;

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            $this->cacheKey($key),
            self::CACHE_TTL_SECONDS,
            function () use ($key, $default) {
                $record = SystemConfig::query()
                    ->where('config_key', $key)
                    ->first();

                return $record?->config_value ?? $default;
            }
        );
    }

    public function set(string $key, mixed $value, bool $isSensitive = false): void
    {
        SystemConfig::query()->updateOrCreate(
            ['config_key' => $key],
            [
                'config_value' => $value,
                'is_sensitive' => $isSensitive,
            ]
        );

        Cache::forget($this->cacheKey($key));
    }

    public function forget(string $key): void
    {
        SystemConfig::query()
            ->where('config_key', $key)
            ->delete();

        Cache::forget($this->cacheKey($key));
    }

    private function cacheKey(string $key): string
    {
        return self::CACHE_PREFIX.$key;
    }
}
