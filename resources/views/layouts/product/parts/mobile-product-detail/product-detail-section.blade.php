@php use App\Http\Controllers\ProductController; @endphp
@if($store == "digikala")
    @if (isset($data['data']))
        <div class="mb-6 rounded-squircle border border-slate bg-slate/10 p-4 shadow-base dark:border-white/10 dark:bg-slate">
            <div class="relative mb-4">
                <div class="swiper product-image-mobile-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <picture>
                                <source type="image/webp"
                                        srcset="{{ProductController::ImgProfile($data['data']['product']['images']['main']['url'][0], 800, 800, 90, true)}}">
                                <img
                                        width="800" height="800"
                                        src="{{ProductController::ImgProfile($data['data']['product']['images']['main']['url'][0], 800, 800, 90, false)}}"
                                        alt="{{$data['data']['product']['title_fa']}}" class="mx-auto rounded-squircle border border-slate/70 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate"
                                        loading="lazy"
                                        decoding="async"
                                >
                            </picture>
                        </div>
                        @if(isset($data['data']['product']['images']['list']) && !empty($data['data']['product']['images']['list']))
                            @foreach ($data['data']['product']['images']['list'] as $thumb)
                                <div class="swiper-slide">
                                    <picture>
                                        <source type="image/webp"
                                                srcset="{{ProductController::ImgProfile($thumb['url'][0], 600, 600, 80, true)}}">
                                        <img
                                                width="600" height="600"
                                                src="{{ProductController::ImgProfile($thumb['url'][0], 600, 600, 80, false)}}"
                                                alt="{{$data['data']['product']['title_fa']}}" class="mx-auto rounded-squircle border border-slate/70 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate"
                                                loading="lazy"
                                                decoding="async"
                                        >
                                    </picture>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="swiper-pagination mt-2 text-center text-xs font-semibold text-slate dark:text-slate-200"></div>
                </div>
                <div class="absolute left-0 top-0 z-10">
                    <div class="flex items-center gap-x-4 text-slate dark:text-white">
                        @auth
                            <div>
                                <form action="{{ route('likes.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button data-tooltip-target="add-to-favorite" class="flex items-center gap-2">
                                        <svg class="h-6 w-6"><use xlink:href="#like"/></svg>
                                    </button>
                                </form>
                            </div>
                            <div>
                                <form action="{{ route('bookmarks.store') }}" method="POST" class="flex items-center">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button data-tooltip-target="add-to-bookmarks">
                                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" class="h-6 w-6"><path d="M5 3.75C5 2.784 5.784 2 6.75 2h10.5c.966 0 1.75.784 1.75 1.75v17.5a.75.75 0 0 1-1.218.586L12 17.21l-5.781 4.625A.75.75 0 0 1 5 21.25Z"></path></svg>
                                    </button>
                                </form>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>

            @if(isset($data['data']['product']['brand']['code']))
                <div class="mb-4">
                    <a href="/product/brand/{{$data['data']['product']['brand']['code']}}/" class="inline-flex items-center gap-1 rounded-squircle border border-slate bg-white px-3 py-1.5 text-sm text-primary transition-colors hover:border-primary hover:text-primary dark:border-white/10 dark:bg-slate dark:text-primary dark:hover:border-primary dark:hover:text-primary">
                        <span>برند:</span>
                        <span class="font-medium">{{$data['data']['product']['brand']['title_fa']}}</span>
                    </a>
                </div>
            @endif


            <div class="mb-4 font-semibold text-slate dark:text-white md:text-lg">{{$data['data']['product']['title_fa']}}</div>
            <div class="mb-2 grid grid-cols-4 gap-2 text-sm md:text-base">
                <div class="col-span-3 grid grid-cols-3 gap-2">
                    <a href="{{ProductController::AffiliateLinkGenerator("digikala",$data['data']['product']['id'])}}"
                       target="_blank" data-analytics-goal="tr_dk" data-product-id="{{ $data['data']['product']['id'] }}"
                       class="inline-flex h-9 items-center justify-center gap-1 rounded-squircle border border-slate bg-white px-2 text-slate transition-colors hover:border-slate hover:text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200 dark:hover:border-slate dark:hover:text-slate">
                        <svg class="h-4 w-4"><use xlink:href="#id"/></svg>
                        <span class="text-[11px] font-medium">{{$data['data']['product']['id']}}</span>
                    </a>
                    @if ($data['data']['product']['rating']['rate'] != 0)
                        <span class="inline-flex h-9 items-center justify-center gap-1 rounded-squircle border border-slate bg-white px-2 text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200">
                            <svg class="h-4 w-4"><use xlink:href="#gestures"/></svg>
                            <span class="text-[11px] font-medium">{{$data['data']['product']['rating']['rate']}}%</span>
                        </span>
                    @endif
                    @if ($data['data']['product']['comments_count'] != 0)
                        <a href="#" class="inline-flex h-9 items-center justify-center gap-1 rounded-squircle border border-slate bg-white px-2 text-slate transition-colors hover:border-slate hover:text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200 dark:hover:border-slate dark:hover:text-slate">
                            <svg class="h-4 w-4"><use xlink:href="#comments-icon"/></svg>
                            <span class="text-[11px] font-medium">{{$data['data']['product']['comments_count']}}</span>
                        </a>
                    @endif
                </div>
                <button type="button" data-modal-target="product-share-modal" data-modal-toggle="product-share-modal"
                        class="inline-flex h-9 w-full items-center justify-center rounded-squircle border border-slate bg-white text-slate transition-colors hover:border-slate hover:text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200 dark:hover:border-slate dark:hover:text-slate"
                        aria-label="اشتراک گذاری">
                    <svg class="h-5 w-5">
                        <use xlink:href="#social-share"/>
                    </svg>
                </button>
            </div>

            @if (isset($data['data']['product']['colors']) && !empty($data['data']['product']['colors']))
                <div class="my-4 h-px w-full bg-slate dark:bg-white/5"></div>
                <div class="mb-6 space-y-4">
                    <div class="text-sm font-medium text-slate dark:text-white md:text-base">رنگ های موجود</div>
                    <fieldset class="flex flex-wrap items-center gap-2">
                        <legend class="sr-only">Color</legend>
                        @foreach ($data['data']['product']['colors'] as $colors)
                            <div>
                                <input checked="checked" type="radio" name="color" value="color-1" id="color-1" class="peer hidden dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" />
                                <label for="color-1"
                                       class="relative block cursor-pointer rounded-squircle border border-slate bg-white px-2.5 py-2 shadow-base transition-colors hover:border-primary dark:border-white/10 dark:bg-slate dark:hover:border-primary">
                                    <a class="flex items-center gap-x-2"
                                       href="{{ProductController::AffiliateLinkGenerator("digikala",$data['data']['product']['id'])}}"
                                       target="_blank" data-analytics-goal="tr_dk" data-product-id="{{ $data['data']['product']['id'] }}">
                                        <div class="h-5 w-5 rounded-full border-2 border-slate shadow-base dark:border-white/30 product-color-chip" data-color-value="{{$colors['hex_code']}}"></div>
                                        <p class="text-sm text-slate dark:text-slate-200 md:text-base">{{$colors['title']}}</p>
                                    </a>
                                </label>
                            </div>
                        @endforeach
                    </fieldset>
                </div>
            @endif
        </div>
    @endif
