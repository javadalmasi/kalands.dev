@php
    $type = $type ?? 'top';
    $catSettings = ($type === 'bottom') ? ($homeCategoryBanners['categories_bottom'] ?? []) : ($homeCategoryBanners['categories_top'] ?? []);
    $isEnabled = (bool) ($catSettings['enabled'] ?? ($type === 'top'));
    $showTitle = (bool) ($catSettings['show_title'] ?? false);
    $title = ($showTitle) ? ($catSettings['title'] ?? ($type === 'top' ? 'دسته‌بندی‌های پیشنهادی' : '')) : null;
    $categories = $catSettings['items'] ?? [];
    $viewType = $catSettings['view_type'] ?? 'slider';
    $gridCols = $catSettings['grid_cols'] ?? 6;
    $gridRows = $catSettings['grid_rows'] ?? 1;
    $sliderEffect = 'slide';
    $showNavigation = (bool) ($catSettings['show_navigation'] ?? false);
    $showPagination = (bool) ($catSettings['show_pagination'] ?? false);

    $categorySliderConfig = [
        'enabled' => true,
        'pagination' => $showPagination ? 'bullets' : 'none',
        'direction' => 'horizontal',
        'navigation' => $showNavigation,
        'effect' => $sliderEffect,
        'loop' => false,
        'autoplay' => false,
        'spaceBetween' => 14,
        'breakpoints' => [
            '0' => ['slidesPerView' => 2.2, 'spaceBetween' => 10],
            '480' => ['slidesPerView' => 3.2, 'spaceBetween' => 10],
            '768' => ['slidesPerView' => 5.2, 'spaceBetween' => 12],
            '1024' => ['slidesPerView' => (int) ($gridCols ?? 12), 'spaceBetween' => 14],
        ],
    ];
@endphp

@if($isEnabled && !empty($categories))
<section class="mb-12">
    <div class="container relative">
        @if($title)
            <div class="mb-6 flex items-center border-r-4 border-primary pr-4">
                <h3 class="text-lg font-black text-slate-800 dark:text-white lg:text-xl">{{ $title }}</h3>
            </div>
        @endif

        @if($viewType === 'slider')
            <div class="swiper home-category-slider banner-slider" data-slider-config='@json($categorySliderConfig)'>
                <div class="swiper-wrapper">
                    @foreach($categories as $item)
                        <div class="swiper-slide">
                            <a class="group relative flex flex-col items-center gap-2" href="{{ $item['link'] ?? '#' }}" title="{{ $item['description'] ?? '' }}">
                                <img alt="{{ $item['alt'] ?? $item['title'] ?? 'دسته‌بندی' }}" class="h-20 w-20 rounded-full object-cover md:h-24 md:w-24" src="{{ $item['image'] ?? '' }}" loading="lazy"/>
                                <p class="line-clamp-1 text-center text-xs font-medium text-slate dark:text-white md:text-sm">{{ $item['title'] ?? '' }}</p>
                                @if(!empty($item['description']))
                                    <span class="pointer-events-none absolute -top-8 z-10 hidden rounded-md bg-slate px-2 py-1 text-xs text-white group-hover:block">{{ $item['description'] }}</span>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
                @if($showNavigation)
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                @endif
                @if($showPagination)
                    <div class="swiper-pagination"></div>
                @endif
            </div>
        @else
            <div class="category-grid-custom" style="--cols-desktop: {{ $gridCols }}; --rows-desktop: {{ $gridRows }};">
                @foreach($categories as $item)
                    <a class="group relative flex flex-col items-center gap-2" href="{{ $item['link'] ?? '#' }}" title="{{ $item['description'] ?? '' }}">
                        <img alt="{{ $item['alt'] ?? $item['title'] ?? 'دسته‌بندی' }}" class="h-20 w-20 rounded-full object-cover md:h-24 md:w-24" src="{{ $item['image'] ?? '' }}" loading="lazy"/>
                        <p class="line-clamp-1 text-center text-xs font-medium text-slate dark:text-white md:text-sm">{{ $item['title'] ?? '' }}</p>
                        @if(!empty($item['description']))
                            <span class="pointer-events-none absolute -top-8 z-10 hidden rounded-md bg-slate px-2 py-1 text-xs text-white group-hover:block">{{ $item['description'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
            <style>
                .category-grid-custom {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 1rem;
                }
                @media (min-width: 640px) { .category-grid-custom { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
                @media (min-width: 768px) { .category-grid-custom { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
                @media (min-width: 1024px) {
                    .category-grid-custom {
                        grid-template-columns: repeat(var(--cols-desktop, 6), minmax(0, 1fr));
                        grid-template-rows: repeat(var(--rows-desktop, 1), minmax(0, 1fr));
                    }
                }
            </style>
        @endif
    </div>
</section>
@endif
