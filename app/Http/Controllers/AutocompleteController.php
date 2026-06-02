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
                ]
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
                        'auto_complete' => $autoCompleteData
                    ]
                ]);

                // Apply caching headers if results exist
                if (!empty($autoCompleteData)) {
                    $cacheSettings = $settingsRepository->get('cache.webservices', []);
                    $ttl = (int) ($cacheSettings['autocomplete_ttl'] ?? 31536000);
                    $litespeedEnabled = (bool) ($cacheSettings['autocomplete_litespeed'] ?? true);
                    $type = $cacheSettings['autocomplete_cache_type'] ?? 'public';

                    $customCc = $cacheSettings['autocomplete_custom_cc'] ?? null;
                    $customLsc = $cacheSettings['autocomplete_custom_lsc'] ?? null;
                    $customCdn = $cacheSettings['autocomplete_custom_cdn'] ?? null;
                    $customCf = $cacheSettings['autocomplete_custom_cf'] ?? null;

                    if ($ttl > 0 || $customCc) {
                        $response->header('Cache-Control', $customCc ?: "{$type}, max-age={$ttl}, s-maxage={$ttl}");

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
                }

                return $response;
            } else {
                // Return empty results if the external API fails
                return response()->json([
                    'status' => 200,
                    'data' => [
                        'auto_complete' => []
                    ]
                ]);
            }
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Autocomplete API error: ' . $e->getMessage());

            // Return empty results if there's an error
            return response()->json([
                'status' => 200,
                'data' => [
                    'auto_complete' => []
                ]
            ]);
        }
    }
}