@php use App\Http\Controllers\ProductController; @endphp
@if($store == "digikala")
    @if(!empty($data['data']))
        <div class="mb-6 rounded-squircle bg-white p-6 shadow-base dark:bg-slate">
            <div class="mb-6 grid flex-grow grid-cols-12 gap-8">
                <div class="col-span-12 lg:col-span-4">
                    <div class="space-y-4">
                        <div class="group relative aspect-square overflow-hidden rounded-squircle border border-slate-100 bg-slate-50/30 p-4 dark:border-white/5 dark:bg-white/5">
                            <picture>
                                <source type="image/webp"
                                        srcset="{{ProductController::ImgProfile($data['data']['product']['images']['main']['url'][0], 800, 800, 90, true)}}">
                                <img
                                        width="800" height="800"
                                        src="{{ProductController::ImgProfile($data['data']['product']['images']['main']['url'][0], 800, 800, 90, false)}}"
                                        alt="{{$data['data']['product']['title_fa']}}" class="mx-auto h-full w-full object-contain transition-transform duration-700 group-hover:scale-110"
                                        loading="lazy"
                                        decoding="async"
                                >
                            </picture>
                        </div>
                        @if(isset($data['data']['product']['images']['list']) && !empty($data['data']['product']['images']['list']))
                            <div class="flex items-center justify-center gap-x-3">
                                @php($couner = 0)
                                @foreach ($data['data']['product']['images']['list'] as $thumb)
                                    @if (count($data['data']['product']['images']['list']) >= 4)
                                        @if ($couner < 3)
                                            <button type="button" data-modal-target="product-gallery-modal"
                                                    data-modal-toggle="product-gallery-modal"
                                                    class="cursor-pointer overflow-hidden rounded-squircle border border-slate-100 p-1 transition-all hover:border-primary dark:border-white/5 dark:hover:border-primary">
                                                <picture>
                                                    <source type="image/webp"
                                                            srcset="{{ProductController::ImgProfile($thumb['url'][0], 200, 200, 80, true)}}">
                                                    <img
                                                            width="200" height="200"
                                                            src="{{ProductController::ImgProfile($thumb['url'][0], 200, 200, 80, false)}}"
                                                            alt="{{$data['data']['product']['title_fa']}}"
                                                            class="h-16 w-16 xl:h-20 xl:w-20 object-contain"
                                                            loading="lazy"
                                                            decoding="async"
                                                    >
                                                </picture>
                                            </button>
                                        @endif
                                        @if ($couner == 3)
                                            <button type="button" data-modal-target="product-gallery-modal"
                                                    data-modal-toggle="product-gallery-modal"
                                                    class="relative cursor-pointer overflow-hidden rounded-squircle border border-slate-100 p-1 transition-all hover:border-primary dark:border-white/5 dark:hover:border-primary">
                                                <picture>
                                                    <source type="image/webp"
                                                            srcset="{{ProductController::ImgProfile($thumb['url'][0], 200, 200, 80, true)}}">
                                                    <img
                                                            width="200" height="200"
                                                            src="{{ProductController::ImgProfile($thumb['url'][0], 200, 200, 80, false)}}"
                                                            alt="{{$data['data']['product']['title_fa']}}"
                                                            class="h-16 w-16 blur xl:h-20 xl:w-20 object-contain"
                                                            loading="lazy"
                                                            decoding="async"
                                                    >
                                                </picture>
                                                <div class="absolute inset-0 flex items-center justify-center bg-black/20 text-white">
                                                    <svg class="h-6 w-6"><use xlink:href="#horizontal-dot"/></svg>
                                                </div>
                                            </button>
                                        @endif
                                        @if ($couner > 3)
                                            @break
                                        @endif
                                        @php($couner++)
                                    @else
                                        <button type="button" data-modal-target="product-gallery-modal"
                                                data-modal-toggle="product-gallery-modal"
                                                class="cursor-pointer overflow-hidden rounded-squircle border border-slate-100 p-1 transition-all hover:border-primary dark:border-white/5 dark:hover:border-primary">
                                            <picture>
                                                <source type="image/webp"
                                                        srcset="{{ProductController::ImgProfile($thumb['url'][0], 200, 200, 80, true)}}">
                                                <img
                                                        width="200" height="200"
                                                        src="{{ProductController::ImgProfile($thumb['url'][0], 200, 200, 80, false)}}"
                                                        alt="{{$data['data']['product']['title_fa']}}"
                                                        class="h-16 w-16 xl:h-20 xl:w-20 object-contain"
                                                        loading="lazy"
                                                        decoding="async"
                                                >
                                            </picture>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-8 flex flex-col">
                    <div class="mb-2 flex items-center justify-between">
                        @if(isset($data['data']['product']['brand']['code']))
                            <a href="/product/brand/{{$data['data']['product']['brand']['code']}}/" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:opacity-80 transition-opacity">
                                <span>{{$data['data']['product']['brand']['title_fa']}}</span>
                                <svg class="h-4 w-4 rotate-180"><use xlink:href="#chevron-right"/></svg>
                            </a>
                        @endif
                        <div class="flex items-center gap-3">
                            @auth
                            <form action="{{ route('bookmarks.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="p-2 rounded-full bg-slate-50 text-slate-400 hover:text-primary transition-colors dark:bg-white/5">
                                    <svg class="h-5 w-5 @if($product->bookmarks()->where('user_id', auth()->id())->count()) fill-primary text-primary @endif"><use xlink:href="#order"/></svg>
                                </button>
                            </form>
                            @endauth
                            <button type="button" data-modal-target="product-share-modal" data-modal-toggle="product-share-modal" class="p-2 rounded-full bg-slate-50 text-slate-400 hover:text-primary transition-colors dark:bg-white/5">
                                <svg class="h-5 w-5"><use xlink:href="#social-share"/></svg>
                            </button>
                        </div>
                    </div>
                    <h1 class="mb-4 text-xl font-black text-slate-800 dark:text-white lg:text-2xl leading-relaxed">{{$data['data']['product']['title_fa']}}</h1>
                    <div class="grid flex-grow grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            @if (isset($data['data']['product']['title_en']))
                                <h2 class="text-sm font-medium text-slate-400 dark:text-slate-500 uppercase tracking-wide">{{$data['data']['product']['title_en']}}</h2>
                            @endif
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 text-xs font-medium text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                    <svg class="h-4 w-4"><use xlink:href="#id"/></svg>
                                    <span>شناسه: {{$data['data']['product']['id']}}</span>
                                </div>
                                @if ($data['data']['product']['rating']['rate'] != 0)
                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 dark:bg-slate-800 text-xs font-bold text-primary">
                                    <svg class="h-4 w-4"><use xlink:href="#like"/></svg>
                                    <span>{{$data['data']['product']['rating']['rate']}}% رضایت</span>
                                </div>
                                @endif
                            </div>
                            @if (isset($data['data']['product']['review']['attributes']))
                                <div class="space-y-4">
                                    <p class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest border-r-4 border-primary pr-2">ویژگی‌های کلیدی</p>
                                    <ul class="space-y-3">
                                        @foreach (array_slice($data['data']['product']['review']['attributes'], 0, 5) as $attributes)
                                            <li class="flex items-start gap-2">
                                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></span>
                                                <div class="flex flex-wrap gap-1 text-sm">
                                                    <span class="font-medium text-slate-500 dark:text-slate-400">{{$attributes['title']}}:</span>
                                                    <span class="text-slate-700 dark:text-slate-200">{{$attributes['values'][0]}}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-6">
                            @if (isset($data['data']['product']['colors'][0]['hex_code']))
                                <div class="space-y-3">
                                    <p class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest border-r-4 border-primary pr-2">انتخاب رنگ</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($data['data']['product']['colors'] as $colors)
                                            <label class="relative group cursor-pointer">
                                                <input type="radio" name="color" class="peer sr-only dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" @if($loop->first) checked @endif>
                                                <div class="flex items-center gap-2 px-3 py-2 rounded-full border border-slate-200 bg-white transition-all peer-checked:border-primary peer-checked:ring-4 peer-checked:ring-primary/10 dark:border-white/10 dark:bg-slate-800">
                                                    <span class="h-5 w-5 rounded-full border border-black/10" style="background-color: {{$colors['hex_code']}}"></span>
                                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{$colors['title']}}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-squircle bg-slate-50/50 p-6 dark:bg-white/5 space-y-6">
                                @if (isset($data['data']['product']['default_variant']['warranty']['title_fa']))
                                    <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm dark:bg-slate-800">
                                            <svg class="h-6 w-6 text-primary"><use xlink:href="#verified"/></svg>
                                        </div>
                                        <span class="font-medium">{{ProductController::TextMuted($data['data']['product']['default_variant']['warranty']['title_fa'])}}</span>
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    @if (isset($data['data']['product']['default_variant']['price']['selling_price']))
                                        <div class="flex flex-col items-end gap-1">
                                            @if (isset($data['data']['product']['default_variant']['price']['rrp_price']) && $data['data']['product']['default_variant']['price']['discount_percent'] != 0)
                                                <div class="flex items-center gap-2">
                                                    <del class="text-sm font-medium text-slate-400">{{number_format($data['data']['product']['default_variant']['price']['rrp_price']/10)}}</del>
                                                    <span class="px-2 py-0.5 rounded-full bg-warning text-[11px] font-black text-white">{{$data['data']['product']['default_variant']['price']['discount_percent']}}%</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-3xl font-black text-slate-800 dark:text-white">{{number_format($data['data']['product']['default_variant']['price']['selling_price'] / 10)}}</span>
                                                <svg class="h-6 w-6 text-primary"><use xlink:href="#toman"/></svg>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    @if ($data['data']['product']['status'] == "marketable")
                                        <a target="_blank" rel="nofollow" referrerpolicy="no-referrer"
                                           href="{{ProductController::GetBaseLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                                           data-human-href="{{ProductController::GetSpecialLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                                           data-analytics-goal="tr_atc" data-store="digikala"
                                           data-product-id="{{ $data['data']['product']['id'] }}"
                                           class="btn-primary w-full shadow-lg shadow-primary/30 py-4 text-base js-special-link">
                                            <span>افزودن به سبد خرید</span>
                                            <svg class="h-5 w-5 rotate-180"><use xlink:href="#chevron-right"/></svg>
                                        </a>
                                    @else
                                        <a target="_blank" rel="nofollow" referrerpolicy="no-referrer"
                                           href="{{ProductController::GetBaseLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                                           data-human-href="{{ProductController::GetSpecialLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                                           data-analytics-goal="tr_dk"
                                           data-product-id="{{ $data['data']['product']['id'] }}"
                                           data-human-text="مشاهده اطلاعات بیشتر"
                                           data-human-style="btn-secondary"
                                           class="btn-primary w-full py-4 text-base flex items-center justify-center gap-2 js-status-aware-btn">
                                            <span class="js-btn-text">افزودن به سبد خرید</span>
                                            <svg class="h-5 w-5 rotate-180"><use xlink:href="#chevron-right"/></svg>
                                        </a>
                                    @endif
                                    
                                    @if (isset($data['data']['product']['variants']) && !empty($data['data']['product']['variants'][1]))
                                        <a href="#sellers" data-analytics-goal="tr_sl" data-product-id="{{ $data['data']['product']['id'] }}" class="btn-subtle w-full py-4 text-base">
                                            <span>مشاهده سایر فروشندگان</span>
                                            <svg class="h-5 w-5 rotate-180"><use xlink:href="#chevron-right"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

@if($store == "basalam")
    @if(!empty($data))
        <div class="mb-6 rounded-squircle bg-white p-6 shadow-base dark:bg-slate">
            <div class="mb-6 grid flex-grow grid-cols-12 gap-8">
                <div class="col-span-12 lg:col-span-4">
                    <div class="space-y-4">
                        <div class="group relative aspect-square overflow-hidden rounded-squircle border border-slate-100 bg-slate-50/30 p-4 dark:border-white/5 dark:bg-white/5">
                            <picture>
                                <source type="image/webp"
                                        srcset="{{ProductController::ImgProfile($data['photo']['large'],null,null,null,null,null,"basalam")}}">
                                <img
                                        width="800" height="800"
                                        src="{{ProductController::ImgProfile($data['photo']['large'],null,null,null,null,null,"basalam")}}"
                                        alt="{{$data['title']}}" class="mx-auto h-full w-full object-contain transition-transform duration-700 group-hover:scale-110"
                                        loading="lazy"
                                        decoding="async"
                                >
                            </picture>
                        </div>
                        <div class="flex items-center justify-center gap-x-3">
                            @php($couner = 0)
                            @foreach ($data['photos'] as $thumb)
                                @if (count($data['photos']) >= 4)
                                    @if ($couner < 3)
                                        <button type="button" data-modal-target="product-gallery-modal"
                                                data-modal-toggle="product-gallery-modal"
                                                class="cursor-pointer overflow-hidden rounded-squircle border border-slate-100 p-1 transition-all hover:border-primary dark:border-white/5 dark:hover:border-primary">
                                            <picture>
                                                <source type="image/webp"
                                                        srcset="{{ProductController::ImgProfile($thumb['large'],null,null,null,null,null,"basalam")}}">
                                                <img
                                                        width="200" height="200"
                                                        src="{{ProductController::ImgProfile($thumb['large'],null,null,null,null,null,"basalam")}}"
                                                        alt="{{$data['title']}}"
                                                        class="h-16 w-16 xl:h-20 xl:w-20 object-contain"
                                                        loading="lazy"
                                                        decoding="async"
                                                >
                                            </picture>
                                        </button>
                                    @endif
                                    @if ($couner == 3)
                                        <button type="button" data-modal-target="product-gallery-modal"
                                                data-modal-toggle="product-gallery-modal"
                                                class="relative cursor-pointer overflow-hidden rounded-squircle border border-slate-100 p-1 transition-all hover:border-primary dark:border-white/5 dark:hover:border-primary">
                                            <picture>
                                                <source type="image/webp"
                                                        srcset="{{ProductController::ImgProfile($thumb['extra_small'],null,null,null,null,null,"basalam")}}">
                                                <img
                                                        width="200" height="200"
                                                        src="{{ProductController::ImgProfile($thumb['extra_small'],null,null,null,null,null,"basalam")}}"
                                                        alt="{{$data['title']}}"
                                                        class="h-16 w-16 blur xl:h-20 xl:w-20 object-contain"
                                                        loading="lazy"
                                                        decoding="async"
                                                >
                                            </picture>
                                            <div class="absolute inset-0 flex items-center justify-center bg-black/20 text-white">
                                                <svg class="h-6 w-6"><use xlink:href="#horizontal-dot"/></svg>
                                            </div>
                                        </button>
                                    @endif
                                    @if ($couner > 3)
                                        @break
                                    @endif
                                    @php($couner++)
                                @else
                                    <button type="button" data-modal-target="product-gallery-modal"
                                            data-modal-toggle="product-gallery-modal"
                                            class="cursor-pointer overflow-hidden rounded-squircle border border-slate-100 p-1 transition-all hover:border-primary dark:border-white/5 dark:hover:border-primary">
                                        <picture>
                                            <source type="image/webp"
                                                    srcset="{{ProductController::ImgProfile($thumb['extra_small'],null,null,null,null,null,"basalam")}}">
                                            <img
                                                    width="200" height="200"
                                                    src="{{ProductController::ImgProfile($thumb['extra_small'],null,null,null,null,null,"basalam")}}"
                                                    alt="{{$data['title']}}"
                                                    class="h-16 w-16 xl:h-20 xl:w-20 object-contain"
                                                    loading="lazy"
                                                    decoding="async"
                                            >
                                        </picture>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-8 flex flex-col">
                    <div class="mb-2 flex items-center justify-end">
                        <div class="flex items-center gap-3">
                            @auth
                            <form action="{{ route('bookmarks.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="p-2 rounded-full bg-slate-50 text-slate-400 hover:text-primary transition-colors dark:bg-white/5">
                                    <svg class="h-5 w-5 @if($product->bookmarks()->where('user_id', auth()->id())->count()) fill-primary text-primary @endif"><use xlink:href="#order"/></svg>
                                </button>
                            </form>
                            @endauth
                            <button type="button" data-modal-target="product-share-modal" data-modal-toggle="product-share-modal" class="p-2 rounded-full bg-slate-50 text-slate-400 hover:text-primary transition-colors dark:bg-white/5">
                                <svg class="h-5 w-5"><use xlink:href="#social-share"/></svg>
                            </button>
                        </div>
                    </div>
                    <h1 class="mb-4 text-xl font-black text-slate-800 dark:text-white lg:text-2xl leading-relaxed">{{$data['title']}}</h1>
                    <div class="grid flex-grow grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 text-xs font-medium text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                    <svg class="h-4 w-4"><use xlink:href="#id"/></svg>
                                    <span>شناسه: {{$data['id']}}</span>
                                </div>
                                @if ($data['rating'] != 0)
                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 dark:bg-slate-800 text-xs font-bold text-primary">
                                    <svg class="h-4 w-4"><use xlink:href="#like"/></svg>
                                    <span>{{$data['rating']*20}}% رضایت</span>
                                </div>
                                @endif
                            </div>
                            @if (isset($data['attribute_groups'][0]['attributes']))
                                <div class="space-y-4">
                                    <p class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest border-r-4 border-primary pr-2">ویژگی‌های کلیدی</p>
                                    <ul class="space-y-3">
                                        @foreach (array_slice($data['attribute_groups'][0]['attributes'], 0, 5) as $attributes)
                                            <li class="flex items-start gap-2">
                                                <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary"></span>
                                                <div class="flex flex-wrap gap-1 text-sm">
                                                    <span class="font-medium text-slate-500 dark:text-slate-400">{{ProductController::RemoveEmoji($attributes['title'])}}:</span>
                                                    <span class="text-slate-700 dark:text-slate-200">{{ProductController::RemoveEmoji($attributes['value'])}}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-6">
                            @if (isset($data['variants']['properties']) && $data['variants']['properties'][0]['property']['type'] == "color")
                                <div class="space-y-3">
                                    <p class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-widest border-r-4 border-primary pr-2">انتخاب رنگ</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($data['variants']['properties'] as $colors)
                                            @if($colors['property']['type'] == "color")
                                            <label class="relative group cursor-pointer">
                                                <input type="radio" name="color" class="peer sr-only dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" @if($loop->first) checked @endif>
                                                <div class="flex items-center gap-2 px-3 py-2 rounded-full border border-slate-200 bg-white transition-all peer-checked:border-primary peer-checked:ring-4 peer-checked:ring-primary/10 dark:border-white/10 dark:bg-slate-800">
                                                    <span class="h-5 w-5 rounded-full border border-black/10" style="background-color: {{$colors['value']['value']}}"></span>
                                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-300">{{$colors['value']['title']}}</span>
                                                </div>
                                            </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-squircle bg-slate-50/50 p-6 dark:bg-white/5 space-y-6">
                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300 tooltip-trigger relative cursor-help">
                                    <span class="font-black text-slate-800 dark:text-white">{{ ProductController::RemoveEmoji($data['vendor']['owner']['vendor']['title']) }}</span>
                                    <span class="rounded-full bg-slate-50 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-primary">باسلام</span>
                                    <div class="tooltip invisible absolute bottom-full right-0 mb-2 w-48 p-2 bg-slate-800 text-white text-[10px] rounded-lg opacity-0 transition-all group-hover:visible group-hover:opacity-100">
                                        فروشنده رسمی باسلام. ضمانت اصالت و سلامت کالا.
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    @if (isset($data['price']))
                                        <div class="flex flex-col items-end gap-1">
                                            @if (isset($data['primary_price']) && $data['primary_price'] != 0 && $data['primary_price'] > $data['price'])
                                                <div class="flex items-center gap-2">
                                                    <del class="text-sm font-medium text-slate-400">{{number_format($data['primary_price']/10)}}</del>
                                                    <span class="px-2 py-0.5 rounded-full bg-warning text-[11px] font-black text-white">{{round((($data['primary_price'] - $data['price']) / $data['primary_price'])*100)}}%</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-3xl font-black text-slate-800 dark:text-white">{{number_format($data['price'] / 10)}}</span>
                                                <svg class="h-6 w-6 text-primary"><use xlink:href="#toman"/></svg>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    <a target="_blank" rel="nofollow" referrerpolicy="no-referrer"
                                       href="{{ProductController::GetBaseLink("basalam",null,$data['title'])}}"
                                       data-human-href="{{ProductController::GetSpecialLink("basalam",null,$data['title'])}}"
                                       data-analytics-goal="tr_atc" data-store="basalam"
                                       data-product-id="{{ $data['id'] }}"
                                       class="btn-primary w-full shadow-lg shadow-primary/30 py-4 text-base js-special-link">
                                        <span>افزودن به سبد خرید</span>
                                        <svg class="h-5 w-5 rotate-180"><use xlink:href="#chevron-right"/></svg>
                                    </a>
                                    
                                    <a href="#sellers" data-analytics-goal="tr_sl" data-product-id="{{ $data['id'] }}" class="btn-subtle w-full py-4 text-base">
                                        <span>مشاهده قیمت در سایر فروشگاه‌ها</span>
                                        <svg class="h-5 w-5 rotate-180"><use xlink:href="#chevron-right"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
