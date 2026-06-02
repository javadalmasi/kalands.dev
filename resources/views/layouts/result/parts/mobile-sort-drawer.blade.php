<div aria-labelledby="shop-sort-drawer-navigation-label"
     class="fixed bottom-0 left-0 right-0 z-40 h-auto w-full translate-y-full rounded-t-3xl bg-white transition-transform duration-300 dark:bg-slate"
     id="shop-sort-drawer-navigation" tabindex="-1">
    <div class="flex items-center justify-between gap-x-4 border-b border-slate p-4 pb-5 dark:border-white/5">
        <h5 class="text-lg font-bold text-slate-800 dark:text-white"> مرتب سازی بر اساس </h5>
        <button aria-controls="user-account-drawer-navigation"
                class="inline-flex items-center rounded-squircle bg-transparent p-1.5 text-sm text-slate hover:bg-slate hover:text-slate dark:text-slate-200 dark:hover:bg-black dark:hover:text-white"
                data-drawer-hide="shop-sort-drawer-navigation" type="button">
            <svg class="h-5 w-5">
                <use xlink:href="#close"/>
            </svg>
            <span class="sr-only">Close menu</span>
        </button>
    </div>
    <div class="main-scroll h-full space-y-2 divide-y divide-slate overflow-y-auto p-4 dark:divide-white/5">
        <fieldset class="flex flex-col space-y-2">
            <legend class="sr-only">Sort</legend>
            @if(isset($Data['data']['sort_options']) && is_array($Data['data']['sort_options']))
                @foreach($Data['data']['sort_options'] as $by)
                    <button type="button"
                            data-sort-url="{{request()->fullUrlWithQuery(['sort'=>$by['id'], 'page' => 1])}}"
                            class="relative block w-full cursor-pointer rounded-squircle border border-slate p-4 shadow-base dark:border-white/5 @if((int)request()->query('sort', 1) === (int)$by['id']) border-primary dark:border-primary @endif">
                        <p class="text-center font-semibold text-slate-700 dark:text-slate-100">{{$by['title_fa']}}</p>
                    </button>
                @endforeach
            @endif
        </fieldset>
    </div>
</div>
