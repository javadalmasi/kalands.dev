<div class="mb-6 border-b border-slate-100 pb-2 dark:border-white/10">
    <ul class="flex items-center gap-2 overflow-x-auto text-center text-sm font-medium"
        id="productDCSTab"
        data-tabs-toggle="#productDCSTab"
        data-tabs-active-classes="border-primary bg-slate-50 dark:bg-slate-800 text-primary dark:border-primary dark:bg-primary/20 dark:text-primary"
        data-tabs-inactive-classes="border-transparent bg-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
        role="tablist">
        @if($store == "digikala")
            @if (isset($data['data']['product']['variants']) && !empty($data['data']['product']['variants'][1]))
                <li role="presentation">
                    <button
                            class="inline-flex whitespace-nowrap rounded-squircle border-b-2 px-4 py-3 transition-all duration-300"
                            id="seller-tab" data-tabs-target="#sellers" type="button" role="tab"
                            aria-controls="sellers" aria-selected="true">
                        فروشندگان
                    </button>
                </li>
            @endif
            @if (isset($data['data']['product']['specifications']) && !empty($data['data']['product']['specifications'][0]) && !empty($data['data']['product']['specifications'][0]['attributes']))
                <li role="presentation">
                    <button
                            class="inline-flex whitespace-nowrap rounded-squircle border-b-2 px-4 py-3 transition-all duration-300"
                            id="specs-tab" data-tabs-target="#specs" type="button" role="tab"
                            aria-controls="specs" aria-selected="false">
                        مشخصات
                    </button>
                </li>
            @endif
        @endif
        @if($store == "basalam")
            <li role="presentation">
                <button
                        class="inline-flex whitespace-nowrap rounded-squircle border-b-2 px-4 py-3 transition-all duration-300"
                        id="seller-tab" data-tabs-target="#sellers" type="button" role="tab"
                        aria-controls="sellers" aria-selected="true">
                    فروشندگان
                </button>
            </li>
            @if(isset($data['category_list']) && !empty($data['category_list']))
                <li role="presentation">
                    <button
                            class="inline-flex whitespace-nowrap rounded-squircle border-b-2 px-4 py-3 transition-all duration-300"
                            id="tags-tab" data-tabs-target="#tags" type="button" role="tab"
                            aria-controls="tags" aria-selected="false">
                        برچسب ها
                    </button>
                </li>
            @endif
        @endif
        <li role="presentation">
            <button
                    class="inline-flex whitespace-nowrap rounded-squircle border-b-2 px-4 py-3 transition-all duration-300"
                    id="comments-tab" data-tabs-target="#comments" type="button" role="tab"
                    aria-controls="comments" aria-selected="false">
                دیدگاه ها
            </button>
        </li>
    </ul>
</div>
