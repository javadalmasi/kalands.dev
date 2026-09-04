<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ResultController extends Controller
{
    private function normalizeResultPayload($payload): array
    {
        if (! is_array($payload)) {
            return ['status' => 500, 'data' => ['products' => []]];
        }

        if (! isset($payload['data']) || ! is_array($payload['data'])) {
            $payload['data'] = [];
        }

        if (! isset($payload['data']['products']) || ! is_array($payload['data']['products'])) {
            $payload['data']['products'] = [];
        }

        return $payload;
    }

    private function productUniqueKey(array $item, int $index = 0): string
    {
        if (isset($item['id']) && $item['id'] !== null && $item['id'] !== '') {
            return 'id:'.(string) $item['id'];
        }

        $uri = $item['url']['uri'] ?? '';

        if (is_string($uri) && $uri !== '') {
            return 'uri:'.$uri;
        }

        $title = $item['title_fa'] ?? ($item['title'] ?? '');

        if (is_string($title) && $title !== '') {
            return 'title:'.$title;
        }

        return 'index:'.$index;
    }

    private function deduplicateProducts(array $products): array
    {
        $uniqueProducts = [];
        $seen = [];

        foreach (array_values($products) as $index => $product) {
            if (! is_array($product)) {
                continue;
            }

            // Filter by Digikala URL pattern
            $uri = $product['url']['uri'] ?? '';
            if ($uri !== '' && str_contains($uri, 'dkp-')) {
                if (! str_starts_with($uri, '/product/dkp-')) {
                    continue;
                }
            }

            $key = $this->productUniqueKey($product, $index);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $uniqueProducts[] = $product;
        }

        return $uniqueProducts;
    }

    private function deduplicateDataProducts(array &$data): void
    {
        $products = $data['data']['products'] ?? null;

        if (! is_array($products)) {
            return;
        }

        $data['data']['products'] = $this->deduplicateProducts($products);
    }

    private function buildSeoMeta(): array
    {
        $request = request();
        $path = trim($request->path(), '/');
        $isResultRoot = $path === 'result';
        $isSellerOrMain = str_starts_with($path, 'seller/') || str_starts_with($path, 'main/');

        $filterKeys = ['brands', 'categories', 'colors'];
        $activeFilterGroups = 0;
        $activeFilterValues = 0;

        foreach ($filterKeys as $filterKey) {
            $values = $request->input($filterKey, []);

            if (! is_array($values)) {
                continue;
            }

            $values = array_values(array_filter($values, fn ($value) => $value !== null && $value !== ''));

            if (! empty($values)) {
                $activeFilterGroups++;
                $activeFilterValues += count($values);
            }
        }

        $canonical = null;
        $canonicalQuery = [];

        if (! ($isResultRoot && $request->filled('q'))) {
            $canonical = rtrim(config('app.url'), '/').'/'.ltrim($path, '/');

            if (
                $activeFilterGroups === 0
                && ! $request->filled('q')
                && $request->filled('page')
                && (int) $request->query('page') > 1
            ) {
                $canonicalQuery['page'] = (int) $request->query('page');
            }

            if (! empty($canonicalQuery)) {
                $canonical .= '?'.http_build_query($canonicalQuery);
            }
        }

        $robots = 'index';

        if ($isResultRoot && $request->filled('q')) {
            $robots = 'noindex';
        } elseif ($isSellerOrMain && ($activeFilterGroups >= 2 || $activeFilterValues > 2)) {
            $robots = 'noindex';
        }

        return [
            'canonical' => $canonical,
            'robots' => $robots,
        ];
    }

    private function signingKey(): string
    {
        $key = (string) config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);

            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }

    private function normalizeQuery(array $query): array
    {
        unset($query['page'], $query['pageno']);

        foreach ($query as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $query[$key] = array_values(array_map(function ($item) {
                        return is_array($item) ? $this->normalizeQuery($item) : $item;
                    }, $value));
                } else {
                    $query[$key] = $this->normalizeQuery($value);
                }
            }
        }

        ksort($query);

        return $query;
    }

    private function encodeInfiniteToken(string $path, array $query): string
    {
        $payload = [
            'path' => trim($path, '/'),
            'query' => $this->normalizeQuery($query),
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $json, $this->signingKey());
        $encoded = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');

        return $encoded.'.'.$signature;
    }

    private function decodeInfiniteToken(string $token): ?array
    {
        if (! str_contains($token, '.')) {
            return null;
        }

        [$encoded, $signature] = explode('.', $token, 2);
        $json = base64_decode(strtr($encoded, '-_', '+/'), true);

        if ($json === false) {
            return null;
        }

        $expectedSignature = hash_hmac('sha256', $json, $this->signingKey());

        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded) || ! isset($decoded['path']) || ! isset($decoded['query'])) {
            return null;
        }

        if (! is_string($decoded['path']) || ! is_array($decoded['query'])) {
            return null;
        }

        return $decoded;
    }

    private function filterValuesFromQuery(array $query, string $key): array
    {
        $values = $query[$key] ?? [];

        if (! is_array($values)) {
            return [];
        }

        return array_values(array_filter($values, fn ($value) => $value !== null && $value !== ''));
    }

    private function scalarFromQuery(array $query, string $key): string
    {
        $value = $query[$key] ?? '';

        if (is_array($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function indexedQueryString(string $name, array $values): string
    {
        if (empty($values)) {
            return '';
        }

        $queryString = '';

        foreach (array_values($values) as $index => $value) {
            $queryString .= '&'.$name.'['.$index.']='.urlencode((string) $value);
        }

        return $queryString;
    }

    private function buildApiEndpoint(string $path, array $query, int $page): string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));
        $sort = $this->scalarFromQuery($query, 'sort');
        $sortValue = ($sort === '' || ! is_numeric($sort)) ? 1 : (int) $sort;
        $qValue = $this->scalarFromQuery($query, 'q');
        $qQuery = $qValue !== '' ? '&q='.urlencode($qValue) : '';
        $listValue = $this->scalarFromQuery($query, 'list');

        $categories = $this->indexedQueryString('categories', $this->filterValuesFromQuery($query, 'categories'));
        $brands = $this->indexedQueryString('brands', $this->filterValuesFromQuery($query, 'brands'));
        $colors = $this->indexedQueryString('color_palettes', $this->filterValuesFromQuery($query, 'colors'));

        if (($segments[0] ?? '') === 'result' && count($segments) === 1) {
            if ($listValue !== '') {
                return 'promotions/plp_'.$listValue.'/?page='.$page.'&sort='.$sortValue.$categories.$brands.$colors;
            }

            return 'search/?q='.urlencode($qValue).'&page='.$page.'&sort='.$sortValue.$categories.$brands.$colors;
        }

        if (($segments[0] ?? '') === 'result' && count($segments) === 2) {
            $category = str_replace('category-', '', urldecode($segments[1]));

            return 'categories/'.$category.'/search/?page='.$page.'&sort='.$sortValue.$brands.$qQuery.$colors;
        }

        if (($segments[0] ?? '') === 'result' && count($segments) >= 3) {
            $category = str_replace('category-', '', urldecode($segments[1]));
            $brand = urldecode($segments[2]);

            return 'categories/'.$category.'/brands/'.$brand.'/search/?page='.$page.'&sort='.$sortValue.$qQuery.$colors;
        }

        if (($segments[0] ?? '') === 'main' && isset($segments[1])) {
            $category = urldecode($segments[1]);

            return 'categories/'.$category.'/search/?page='.$page.'&sort='.$sortValue.$brands.$qQuery.$colors;
        }

        if (($segments[0] ?? '') === 'seller' && isset($segments[1])) {
            $seller = urldecode($segments[1]);

            return 'sellers/'.$seller.'/?page='.$page.'&sort='.$sortValue.$categories.$brands.$qQuery.$colors;
        }

        if (($segments[0] ?? '') === 'product' && ($segments[1] ?? '') === 'brand' && isset($segments[2])) {
            $brandName = urldecode($segments[2]);

            return 'brands/'.$brandName.'/?page='.$page.'&sort='.$sortValue.$categories.$colors.$qQuery;
        }

        return 'search/?q='.urlencode($qValue).'&page='.$page.'&sort='.$sortValue.$categories.$brands.$colors;
    }

    private function buildInfiniteMeta(array $data): array
    {
        $currentPage = (int) ($data['data']['pager']['current_page'] ?? 1);
        $maxPages = min((int) ($data['data']['pager']['total_pages'] ?? 1), 100);
        $nextPage = min($currentPage + 1, $maxPages);

        return [
            'endpoint' => route('result.infinite'),
            'token' => $this->encodeInfiniteToken(request()->path(), request()->query()),
            'nextPage' => $nextPage,
            'hasMore' => $currentPage < $maxPages,
        ];
    }

    public function infinite(Request $request)
    {
        $page = (int) $request->query('page', 2);
        $token = (string) $request->query('token', '');

        if ($page < 2 || $page > 100 || $token === '') {
            return response()->json(['message' => 'Invalid request.'], 422);
        }

        $decodedToken = $this->decodeInfiniteToken($token);

        if ($decodedToken === null) {
            return response()->json(['message' => 'Invalid token.'], 403);
        }

        $endpoint = $this->buildApiEndpoint($decodedToken['path'], $decodedToken['query'], $page);
        $data = ProductController::DigikalaApi($endpoint, 'v1');
        $products = $this->deduplicateProducts($data['data']['products'] ?? []);
        $maxPages = min((int) ($data['data']['pager']['total_pages'] ?? $page), 100);
        $currentPage = min((int) ($data['data']['pager']['current_page'] ?? $page), $maxPages);

        $html = '';

        foreach ($products as $item) {
            $html .= view('layouts.result.parts.product-card', ['item' => $item])->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $currentPage < $maxPages,
            'nextPage' => min($currentPage + 1, $maxPages),
            'isEmpty' => empty($products),
        ]);
    }

    public function brand($brand_name)
    {
        if (request()->has('pageno')) {
            return redirect(str_replace('pageno=', 'page=', request()->fullUrl()), 301);
        }
        if (request()->has('page')) {
            if (request()->get('page') > 100) {
                abort(403, 'محدود به ۱۰۰ صفحه هستید.');
            }
            $page['num'] = request()->get('page');
            $page['Previous']['status'] = true;
            $page['Previous']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') - 1, request()->fullUrl());
            $page['Next']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 1, request()->fullUrl());
            $page['Next']['2x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 2, request()->fullUrl());
            $page['Next']['3x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 3, request()->fullUrl());
            $page['Next']['10x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 10, request()->fullUrl());
        } else {
            $page['Previous']['status'] = false;
            $page['num'] = 1;
            $page['Next']['1x'] = request()->fullUrlWithQuery(['page' => '2']);
            $page['Next']['2x'] = request()->fullUrlWithQuery(['page' => '3']);
            $page['Next']['3x'] = request()->fullUrlWithQuery(['page' => '4']);
            $page['Next']['10x'] = request()->fullUrlWithQuery(['page' => '11']);
        }
        if (request()->has('sort')) {
            $sort = request()->get('sort');
        } else {
            $sort = 1;
        }
        $couner = 0;
        if (request()->has('categories')) {
            $categories = '';
            foreach (request()->get('categories') as $category) {
                $categories .= '&categories['.$couner.']='.$category;
                $next_categories_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_categories_id = 0;
            $categories = '';
        }
        $couner = 0;
        if (request()->has('colors')) {
            $colors = '';
            foreach (request()->get('colors') as $color) {
                $colors .= '&color_palettes['.$couner.']='.$color;
                $next_colors_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_colors_id = 0;
            $colors = '';
        }
        if (request()->has('q')) {
            $query = '&q='.request()->get('q');
        } else {
            $query = '';
        }
        $Data = $this->normalizeResultPayload(
            ProductController::DigikalaApi('brands/'.$brand_name.'/?page='.$page['num'].'&sort='.$sort.$categories.$colors.$query, 'v1', true)
        );

        if (($Data['status'] ?? null) == 301) {
            $premiumData = $this->normalizeResultPayload(
                ProductController::DigikalaApi('brands/'.$brand_name.'/premium/?page='.$page['num'].'&sort='.$sort.$categories.$colors.$query, 'v1')
            );
            $premiumBrandId = $premiumData['data']['brand']['id'] ?? null;
            $premiumBrandTitle = $premiumData['data']['brand']['title_fa'] ?? null;

            if ($premiumBrandId && $premiumBrandTitle) {
                return redirect(config('app.url').'/result/?brands[0]='.$premiumBrandId.'&title='.$premiumBrandTitle);
            }

            abort(404);
        }

        $isValidBrandResponse = ((int) ($Data['status'] ?? 500) === 200)
            && isset($Data['data']['brand'])
            && is_array($Data['data']['brand'])
            && ! empty($Data['data']['brand']['id']);

        if (! $isValidBrandResponse) {
            abort(404);
        }

        $this->deduplicateDataProducts($Data);
        $seo = $this->buildSeoMeta();
        $infinite = $this->buildInfiniteMeta($Data);

        return view('layouts.result.index', compact('Data', 'page', 'next_categories_id', 'next_colors_id', 'seo', 'infinite'));
    }

    public function query()
    {
        if (request()->has('pageno')) {
            return redirect(str_replace('pageno=', 'page=', request()->fullUrl()), 301);
        }
        if (request()->has('page')) {
            if (request()->get('page') > 100) {
                abort(403, 'محدود به ۱۰۰ صفحه هستید.');
            }
            $page['num'] = request()->get('page');
            $page['Previous']['status'] = true;
            $page['Previous']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') - 1, request()->fullUrl());
            $page['Next']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 1, request()->fullUrl());
            $page['Next']['2x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 2, request()->fullUrl());
            $page['Next']['3x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 3, request()->fullUrl());
            $page['Next']['10x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 10, request()->fullUrl());
        } else {
            $page['Previous']['status'] = false;
            $page['num'] = 1;
            $page['Next']['1x'] = request()->fullUrlWithQuery(['page' => '2']);
            $page['Next']['2x'] = request()->fullUrlWithQuery(['page' => '3']);
            $page['Next']['3x'] = request()->fullUrlWithQuery(['page' => '4']);
            $page['Next']['10x'] = request()->fullUrlWithQuery(['page' => '11']);
        }
        if (request()->has('sort')) {
            $sort = request()->get('sort');
        } else {
            $sort = 1;
        }
        $couner = 0;
        if (request()->has('categories')) {
            $categories = '';
            foreach (request()->get('categories') as $category) {
                $categories .= '&categories['.$couner.']='.$category;
                $next_categories_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_categories_id = 0;
            $categories = '';
        }
        $couner = 0;
        if (request()->has('brands')) {
            $brands = '';
            foreach (request()->get('brands') as $brand) {
                $brands .= '&brands['.$couner.']='.$brand;
                $next_brands_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_brands_id = 0;
            $brands = '';
        }
        $couner = 0;
        if (request()->has('colors')) {
            $colors = '';
            foreach (request()->get('colors') as $color) {
                $colors .= '&color_palettes['.$couner.']='.$color;
                $next_colors_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_colors_id = 0;
            $colors = '';
        }
        $filter_search_enable = false;
        if (request()->has('list')) {
            $Data = ProductController::DigikalaApi('promotions/plp_'.request()->get('list').'/'.'?page='.$page['num'].'&sort='.$sort.$categories.$brands.$colors, 'v1');
        } else {
            $Data = ProductController::DigikalaApi('search/?q='.request()->get('q').'&page='.$page['num'].'&sort='.$sort.$categories.$brands.$colors, 'v1');
        }
        $this->deduplicateDataProducts($Data);
        $seo = $this->buildSeoMeta();
        $infinite = $this->buildInfiniteMeta($Data);

        return view('layouts.result.index', compact('Data', 'page', 'next_categories_id', 'next_brands_id', 'filter_search_enable', 'next_categories_id', 'seo', 'infinite'));
    }

    public function category($category)
    {
        if (request()->has('pageno')) {
            return redirect(str_replace('pageno=', 'page=', request()->fullUrl()), 301);
        }
        if (request()->has('page')) {
            if (request()->get('page') > 100) {
                abort(403, 'محدود به ۱۰۰ صفحه هستید.');
            }
            $page['num'] = request()->get('page');
            $page['Previous']['status'] = true;
            $page['Previous']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') - 1, request()->fullUrl());
            $page['Next']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 1, request()->fullUrl());
            $page['Next']['2x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 2, request()->fullUrl());
            $page['Next']['3x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 3, request()->fullUrl());
            $page['Next']['10x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 10, request()->fullUrl());
        } else {
            $page['Previous']['status'] = false;
            $page['num'] = 1;
            $page['Next']['1x'] = request()->fullUrlWithQuery(['page' => '2']);
            $page['Next']['2x'] = request()->fullUrlWithQuery(['page' => '3']);
            $page['Next']['3x'] = request()->fullUrlWithQuery(['page' => '4']);
            $page['Next']['10x'] = request()->fullUrlWithQuery(['page' => '11']);
        }
        if (request()->has('sort')) {
            $sort = request()->get('sort');
        } else {
            $sort = 1;
        }
        $couner = 0;
        if (request()->has('brands')) {
            $brands = '';
            foreach (request()->get('brands') as $brand) {
                $brands .= '&brands['.$couner.']='.$brand;
                $next_brands_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_brands_id = 0;
            $brands = '';
        }
        if (request()->has('q')) {
            $query = '&q='.request()->get('q');
        } else {
            $query = '';
        }
        $couner = 0;
        if (request()->has('colors')) {
            $colors = '';
            foreach (request()->get('colors') as $color) {
                $colors .= '&color_palettes['.$couner.']='.$color;
                $next_colors_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_colors_id = 0;
            $colors = '';
        }
        $Data = ProductController::DigikalaApi('categories/'.str_replace('category-', '', $category).'/search/?page='.$page['num'].'&sort='.$sort.$brands.$query.$colors, 'v1');
        $this->deduplicateDataProducts($Data);
        $seo = $this->buildSeoMeta();
        $infinite = $this->buildInfiniteMeta($Data);

        return view('layouts.result.index', compact('Data', 'page', 'next_brands_id', 'next_colors_id', 'seo', 'infinite'));
    }

    public function category_brand($category, $brand)
    {
        if (request()->has('pageno')) {
            return redirect(str_replace('pageno=', 'page=', request()->fullUrl()), 301);
        }
        if (request()->has('page')) {
            if (request()->get('page') > 100) {
                abort(403, 'محدود به ۱۰۰ صفحه هستید.');
            }
            $page['num'] = request()->get('page');
            $page['Previous']['status'] = true;
            $page['Previous']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') - 1, request()->fullUrl());
            $page['Next']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 1, request()->fullUrl());
            $page['Next']['2x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 2, request()->fullUrl());
            $page['Next']['3x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 3, request()->fullUrl());
            $page['Next']['10x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 10, request()->fullUrl());
        } else {
            $page['Previous']['status'] = false;
            $page['num'] = 1;
            $page['Next']['1x'] = request()->fullUrlWithQuery(['page' => '2']);
            $page['Next']['2x'] = request()->fullUrlWithQuery(['page' => '3']);
            $page['Next']['3x'] = request()->fullUrlWithQuery(['page' => '4']);
            $page['Next']['10x'] = request()->fullUrlWithQuery(['page' => '11']);
        }
        if (request()->has('sort')) {
            $sort = request()->get('sort');
        } else {
            $sort = 1;
        }
        if (request()->has('q')) {
            $query = '&q='.request()->get('q');
        } else {
            $query = '';
        }
        $couner = 0;
        if (request()->has('colors')) {
            $colors = '';
            foreach (request()->get('colors') as $color) {
                $colors .= '&color_palettes['.$couner.']='.$color;
                $next_colors_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_colors_id = 0;
            $colors = '';
        }
        $Data = ProductController::DigikalaApi('categories/'.str_replace('category-', '', $category).'/brands/'.$brand.'/search/?page='.$page['num'].'&sort='.$sort.$query.$colors, 'v1');

        if ((int) ($Data['status'] ?? 500) !== 200) {
            abort(404);
        }

        $this->deduplicateDataProducts($Data);
        $seo = $this->buildSeoMeta();
        $infinite = $this->buildInfiniteMeta($Data);

        return view('layouts.result.index', compact('Data', 'page', 'next_colors_id', 'seo', 'infinite'));
    }

    public function main_category($category)
    {
        if (request()->has('pageno')) {
            return redirect(str_replace('pageno=', 'page=', request()->fullUrl()), 301);
        }
        if (request()->has('page')) {
            if (request()->get('page') > 100) {
                abort(403, 'محدود به ۱۰۰ صفحه هستید.');
            }
            $page['num'] = request()->get('page');
            $page['Previous']['status'] = true;
            $page['Previous']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') - 1, request()->fullUrl());
            $page['Next']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 1, request()->fullUrl());
            $page['Next']['2x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 2, request()->fullUrl());
            $page['Next']['3x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 3, request()->fullUrl());
            $page['Next']['10x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 10, request()->fullUrl());
        } else {
            $page['Previous']['status'] = false;
            $page['num'] = 1;
            $page['Next']['1x'] = request()->fullUrlWithQuery(['page' => '2']);
            $page['Next']['2x'] = request()->fullUrlWithQuery(['page' => '3']);
            $page['Next']['3x'] = request()->fullUrlWithQuery(['page' => '4']);
            $page['Next']['10x'] = request()->fullUrlWithQuery(['page' => '11']);
        }
        if (request()->has('sort')) {
            $sort = request()->get('sort');
        } else {
            $sort = 1;
        }

        $couner = 0;
        if (request()->has('brands')) {
            $brands = '';
            foreach (request()->get('brands') as $brand) {
                $brands .= '&brands['.$couner.']='.$brand;
                $next_brands_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_brands_id = 0;
            $brands = '';
        }
        if (request()->has('q')) {
            $query = '&q='.request()->get('q');
        } else {
            $query = '';
        }
        $couner = 0;
        if (request()->has('colors')) {
            $colors = '';
            foreach (request()->get('colors') as $color) {
                $colors .= '&color_palettes['.$couner.']='.$color;
                $next_colors_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_colors_id = 0;
            $colors = '';
        }
        $Data = ProductController::DigikalaApi('categories/'.$category.'/search/?page='.$page['num'].'&sort='.$sort.$brands.$query.$colors, 'v1');
        $this->deduplicateDataProducts($Data);
        $seo = $this->buildSeoMeta();
        $infinite = $this->buildInfiniteMeta($Data);

        return view('layouts.result.index', compact('Data', 'page', 'next_brands_id', 'seo', 'infinite'));
    }

    public function seller($seller_id)
    {
        if (request()->has('pageno')) {
            return redirect(str_replace('pageno=', 'page=', request()->fullUrl()), 301);
        }
        if (request()->has('page')) {
            if (request()->get('page') > 100) {
                abort(403, 'محدود به ۱۰۰ صفحه هستید.');
            }
            $page['num'] = request()->get('page');
            $page['Previous']['status'] = true;
            $page['Previous']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') - 1, request()->fullUrl());
            $page['Next']['1x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 1, request()->fullUrl());
            $page['Next']['2x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 2, request()->fullUrl());
            $page['Next']['3x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 3, request()->fullUrl());
            $page['Next']['10x'] = str_replace('page='.request()->get('page'), 'page='.request()->get('page') + 10, request()->fullUrl());
        } else {
            $page['Previous']['status'] = false;
            $page['num'] = 1;
            $page['Next']['1x'] = request()->fullUrlWithQuery(['page' => '2']);
            $page['Next']['2x'] = request()->fullUrlWithQuery(['page' => '3']);
            $page['Next']['3x'] = request()->fullUrlWithQuery(['page' => '4']);
            $page['Next']['10x'] = request()->fullUrlWithQuery(['page' => '11']);
        }
        if (request()->has('sort')) {
            $sort = request()->get('sort');
        } else {
            $sort = 1;
        }
        $couner = 0;
        if (request()->has('brands')) {
            $brands = '';
            foreach (request()->get('brands') as $brand) {
                $brands .= '&brands['.$couner.']='.$brand;
                $next_brands_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_brands_id = 0;
            $brands = '';
        }
        $couner = 0;
        if (request()->has('categories')) {
            $categories = '';
            foreach (request()->get('categories') as $category) {
                $categories .= '&categories['.$couner.']='.$category;
                $next_categories_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_categories_id = 0;
            $categories = '';
        }
        $couner = 0;
        if (request()->has('brands')) {
            $brands = '';
            foreach (request()->get('brands') as $brand) {
                $brands .= '&brands['.$couner.']='.$brand;
                $next_brands_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_brands_id = 0;
            $brands = '';
        }
        if (request()->has('q')) {
            $query = '&q='.request()->get('q');
        } else {
            $query = '';
        }
        $couner = 0;
        if (request()->has('colors')) {
            $colors = '';
            foreach (request()->get('colors') as $color) {
                $colors .= '&color_palettes['.$couner.']='.$color;
                $next_colors_id = $couner + 1;
                $couner++;
            }
        } else {
            $next_colors_id = 0;
            $colors = '';
        }
        $Data = ProductController::DigikalaApi('sellers/'.$seller_id.'/?page='.$page['num'].'&sort='.$sort.$categories.$brands.$query.$colors, 'v1');
        $this->deduplicateDataProducts($Data);
        $seo = $this->buildSeoMeta();
        $infinite = $this->buildInfiniteMeta($Data);

        return view('layouts.result.index', compact('Data', 'page', 'next_categories_id', 'next_brands_id', 'next_colors_id', 'seo', 'infinite'));
    }
}
