@extends('layouts.index')
@section('head')
    @php
    use App\Http\Controllers\ProductController;

    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'url'      => 'https://www.kalands.ir',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => 'https://www.kalands.ir/result/?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $schemaProducts = $Data['data']['products'] ?? [];
    $schemaPage = max(1, (int) request()->query('page', 1));
    $schemaHasNext = !empty($infinite['hasMore']);
    $schemaNextPage = $schemaHasNext ? $schemaPage + 1 : null;
    $schemaNextUrl = $schemaHasNext ? request()->fullUrlWithQuery(['page' => $schemaNextPage]) : null;
    $schemaCurrentUrl = request()->fullUrl();

    $schemaItemListElements = [];
    foreach ($schemaProducts as $index => $item) {
        $itemUrlPath = str_replace('dkp-', '', $item['url']['uri'] ?? '#');
        $itemUrl = str_starts_with($itemUrlPath, 'http')
            ? $itemUrlPath
            : rtrim(config('app.url'), '/') . '/' . ltrim($itemUrlPath, '/');
        $rawImage = $item['images']['main']['url'][0] ?? null;
        $schemaImage = null;
        if (is_string($rawImage) && $rawImage !== '') {
            $schemaImage = ProductController::ImgProfile($rawImage, 800, 800, 80, false, true);
        }

        $offers = null;
        $sellingPrice = (int)($item['default_variant']['price']['selling_price'] ?? 0);
        if ($sellingPrice > 0) {
            $offers = [
                '@type' => 'Offer',
                'priceCurrency' => 'IRR',
                'price' => $sellingPrice,
                'availability' => 'https://schema.org/InStock',
                'url' => $itemUrl,
            ];
        }

        $schemaItem = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'item' => array_filter([
                '@type' => 'Product',
                'name' => ProductController::LinkReplaced($item['title_fa'] ?? ''),
                'url' => $itemUrl,
                'image' => $schemaImage,
                'offers' => $offers,
            ]),
        ];
        $schemaItemListElements[] = $schemaItem;
    }

    $resultSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        'url' => $schemaCurrentUrl,
        'name' => (request()->filled('q') ? 'نتایج جستجو: ' . request()->query('q') : 'نتایج محصولات کالندز'),
        'isPartOf' => [
            '@type' => 'WebSite',
            'url' => 'https://www.kalands.ir',
            'name' => 'کالندز',
        ],
        'nextPage' => $schemaNextUrl,
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => count($schemaItemListElements),
            'itemListElement' => $schemaItemListElements,
        ],
    ]);
    @endphp
    <script type="application/ld+json">
    @json($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    </script>
    <script type="application/ld+json">
    @json($resultSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    </script>

    @if(isset($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif
    @if(isset($seo['robots']))
        <meta name="robots" content="{{ $seo['robots'] }}">
    @endif

    @if(isset($Data['data']['seo']['title']))
        <title>{{\App\Http\Controllers\ProductController::TextReplaced($Data['data']['seo']['title'])}}</title>
    @elseif(request()->has('q'))
        <title>{{request()->get('q')}} | کالندز</title>
    @elseif(isset($Data['data']['brand']['title_fa']))
        <title>خرید و قیمت محصولات برند {{$Data['data']['brand']['title_fa']}} | کالندز</title>
    @elseif(isset($Data['data']['promotion']['title_fa']))
        <title>{{$Data['data']['promotion']['title_fa']}} | کالندز</title>
    @elseif(request()->has('title'))
        <title>خرید و قیمت محصولات برند {{request()->get('title')}} | کالندز</title>
    @else
        <title>کالندز</title>
    @endif
@endsection
@section('content')
    <main class="flex-grow bg-slate pb-14 pt-36 dark:bg-black xs:pt-36">
        <div class="container relative">
            <!-- Mobile Options Section -->
            <div class="mb-6 grid grid-cols-2 gap-3 md:hidden">
                <!-- Filter -->
                <button aria-controls="shop-filter-drawer-navigation"
                        class="flex w-full items-center justify-center gap-x-2 rounded-squircle border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-base dark:border-white/10 dark:bg-slate dark:text-white xs:text-base"
                        data-drawer-show="shop-filter-drawer-navigation" data-drawer-placement="bottom"
                        data-drawer-target="shop-filter-drawer-navigation" type="button">
                    <svg class="h-6 w-6">
                        <use xlink:href="#filter"/>
                    </svg>
                    <div>فیلتر</div>
                </button>
                <!-- Sort -->
                <button aria-controls="shop-sort-drawer-navigation"
                        class="flex w-full items-center justify-center gap-x-2 rounded-squircle border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 shadow-base dark:border-white/10 dark:bg-slate dark:text-white xs:text-base"
                        data-drawer-show="shop-sort-drawer-navigation" data-drawer-placement="bottom"
                        data-drawer-target="shop-sort-drawer-navigation" type="button">
                    <svg class="h-6 w-6">
                        <use xlink:href="#sort"/>
                    </svg>
                    <div>مرتب سازی</div>
                </button>
            </div>
            <div class="grid grid-cols-12 grid-rows-[60px_min(500px,_1fr)] gap-4">
                <!-- Desktop Filter Section -->
                @include('layouts.result.parts.desktop-filter-section')
                <div class="col-span-12 space-y-4 md:col-span-8 lg:col-span-9">
                    <!-- Desktop Sort Section -->
                    @include('layouts.result.parts.desktop-sort-section')
                    <!-- Products Grid -->
                    @php
                        $initialProducts = $Data['data']['products'] ?? [];
                    @endphp
                    <div id="resultProductsContainer">
                        @include('layouts.result.parts.products-grid')
                    </div>
                    <div id="resultEmptyState" @class(['hidden' => !empty($initialProducts)])>
                        @include('layouts.result.parts.empty-search-state')
                    </div>
                    <div id="resultInfiniteSentinel" @class(['h-14' => ($infinite['hasMore'] ?? false), 'hidden' => !($infinite['hasMore'] ?? false)])></div>
                    <div id="resultInfiniteLoader" class="hidden justify-center pb-2">
                        <div class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-base dark:border-white/10 dark:bg-slate dark:text-slate-200">
                            در حال آماده‌سازی محصولات...
                        </div>
                    </div>
                    <div id="resultAjaxMeta"
                         data-endpoint="{{ $infinite['endpoint'] ?? '' }}"
                         data-token="{{ $infinite['token'] ?? '' }}"
                         data-next-page="{{ $infinite['nextPage'] ?? 2 }}"
                         data-has-more="{{ !empty($infinite['hasMore']) ? '1' : '0' }}"
                         hidden></div>
                </div>
            </div>
        </div>
    </main>
    @include('layouts.result.parts.mobile-filter-drawer')
    <!-- Mobile Sort Drawer -->
    @include('layouts.result.parts.mobile-sort-drawer')
    @vite(['resources/js/visitor-info.js', 'resources/js/result-page.js'])
@endsection
