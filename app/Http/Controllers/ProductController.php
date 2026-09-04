<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessProductCategoriesJob;
use App\Models\Comment;
use App\Models\Product;
use App\Models\ProductIdMapping;
use App\Repositories\SettingsRepository;
use App\Services\Auth\JsChallengeService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    private static function resolveProductId(string $productId, string $store): string
    {
        $mapping = ProductIdMapping::where('old_product_id', $productId)
            ->where('store', $store)
            ->where('is_active', true)
            ->first();

        return $mapping?->new_product_id ?? $productId;
    }

    public static function DigikalaApi($url, $version, $skip = null, $cache = null)
    {
        // www.kalands.ir/api/services/dcom
        // http://bws.kalands.ir/api/3600/
        if ($cache) {
            $backend = '89.42.44.25/api/86400';
        } else {
            $backend = '89.42.44.25/api/3600';
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => request()->header('user-agent'),
                'Host' => 'bws.kalands.ir',
            ])->withOptions(['verify' => false])
                ->timeout(10)
                ->get('http://'.$backend.'/'.$version.'/'.$url);
        } catch (\Throwable $exception) {
            if ($skip == true) {
                return ['status' => 503, 'data' => ['products' => []]];
            }

            abort(503, 'ارتباط با سرویس محصول برقرار نشد.');
        }

        $data = $response->json() ?? [];
        $status = (int) ($data['status'] ?? $response->status());

        if ($status === 200 && $response->status() === 200) {
            return $data;
        }

        if ($skip != true) {
            if (($status === 301 || $status === 302) && ($redirectUri = data_get($data, 'redirect_url.uri'))) {
                if (str_contains($redirectUri, '/fresh/product/')) {
                    try {
                        $freshResponse = Http::withHeaders([
                            'User-Agent' => request()->header('user-agent'),
                            'Host' => 'bws.kalands.ir',
                        ])->withOptions(['verify' => false])
                            ->timeout(10)
                            ->get('http://'.$backend.'/fresh/v1/'.$url);

                        return $freshResponse->json() ?? [];
                    } catch (\Throwable $exception) {
                        abort(503, 'ارتباط با سرویس محصول برقرار نشد.');
                    }
                }

                if (preg_match('#/product/dkp-(\d+)#', $redirectUri, $matches)) {
                    $newProductId = $matches[1];

                    if (preg_match('#product/(\d+)#', $url, $oldMatches)) {
                        $oldProductId = $oldMatches[1];
                        if ($oldProductId !== $newProductId) {
                            ProductIdMapping::updateOrCreate(
                                [
                                    'old_product_id' => $oldProductId,
                                    'store' => 'digikala',
                                ],
                                [
                                    'new_product_id' => $newProductId,
                                    'reason' => 'Auto-created from 301 redirect',
                                    'is_active' => true,
                                ]
                            );
                        }
                    }

                    return self::DigikalaApi('product/'.$newProductId.'/', $version, $skip, $cache);
                }
            }

            abort($status, 'Error Code : '.$status);
        }

        return $data;
    }

    public static function BasalamApi($url, $version, $skip = null)
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => request()->header('user-agent'),
                'Host' => 'bws.kalands.ir',
            ])->withOptions(['verify' => false])
                ->timeout(10)
                ->get('https://89.42.44.25/api/bs/86400/'.$version.'/'.$url);
        } catch (\Throwable $exception) {
            if ($skip == true) {
                return null;
            }

            abort(503, 'ارتباط با سرویس باسلام برقرار نشد.');
        }

        if ($response->ok()) {
            return $response->json();
        }

        if ($skip != true) {
            abort($response->status(), 'Error Code : '.$response->status());
        }

        return null;
    }

    public static function KhanomiApi($url, $version, $skip = null)
    {
        try {
            $response = Http::withHeaders(['User-Agent' => request()->header('user-agent')])
                ->timeout(10)
                ->get('https://core.basalam.com/'.$version.'/'.$url);
        } catch (\Throwable $exception) {
            if ($skip == true) {
                return null;
            }

            abort(503, 'ارتباط با سرویس برقرار نشد.');
        }

        if ($response->ok()) {
            return $response->json();
        }

        if ($skip != true) {
            abort($response->status(), 'Error Code : '.$response->status());
        }

        return null;
    }

    public static function show(Product $product)
    {
        $challenge = app(JsChallengeService::class)->issue('comment_form');
        $comments = Comment::query()
            ->where('product_id', $product->id)
            ->where('status', Comment::STATUS_APPROVED)
            ->whereNull('parent_id')
            ->with('children')
            ->get();

        return view('products.show', compact('product', 'comments', 'challenge'));
    }

    // Views Functions
    private static function renderInactiveProductView($title = null)
    {
        $recommendations = [];
        $recommendationTitle = 'محصولات پیشنهادی';

        if ($title) {
            // Clean title for better search results
            $searchQuery = self::TextMuted($title);
            $searchData = self::DigikalaApi('search/?q='.urlencode($searchQuery).'&page=1', 'v1', true);
            $recommendations = $searchData['data']['products'] ?? [];
            if (! empty($recommendations)) {
                $recommendationTitle = 'محصولات مشابه';
            }
        }

        if (empty($recommendations)) {
            $offersData = self::DigikalaApi('incredible-offers/products/?page=1', 'v1', true);
            $recommendations = $offersData['data']['products'] ?? [];
            $recommendationTitle = 'پیشنهادات شگفت‌انگیز';
        }

        // Filter and deduplicate recommendations
        $recommendations = array_values(array_filter($recommendations, function ($item) {
            $uri = $item['url']['uri'] ?? '';
            if (str_contains($uri, 'dkp-')) {
                return str_starts_with($uri, '/product/dkp-');
            }

            return true;
        }));

        $recommendations = array_slice($recommendations, 0, 16);

        return response()->view('errors.product-inactive', [
            'recommendations' => $recommendations,
            'recommendationTitle' => $recommendationTitle,
            'productTitle' => $title,
        ]);
    }

    public static function ProductParser($ProductID, $ProductName)
    {
        if (str_starts_with(request()->path(), 'product/XBS-')) {
            $data = self::BasalamApi('product/'.str_replace('XBS-', '', $ProductID), 'api_v1.0');
            $basalamProduct = Product::find((string) $data['id']);
            if ($basalamProduct && ! $basalamProduct->is_active) {
                return self::renderInactiveProductView($data['title'] ?? null);
            }
            $basalamProduct = Product::where('id', (string) $data['id'])->first();
            if (! $basalamProduct) {
                $basalamProduct = Product::create([
                    'id' => (string) $data['id'],
                    'title' => $data['title'],
                    'store' => 'basalam',
                ]);
            }

            if (! $basalamProduct->category_id && ! empty($data['navigation'])) {
                // Prepare navigation data (Basalam uses a different structure)
                $breadcrumb = [];
                $nav = $data['navigation'];
                // Traverse up
                $tempNav = $nav;
                while ($tempNav) {
                    array_unshift($breadcrumb, ['title' => $tempNav['title']]);
                    $tempNav = $tempNav['parent'] ?? null;
                }

                ProcessProductCategoriesJob::dispatch((string) $data['id'], $breadcrumb, 'basalam');
            }
            $comments = Comment::query()
                ->where('product_id', (string) $data['id'])
                ->where('status', Comment::STATUS_APPROVED)
                ->whereNull('parent_id')
                ->with([
                    'children' => function ($query) {
                        $query->with('votes');
                    },
                    'votes',
                ])
                ->withCount([
                    'votes as likes_count' => fn ($query) => $query->where('vote', 1),
                    'votes as dislikes_count' => fn ($query) => $query->where('vote', -1),
                ])
                ->get();
            $challenge = app(JsChallengeService::class)->issue('comment_form');
            $commentSettings = app(SettingsRepository::class)->get('comments.settings', ['enabled' => true, 'disabled_message' => 'ارسال نظر در این زمان ممکن نیست.']);

            $response = response()->view('layouts.product.index', [
                'data' => $data,
                'product' => Product::find((string) $data['id']),
                'comments' => $comments,
                'challenge' => $challenge,
                'commentSettings' => $commentSettings,
                'store' => 'basalam',
            ]);

            self::applyProductCacheHeaders($response);

            return $response;
        } elseif (str_starts_with(request()->path(), 'product/xbs-')) {
            return redirect(config('app.url').'/product/'.str_replace('xbs-', 'XBS-', $ProductID).'/'.$ProductName);
        } else {
            $resolvedProductId = self::resolveProductId($ProductID, 'digikala');
            $data = self::DigikalaApi('product/'.$resolvedProductId.'/', 'v2');

            $actualProductId = $data['data']['product']['id'] ?? $resolvedProductId;

            $digikalaProduct = Product::find($actualProductId);
            if ($digikalaProduct && ! $digikalaProduct->is_active) {
                return self::renderInactiveProductView($data['data']['product']['title_fa'] ?? null);
            }

            if (! empty($data['data']['product']['is_inactive'])) {
                $title = $data['data']['product']['title_fa'] ?? null;
                Product::where('id', $actualProductId)->update(['is_active' => false]);

                return self::renderInactiveProductView($title);
            }

            $digikalaProduct = Product::where('id', (string) $actualProductId)->first();
            if (! $digikalaProduct) {
                $digikalaProduct = Product::create([
                    'id' => (string) $actualProductId,
                    'title' => $data['data']['product']['title_fa'],
                    'store' => 'digikala',
                    'is_active' => true,
                ]);
            }

            if (! $digikalaProduct->category_id && ! empty($data['data']['product']['breadcrumb'])) {
                $breadcrumb = $data['data']['product']['breadcrumb'];

                // 1. Remove the first item if it's "Digikala"
                if (! empty($breadcrumb) && in_array($breadcrumb[0]['title'] ?? '', ['دیجی‌کالا', 'دیجیکالا'])) {
                    array_shift($breadcrumb);
                }

                // 2. Remove the last item which is the product title
                if (count($breadcrumb) > 0) {
                    array_pop($breadcrumb);
                }

                if (! empty($breadcrumb)) {
                    ProcessProductCategoriesJob::dispatch(
                        (string) $data['data']['product']['id'],
                        $breadcrumb,
                        'digikala'
                    );
                }
            }

            $mappedIds = ProductIdMapping::where('new_product_id', $actualProductId)
                ->where('store', 'digikala')
                ->where('is_active', true)
                ->pluck('old_product_id')
                ->toArray();
            $allProductIds = array_merge([$actualProductId], $mappedIds);

            $comments = Comment::query()
                ->whereIn('product_id', $allProductIds)
                ->where('status', Comment::STATUS_APPROVED)
                ->whereNull('parent_id')
                ->with([
                    'children' => function ($query) {
                        $query->with('votes');
                    },
                    'votes',
                ])
                ->withCount([
                    'votes as likes_count' => fn ($query) => $query->where('vote', 1),
                    'votes as dislikes_count' => fn ($query) => $query->where('vote', -1),
                ])
                ->get();
            $challenge = app(JsChallengeService::class)->issue('comment_form');
            $commentSettings = app(SettingsRepository::class)->get('comments.settings', ['enabled' => true, 'disabled_message' => 'ارسال نظر در این زمان ممکن نیست.']);

            $response = response()->view('layouts.product.index', [
                'data' => $data,
                'product' => Product::find($actualProductId),
                'comments' => $comments,
                'challenge' => $challenge,
                'commentSettings' => $commentSettings,
                'store' => 'digikala',
            ]);

            self::applyProductCacheHeaders($response);

            return $response;
        }
    }

    public static function ProductRedirect($ProductID)
    {
        if (str_starts_with(request()->path(), 'product/XBS-')) {
            return redirect(config('app.url').'/product/'.$ProductID.'/no?utm_source=to_new_url&utm_medium=redirect&utm_campaign=old_url');

        } elseif (str_starts_with(request()->path(), 'product/xbs-')) {
            return redirect(config('app.url').'/product/'.str_replace('xbs-', 'XBS-', $ProductID).'/no?utm_source=to_new_url&utm_medium=redirect&utm_campaign=old_url');
        } else {
            $resolvedProductId = self::resolveProductId($ProductID, 'digikala');
            $data = self::DigikalaApi('product/'.$resolvedProductId.'/', 'v2');

            $actualProductId = $data['data']['product']['id'] ?? $resolvedProductId;

            $digikalaProduct = Product::find($actualProductId);
            if ($digikalaProduct && ! $digikalaProduct->is_active) {
                return self::renderInactiveProductView($data['data']['product']['title_fa'] ?? null);
            }

            if (! empty($data['data']['product']['is_inactive'])) {
                $title = $data['data']['product']['title_fa'] ?? null;
                Product::where('id', $actualProductId)->update(['is_active' => false]);

                return self::renderInactiveProductView($title);
            }

            return redirect(str_replace('dkp-', '', $data['data']['product']['url']['uri']), 302);
        }
    }

    public static function ImgProfile($url, $w, $h, $q, $webp, $CleanURl = null, $Store = 'digikala')
    {
        $cdn = config('services.cdn.images');

        if ($Store == 'digikala') {
            $url = str_replace(
                ['https://dkstatics-public.digikala.com/digikala-products/', 'https://dkstatics-public-2.digikala.com/digikala-products/', 'https://dkstatics-private.digikala.com/digikala-products/', 'https://dkstatics-public.digikala.com/digikala-content-creation-requests/', 'https://dkstatics-public-2.digikala.com/digikala-content-creation-requests/', 'https://dkstatics-private.digikala.com/digikala-content-creation-requests/'],
                ['https://'.$cdn.'/klnd/01/', 'https://'.$cdn.'/klnd/02/', 'https://'.$cdn.'/klnd/04/', 'https://'.$cdn.'/klnd/05/', 'https://'.$cdn.'/klnd/05/', 'https://'.$cdn.'/klnd/05/'],
                $url
            );
            $part_url = parse_url($url);
            $hostname = $part_url['host'] ?? '';
            $path = $part_url['path'] ?? '';

            if ($CleanURl == true) {
                return 'https://'.$hostname.$path;
            }

            return 'https://'.$hostname.$path.'?x-oss-process=image/resize,m_pad,h_'.$h.',w_'.$w.',color_FFFFFF/quality,q_'.$q.($webp == true ? '/format,webp' : '');
        }
        if ($Store == 'basalam') {
            preg_match('/https:\/\/statics.basalam.com\/public-([0-9]*)\/users\//', $url, $output_array);
            if (! empty($output_array[1])) {
                $baslink = ['باسلام', 'https://statics.basalam.com/public-'.$output_array[1].'/users/'];
                $kllink = ['کالندز', 'https://'.$cdn.'/klnd/03/'.$output_array[1].'/'];

                return str_replace($baslink, $kllink, $url);
            } else {
                return str_replace('https://statics.basalam.com/public/users/', 'https://'.$cdn.'/klnd/03/', $url);
            }
        }
    }

    public static function GetSpecialLink($shop, $id, $title = null)
    {
        if ($shop == 'digikala') {
            if ($id && is_numeric($id)) {
                return config('app.url').'/go/d'.$id;
            }
            if ($title) {
                $b64 = base64_encode($title);
                $b64 = str_replace(['+', '/', '='], ['-', '_', ''], $b64);

                return config('app.url').'/go/ds_'.$b64;
            }

            return '#';
        }
        if ($shop == 'basalam') {
            if ($id && is_numeric($id)) {
                return config('app.url').'/go/b'.$id;
            }

            return '#';
        }

        return '#';
    }

    public static function GetBaseLink($shop, $id, $title = null)
    {
        if ($shop == 'digikala' && isset($title)) {
            if ($id) {
                return config('app.url').'/product/'.$id.'/'.str_slug_persian($title);
            }

            return config('app.url').'/result/?q='.urlencode($title).'&sort=22';
        }

        return '#';
    }

    public static function AffiliateLinkGenerator($shop, $id, $title = null)
    {
        if (! request()->isRobot() && ! request()->hasHeader('asnbot')) {
            return self::GetSpecialLink($shop, $id, $title);
        } else {
            return self::GetBaseLink($shop, $id, $title);
        }
    }

    // Replace Functions
    public static function LinkReplaced($Query)
    {
        $cdn = config('services.cdn.images');

        $MainLink = [
            'www.digikala.com', '/product/dkp-', '/search',
            'dkstatics-public.digikala.com\/digikala-products\/', 'dkstatics-public-2.digikala.com\/digikala-products\/', 'dkstatics-private.digikala.com\/digikala-products\/', 'dkstatics-public.digikala.com\/digikala-content-creation-requests\/',
            'دیجیکالا', 'دیجی کالا', 'دیجی‌کالا',
            'dkstatics-public.digikala.com/digikala-products/', 'dkstatics-public-2.digikala.com/digikala-products/', 'dkstatics-private.digikala.com/digikala-products/', 'dkstatics-public.digikala.com/digikala-content-creation-requests/',
            'statics.basalam.com\/public\/users\/', 'statics.basalam.com/public/users/',
            'باسلام',
            'quality,q_90', 'quality,q_80',
        ];
        $ReplacedLink = [
            'www.kalands.ir', '/product/', '/result',
            $cdn.'\/klnd\/01', $cdn.'\/klnd\/02', $cdn.'\/klnd\/04', $cdn.'\/klnd\/05',
            'کالندز', 'کالندز', 'کالندز',
            $cdn.'/klnd/01', $cdn.'/klnd/02', $cdn.'/klnd/04', $cdn.'/klnd/05',
            $cdn.'\/klnd\/03', $cdn.'/klnd/03',
            'کالندز',
            'quality,q_90/format,webp', 'quality,q_80/format,webp',
        ];
        $Result = str_replace($MainLink, $ReplacedLink, $Query);

        return $Result;
    }

    public static function TextReplaced($Query)
    {
        $MainText = ['| فروشگاه اینترنتی دیجی‌کالا', 'دیجیکالا', 'دیجی کالا', 'دیجی‌کالا'];
        $ReplacesText = ['', 'کالندز', 'کالندز', 'کالندز'];
        $Result = str_replace($MainText, $ReplacesText, $Query);

        return $Result;
    }

    public static function TextMuted($Query)
    {
        $MainText = ['| فروشگاه اینترنتی دیجی‌کالا', 'دیجیکالا', 'دیجی کالا', 'دیجی‌کالا'];
        $Result = str_replace($MainText, '', $Query);

        return $Result;
    }

    private static function applyProductCacheHeaders(Response $response): void
    {
        $cacheSettings = app(SettingsRepository::class)->get('cache.webservices', []);

        $ttl = (int) ($cacheSettings['product_ttl'] ?? 86400);
        $type = $cacheSettings['product_cache_type'] ?? 'public';
        $litespeedEnabled = (bool) ($cacheSettings['product_litespeed'] ?? true);

        if ($ttl > 0) {
            $response->header('Cache-Control', "{$type}, max-age={$ttl}, s-maxage={$ttl}");
            $response->header('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + $ttl));
            $response->header('CDN-Cache-Control', $type === 'public' ? "max-age={$ttl}" : null);
            $response->header('Cloudflare-CDN-Cache-Control', $type === 'public' ? "max-age={$ttl}" : null);
            if ($litespeedEnabled) {
                $response->header('X-LiteSpeed-Cache-Control', "{$type}, max-age={$ttl}");
            }
        }
    }

    // 3th Functiuons
    public static function RemoveEmoji($text)
    {
        $regexEmoticons = [
            '/[\x{0023}]/u',
            '/[\x{002A}]/u',
            '/[\x{00A9}]/u',
            '/[\x{00AE}]/u',
            '/[\x{200D}]/u',
            '/[\x{203C}]/u',
            '/[\x{2049}]/u',
            '/[\x{20E3}]/u',
            '/[\x{2122}]/u',
            '/[\x{2139}]/u',
            '/[\x{2194}-\x{2199}]/u',
            '/[\x{21A9}-\x{21AA}]/u',
            '/[\x{231A}-\x{231B}]/u',
            '/[\x{2328}]/u',
            '/[\x{23CF}]/u',
            '/[\x{23E9}-\x{23F3}]/u',
            '/[\x{23F8}-\x{23FA}]/u',
            '/[\x{24C2}]/u',
            '/[\x{25AA}-\x{25AB}]/u',
            '/[\x{25B6}]/u',
            '/[\x{25C0}]/u',
            '/[\x{25FB}-\x{25FE}]/u',
            '/[\x{2600}-\x{2604}]/u',
            '/[\x{260E}]/u',
            '/[\x{2611}]/u',
            '/[\x{2614}-\x{2615}]/u',
            '/[\x{2618}]/u',
            '/[\x{261D}]/u',
            '/[\x{2620}]/u',
            '/[\x{2622}-\x{2623}]/u',
            '/[\x{2626}]/u',
            '/[\x{262A}]/u',
            '/[\x{262E}-\x{262F}]/u',
            '/[\x{2638}-\x{263A}]/u',
            '/[\x{2640}]/u',
            '/[\x{2642}]/u',
            '/[\x{2648}-\x{2653}]/u',
            '/[\x{265F}-\x{2660}]/u',
            '/[\x{2663}]/u',
            '/[\x{2665}-\x{2666}]/u',
            '/[\x{2668}]/u',
            '/[\x{267B}]/u',
            '/[\x{267E}-\x{267F}]/u',
            '/[\x{2692}-\x{2697}]/u',
            '/[\x{2699}]/u',
            '/[\x{269B}-\x{269C}]/u',
            '/[\x{26A0}-\x{26A1}]/u',
            '/[\x{26AA}-\x{26AB}]/u',
            '/[\x{26B0}-\x{26B1}]/u',
            '/[\x{26BD}-\x{26BE}]/u',
            '/[\x{26C4}-\x{26C5}]/u',
            '/[\x{26C8}]/u',
            '/[\x{26CE}-\x{26CF}]/u',
            '/[\x{26D1}]/u',
            '/[\x{26D3}-\x{26D4}]/u',
            '/[\x{26E9}-\x{26EA}]/u',
            '/[\x{26F0}-\x{26F5}]/u',
            '/[\x{26F7}-\x{26FA}]/u',
            '/[\x{26FD}]/u',
            '/[\x{2702}]/u',
            '/[\x{2705}]/u',
            '/[\x{2708}-\x{270D}]/u',
            '/[\x{270F}]/u',
            '/[\x{2712}]/u',
            '/[\x{2714}]/u',
            '/[\x{2716}]/u',
            '/[\x{271D}]/u',
            '/[\x{2721}]/u',
            '/[\x{2728}]/u',
            '/[\x{2733}-\x{2734}]/u',
            '/[\x{2744}]/u',
            '/[\x{2747}]/u',
            '/[\x{274C}]/u',
            '/[\x{274E}]/u',
            '/[\x{2753}-\x{2755}]/u',
            '/[\x{2757}]/u',
            '/[\x{2763}-\x{2764}]/u',
            '/[\x{2795}-\x{2797}]/u',
            '/[\x{27A1}]/u',
            '/[\x{27B0}]/u',
            '/[\x{27BF}]/u',
            '/[\x{2934}-\x{2935}]/u',
            '/[\x{2B05}-\x{2B07}]/u',
            '/[\x{2B1B}-\x{2B1C}]/u',
            '/[\x{2B50}]/u',
            '/[\x{2B55}]/u',
            '/[\x{3030}]/u',
            '/[\x{303D}]/u',
            '/[\x{3297}]/u',
            '/[\x{3299}]/u',
            '/[\x{FE0F}]/u',
            '/[\x{1F004}]/u',
            '/[\x{1F0CF}]/u',
            '/[\x{1F170}-\x{1F171}]/u',
            '/[\x{1F17E}-\x{1F17F}]/u',
            '/[\x{1F18E}]/u',
            '/[\x{1F191}-\x{1F19A}]/u',
            '/[\x{1F1E6}-\x{1F1FF}]/u',
            '/[\x{1F201}-\x{1F202}]/u',
            '/[\x{1F21A}]/u',
            '/[\x{1F22F}]/u',
            '/[\x{1F232}-\x{1F23A}]/u',
            '/[\x{1F250}-\x{1F251}]/u',
            '/[\x{1F300}-\x{1F321}]/u',
            '/[\x{1F324}-\x{1F393}]/u',
            '/[\x{1F396}-\x{1F397}]/u',
            '/[\x{1F399}-\x{1F39B}]/u',
            '/[\x{1F39E}-\x{1F3F0}]/u',
            '/[\x{1F3F3}-\x{1F3F5}]/u',
            '/[\x{1F3F7}-\x{1F3FA}]/u',
            '/[\x{1F400}-\x{1F4FD}]/u',
            '/[\x{1F4FF}-\x{1F53D}]/u',
            '/[\x{1F549}-\x{1F54E}]/u',
            '/[\x{1F550}-\x{1F567}]/u',
            '/[\x{1F56F}-\x{1F570}]/u',
            '/[\x{1F573}-\x{1F57A}]/u',
            '/[\x{1F587}]/u',
            '/[\x{1F58A}-\x{1F58D}]/u',
            '/[\x{1F590}]/u',
            '/[\x{1F595}-\x{1F596}]/u',
            '/[\x{1F5A4}-\x{1F5A5}]/u',
            '/[\x{1F5A8}]/u',
            '/[\x{1F5B1}-\x{1F5B2}]/u',
            '/[\x{1F5BC}]/u',
            '/[\x{1F5C2}-\x{1F5C4}]/u',
            '/[\x{1F5D1}-\x{1F5D3}]/u',
            '/[\x{1F5DC}-\x{1F5DE}]/u',
            '/[\x{1F5E1}]/u',
            '/[\x{1F5E3}]/u',
            '/[\x{1F5E8}]/u',
            '/[\x{1F5EF}]/u',
            '/[\x{1F5F3}]/u',
            '/[\x{1F5FA}-\x{1F64F}]/u',
            '/[\x{1F680}-\x{1F6C5}]/u',
            '/[\x{1F6CB}-\x{1F6D2}]/u',
            '/[\x{1F6E0}-\x{1F6E5}]/u',
            '/[\x{1F6E9}]/u',
            '/[\x{1F6EB}-\x{1F6EC}]/u',
            '/[\x{1F6F0}]/u',
            '/[\x{1F6F3}-\x{1F6F9}]/u',
            '/[\x{1F910}-\x{1F93A}]/u',
            '/[\x{1F93C}-\x{1F93E}]/u',
            '/[\x{1F940}-\x{1F945}]/u',
            '/[\x{1F947}-\x{1F970}]/u',
            '/[\x{1F973}-\x{1F976}]/u',
            '/[\x{1F97A}]/u',
            '/[\x{1F97C}-\x{1F9A2}]/u',
            '/[\x{1F9B0}-\x{1F9B9}]/u',
            '/[\x{1F9C0}-\x{1F9C2}]/u',
            '/[\x{1F9D0}-\x{1F9FF}]/u',
            '/[\x{E0062}-\x{E0063}]/u',
            '/[\x{E006C}]/u',
            '/[\x{E006E}]/u',
            '/[\x{E007F}]/u',
        ];

        return preg_replace($regexEmoticons, '', $text);
    }
}
