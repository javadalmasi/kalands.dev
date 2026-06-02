@php
    use App\Http\Controllers\ProductController;

    $store = $store ?? 'digikala';
    $item = $item ?? [];
    $href = $href ?? '#';
    $newTab = $newTab ?? false;
    $productKey = $productKey ?? '';

    if ($store === 'digikala') {
        $title = ProductController::LinkReplaced($item['title_fa'] ?? '');
        $image = $item['images']['main']['url'][0] ?? null;
        $discountPercent = (int)($item['default_variant']['price']['discount_percent'] ?? 0);
        $rrpPrice = (int)($item['default_variant']['price']['rrp_price'] ?? 0);
        $sellingPrice = (int)($item['default_variant']['price']['selling_price'] ?? 0);
    } else {
        $title = $item['name'] ?? '';
        $image = $item['photo']['MEDIUM'] ?? null;
        $sellingPrice = (int)($item['price'] ?? 0);
        $rrpPrice = (int)($item['primaryPrice'] ?? 0);
        $discountPercent = ($rrpPrice > 0 && $sellingPrice > 0 && $rrpPrice !== $sellingPrice)
            ? (int)round((($rrpPrice - $sellingPrice) / $rrpPrice) * 100)
            : 0;
    }
@endphp

<div class="group relative flex h-full min-h-[320px] md:min-h-[380px] flex-col overflow-hidden rounded-squircle border border-slate-100 bg-white p-3 md:p-4 transition-all duration-500 hover:border-primary/20 hover:shadow-2xl hover:shadow-slate-200/50 dark:border-white/5 dark:bg-slate dark:hover:shadow-none"
    @if($productKey !== '') data-product-key="{{ $productKey }}" @endif>
    
    <div class="relative mb-3 md:mb-4 flex-shrink-0 pt-[100%] overflow-hidden rounded-squircle bg-slate-50/50 dark:bg-white/5">
        <a href="{{ $href }}" @if($newTab) target="_blank" rel="nofollow" referrerpolicy="no-referrer" @endif 
           @if($store === 'digikala') data-analytics-goal="tr_dk" @elseif($store === 'basalam') data-analytics-goal="tr_bs" @endif
           @if($productKey !== '') data-product-id="{{ $productKey }}" @endif
           class="absolute inset-0 flex items-center justify-center p-3 md:p-4" aria-label="{{ $title }}">
            @if($image)
                <picture class="h-full w-full">
                    @php($imgUrl = $store === 'basalam' ? ProductController::ImgProfile($image, null, null, null, null, null, 'basalam') : ProductController::ImgProfile($image, 400, 400, 80, false))
                    @php($webpUrl = $store === 'basalam' ? $imgUrl : ProductController::ImgProfile($image, 400, 400, 80, true))
                    <source type="image/webp" srcset="{{ $webpUrl }}">
                    <img width="400" height="400"
                         src="{{ $imgUrl }}"
                         alt="{{ $title }}"
                         class="h-full w-full object-contain transition-transform duration-700 group-hover:scale-110"
                         loading="lazy" decoding="async">
                </picture>
            @endif
        </a>
        
        @if($discountPercent > 0)
            <div class="absolute right-2 top-2 rounded-full bg-warning px-2 py-1 text-[10px] font-black text-white shadow-lg">
                {{ $discountPercent }}%
            </div>
        @endif
    </div>

    <div class="mb-2 md:mb-3 flex-grow">
        <a class="line-clamp-2 text-xs md:text-sm font-bold leading-relaxed text-slate-700 transition-colors group-hover:text-primary dark:text-slate-200"
           href="{{ $href }}" @if($newTab) target="_blank" rel="nofollow" referrerpolicy="no-referrer" @endif
           @if($store === 'digikala') data-analytics-goal="tr_dk" @elseif($store === 'basalam') data-analytics-goal="tr_bs" @endif
           @if($productKey !== '') data-product-id="{{ $productKey }}" @endif>
            {{ $title }}
        </a>
    </div>

    <div class="mt-auto pt-2 md:pt-3 border-t border-slate-50 dark:border-white/5">
        <div class="flex flex-col items-end gap-0.5 md:gap-1">
            @if($sellingPrice > 0)
                @if($discountPercent > 0)
                    <del class="text-[11px] font-medium text-slate-400 decoration-warning/50">{{ number_format($rrpPrice / 10) }}</del>
                @endif
                <div class="flex items-center gap-1">
                    <span class="text-base md:text-lg font-black text-slate-800 dark:text-white">{{ number_format($sellingPrice / 10) }}</span>
                    <svg class="h-3.5 w-3.5 md:h-4 md:w-4 text-primary"><use xlink:href="#toman"/></svg>
                </div>
            @else
                <span class="text-sm font-bold text-slate-400">ناموجود</span>
            @endif
        </div>
    </div>

    <div class="absolute inset-x-0 bottom-0 h-1 translate-y-full bg-primary transition-transform duration-500 group-hover:translate-y-0"></div>
</div>
