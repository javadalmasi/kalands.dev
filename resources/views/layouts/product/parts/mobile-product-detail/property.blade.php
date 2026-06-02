@if($store == "digikala")
    @if (isset($data['data']['product']['review']['attributes']))
        <div class="mb-6 rounded-squircle border border-slate bg-white p-4 shadow-base dark:border-white/10 dark:bg-slate lg:hidden">
            <p class="mb-3 text-sm font-bold text-slate dark:text-white">ویژگی های محصول</p>
            <ul class="space-y-3">
                @foreach (array_slice($data['data']['product']['review']['attributes'], 0, 4) as $attributes)
                    <li class="flex gap-x-2">
                        <div class="min-w-fit text-xs text-slate dark:text-slate-200"> {{$attributes['title']}}:
                        </div>
                        <div class="text-sm text-slate dark:text-slate-200"> {{$attributes['values'][0]}}</div>
                    </li>
                @endforeach
            </ul>
            @if(count($data['data']['product']['review']['attributes']) > 4)
                <div class="relative mt-3" data-mobile-attrs>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-white via-white/90 to-transparent dark:from-slate dark:via-slate/90" data-mobile-attrs-fade></div>
                    <ul class="hidden space-y-3" data-mobile-attrs-extra>
                        @foreach (array_slice($data['data']['product']['review']['attributes'], 4) as $attributes)
                            <li class="flex gap-x-2">
                                <div class="min-w-fit text-xs text-slate dark:text-slate-200"> {{$attributes['title']}}:</div>
                                <div class="text-sm text-slate dark:text-slate-200"> {{$attributes['values'][0]}}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-3 flex justify-center">
                    <button type="button" class="text-sm font-medium text-primary transition-opacity dark:text-primary" data-mobile-attrs-more>نمایش بیشتر</button>
                </div>
                <div class="mt-3 hidden justify-center" data-mobile-attrs-less-wrap>
                    <button type="button" class="text-sm font-medium text-slate dark:text-slate-200" data-mobile-attrs-less>نمایش کمتر</button>
                </div>
            @endif
        </div>
    @endif
@endif

@if($store == "basalam")
    @if (isset($data['attribute_groups'][0]['attributes']))
        <div class="mb-6 rounded-squircle border border-slate bg-white p-4 shadow-base dark:border-white/10 dark:bg-slate lg:hidden">
            <p class="mb-3 text-sm font-bold text-slate dark:text-white">ویژگی های محصول</p>
            <ul class="space-y-3">
                @foreach (array_slice($data['attribute_groups'][0]['attributes'], 0, 4) as $attributes)
                    <li class="flex gap-x-2">
                        <div class="min-w-fit text-xs text-slate dark:text-slate-200"> {{$attributes['title']}}:
                        </div>
                        <div class="text-sm text-slate dark:text-slate-200">{{$attributes['value']}}</div>
                    </li>
                @endforeach
            </ul>
            @if(count($data['attribute_groups'][0]['attributes']) > 4)
                <div class="relative mt-3" data-mobile-attrs>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-white via-white/90 to-transparent dark:from-slate dark:via-slate/90" data-mobile-attrs-fade></div>
                    <ul class="hidden space-y-3" data-mobile-attrs-extra>
                        @foreach (array_slice($data['attribute_groups'][0]['attributes'], 4) as $attributes)
                            <li class="flex gap-x-2">
                                <div class="min-w-fit text-xs text-slate dark:text-slate-200"> {{$attributes['title']}}:</div>
                                <div class="text-sm text-slate dark:text-slate-200">{{$attributes['value']}}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="mt-3 flex justify-center">
                    <button type="button" class="text-sm font-medium text-primary transition-opacity dark:text-primary" data-mobile-attrs-more>نمایش بیشتر</button>
                </div>
                <div class="mt-3 hidden justify-center" data-mobile-attrs-less-wrap>
                    <button type="button" class="text-sm font-medium text-slate dark:text-slate-200" data-mobile-attrs-less>نمایش کمتر</button>
                </div>
            @endif
        </div>
    @endif
@endif
