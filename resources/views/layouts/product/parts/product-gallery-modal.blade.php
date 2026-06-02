@php use App\Http\Controllers\ProductController; @endphp
@if($store == "digikala")
    @if (!empty($data['data']['product']['images']['list']))
        <div id="product-gallery-modal" tabindex="-1" aria-hidden="true"
             class="modal-anim main-scroll fixed inset-0 z-50 hidden h-screen w-screen overflow-hidden bg-black p-0" data-modal-root>
            <div class="absolute inset-0 bg-white dark:bg-slate" data-modal-overlay="product-gallery-modal"></div>
            <div class="modal-panel relative z-10 h-screen w-screen opacity-0 transition duration-200 ease-out" data-modal-content data-modal-no-scale>
                <div class="h-full overflow-hidden rounded-none border-0 bg-white dark:bg-slate">
                    <div class="border-b border-slate-100 px-4 py-4 dark:border-white/10 sm:px-6">
                        <div class="flex items-center justify-between gap-4">
                            <span class="line-clamp-1 text-base font-bold text-slate-800 dark:text-white lg:text-lg">{{$data['data']['product']['title_fa']}}</span>
                            <button class="rounded-full p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10" data-modal-hide="product-gallery-modal" type="button">
                                <svg class="h-6 w-6"><use xlink:href="#close"/></svg>
                                <span class="sr-only">بستن گالری</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid h-[calc(100%-68px)] grid-cols-1 gap-4 p-2 lg:grid-cols-[1fr_260px] lg:p-6">
                        <div class="relative flex h-full items-center justify-center overflow-hidden rounded-squircle bg-slate-50/50 p-4 dark:bg-white/5">
                            <button type="button" data-gallery-prev class="absolute right-6 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white p-3 text-slate-700 shadow-lg transition hover:scale-110 active:scale-95 dark:bg-slate-800 dark:text-white">
                                <svg class="h-6 w-6"><use xlink:href="#chevron-right"/></svg>
                            </button>
                            <button type="button" data-gallery-next class="absolute left-6 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white p-3 text-slate-700 shadow-lg transition hover:scale-110 active:scale-95 dark:bg-slate-800 dark:text-white">
                                <svg class="h-6 w-6"><use xlink:href="#chevron-left"/></svg>
                            </button>

                            <img data-gallery-main
                                 src="{{ProductController::ImgProfile($data['data']['product']['images']['list'][0]['url'][0],900, 900, 80, false)}}"
                                 alt="{{$data['data']['product']['title_fa']}}"
                                 class="h-full w-full max-w-[1000px] object-contain transition-all duration-500"
                                 loading="eager" decoding="async">
                        </div>

                        <div class="hidden h-full overflow-auto rounded-squircle bg-slate-50/30 p-4 dark:bg-white/5 lg:block">
                            <div class="grid grid-cols-1 gap-3" data-gallery-thumbs>
                                @foreach ($data['data']['product']['images']['list'] as $index => $item)
                                    <button type="button"
                                            data-gallery-thumb
                                            data-index="{{$index}}"
                                            data-src="{{ProductController::ImgProfile($item['url'][0],900, 900, 80, false)}}"
                                            class="group relative overflow-hidden rounded-squircle border-2 transition-all duration-300 {{ $loop->first ? 'border-primary shadow-md' : 'border-transparent hover:border-slate-200' }}">
                                        <img src="{{ProductController::ImgProfile($item['url'][0],150, 150, 80, false)}}"
                                             alt="{{$data['data']['product']['title_fa']}}"
                                             class="mx-auto h-24 w-24 object-contain p-2 group-hover:scale-110 transition-transform" loading="lazy" decoding="async">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif

@if($store == "basalam")
    @if (!empty($data['photos']))
        <div id="product-gallery-modal" tabindex="-1" aria-hidden="true"
             class="modal-anim main-scroll fixed inset-0 z-50 hidden h-screen w-screen overflow-hidden bg-black p-0" data-modal-root>
            <div class="absolute inset-0 bg-white dark:bg-slate" data-modal-overlay="product-gallery-modal"></div>
            <div class="modal-panel relative z-10 h-screen w-screen opacity-0 transition duration-200 ease-out" data-modal-content data-modal-no-scale>
                <div class="h-full overflow-hidden rounded-none border-0 bg-white dark:bg-slate">
                    <div class="border-b border-slate-100 px-4 py-4 dark:border-white/10 sm:px-6">
                        <div class="flex items-center justify-between gap-4">
                            <span class="line-clamp-1 text-base font-bold text-slate-800 dark:text-white lg:text-lg">{{$data['title']}}</span>
                            <button class="rounded-full p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10" data-modal-hide="product-gallery-modal" type="button">
                                <svg class="h-6 w-6"><use xlink:href="#close"/></svg>
                                <span class="sr-only">بستن گالری</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid h-[calc(100%-68px)] grid-cols-1 gap-4 p-2 lg:grid-cols-[1fr_260px] lg:p-6">
                        <div class="relative flex h-full items-center justify-center overflow-hidden rounded-squircle bg-slate-50/50 p-4 dark:bg-white/5">
                            <button type="button" data-gallery-prev class="absolute right-6 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white p-3 text-slate-700 shadow-lg transition hover:scale-110 active:scale-95 dark:bg-slate-800 dark:text-white">
                                <svg class="h-6 w-6"><use xlink:href="#chevron-right"/></svg>
                            </button>
                            <button type="button" data-gallery-next class="absolute left-6 top-1/2 z-10 -translate-y-1/2 rounded-full bg-white p-3 text-slate-700 shadow-lg transition hover:scale-110 active:scale-95 dark:bg-slate-800 dark:text-white">
                                <svg class="h-6 w-6"><use xlink:href="#chevron-left"/></svg>
                            </button>

                            <img data-gallery-main
                                 src="{{ProductController::ImgProfile($data['photos'][0]['large'],null,null,null,null,null,'basalam')}}"
                                 alt="{{$data['title']}}"
                                 class="h-full w-full max-w-[1000px] object-contain transition-all duration-500"
                                 loading="eager" decoding="async">
                        </div>

                        <div class="hidden h-full overflow-auto rounded-squircle bg-slate-50/30 p-4 dark:bg-white/5 lg:block">
                            <div class="grid grid-cols-1 gap-3" data-gallery-thumbs>
                                @foreach ($data['photos'] as $index => $item)
                                    <button type="button"
                                            data-gallery-thumb
                                            data-index="{{$index}}"
                                            data-src="{{ProductController::ImgProfile($item['large'],null,null,null,null,null,'basalam')}}"
                                            class="group relative overflow-hidden rounded-squircle border-2 transition-all duration-300 {{ $loop->first ? 'border-primary shadow-md' : 'border-transparent hover:border-slate-200' }}">
                                        <img src="{{ProductController::ImgProfile($item['large'],null,null,null,null,null,'basalam')}}"
                                             alt="{{$data['title']}}"
                                             class="mx-auto h-24 w-24 object-contain p-2 group-hover:scale-110 transition-transform" loading="lazy" decoding="async">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
