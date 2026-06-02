<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GeoIp2\Database\Reader;

class VisitorInfoController extends Controller
{
    /**
     * Display visitor information in a plain text format.
     * Inspired by Cloudflare's cdn-cgi/trace
     */
    public function index(Request $request, \App\Repositories\SettingsRepository $settingsRepository)
    {
        $ip = $request->ip();
        $userAgent = $request->header('User-Agent');
        $host = $request->header('Host');
        $scheme = $request->getScheme();
        $protocol = $request->getProtocolVersion();

        $asn = 'unknown';
        $loc = 'unknown';

        try {
            $asnDatabasePath = storage_path('app/geoip/GeoLite2-ASN.mmdb');
            if (file_exists($asnDatabasePath)) {
                $readerAsn = new Reader($asnDatabasePath);
                $record = $readerAsn->asn($ip);
                $asn = $record->autonomousSystemNumber;
                $readerAsn->close();
            }
        } catch (\Exception $e) {}

        try {
            $countryDatabasePath = storage_path('app/geoip/GeoLite2-Country.mmdb');
            if (file_exists($countryDatabasePath)) {
                $readerCountry = new Reader($countryDatabasePath);
                $record = $readerCountry->country($ip);
                $loc = $record->country->isoCode ?? 'unknown';
                $readerCountry->close();
            }
        } catch (\Exception $e) {}

        $isBot = $request->isRobot() ? 'on' : 'off';
        $asnbot = $request->hasHeader('asnbot') ? 'on' : 'off';

        $output = [
            'ip' => $ip,
            'h' => $host,
            'visit_scheme' => $scheme,
            'uag' => $userAgent,
            'loc' => $loc,
            'asn' => $asn,
            'http' => 'HTTP/' . $protocol,
            'st' => $isBot,
            'ast' => $asnbot,
            'ts' => microtime(true),
        ];

        $plainText = '';
        foreach ($output as $key => $value) {
            $plainText .= "{$key}={$value}\n";
        }

        $response = response($plainText)->header('Content-Type', 'text/plain');

        $cacheSettings = $settingsRepository->get('cache.webservices', []);
        $ttl = (int) ($cacheSettings['visitor_info_ttl'] ?? 3600);
        $type = $cacheSettings['visitor_info_cache_type'] ?? 'private';
        $litespeedEnabled = (bool) ($cacheSettings['visitor_info_litespeed'] ?? true);

        $customCc = $cacheSettings['visitor_info_custom_cc'] ?? null;
        $customLsc = $cacheSettings['visitor_info_custom_lsc'] ?? null;
        $customCdn = $cacheSettings['visitor_info_custom_cdn'] ?? null;
        $customCf = $cacheSettings['visitor_info_custom_cf'] ?? null;

        if ($ttl > 0 || $customCc) {
            $response->header('Cache-Control', $customCc ?: "{$type}, max-age={$ttl}");

            $cdnValue = $customCdn ?: ($type === 'public' ? "max-age={$ttl}" : null);
            if ($cdnValue) {
                $response->header('CDN-Cache-Control', $cdnValue);
            }

            $cfValue = $customCf ?: ($type === 'public' ? "max-age={$ttl}" : null);
            if ($cfValue) {
                $response->header('Cloudflare-CDN-Cache-Control', $cfValue);
            }

            $response->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $ttl));

            if ($litespeedEnabled || $customLsc) {
                $response->header('X-LiteSpeed-Cache-Control', $customLsc ?: "{$type}, max-age={$ttl}");
            }
        }

        return $response;
    }
}
