@php use App\Http\Controllers\ProductController; @endphp
<div id="productDCSTab">
    @if($store == "digikala")
        @if (isset($data['data']['product']['variants'][1]))
            <div id="sellers" role="tabpanel" aria-labelledby="sellers-tab">
                <div class="tab-panel-clamp relative overflow-hidden rounded-squircle border border-slate-100 bg-white p-3 dark:border-white/10 dark:bg-slate" data-clamp>
                    <div class="mb-3 flex items-center justify-end">
                        <label for="seller-sort-dk" class="sr-only">مرتب‌سازی فروشندگان</label>
                        <select id="seller-sort-dk" data-seller-sort class="rounded-squircle border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 transition-colors hover:border-primary focus:border-primary focus:outline-none dark:hover:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <option value="price-asc">قیمت: ارزان‌تر</option>
                            <option value="price-desc">قیمت: گران‌تر</option>
                            <option value="name-asc">نام فروشگاه: الفبا</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-2" data-sellers-list>
                        @foreach ($data['data']['product']['variants'] as $variant)
                            <div class="group cursor-pointer rounded-squircle border border-slate-100 bg-slate-50/50 p-3 transition-all duration-300 hover:border-primary/30 hover:bg-white hover:shadow-lg hover:shadow-slate-200/50 dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:shadow-none js-special-link"
                                 data-seller-item data-seller-name="{{ mb_strtolower($variant['seller']['title']) }}"
                                 data-seller-price="{{ $variant['price']['selling_price'] ?? 0 }}"
                                 data-seller-link="{{ ProductController::AffiliateLinkGenerator('digikala', $data['data']['product']['id'], $data['data']['product']['title_fa']) }}"
                                 data-human-href="{{ ProductController::GetSpecialLink('digikala', $data['data']['product']['id'], $data['data']['product']['title_fa']) }}"
                                 role="link" tabindex="0">
                                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                    <div class="min-w-0 md:w-[32%]">
                                        <div class="flex min-w-0 items-center gap-2 text-sm font-semibold text-slate-700 dark:text-white">
                                            <span class="line-clamp-1">{{ $variant['seller']['title'] }}</span>
                                            <span class="rounded-full bg-slate-50 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-primary">دیجی‌کالا</span>
                                        </div>
                                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                            از {{ $variant['seller']['registration_date'] }} پیش
                                        </p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400 md:w-[43%]">
                                    @if (isset($variant['color']['title']))
                                        <div class="flex items-center gap-2 rounded-full border border-slate-200 px-2 py-1 dark:border-white/10">
                                            <span class="h-3 w-3 rounded-full shadow-sm" style="background-color:{{ $variant['color']['hex_code'] }}"></span>
                                            <span>{{ $variant['color']['title'] }}</span>
                                        </div>
                                    @endif
                                    @if (isset($variant['warranty']['title_fa']))
                                        <p class="rounded-full border border-slate-200 px-2 py-1 dark:border-white/10">{{ $variant['warranty']['title_fa'] }}</p>
                                    @endif
                                    @if (isset($variant['size']['title']))
                                        <p class="rounded-full border border-slate-200 px-2 py-1 dark:border-white/10">اندازه: {{ $variant['size']['title'] }}</p>
                                    @endif
                                    </div>
                                    <div class="md:w-[25%] md:text-left">
                                        @if (isset($variant['price']['rrp_price']) && $variant['price']['discount_percent'] != 0)
                                            <div class="mb-1 flex items-center gap-2 text-xs md:justify-end">
                                                <del class="text-slate-400 dark:text-slate-500">{{ number_format($variant['price']['rrp_price'] / 10) }}</del>
                                                <span class="rounded-full bg-warning px-2 py-0.5 font-bold text-white">{{ $variant['price']['discount_percent'] }}%</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-1 text-base font-bold text-primary dark:text-primary md:justify-end">
                                            <span>{{ number_format($variant['price']['selling_price'] / 10) }}</span>
                                            <svg class="h-4 w-4"><use xlink:href="#toman"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 hidden bg-gradient-to-t from-white via-white/90 to-transparent p-4 dark:from-slate dark:via-slate/90" data-clamp-fade>
                        <button type="button" class="pointer-events-auto mx-auto block rounded-squircle border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-white/10 dark:bg-slate-800 dark:text-slate-200" data-clamp-toggle>نمایش ادامه</button>
                    </div>
                </div>
            </div>
        @endif

        @if (isset($data['data']['product']['specifications']) && !empty($data['data']['product']['specifications'][0]) && !empty($data['data']['product']['specifications'][0]['attributes']))
            <div class="hidden" id="specs" role="tabpanel" aria-labelledby="specs-tab">
                <div class="tab-panel-clamp relative overflow-hidden rounded-squircle border border-slate-100 bg-white p-3 dark:border-white/10 dark:bg-slate" data-clamp>
                    <div class="space-y-6">
                        @foreach ($data['data']['product']['specifications'] as $spec)
                            <div class="rounded-squircle bg-slate-50/50 p-4 dark:bg-white/5">
                                <h3 class="mb-4 text-sm font-bold text-slate-800 dark:text-white border-r-4 border-primary pr-3">{{ $spec['title'] }}</h3>
                                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                                    @foreach ($spec['attributes'] as $attribute)
                                        <div class="flex flex-col rounded-squircle bg-white px-4 py-3 shadow-sm dark:bg-slate-800">
                                            <p class="mb-1 text-[11px] font-medium text-slate-400 dark:text-slate-500">{{ $attribute['title'] }}</p>
                                            <p class="text-sm text-slate-700 dark:text-slate-200">{{ implode(' | ', $attribute['values']) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 hidden bg-gradient-to-t from-white via-white/90 to-transparent p-4 dark:from-slate dark:via-slate/90" data-clamp-fade>
                        <button type="button" class="pointer-events-auto mx-auto block rounded-squircle border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-white/10 dark:bg-slate-800 dark:text-slate-200" data-clamp-toggle>نمایش ادامه</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    @if($store == "basalam")
        <div id="sellers" role="tabpanel" aria-labelledby="sellers-tab">
            <div class="tab-panel-clamp relative overflow-hidden rounded-squircle border border-slate-100 bg-white p-3 dark:border-white/10 dark:bg-slate" data-clamp>
                <div class="mb-3 flex items-center justify-end">
                    <label for="seller-sort-bs" class="sr-only">مرتب‌سازی فروشندگان</label>
                    <select id="seller-sort-bs" data-seller-sort class="rounded-squircle border border-slate-200 bg-white px-3 py-2 text-xs text-slate-600 transition-colors hover:border-primary focus:border-primary focus:outline-none dark:hover:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                        <option value="price-asc">قیمت: ارزان‌تر</option>
                        <option value="price-desc">قیمت: گران‌تر</option>
                        <option value="name-asc">نام فروشگاه: الفبا</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-2" data-sellers-list>
                    <div class="group cursor-pointer rounded-squircle border border-slate-100 bg-slate-50/50 p-3 transition-all duration-300 hover:border-primary/30 hover:bg-white hover:shadow-lg hover:shadow-slate-200/50 dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:shadow-none js-special-link"
                         data-seller-item data-seller-name="{{ mb_strtolower(ProductController::RemoveEmoji($data['vendor']['owner']['vendor']['title'])) }}"
                         data-seller-price="{{ $data['price'] ?? 0 }}"
                         data-seller-link="{{ ProductController::AffiliateLinkGenerator('basalam', $data['id'], $data['title']) }}"
                         data-human-href="{{ ProductController::GetSpecialLink('basalam', $data['id'], $data['title']) }}"
                         role="link" tabindex="0">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div class="min-w-0 md:w-[32%]">
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-white">
                                    <span class="line-clamp-1">{{ ProductController::RemoveEmoji($data['vendor']['owner']['vendor']['title']) }}</span>
                                    <span class="rounded-full bg-slate-50 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-primary">باسلام</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">فروشنده اصلی باسلام</p>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 md:w-[43%]">
                                ارسال مستقیم از فروشنده اصلی
                            </div>
                            @if (isset($data['price']))
                                <div class="md:w-[25%] md:text-left">
                                    <div class="flex items-center gap-1 text-base font-bold text-primary dark:text-primary md:justify-end">
                                        <span>{{ number_format($data['price'] / 10) }}</span>
                                        <svg class="h-4 w-4"><use xlink:href="#toman"/></svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="group cursor-pointer rounded-squircle border border-slate-100 bg-slate-50/50 p-3 transition-all duration-300 hover:border-primary/30 hover:bg-white hover:shadow-lg hover:shadow-slate-200/50 dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10 dark:hover:shadow-none js-special-link"
                         data-seller-item data-seller-name="دیجی‌کالا"
                         data-seller-price="{{ $data['price'] ?? 0 }}"
                         data-seller-link="{{ ProductController::AffiliateLinkGenerator('digikala', null, $data['title']) }}"
                         data-human-href="{{ ProductController::GetSpecialLink('digikala', null, $data['title']) }}"
                         role="link" tabindex="0">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div class="min-w-0 md:w-[32%]">
                                <div class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-white">
                                    <span>دیجی‌کالا</span>
                                    <span class="rounded-full bg-slate-50 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-primary">دیجی‌کالا</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">بازه تخمینی قیمت مشابه</p>
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 md:w-[43%]">
                                بر اساس قیمت روز محصول مشابه
                            </div>
                            @if (isset($data['price']))
                                <div class="md:w-[25%]">
                                    <div class="flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400 md:justify-end">
                                        از <span class="font-bold text-primary dark:text-primary">{{ number_format(($data['price']/10)-($data['price']/50)) }}</span>
                                        تا <span class="font-bold text-warning dark:text-warning">{{ number_format(($data['price']/10)+($data['price']/80)) }}</span>
                                        <svg class="h-4 w-4 text-primary dark:text-primary"><use xlink:href="#toman"/></svg>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 hidden bg-gradient-to-t from-white via-white/90 to-transparent p-4 dark:from-slate dark:via-slate/90" data-clamp-fade>
                    <button type="button" class="pointer-events-auto mx-auto block rounded-squircle border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-white/10 dark:bg-slate-800 dark:text-slate-200" data-clamp-toggle>نمایش ادامه</button>
                </div>
            </div>
        </div>

        @if(isset($data['category_list']) && !empty($data['category_list']))
            <div id="tags" class="hidden" role="tabpanel" aria-labelledby="tags-tab">
                <div class="tab-panel-clamp relative overflow-hidden rounded-squircle border border-slate-100 bg-white p-4 dark:border-white/10 dark:bg-slate" data-clamp>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($data['category_list'] as $tag)
                            <a href="{{ config('app.url').'/result/?q='.str_slug_persian($tag['title'],'+').'&sort=22' }}"
                               target="_blank"
                               class="inline-flex items-center rounded-squircle border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition-all duration-300 hover:border-primary hover:bg-slate-50 dark:bg-slate-800 hover:text-primary dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:border-primary dark:hover:text-primary">
                                #{{ $tag['title'] }}
                            </a>
                        @endforeach
                    </div>
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 hidden bg-gradient-to-t from-white via-white/90 to-transparent p-4 dark:from-slate dark:via-slate/90" data-clamp-fade>
                        <button type="button" class="pointer-events-auto mx-auto block rounded-squircle border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-white/10 dark:bg-slate-800 dark:text-slate-200" data-clamp-toggle>نمایش ادامه</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div id="comments" class="hidden" role="tabpanel" aria-labelledby="comments-tab">
        <div class="tab-panel-clamp relative overflow-hidden rounded-squircle border border-slate-100 bg-white p-3 dark:border-white/10 dark:bg-slate" data-clamp>
            <div class="mb-6 rounded-squircle bg-slate-50/50 p-6 dark:bg-white/5">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="flex flex-col items-center justify-center rounded-squircle bg-white p-4 shadow-sm dark:bg-slate-800">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">تعداد کل دیدگاه</p>
                        <p class="mt-2 text-3xl font-black text-primary">{{ $comments->count() }}</p>
                    </div>
                    <div class="flex flex-col items-center justify-center rounded-squircle bg-white p-4 shadow-sm dark:bg-slate-800">
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">میانگین رضایت کاربران</p>
                        <p class="mt-2 text-3xl font-black text-primary">{{ $comments->count() ? '۴.۲' : '۰.۰' }}</p>
                    </div>
                    <div class="flex flex-col items-center justify-center rounded-squircle bg-white p-4 shadow-sm dark:bg-slate-800">
                        <p class="mb-3 text-xs font-medium text-slate-500 dark:text-slate-400">نظر شما چیست؟</p>
                        <button type="button" data-review-open class="btn-primary w-full shadow-md">ثبت نظر جدید</button>
                    </div>
                </div>
            </div>
            <div class="px-2">
                <x-comments.index :comments="$comments"/>
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 hidden bg-gradient-to-t from-white via-white/90 to-transparent p-4 dark:from-slate dark:via-slate/90" data-clamp-fade>
                <button type="button" class="pointer-events-auto mx-auto block rounded-squircle border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50 dark:border-white/10 dark:bg-slate-800 dark:text-slate-200" data-clamp-toggle>نمایش ادامه</button>
            </div>
        </div>

        <div id="review-modal" class="modal-anim fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4 backdrop-blur-md">
            <div class="modal-panel max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-squircle border border-slate-100 bg-white p-6 opacity-0 scale-95 shadow-2xl transition duration-300 ease-out dark:border-white/10 dark:bg-slate md:p-8">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">ثبت دیدگاه جدید</h3>
                    <button type="button" data-review-close class="rounded-full p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10">
                        <svg class="h-6 w-6"><use xlink:href="#close"/></svg>
                    </button>
                </div>
                <x-comments.form :product="$product" :challenge="$challenge" :commentSettings="$commentSettings"/>
            </div>
        </div>
    </div>
</div>
