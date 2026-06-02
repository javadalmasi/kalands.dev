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
        $affiliate = AffiliateLink::query()->where('slug', $slug)->first();

        // If link exists in DB, handle it
        if ($affiliate) {
            if ($affiliate->status !== 'active') {
                abort(404);
            }

            $affiliate->increment('click_count');
            $this->trackDailyStat($affiliate->store);
            return $this->processRedirection($affiliate->link, $settingsRepository, $affiliate->store);
        }

        // If not in DB, try to reverse the slug to product_id
        // Handle Basalam Product: b{id}
        if (preg_match('/^b([0-9a-z]+)$/', $slug, $matches)) {
            $productId = base_convert($matches[1], 36, 10);
            return $this->fetchFromBasalamAndRedirect($productId, $slug, $settingsRepository);
        }

        // Handle Digikala Product: d{id}
        if (preg_match('/^d([0-9a-z]+)$/', $slug, $matches)) {
            $productId = base_convert($matches[1], 36, 10);
            return $this->processDigikalaAndRedirect($productId, $slug, $settingsRepository);
        }

        // Handle Search Links: ds_{base64_query} or bs_{base64_query}
        if (preg_match('/^(ds|bs)_([a-zA-Z0-9\-\_=]+)$/', $slug, $matches)) {
            $store = $matches[1] === 'ds' ? 'digikala' : 'basalam';
            $query = base64_decode(str_replace(['-', '_'], ['+', '/'], $matches[2]));
            if ($query) {
                return $this->processSearchAndRedirect($query, $slug, $store, $settingsRepository);
            }
        }

        abort(404);
    }

    public function fetchAndRedirect(string $productId, SettingsRepository $settingsRepository): RedirectResponse
    {
        // Legacy support or internal use
        $slug = 'b' . base_convert($productId, 10, 36);
        return $this->redirect($slug, $settingsRepository);
    }

    private function fetchFromBasalamAndRedirect(string $productId, string $slug, SettingsRepository $settingsRepository): RedirectResponse
    {
        $settings = $settingsRepository->get('affiliate.basalam', []);
        $apiUrl = 'https://api-affiliate.basalam.com/api/v1/tracking/links';

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0',
                'Accept' => '*/*',
                'Referer' => 'https://affiliate.basalam.com/',
                'Origin' => 'https://affiliate.basalam.com',
                'Cookie' => 'accessToken=' . ($settings['access_token'] ?? ''),
            ])->post($apiUrl, [
                'merchant_id' => (int) ($settings['merchant_id'] ?? 0),
                'reference_type' => 'PRODUCT',
                'reference_id' => $productId,
                'title' => 'لینک محصول',
                'utm_campaign' => 'affiliate_' . round(microtime(true) * 1000)
            ]);

            $data = $response->json();

            if (isset($data['detail'])) {
                AffiliateLink::query()->updateOrCreate(
                    ['product_id' => $productId, 'store' => 'basalam'],
                    ['link' => 'error', 'status' => 'error', 'slug' => $slug]
                );
                abort(404, 'محصول یافت نشد');
            }

            if (isset($data['short_url'])) {
                $shortUrl = $data['short_url'];
                $prefix = rtrim($settings['url_prefix'] ?? 'https://a.bslm.ir/api/v1/tracking/click/', '/') . '/';

                $linkToSave = str_starts_with($shortUrl, $prefix)
                    ? substr($shortUrl, strlen($prefix))
                    : $shortUrl;

                $affiliate = AffiliateLink::query()->updateOrCreate(
                    ['product_id' => $productId, 'store' => 'basalam'],
                    ['link' => $linkToSave, 'status' => 'active', 'slug' => $slug]
                );
                $affiliate->increment('click_count');
                $this->trackDailyStat('basalam');

                return $this->processRedirection($linkToSave, $settingsRepository, 'basalam');
            }

            throw new \Exception('Short URL not found in response');
        } catch (\Throwable $exception) {
            AffiliateLink::query()->updateOrCreate(
                ['product_id' => $productId, 'store' => 'basalam'],
                ['link' => 'error', 'status' => 'error', 'slug' => $slug]
            );

            abort(404);
        }
    }

    private function processDigikalaAndRedirect(string $productId, string $slug, SettingsRepository $settingsRepository): RedirectResponse
    {
        $affiliate = AffiliateLink::query()->updateOrCreate(
            ['product_id' => $productId, 'store' => 'digikala'],
            ['slug' => $slug, 'link' => 'dgkl', 'status' => 'active']
        );
        $affiliate->increment('click_count');
        $this->trackDailyStat('digikala');

        $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/product/dkp-' . $productId . '/');
        return $this->secureRedirect($fullUrl);
    }

    private function processSearchAndRedirect(string $query, string $slug, string $store, SettingsRepository $settingsRepository): RedirectResponse
    {
        $affiliate = AffiliateLink::query()->updateOrCreate(
            ['product_id' => 'search:' . md5($query), 'store' => $store],
            ['slug' => $slug, 'link' => 'search:' . $query, 'status' => 'active']
        );
        $affiliate->increment('click_count');
        $this->trackDailyStat($store);

        if ($store === 'digikala') {
            $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/search/?q=' . urlencode($query));
        } else {
            // Basalam search logic if needed, but for now we follow the user's pattern
            $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/search/?q=' . urlencode($query));
        }

        return $this->secureRedirect($fullUrl);
    }

    private function trackDailyStat(string $store): void
    {
        \App\Models\AffiliateDailyStat::query()->updateOrCreate(
            ['date' => now()->toDateString(), 'store' => $store],
            ['clicks' => \Illuminate\Support\Facades\DB::raw('clicks + 1')]
        );
    }

    private function processRedirection(string $savedLink, SettingsRepository $settingsRepository, string $store = 'basalam'): RedirectResponse
    {
        if ($store === 'digikala') {
            $slug = request()->route('slug');
            if (str_starts_with($slug, 'ds_')) {
                $query = base64_decode(str_replace(['-', '_'], ['+', '/'], substr($slug, 3)));
                $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/search/?q=' . urlencode($query));
            } else {
                $productId = base_convert(substr($slug, 1), 36, 10);
                $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/product/dkp-' . $productId . '/');
            }
            return $this->secureRedirect($fullUrl);
        }

        if (str_starts_with($savedLink, 'search:')) {
             $query = substr($savedLink, 7);
             $fullUrl = 'https://dgkl.io/api/v1/Click/b/4dJ4L?b64=' . base64_encode('https://www.digikala.com/search/?q=' . urlencode($query));
             return $this->secureRedirect($fullUrl);
        }

        $settings = $settingsRepository->get('affiliate.basalam', []);
        $prefix = rtrim($settings['url_prefix'] ?? 'https://a.bslm.ir/api/v1/tracking/click/', '/') . '/';

        $fullUrl = str_contains($savedLink, 'http') ? $savedLink : $prefix . $savedLink;
        return $this->secureRedirect($fullUrl);
    }

    private function secureRedirect(string $link): RedirectResponse
    {
        return redirect()->away($link)->withHeaders([
            'X-Robots-Tag' => 'noindex, noarchive, nofollow',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }
}
