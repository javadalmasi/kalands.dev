<div id="resultDesktopFilter" class="col-span-4 row-span-2 hidden md:block lg:col-span-3">
    <div class="sticky top-32 mb-4 overflow-hidden rounded-squircle bg-white shadow-base dark:bg-slate">
        <div dir="ltr"
             class="flex max-h-[calc(95vh_-_100px)] flex-col overflow-y-auto overflow-x-hidden px-4 py-3">
            <div dir="rtl">
                <!-- Title -->
                <div class="mb-6 flex items-center justify-between border-r-4 border-primary pr-3">
                    <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-wider">فیلترها</h3>
                    @if(count(request()->all()) > 0)
                        <a href="{{ request()->url() }}" class="text-[10px] font-bold text-primary hover:underline">حذف همه</a>
                    @endif
                </div>
                
                <ul class="space-y-4">
                    @if(!isset($filter_search_enable))
                        <!-- Search -->
                        <li>
                            <form action="{{request()->fullUrl()}}" method="get" data-result-search-form>
                                <div class="group flex items-center gap-2 rounded-squircle bg-slate-50 px-3 py-2 dark:bg-white/5 focus-within:ring-2 focus-within:ring-primary/20 transition-all">
                                    <svg class="h-4 w-4 text-slate-400 group-focus-within:text-primary"><use xlink:href="#search"/></svg>
                                    <input class="w-full bg-transparent border-none p-0 text-xs font-medium text-slate-700 outline-none placeholder:text-slate-400 focus:ring-0 dark:bg-transparent dark:text-white dark:border-white/10" placeholder="جستجو در نتایج..." type="text" name="q" value="{{(request()->has('q') ? request()->get('q') : '')}}" />
                                </div>
                            </form>
                        </li>
                    @endif

                    @if(isset($Data['data']['filters']['categories']) && isset($next_categories_id))
                        <!-- Categories -->
                        <li class="border-b border-slate-50 dark:border-white/5 pb-4">
                            <details class="group" open>
                                <summary class="flex cursor-pointer items-center justify-between py-2 text-sm font-bold text-slate-700 dark:text-white list-none">
                                    <span>دسته‌بندی‌ها</span>
                                    <svg class="h-4 w-4 transition-transform group-open:rotate-180 text-slate-400"><use xlink:href="#chevron-down"/></svg>
                                </summary>
                                <div class="mt-3 space-y-1 max-h-60 overflow-y-auto custom-scrollbar">
                                    @foreach($Data['data']['filters']['categories']['options'] as $category)
                                        @php($isActive = request()->has('categories') && in_array($category['id'], request()->get('categories')))
                                        <button type="button"
                                                data-filter-url="{{request()->fullUrlWithQuery(['categories['.$next_categories_id.']' => $category['id']])}}"
                                                class="flex w-full items-center justify-between rounded-squircle px-3 py-2 text-xs transition-all {{ $isActive ? 'bg-slate-50 dark:bg-slate-800 text-primary font-black' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-white/5' }}">
                                            <span>{{$category['title_fa']}}</span>
                                            @if($isActive)
                                                <svg class="h-4 w-4"><use xlink:href="#check"/></svg>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </details>
                        </li>
                    @endif

                    @if(isset($Data['data']['filters']['brands']) && isset($next_brands_id))
                        <!-- brands -->
                        <li class="border-b border-slate-50 dark:border-white/5 pb-4">
                            <details class="group">
                                <summary class="flex cursor-pointer items-center justify-between py-2 text-sm font-bold text-slate-700 dark:text-white list-none">
                                    <span>برندها</span>
                                    <svg class="h-4 w-4 transition-transform group-open:rotate-180 text-slate-400"><use xlink:href="#chevron-down"/></svg>
                                </summary>
                                <div class="mt-3 space-y-3">
                                    <div class="flex items-center gap-2 rounded-squircle bg-slate-50 px-3 py-2 dark:bg-white/5">
                                        <svg class="h-4 w-4 text-slate-400"><use xlink:href="#search"/></svg>
                                        <input id="brandListFilterDesktopSearchInput" class="w-full bg-transparent border-none p-0 text-[11px] font-medium outline-none focus:ring-0 dark:bg-transparent dark:text-white dark:border-white/10" placeholder="جستجوی برند..." type="text" />
                                    </div>
                                    <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-1" id="brandListFilterDesktop">
                                        @foreach($Data['data']['filters']['brands']['options'] as $brand)
                                            @php($isActive = request()->has('brands') && in_array($brand['id'], request()->get('brands')))
                                            <button type="button"
                                                    data-filter-url="{{request()->fullUrlWithQuery(['brands['.$next_brands_id.']' => $brand['id']])}}"
                                                    class="flex w-full items-center justify-between rounded-squircle px-3 py-2 text-xs transition-all {{ $isActive ? 'bg-slate-50 dark:bg-slate-800 text-primary font-black' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-white/5' }}">
                                                <div class="flex flex-col items-start gap-0.5">
                                                    <span class="font-bold">{{$brand['title_fa']}}</span>
                                                    <span class="text-[10px] opacity-60 uppercase">{{$brand['title_en']}}</span>
                                                </div>
                                                @if($isActive)
                                                    <svg class="h-4 w-4"><use xlink:href="#check"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </details>
                        </li>
                    @endif

                    @if(isset($Data['data']['filters']['color_palettes']) && isset($next_colors_id))
                        <!-- colors -->
                        <li class="pb-2">
                            <details class="group">
                                <summary class="flex cursor-pointer items-center justify-between py-2 text-sm font-bold text-slate-700 dark:text-white list-none">
                                    <span>رنگ‌ها</span>
                                    <svg class="h-4 w-4 transition-transform group-open:rotate-180 text-slate-400"><use xlink:href="#chevron-down"/></svg>
                                </summary>
                                <div class="mt-3 space-y-3">
                                    <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-1" id="colorListFilterDesktop">
                                        @foreach($Data['data']['filters']['color_palettes']['options'] as $color)
                                            @php($isActive = request()->has('colors') && in_array($color['id'], request()->get('colors')))
                                            <button type="button"
                                                    data-filter-url="{{request()->fullUrlWithQuery(['colors['.$next_colors_id.']' => $color['id']])}}"
                                                    class="flex w-full items-center justify-between rounded-squircle px-3 py-2 text-xs transition-all {{ $isActive ? 'bg-slate-50 dark:bg-slate-800 text-primary font-black' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-white/5' }}">
                                                <div class="flex items-center gap-3">
                                                    <span class="h-4 w-4 rounded-full border border-black/10 shadow-sm" style="background: {{$color['hex_code']}}"></span>
                                                    <span class="font-medium">{{$color['title']}}</span>
                                                </div>
                                                @if($isActive)
                                                    <svg class="h-4 w-4"><use xlink:href="#check"/></svg>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </details>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>