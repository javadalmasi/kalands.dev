<!-- Mobile Filter Drawer -->
<div aria-labelledby="shop-filter-drawer-navigation-label"
     class="fixed bottom-0 left-0 right-0 z-40 h-full w-full translate-y-full bg-white transition-transform duration-300 dark:bg-slate"
     id="shop-filter-drawer-navigation" tabindex="-1">
    <div class="flex items-center justify-between gap-x-4 border-b border-slate p-4 pb-5 dark:border-white/5">
        <h5 class="text-lg font-bold text-slate-800 dark:text-white"> فیلتر محصولات </h5>
        <button aria-controls="user-account-drawer-navigation"
                class="inline-flex items-center rounded-squircle bg-transparent p-1.5 text-sm text-slate hover:bg-slate hover:text-slate dark:text-slate-200 dark:hover:bg-black dark:hover:text-white"
                data-drawer-hide="shop-filter-drawer-navigation" type="button">
            <svg class="h-5 w-5">
                <use xlink:href="#close"/>
            </svg>
            <span class="sr-only">Close menu</span>
        </button>
    </div>
    <div class="h-full pb-[150px]">
        <ul class="h-full space-y-6 overflow-y-auto p-4">
            @if(!isset($filter_search_enable))
                <!-- Search -->
                <li>
                    <form action="{{request()->fullUrl()}}" method="get" data-result-search-form>
                        <label class="sr-only">Shop search</label>
                        <input class="w-full rounded-squircle border border-slate-200 bg-white px-3 py-3 text-slate-700 outline-none placeholder:text-sm placeholder:text-slate-400 focus:ring-0 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="جستجو در بین نتایج ..." type="text" name="q" value="{{(request()->has('q') ? request()->get('q') : '')}}" />
                    </form>
                </li>
            @endif
            @if(isset($Data['data']['filters']['categories']) && isset($next_categories_id))
                <!-- Categories -->
                <li>
                    <details class="[&amp;_summary::-webkit-details-marker]:hidden group">
                        <summary
                                class="flex cursor-pointer items-center justify-between rounded-squircle py-3 text-slate dark:text-white">
                            <span> دسته بندی ها </span>
                            <span class="shrink-0 transition duration-200 group-open:-rotate-90">
							<svg class="h-5 w-5">
								<use xlink:href="#chevron-left"/>
							</svg>
						</span>
                        </summary>
                        <div class="mt-2 max-h-60 overflow-y-auto pl-1">
                            <ul class="space-y-2 rounded-squircle">
                                @foreach($Data['data']['filters']['categories']['options'] as $category)
                                    <li>
                                        <button type="button"
                                                data-filter-url="{{request()->fullUrlWithQuery(['categories['.$next_categories_id.']' => $category['id']])}}"
                                                class="flex w-full cursor-pointer items-center gap-x-2 rounded-squircle px-4 py-3 text-right text-slate dark:text-white @if(request()->has('categories') && in_array($category['id'],request()->get('categories'))) font-bold @endif">
                                            <span>{{$category['title_fa']}}</span>
                                            <svg class="h-5 w-5">
                                                <use xlink:href="#chevron-left"/>
                                            </svg>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </details>
                </li>
            @endif
            @if(isset($Data['data']['filters']['brands']) && isset($next_brands_id))
                <!-- brands -->
                <li>
                    <details class="[&amp;_summary::-webkit-details-marker]:hidden group">
                        <summary
                                class="flex cursor-pointer items-center justify-between rounded-squircle py-3 text-slate dark:text-white">
                            <span> برند ها </span>
                            <span class="shrink-0 transition duration-200 group-open:-rotate-90">
							<svg class="h-5 w-5">
								<use xlink:href="#chevron-left"/>
							</svg>
						</span>
                        </summary>
                        <div class="mt-2 max-h-60 overflow-y-auto pl-1">
                            <ul class="space-y-2 rounded-squircle" id="brandListFilterMobile">
                                <li class="p-2">
                                    <label class="sr-only">Brand search</label>
                                    <input id="brandListFilterMobileSearchInput" class="w-full rounded-squircle border border-slate-200 bg-white px-3 py-3 text-slate-700 outline-none placeholder:text-sm placeholder:text-slate-400 focus:ring-0 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="جستجوی برند ..." type="text" />
                                </li>
                                @foreach($Data['data']['filters']['brands']['options'] as $brand)
                                    <li>
                                        <div class="flex w-full items-center gap-x-2 pr-4">
                                            <button type="button"
                                                    data-filter-url="{{request()->fullUrlWithQuery(['brands['.$next_brands_id.']' => $brand['id']])}}"
                                                    class="flex w-full cursor-pointer items-center justify-between py-2 pl-4 text-right text-slate dark:text-slate-200 @if(request()->has('brands') && in_array($brand['id'],request()->get('brands'))) font-bold @else font-medium @endif">
                                                <span>{{$brand['title_fa']}}</span>
                                                <span>{{$brand['title_en']}}</span>
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </details>
                </li>
            @endif
            @if(isset($Data['data']['filters']['color_palettes']) && isset($next_colors_id))
                <!-- Colors -->
                <li>
                    <details class="[&amp;_summary::-webkit-details-marker]:hidden group">
                        <summary
                                class="flex cursor-pointer items-center justify-between rounded-squircle py-3 text-slate dark:text-white">
                            <span> رنگ ها </span>
                            <span class="shrink-0 transition duration-200 group-open:-rotate-90">
							<svg class="h-5 w-5">
								<use xlink:href="#chevron-left"/>
							</svg>
						</span>
                        </summary>
                        <div class="mt-2 max-h-60 overflow-y-auto pl-1">
                            <ul class="space-y-2 rounded-squircle" id="colorListFilterMobile">
                                <li class="p-2">
                                    <label class="sr-only">Color search</label>
                                    <input id="colorListFilterMobileSearchInput" class="w-full rounded-squircle border border-slate-200 bg-white px-3 py-3 text-slate-700 outline-none placeholder:text-sm placeholder:text-slate-400 focus:ring-0 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="جستجوی رنگ ..." type="text" />
                                </li>
                                @foreach($Data['data']['filters']['color_palettes']['options'] as $color)
                                    <li>
                                        <div class="flex w-full items-center gap-x-2 pr-4">
                                            <button type="button"
                                                    data-filter-url="{{request()->fullUrlWithQuery(['colors['.$next_colors_id.']' => $color['id']])}}"
                                                    class="flex w-full cursor-pointer items-center justify-between py-2 pl-4 text-right text-slate dark:text-slate-200 @if(request()->has('colors') && in_array($color['id'],request()->get('colors'))) font-bold @else font-medium @endif">
                                                <span>{{$color['title']}}</span>
                                                <span class="h-4 w-4 rounded-full ring-2 ring-slate dark:ring-slate"
                                                      style="background: {{$color['hex_code']}}"></span>
                                            </button>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </details>
                </li>
            @endif
        </ul>
    </div>
</div>