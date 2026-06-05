<div aria-labelledby="shop-sort-drawer-navigation-label"
     class="fixed bottom-0 left-0 right-0 z-40 h-auto w-full translate-y-full rounded-squircle bg-white transition-transform duration-300 dark:bg-slate"
     id="shop-sort-drawer-navigation" tabindex="-1">
    <div class="flex items-center justify-between gap-x-4 border-b border-slate px-5 py-4 dark:border-white/5">
        <div class="flex items-center gap-3 border-r-4 border-primary pr-3">
            <h5 class="text-base font-black text-slate-800 dark:text-white">مرتب‌سازی</h5>
        </div>
        <button aria-controls="user-account-drawer-navigation"
                class="inline-flex items-center rounded-squircle bg-transparent p-1.5 text-sm text-slate hover:bg-slate hover:text-slate dark:text-slate-200 dark:hover:bg-black dark:hover:text-white"
                data-drawer-hide="shop-sort-drawer-navigation" type="button">
            <svg class="h-5 w-5">
                <use xlink:href="#close"/>
            </svg>
            <span class="sr-only">Close menu</span>
        </button>
    </div>
    <div class="space-y-1 p-5">
        @if(isset($Data['data']['sort_options']) && is_array($Data['data']['sort_options']))
            @foreach($Data['data']['sort_options'] as $by)
                @php($isActive = (int)request()->query('sort', 1) === (int)$by['id'])
                <button type="button"
                        data-sort-url="{{request()->fullUrlWithQuery(['sort'=>$by['id'], 'page' => 1])}}"
                        class="flex w-full items-center gap-3 rounded-squircle px-4 py-3.5 text-xs font-bold transition-all {{ $isActive ? 'bg-primary/10 text-primary shadow-none' : 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-white/5' }}">
                    @if($isActive)
                        <svg class="h-4 w-4 shrink-0 text-primary"><use xlink:href="#check"/></svg>
                    @else
                        <span class="h-4 w-4 shrink-0 rounded-full border-2 border-slate-300 dark:border-slate-600"></span>
                    @endif
                    <span>{{$by['title_fa']}}</span>
                </button>
            @endforeach
        @endif
    </div>
</div>
