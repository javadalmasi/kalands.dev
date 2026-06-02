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
    <script>
        const resultState = {
            endpoint: '',
            token: '',
            nextPage: 2,
            hasMore: false,
            isInfiniteLoading: false,
            isPageLoading: false,
            observer: null,
            prefetchObserver: null,
            prefetchedPage: null,
            prefetchedData: null,
            prefetchPromise: null,
        };

        const addSearchFunctionality = (searchInput, itemList) => {
            if (!searchInput || !itemList || searchInput.dataset.searchBound === '1') {
                return;
            }

            searchInput.dataset.searchBound = '1';
            searchInput.addEventListener('input', () => {
                const searchTerm = searchInput.value.trim().toLowerCase();
                const items = itemList.querySelectorAll('li:not(:first-child)');

                items.forEach((item) => {
                    const spans = item.querySelectorAll('span');
                    const firstText = (spans[0]?.textContent || '').toLowerCase();
                    const secondText = (spans[1]?.textContent || '').toLowerCase();
                    item.style.display = firstText.includes(searchTerm) || secondText.includes(searchTerm) ? 'block' : 'none';
                });
            });
        };

        const initFilterSearchInputs = () => {
            addSearchFunctionality(document.getElementById('brandListFilterDesktopSearchInput'), document.getElementById('brandListFilterDesktop'));
            addSearchFunctionality(document.getElementById('brandListFilterMobileSearchInput'), document.getElementById('brandListFilterMobile'));
            addSearchFunctionality(document.getElementById('colorListFilterDesktopSearchInput'), document.getElementById('colorListFilterDesktop'));
            addSearchFunctionality(document.getElementById('colorListFilterMobileSearchInput'), document.getElementById('colorListFilterMobile'));
        };

        const getProductsGrid = () => document.getElementById('resultProductsGrid');
        const getLoader = () => document.getElementById('resultInfiniteLoader');
        const getSentinel = () => document.getElementById('resultInfiniteSentinel');
        const getEmptyState = () => document.getElementById('resultEmptyState');

        const readInfiniteMeta = () => {
            const meta = document.getElementById('resultAjaxMeta');

            if (!meta) {
                resultState.endpoint = '';
                resultState.token = '';
                resultState.nextPage = 2;
                resultState.hasMore = false;
                resultState.prefetchedPage = null;
                resultState.prefetchedData = null;
                resultState.prefetchPromise = null;
                return;
            }

            resultState.endpoint = meta.dataset.endpoint || '';
            resultState.token = meta.dataset.token || '';
            resultState.nextPage = Number(meta.dataset.nextPage || 2);
            resultState.hasMore = meta.dataset.hasMore === '1';
            resultState.prefetchedPage = null;
            resultState.prefetchedData = null;
            resultState.prefetchPromise = null;
        };

        const collectSeenKeys = (productsGrid) => {
            const keys = new Set();

            productsGrid.querySelectorAll('[data-product-key]').forEach((node) => {
                const key = node.getAttribute('data-product-key');

                if (key) {
                    keys.add(key);
                }
            });

            return keys;
        };

        const appendUniqueProducts = (html) => {
            const productsGrid = getProductsGrid();

            if (!productsGrid) {
                return;
            }

            const template = document.createElement('template');
            template.innerHTML = html;

            const seenKeys = collectSeenKeys(productsGrid);
            const fragment = document.createDocumentFragment();

            Array.from(template.content.children).forEach((child) => {
                if (!(child instanceof HTMLElement)) {
                    return;
                }

                const key = child.getAttribute('data-product-key');

                if (key && seenKeys.has(key)) {
                    return;
                }

                if (key) {
                    seenKeys.add(key);
                }

                fragment.appendChild(child);
            });

            productsGrid.appendChild(fragment);
        };

        const toggleEmptyState = () => {
            const productsGrid = getProductsGrid();
            const emptyState = getEmptyState();

            if (!productsGrid || !emptyState) {
                return;
            }

            const hasCards = productsGrid.querySelectorAll('[data-product-key]').length > 0 || productsGrid.children.length > 0;
            emptyState.classList.toggle('hidden', hasCards);
        };

        const disconnectObserver = () => {
            if (resultState.observer) {
                resultState.observer.disconnect();
                resultState.observer = null;
            }

            if (resultState.prefetchObserver) {
                resultState.prefetchObserver.disconnect();
                resultState.prefetchObserver = null;
            }
        };

        const fetchInfinitePage = async (page) => {
            const params = new URLSearchParams({
                token: resultState.token,
                page: String(page),
            });
            const response = await fetch(`${resultState.endpoint}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            return response.json();
        };

        const primeNextPage = () => {
            if (!resultState.hasMore || resultState.endpoint === '' || resultState.token === '' || resultState.isPageLoading) {
                return;
            }

            if (resultState.prefetchedPage === resultState.nextPage && resultState.prefetchedData) {
                return;
            }

            if (resultState.prefetchPromise) {
                return;
            }

            const pageToPrefetch = resultState.nextPage;
            resultState.prefetchPromise = fetchInfinitePage(pageToPrefetch)
                .then((data) => {
                    resultState.prefetchedPage = pageToPrefetch;
                    resultState.prefetchedData = data;
                })
                .catch(() => {
                    resultState.prefetchedPage = null;
                    resultState.prefetchedData = null;
                })
                .finally(() => {
                    resultState.prefetchPromise = null;
                });
        };

        const stopInfinite = () => {
            resultState.hasMore = false;
            disconnectObserver();

            const sentinel = getSentinel();
            const loader = getLoader();

            if (sentinel) {
                sentinel.classList.add('hidden');
                sentinel.classList.remove('h-14');
            }

            if (loader) {
                loader.classList.add('hidden');
                loader.classList.remove('flex');
            }
        };

        const refreshInfiniteUi = () => {
            const sentinel = getSentinel();
            const loader = getLoader();

            if (sentinel) {
                sentinel.classList.toggle('hidden', !resultState.hasMore);
                sentinel.classList.toggle('h-14', resultState.hasMore);
            }

            if (loader && !resultState.isInfiniteLoading && !resultState.isPageLoading) {
                loader.classList.add('hidden');
                loader.classList.remove('flex');
            }
        };

        const syncPageInUrl = (page) => {
            const safePage = Math.max(1, Number(page) || 1);
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('page', String(safePage));
            nextUrl.searchParams.delete('pageno');
            window.history.replaceState({url: nextUrl.toString()}, '', nextUrl.toString());
        };

        const loadMore = async () => {
            if (resultState.isInfiniteLoading || !resultState.hasMore || resultState.endpoint === '' || resultState.token === '') {
                return;
            }

            const loader = getLoader();
            resultState.isInfiniteLoading = true;

            if (loader) {
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            }

            try {
                let data = null;
                if (resultState.prefetchedPage === resultState.nextPage && resultState.prefetchedData) {
                    data = resultState.prefetchedData;
                } else {
                    data = await fetchInfinitePage(resultState.nextPage);
                }

                resultState.prefetchedPage = null;
                resultState.prefetchedData = null;
                const html = String(data.html || '');

                if (html.trim() !== '') {
                    appendUniqueProducts(html);
                }

                resultState.hasMore = Boolean(data.hasMore);
                resultState.nextPage = Number(data.nextPage || (resultState.nextPage + 1));
                syncPageInUrl(resultState.nextPage - 1);

                if (!resultState.hasMore) {
                    stopInfinite();
                } else {
                    primeNextPage();
                }

                toggleEmptyState();
            } catch (error) {
                console.error('Infinite scroll failed:', error);
                stopInfinite();
            } finally {
                resultState.isInfiniteLoading = false;
                refreshInfiniteUi();
            }
        };

        const initInfiniteObserver = () => {
            readInfiniteMeta();
            toggleEmptyState();
            disconnectObserver();
            refreshInfiniteUi();

            if (!resultState.hasMore) {
                return;
            }

            const sentinel = getSentinel();

            if (!sentinel) {
                return;
            }

            resultState.observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        loadMore();
                    }
                });
            }, {rootMargin: '300px 0px'});

            resultState.observer.observe(sentinel);

            resultState.prefetchObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        primeNextPage();
                    }
                });
            }, {rootMargin: '1200px 0px'});

            resultState.prefetchObserver.observe(sentinel);
        };

        const syncHeadElement = (sourceDocument, selector) => {
            const currentNode = document.head.querySelector(selector);
            const incomingNode = sourceDocument.head.querySelector(selector);

            if (incomingNode) {
                const clone = incomingNode.cloneNode(true);

                if (currentNode) {
                    currentNode.replaceWith(clone);
                } else {
                    document.head.appendChild(clone);
                }

                return;
            }

            if (currentNode) {
                currentNode.remove();
            }
        };

        const replaceElementById = (sourceDocument, id) => {
            const currentNode = document.getElementById(id);
            const incomingNode = sourceDocument.getElementById(id);

            if (!currentNode) {
                return;
            }

            if (!incomingNode) {
                currentNode.remove();
                return;
            }

            currentNode.replaceWith(incomingNode);
        };

        const closeMobileDrawers = () => {
            ['shop-filter-drawer-navigation', 'shop-sort-drawer-navigation'].forEach((id) => {
                const drawer = document.getElementById(id);

                if (!drawer) {
                    return;
                }

                drawer.classList.add('translate-y-full');
                drawer.setAttribute('aria-hidden', 'true');
                drawer.removeAttribute('aria-modal');
                drawer.removeAttribute('role');
            });

            document.querySelectorAll('.drawer-backdrop').forEach((backdrop) => {
                backdrop.remove();
            });

            document.body.classList.remove('overflow-hidden');
        };

        const normalizeTargetUrl = (rawUrl, resetPage = true) => {
            if (!rawUrl) {
                return null;
            }

            const parsedUrl = new URL(rawUrl, window.location.origin);

            if (parsedUrl.origin !== window.location.origin) {
                return null;
            }

            parsedUrl.searchParams.delete('pageno');

            if (resetPage) {
                parsedUrl.searchParams.set('page', '1');
            } else if (!parsedUrl.searchParams.has('page')) {
                parsedUrl.searchParams.set('page', '1');
            }

            return parsedUrl.toString();
        };

        const loadResultPage = async (targetUrl, pushState = true, resetPage = true) => {
            const normalizedUrl = normalizeTargetUrl(targetUrl, resetPage);

            if (!normalizedUrl || resultState.isPageLoading) {
                return;
            }

            resultState.isPageLoading = true;
            disconnectObserver();

            const loader = getLoader();

            if (loader) {
                loader.classList.remove('hidden');
                loader.classList.add('flex');
            }

            try {
                const response = await fetch(normalizedUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                });

                if (!response.ok) {
                    throw new Error(`Page request failed: ${response.status}`);
                }

                const pageHtml = await response.text();
                const parser = new DOMParser();
                const sourceDocument = parser.parseFromString(pageHtml, 'text/html');

                const requiredNode = sourceDocument.getElementById('resultProductsContainer');

                if (!requiredNode) {
                    throw new Error('Ajax result payload is missing result container.');
                }

                [
                    'resultDesktopFilter',
                    'resultDesktopSort',
                    'resultProductsContainer',
                    'resultEmptyState',
                    'resultInfiniteSentinel',
                    'resultInfiniteLoader',
                    'resultAjaxMeta',
                    'shop-filter-drawer-navigation',
                    'shop-sort-drawer-navigation',
                ].forEach((id) => replaceElementById(sourceDocument, id));

                const incomingTitle = sourceDocument.querySelector('title');

                if (incomingTitle) {
                    document.title = incomingTitle.textContent;
                }

                syncHeadElement(sourceDocument, 'link[rel="canonical"]');
                syncHeadElement(sourceDocument, 'meta[name="robots"]');

                if (pushState) {
                    window.history.pushState({url: normalizedUrl}, '', normalizedUrl);
                }

                initFilterSearchInputs();
                if (typeof window.initFlowbite === 'function') {
                    window.initFlowbite();
                }
                initInfiniteObserver();
            } catch (error) {
                console.error('Ajax page update failed:', error);
                window.location.href = normalizedUrl;
            } finally {
                resultState.isPageLoading = false;
                refreshInfiniteUi();
            }
        };

        document.addEventListener('click', (event) => {
            const actionButton = event.target.closest('[data-filter-url], [data-sort-url]');

            if (!actionButton) {
                return;
            }

            const targetUrl = actionButton.dataset.filterUrl || actionButton.dataset.sortUrl || '';

            if (targetUrl === '') {
                return;
            }

            event.preventDefault();
            closeMobileDrawers();
            loadResultPage(targetUrl, true, true);
        });

        document.addEventListener('submit', (event) => {
            const searchForm = event.target.closest('form[data-result-search-form]');

            if (!searchForm) {
                return;
            }

            event.preventDefault();

            const nextUrl = new URL(window.location.href);
            const formData = new FormData(searchForm);
            const qValue = String(formData.get('q') || '').trim();

            if (qValue === '') {
                nextUrl.searchParams.delete('q');
            } else {
                nextUrl.searchParams.set('q', qValue);
            }

            nextUrl.searchParams.delete('pageno');
            nextUrl.searchParams.set('page', '1');

            closeMobileDrawers();
            loadResultPage(nextUrl.toString(), true, true);
        });

        window.addEventListener('popstate', () => {
            loadResultPage(window.location.href, false, false);
        });

        initFilterSearchInputs();
        initInfiniteObserver();
    </script>
@endsection
