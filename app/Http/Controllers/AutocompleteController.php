<?php

namespace App\Http\Controllers;

use App\Repositories\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AutocompleteController extends Controller
{
    public function search(Request $request, SettingsRepository $settingsRepository)
    {
        $query = $request->input('q', '');

        if (empty(trim($query))) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'auto_complete' => [],
                ],
            ]);
        }

        try {
            // Make request to the actual API
            $response = Http::timeout(10)->get("https://www.kalands.ir/api/services/autocomplete/?q={$query}");

            if ($response->successful()) {
                $responseData = $response->json();

                // Extract only the auto_complete section as requested
                $autoCompleteData = $responseData['data']['auto_complete'] ?? [];

                $response = response()->json([
                    'status' => 200,
                    'data' => [
                        'auto_complete' => $autoCompleteData,
                    ],
                ]);

                // Apply caching headers if results exist
                if (! empty($autoCompleteData)) {
                    $cacheSettings = $settingsRepository->get('cache.webservices', []);
                    $ttl = (int) ($cacheSettings['autocomplete_ttl'] ?? 31536000);
                    $litespeedEnabled = (bool) ($cacheSettings['autocomplete_litespeed'] ?? true);
                    $type = $cacheSettings['autocomplete_cache_type'] ?? 'public';

                    $customCcTtl = $cacheSettings['autocomplete_custom_cc_ttl'] ?? null;
                    $customCcType = $cacheSettings['autocomplete_custom_cc_type'] ?? null;
                    $customLscTtl = $cacheSettings['autocomplete_custom_lsc_ttl'] ?? null;
                    $customLscType = $cacheSettings['autocomplete_custom_lsc_type'] ?? null;
                    $customCdnTtl = $cacheSettings['autocomplete_custom_cdn_ttl'] ?? null;
                    $customCdnType = $cacheSettings['autocomplete_custom_cdn_type'] ?? null;
                    $customCfTtl = $cacheSettings['autocomplete_custom_cf_ttl'] ?? null;
                    $customCfType = $cacheSettings['autocomplete_custom_cf_type'] ?? null;

                    $ccTtl = $customCcTtl ?? $ttl;
                    $ccType = $customCcType ?? $type;
                    $lscTtl = $customLscTtl ?? $ttl;
                    $lscType = $customLscType ?? $type;
                    $cdnTtl = $customCdnTtl ?? $ttl;
                    $cdnType = $customCdnType ?? $type;
                    $cfTtl = $customCfTtl ?? $ttl;
                    $cfType = $customCfType ?? $type;

                    if ($ttl > 0) {
                        $response->header('Cache-Control', "{$ccType}, max-age={$ccTtl}, s-maxage={$ccTtl}");

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
                }

                return $response;
            } else {
                // Return empty results if the external API fails
                return response()->json([
                    'status' => 200,
                    'data' => [
                        'auto_complete' => [],
                    ],
                ]);
            }
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Autocomplete API error: '.$e->getMessage());

            // Return empty results if there's an error
            return response()->json([
                'status' => 200,
                'data' => [
                    'auto_complete' => [],
                ],
            ]);
        }
    }
}
