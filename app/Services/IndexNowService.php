<?php

namespace App\Services;

use App\Jobs\IndexNow\DispatchHourlyJob;
use App\Models\Product;
use App\Repositories\SettingsRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    private const BING_URL = 'https://www.bing.com/indexnow';

    private const YANDEX_URL = 'https://yandex.com/indexnow';

    private const ENGINES = ['bing', 'yandex'];

    public function __construct(
        private SettingsRepository $settings,
    ) {}

    public function getVerificationKey(string $engine): string
    {
        $sharedKey = $this->getSharedVerificationKey();
        if ($sharedKey !== '') {
            return $sharedKey;
        }

        return $this->settings->get("indexnow.{$engine}.verification_key", '');
    }

    public function setVerificationKey(string $engine, string $key): void
    {
        if (in_array($engine, self::ENGINES, true)) {
            $this->setSharedVerificationKey($key);

            return;
        }

        $this->settings->set("indexnow.{$engine}.verification_key", $key);
    }

    public function getSharedVerificationKey(): string
    {
        $sharedKey = (string) $this->settings->get('indexnow.shared.verification_key', '');
        if ($sharedKey !== '') {
            return $sharedKey;
        }

        foreach (self::ENGINES as $engine) {
            $key = (string) $this->settings->get("indexnow.{$engine}.verification_key", '');
            if ($key !== '') {
                return $key;
            }
        }

        return '';
    }

    public function setSharedVerificationKey(string $key): void
    {
        $this->settings->set('indexnow.shared.verification_key', $key);
        foreach (self::ENGINES as $engine) {
            $this->settings->set("indexnow.{$engine}.verification_key", $key);
        }
    }

    public function isEnabled(string $engine): bool
    {
        return (bool) $this->settings->get("indexnow.{$engine}.enabled", false);
    }

    public function setEnabled(string $engine, bool $enabled): void
    {
        $this->settings->set("indexnow.{$engine}.enabled", $enabled);
    }

    public function getHourlyWeights(string $engine): array
    {
        $default = array_fill(0, 24, 1);
        for ($h = 1; $h <= 6; $h++) {
            $default[$h] = 7;
        }

        return $this->settings->get("indexnow.{$engine}.weights", $default);
    }

    public function setHourlyWeights(string $engine, array $weights): void
    {
        $this->settings->set("indexnow.{$engine}.weights", $weights);
    }

    public function getDailyLimit(): int
    {
        return (int) $this->settings->get('indexnow.daily_limit', 100000);
    }

    public function setDailyLimit(int $limit): void
    {
        $this->settings->set('indexnow.daily_limit', max(1, $limit));
    }

    public function getProductsForHour(string $engine, int $hour): int
    {
        $weights = $this->getHourlyWeights($engine);
        $hourWeight = $weights[$hour] ?? 0;
        $totalWeight = array_sum($weights);

        if ($totalWeight === 0 || $hourWeight === 0) {
            return 0;
        }

        $dailyLimit = $this->getDailyLimit();

        return (int) floor(($hourWeight / $totalWeight) * $dailyLimit);
    }

    public function buildProductUrl(Product $product): string
    {
        $appUrl = rtrim(config('app.url'), '/');

        return "{$appUrl}/product/{$product->id}";
    }

    public function submitBatch(array $urls, string $engine): array
    {
        $key = $this->getVerificationKey($engine);
        if (empty($key)) {
            return ['success' => false, 'error' => 'Verification key not set'];
        }

        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $keyLocation = rtrim(config('app.url'), '/')."/{$key}.txt";

        $endpoint = $engine === 'yandex' ? self::YANDEX_URL : self::BING_URL;

        try {
            $response = Http::timeout(30)
                ->retry(2, 1000)
                ->post($endpoint, [
                    'host' => $host,
                    'key' => $key,
                    'keyLocation' => $keyLocation,
                    'urlList' => $urls,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'submitted' => count($urls)];
            }

            $status = $response->status();
            $body = $response->body();

            Log::warning("IndexNow {$engine} submission failed", [
                'status' => $status,
                'body' => $body,
                'url_count' => count($urls),
            ]);

            return [
                'success' => false,
                'error' => "HTTP {$status}: {$body}",
                'status' => $status,
            ];
        } catch (\Throwable $e) {
            Log::error("IndexNow {$engine} HTTP error: ".$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function generateVerificationFile(?string $engine = null): ?string
    {
        $key = $this->getSharedVerificationKey();
        if (empty($key)) {
            return null;
        }

        $path = public_path("{$key}.txt");
        file_put_contents($path, $key);

        return $path;
    }

    public function removeVerificationFile(?string $engine = null): void
    {
        $key = $this->getSharedVerificationKey();
        if (! empty($key)) {
            $path = public_path("{$key}.txt");
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function dispatchContinuousIfDue(): bool
    {
        $lock = Cache::lock('indexnow:auto-dispatch', 10);

        try {
            if (! $lock->get()) {
                return false;
            }

            $hour = (int) now()->format('G');
            $minuteStamp = now()->format('Y-m-d-H-i');
            $lastMinuteStamp = (string) $this->settings->get('indexnow.last_dispatched_minute', '');
            if ($lastMinuteStamp === $minuteStamp) {
                return false;
            }

            if (! $this->hasDispatchableEngine($hour)) {
                return false;
            }

            $perMinuteCap = $this->getPerMinuteCap();
            DispatchHourlyJob::dispatch($hour, $perMinuteCap);
            $this->settings->set('indexnow.last_dispatched_minute', $minuteStamp);

            return true;
        } finally {
            optional($lock)->release();
        }
    }

    private function hasDispatchableEngine(int $hour): bool
    {
        foreach (self::ENGINES as $engine) {
            if (! $this->isEnabled($engine)) {
                continue;
            }

            if ($this->getVerificationKey($engine) === '') {
                continue;
            }

            $scheduled = $this->getProductsForHour($engine, $hour);
            $effective = max($scheduled, $this->getPerMinuteCap());
            if ($effective > 0) {
                return true;
            }
        }

        return false;
    }

    private function getPerMinuteCap(): int
    {
        $cap = (int) ceil($this->getDailyLimit() / 1440);

        return max(1, $cap);
    }

    public function engineLabel(string $engine): string
    {
        return $engine === 'bing' ? 'بینگ' : 'یاندکس';
    }
}
