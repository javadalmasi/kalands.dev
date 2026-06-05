<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Repositories\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;

class AffiliateRedirectController extends Controller
{
    public function redirect(string $slug, SettingsRepository $settingsRepository): RedirectResponse
    {
        $cacheSettings = $settingsRepository->get('cache.webservices', []);

        // Decode Slug using Offset
        // Handle Basalam Product: b{id}
        if (preg_match('/^b([0-9a-z]+)$/', $slug, $matches)) {
            $productId = $matches[1];
            return $this->fetchFromBasalamAndRedirect($productId, $slug, $settingsRepository, $cacheSettings);
        }

        // Handle Digikala Product: d{id}
        if (preg_match('/^d([0-9a-z]+)$/', $slug, $matches)) {
            $productId = $matches[1];
            return $this->redirectToDigikala($productId, $cacheSettings);
        }

        // Handle Digikala Search Links: ds_{base64_query}
        if (preg_match('/^ds_([a-zA-Z0-9\-\_=]+)$/', $slug, $matches)) {
            $query = base64_decode(str_replace(['-', '_'], ['+', '/'], $matches[1]));
            if ($query) {
                return $this->redirectToSearch($query, 'digikala', $cacheSettings);
            }
        }

        abort(404);
    }

    private function fetchFromBasalamAndRedirect(string $productId, string $slug, SettingsRepository $settingsRepository, array $cacheSettings = []): RedirectResponse
    {
        // Check DB first
        $affiliate = AffiliateLink::query()->where('product_id', $productId)->where('store', 'basalam')->first();
        
        $settings = $settingsRepository->get('affiliate.basalam', []);
        $prefix = rtrim($settings['url_prefix'] ?? 'https://a.bslm.ir/api/v1/tracking/click/', '/') . '/';

        if ($affiliate) {
            if ($affiliate->status === 'error') {
                abort(404, 'محصول یافت نشد');
            }
            
            $url = str_starts_with($affiliate->link, 'http') ? $affiliate->link : $prefix . $affiliate->link;
            return $this->secureRedirect($url, $cacheSettings);
        }

        // Request to API
        $apiUrl = 'https://api-affiliate.basalam.com/api/v1/tracking/links';

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:151.0) Gecko/20100101 Firefox/151.0',
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.9,fa-IR;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br, zstd',
                'Referer' => 'https://affiliate.basalam.com/',
                'Content-Type' => 'application/json',
                'Origin' => 'https://affiliate.basalam.com',
                'Connection' => 'keep-alive',
                'Cookie' => 'accessToken=' . ($settings['access_token'] ?? ''),
                'Sec-Fetch-Dest' => 'empty',
                'Sec-Fetch-Mode' => 'cors',
                'Sec-Fetch-Site' => 'same-site',
                'Priority' => 'u=4',
                'TE' => 'trailers',
            ])->post($apiUrl, [
                'merchant_id' => (int) ($settings['merchant_id'] ?? 0),
                'reference_type' => 'PRODUCT',
                'reference_id' => $productId,
                'title' => 'لینک محصول',
                'utm_campaign' => 'affiliate_' . round(microtime(true) * 1000)
            ]);

            $data = $response->json();
            \Illuminate\Support\Facades\Log::info('Basalam API Response', ['status' => $response->status(), 'data' => $data]);

            if (isset($data['detail'])) {
                AffiliateLink::query()->create([
                    'product_id' => $productId,
                    'store' => 'basalam',
                    'link' => $data['detail'],
                    'status' => 'error',
                    'slug' => $slug
                ]);
                abort(404, 'محصول یافت نشد');
            }

            if (isset($data['short_url'])) {
                $shortUrl = $data['short_url'];
                $linkToSave = str_starts_with($shortUrl, $prefix)
                    ? substr($shortUrl, strlen($prefix))
                    : $shortUrl;

                AffiliateLink::query()->create([
                    'product_id' => $productId,
                    'store' => 'basalam',
                    'link' => $linkToSave,
                    'status' => 'active',
                    'slug' => $slug
                ]);

                return $this->secureRedirect($shortUrl, $cacheSettings);
            }

            throw new \Exception('Short URL not found in response');
        } catch (\Throwable $exception) {
            abort(404);
        }
    }

    private function redirectToDigikala(int $productId, array $cacheSettings = []): RedirectResponse
    {
        $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/product/dkp-' . $productId . '/');
        return $this->secureRedirect($fullUrl, $cacheSettings);
    }

    private function redirectToSearch(string $query, string $store, array $cacheSettings = []): RedirectResponse
    {
        $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/search/?q=' . urlencode($query));
        return $this->secureRedirect($fullUrl, $cacheSettings);
    }

    private function secureRedirect(string $link, array $cacheSettings = []): RedirectResponse
    {
        $response = redirect()->away($link)->withHeaders([
            'X-Robots-Tag' => 'noindex, noarchive, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ]);

        $ttl = (int) ($cacheSettings['affiliate_ttl'] ?? 31536000);
        $type = $cacheSettings['affiliate_cache_type'] ?? 'public';
        $litespeedEnabled = (bool) ($cacheSettings['affiliate_litespeed'] ?? true);

        $customCcTtl = $cacheSettings['affiliate_custom_cc_ttl'] ?? null;
        $customCcType = $cacheSettings['affiliate_custom_cc_type'] ?? null;
        $customLscTtl = $cacheSettings['affiliate_custom_lsc_ttl'] ?? null;
        $customLscType = $cacheSettings['affiliate_custom_lsc_type'] ?? null;
        $customCdnTtl = $cacheSettings['affiliate_custom_cdn_ttl'] ?? null;
        $customCdnType = $cacheSettings['affiliate_custom_cdn_type'] ?? null;
        $customCfTtl = $cacheSettings['affiliate_custom_cf_ttl'] ?? null;
        $customCfType = $cacheSettings['affiliate_custom_cf_type'] ?? null;

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

        return $response;
    }
}
