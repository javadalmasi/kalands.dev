@php use App\Http\Controllers\ProductController; @endphp
@if (!empty($items['data']['products']))
    <section class="mb-12">
        <div class="container relative">
            <div class="mb-6 flex items-center justify-between border-r-4 border-primary pr-4">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white lg:text-xl">{{ $items['title'] }}</h3>
                    <p class="text-xs font-medium text-slate-400 mt-1 uppercase tracking-wider">Top curated products for you</p>
                </div>
                @if($items['link']['status'])
                    <a class="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-50 text-sm font-bold text-slate-600 hover:bg-primary hover:text-white transition-all duration-300 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-primary"
                       href="{{$items['link']['href']}}">
                        <span>مشاهده همه</span>
                        <svg class="h-4 w-4 rotate-180"><use xlink:href="#chevron-right"/></svg>
                    </a>
                @endif
            </div>

            <div class="swiper product-slider !pt-2">
                <div class="swiper-wrapper">
                    @foreach ($items['data']['products'] as $item)
                        @php($hrefLink = str_replace('dkp-','',$item['url']['uri']))
                        <div class="swiper-slide h-auto">
                            @include('components.product.card', [
                                'store' => 'digikala',
                                'item' => $item,
                                'href' => $hrefLink,
                                'newTab' => false,
                            ])
                        </div>
                    @endforeach
                </div>
                <div class="swiper-button-next !-left-4 lg:!-left-6"></div>
                <div class="swiper-button-prev !-right-4 lg:!-right-6"></div>
            </div>
        </div>
    </section>
@endif