@endif

@if($store == "basalam")
    @if(!empty($data))
        <div class="mb-6 rounded-squircle border border-slate bg-slate/10 p-4 shadow-base dark:border-white/10 dark:bg-slate">
            <div class="relative mb-4">
                <div class="swiper product-image-mobile-swiper">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <picture>
                                <source type="image/webp"
                                        srcset="{{ProductController::ImgProfile($data['photo']['large'],null,null,null,null,null,"basalam")}}">
                                <img
                                        width="800" height="800"
                                        src="{{ProductController::ImgProfile($data['photo']['large'],null,null,null,null,null,"basalam")}}"
                                        alt="{{$data['title']}}" class="mx-auto rounded-squircle border border-slate/70 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate"
                                        loading="lazy"
                                        decoding="async"
                                >
                            </picture>
                        </div>
                        @foreach ($data['photos'] as $thumb)
                            <div class="swiper-slide">
                                <picture>
                                    <source type="image/webp"
                                            srcset="{{ProductController::ImgProfile($thumb['large'],null,null,null,null,null,"basalam")}}">
                                    <img
                                            width="600" height="600"
                                            src="{{ProductController::ImgProfile($thumb['large'],null,null,null,null,null,"basalam")}}"
                                            alt="{{$data['title']}}" class="mx-auto rounded-squircle border border-slate/70 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate"
                                            loading="lazy"
                                            decoding="async"
                                    >
                                </picture>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination mt-2 text-center text-xs font-semibold text-slate dark:text-slate-200"></div>
                </div>
                <div class="absolute left-0 top-0 z-10">
                    <div class="flex items-center gap-x-4 text-slate dark:text-white">
                        @auth
                            <div>
                                <form action="{{ route('likes.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button data-tooltip-target="add-to-favorite" class="flex items-center gap-2">
                                        <svg class="h-6 w-6"><use xlink:href="#like"/></svg>
                                    </button>
                                </form>
                            </div>
                            <div>
                                <form action="{{ route('bookmarks.store') }}" method="POST" class="flex items-center">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button data-tooltip-target="add-to-bookmarks">
                                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" class="h-6 w-6"><path d="M5 3.75C5 2.784 5.784 2 6.75 2h10.5c.966 0 1.75.784 1.75 1.75v17.5a.75.75 0 0 1-1.218.586L12 17.21l-5.781 4.625A.75.75 0 0 1 5 21.25Z"></path></svg>
                                    </button>
                                </form>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>


            {{--            <div class="mb-4 flex flex-wrap items-center gap-2">--}}
            {{--                <div class="flex items-center gap-x-1 text-sm font-light text-primary dark:text-primary md:text-base">--}}
            {{--                    <a href="/seller/xbs_{{$data['vendor']['owner']['vendor']['identifier']}}/">{{ProductController::RemoveEmoji($data['vendor']['owner']['vendor']['title'])}}</a>--}}
            {{--                </div>--}}
            {{--            </div>--}}


            <div class="mb-4 font-semibold text-slate dark:text-white md:text-lg">{{$data['title']}}</div>
            <div class="mb-2 grid grid-cols-4 gap-2 text-sm md:text-base">
                <div class="col-span-3 grid grid-cols-3 gap-2">
                    <a href="{{ProductController::AffiliateLinkGenerator("basalam",null,$data['title'])}}"
                       target="_blank" data-analytics-goal="tr_bs" data-product-id="{{ $data['id'] }}"
                       class="inline-flex h-9 items-center justify-center gap-1 rounded-squircle border border-slate bg-white px-2 text-slate transition-colors hover:border-slate hover:text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200 dark:hover:border-slate dark:hover:text-slate">
                        <svg class="h-4 w-4"><use xlink:href="#id"/></svg>
                        <span class="text-[11px] font-medium">{{$data['id']}}</span>
                    </a>
                    @if ($data['rating'] != 0)
                        <span class="inline-flex h-9 items-center justify-center gap-1 rounded-squircle border border-slate bg-white px-2 text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200">
                            <svg class="h-4 w-4"><use xlink:href="#gestures"/></svg>
                            <span class="text-[11px] font-medium">{{$data['rating']*20}}%</span>
                        </span>
                    @endif
                    @if ($data['review_count'] != 0)
                        <a href="{{ProductController::AffiliateLinkGenerator("basalam",null,$data['title'])}}" data-analytics-goal="tr_bs" data-product-id="{{ $data['id'] }}" class="inline-flex h-9 items-center justify-center gap-1 rounded-squircle border border-slate bg-white px-2 text-slate transition-colors hover:border-slate hover:text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200 dark:hover:border-slate dark:hover:text-slate">
                            <svg class="h-4 w-4"><use xlink:href="#comments-icon"/></svg>
                            <span class="text-[11px] font-medium">{{$data['review_count']}}</span>
                        </a>
                    @endif
                </div>
                <button type="button" data-modal-target="product-share-modal" data-modal-toggle="product-share-modal"
                        class="inline-flex h-9 w-full items-center justify-center rounded-squircle border border-slate bg-white text-slate transition-colors hover:border-slate hover:text-slate dark:border-white/10 dark:bg-slate dark:text-slate-200 dark:hover:border-slate dark:hover:text-slate"
                        aria-label="اشتراک گذاری">
                    <svg class="h-5 w-5">
                        <use xlink:href="#social-share"/>
                    </svg>
                </button>
            </div>

            @if (isset($data['data']['product']['colors']) && !empty($data['data']['product']['colors']))
                <div class="my-4 h-px w-full bg-slate dark:bg-white/5"></div>
                <div class="mb-6 space-y-4">
                    <div class="text-sm font-medium text-slate dark:text-white md:text-base">رنگ های موجود</div>
                    <fieldset class="flex flex-wrap items-center gap-2">
                        <legend class="sr-only">Color</legend>
                        @foreach ($data['data']['product']['colors'] as $colors)
                            <div>
                                <input checked="checked" type="radio" name="color" value="color-1" id="color-1" class="peer hidden dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" />
                                <label for="color-1"
                                       class="relative block cursor-pointer rounded-squircle border border-slate bg-white px-2.5 py-2 shadow-base transition-colors hover:border-primary dark:border-white/10 dark:bg-slate dark:hover:border-primary">
                                    <a class="flex items-center gap-x-2"
                                       href="{{ProductController::AffiliateLinkGenerator("digikala",$data['data']['product']['id'])}}"
                                       target="_blank">
                                        <div class="h-5 w-5 rounded-full border-2 border-slate shadow-base dark:border-white/30 product-color-chip" data-color-value="{{$colors['hex_code']}}"></div>
                                        <p class="text-sm text-slate dark:text-slate-200 md:text-base">{{$colors['title']}}</p>
                                    </a>
                                </label>
                            </div>
                        @endforeach
                    </fieldset>
                </div>
            @endif
        </div>
    @endif
@endif
