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

        $customCcTtl = $cacheSettings['visitor_info_custom_cc_ttl'] ?? null;
        $customCcType = $cacheSettings['visitor_info_custom_cc_type'] ?? null;
        $customLscTtl = $cacheSettings['visitor_info_custom_lsc_ttl'] ?? null;
        $customLscType = $cacheSettings['visitor_info_custom_lsc_type'] ?? null;
        $customCdnTtl = $cacheSettings['visitor_info_custom_cdn_ttl'] ?? null;
        $customCdnType = $cacheSettings['visitor_info_custom_cdn_type'] ?? null;
        $customCfTtl = $cacheSettings['visitor_info_custom_cf_ttl'] ?? null;
        $customCfType = $cacheSettings['visitor_info_custom_cf_type'] ?? null;

        $ccTtl = $customCcTtl ?? $ttl;
        $ccType = $customCcType ?? $type;
        $lscTtl = $customLscTtl ?? $ttl;
        $lscType = $customLscType ?? $type;
        $cdnTtl = $customCdnTtl ?? $ttl;
        $cdnType = $customCdnType ?? $type;
        $cfTtl = $customCfTtl ?? $ttl;
        $cfType = $customCfType ?? $type;

        if ($ttl > 0) {
            $response->header('Cache-Control', "{$ccType}, max-age={$ccTtl}");

            $cdnValue = $cdnType === 'public' ? "max-age={$cdnTtl}" : null;
            if ($cdnValue) {
                $response->header('CDN-Cache-Control', $cdnValue);
            }

            $cfValue = $cfType === 'public' ? "max-age={$cfTtl}" : null;
            if ($cfValue) {
                $response->header('Cloudflare-CDN-Cache-Control', $cfValue);
            }

            $response->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $ttl));

            if ($litespeedEnabled) {
                $response->header('X-LiteSpeed-Cache-Control', "{$lscType}, max-age={$lscTtl}");
            }
        }

        return $response;
    }
}
