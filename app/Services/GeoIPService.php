<?php

namespace App\Services;

use App\Repositories\SettingsRepository;
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIPService
{
    protected $settings;

    protected $dbDir;

    protected $files = [
        'GeoLite2-ASN.mmdb' => 'https://git.io/GeoLite2-ASN.mmdb',
        'GeoLite2-Country.mmdb' => 'https://git.io/GeoLite2-Country.mmdb',
    ];

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
        $this->dbDir = storage_path('app/geoip');
    }

    /**
     * Update GeoIP databases.
     *
     * @return array Result of the update process.
     */
    public function updateDatabases(): array
    {
        if (! File::isDirectory($this->dbDir)) {
            File::makeDirectory($this->dbDir, 0755, true);
        }

        $results = [];
        $overallSuccess = true;

        foreach ($this->files as $name => $url) {
            $tmpFile = $this->dbDir.'/'.$name.'.tmp';
            $finalFile = $this->dbDir.'/'.$name;

            try {
                $response = Http::timeout(120)->withOptions([
                    'sink' => $tmpFile,
                    'follow_redirects' => true,
                ])->get($url);

                if ($response->successful() && File::exists($tmpFile) && File::size($tmpFile) > 0) {
                    if (File::exists($finalFile)) {
                        File::delete($finalFile);
                    }
                    File::move($tmpFile, $finalFile);
                    $results[$name] = [
                        'status' => 'success',
                        'message' => "بروزرسانی موفق: $name",
                    ];
                } else {
                    if (File::exists($tmpFile)) {
                        File::delete($tmpFile);
                    }
                    $results[$name] = [
                        'status' => 'failed',
                        'message' => "خطا در دانلود: $name (کد وضعیت: {$response->status()})",
                    ];
                    $overallSuccess = false;
                }
            } catch (\Throwable $e) {
                if (File::exists($tmpFile)) {
                    File::delete($tmpFile);
                }
                $results[$name] = [
                    'status' => 'failed',
                    'message' => "خطای سیستمی در دانلود $name: ".$e->getMessage(),
                ];
                $overallSuccess = false;
            }
        }

        $this->logUpdate($results, $overallSuccess);

        // Update last run time
        $this->settings->set('geoip.last_run', now()->toDateTimeString());

        return [
            'success' => $overallSuccess,
            'details' => $results,
        ];
    }

    /**
     * Log the update result.
     */
    protected function logUpdate(array $details, bool $success): void
    {
        $logs = $this->settings->get('geoip.logs', []);

        // Add new log to the beginning
        array_unshift($logs, [
            'executed_at' => now()->toDateTimeString(),
            'status' => $success ? 'success' : 'failed',
            'details' => $details,
        ]);

        // Keep only last 50 logs
        $logs = array_slice($logs, 0, 50);

        $this->settings->set('geoip.logs', $logs);
    }

    public function lookupCountry(string $ip): array
    {
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return [
                'country_code' => null,
                'country_name' => null,
            ];
        }

        return Cache::remember('geoip:country:'.hash('sha256', $ip), 86400, function () use ($ip) {
            $databasePath = $this->dbDir.'/GeoLite2-Country.mmdb';
            if (! File::exists($databasePath)) {
                return [
                    'country_code' => null,
                    'country_name' => null,
                ];
            }

            try {
                $reader = new Reader($databasePath);
                $record = $reader->country($ip);
                $reader->close();

                return [
                    'country_code' => $record->country->isoCode ?: null,
                    'country_name' => $record->country->names['fa'] ?? $record->country->name ?? null,
                ];
            } catch (\Throwable $exception) {
                Log::warning('GeoIP country lookup failed: '.$exception->getMessage());

                return [
                    'country_code' => null,
                    'country_name' => null,
                ];
            }
        });
    }
}
