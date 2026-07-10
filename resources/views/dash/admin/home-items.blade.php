<x-layouts.admin-dashboard title="مدیریت آیتم‌های صفحه اصلی" :helpModuleKey="'home_items_management'">
    @php($authkey = request()->route('authkey'))
    @php($slider = $settings['slider'] ?? [])
    @php($banners_categories = $settings['banners_categories'] ?? [])

    <x-admin.page-header title="مدیریت محتوای صفحه اصلی">
        <x-slot:actions>
            <label class="flex items-center gap-2 text-xs font-bold bg-white/5 border border-slate/10 px-3 py-2 rounded-lg cursor-pointer hover:bg-white/10 transition-colors">
                <label class="admin-switch !w-9 !h-5"><input type="checkbox" id="auto-save-toggle" class="admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                ذخیره خودکار
            </label>
            <button type="button" id="global-save-btn" class="admin-btn admin-btn-primary px-6 shadow-lg shadow-primary/20">
                <span class="material-icons">save</span>
                ذخیره تمامی تغییرات
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="home-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-slider-desktop">
            <span class="material-icons text-base">desktop_windows</span>
            <span>اسلایدر دسکتاپ</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-slider-mobile">
            <span class="material-icons text-base">phonelink_ring</span>
            <span>اسلایدر موبایل</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-banners">
            <span class="material-icons text-base">campaign</span>
            <span>بنرها</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-categories">
            <span class="material-icons text-base">grid_view</span>
            <span>آیکون‌های دسته‌بندی</span>
        </button>
    </x-admin.tab-bar>

    @vite(['resources/js/admin-home-slider.js', 'resources/js/admin-home-banners-categories.js'])

    <form action="{{ route('dash.admin.modules.home-slider.save', ['authkey' => $authkey]) }}" method="POST" id="home-slider-form" data-home-slider-root>
        @csrf
        <input type="hidden" name="config_json" value='@json($slider["config"] ?? [])'>
        <input type="hidden" name="desktop_config_json" value='@json($slider["desktop_config"] ?? [])' data-slider-desktop-config-json>
        <input type="hidden" name="mobile_config_json" value='@json($slider["mobile_config"] ?? [])' data-slider-mobile-config-json>
        <input type="hidden" name="desktop_slides_json" value='@json($slider["desktop_slides"] ?? [])' data-slider-desktop-slides-json>
        <input type="hidden" name="mobile_slides_json" value='@json($slider["mobile_slides"] ?? [])' data-slider-mobile-slides-json>
        <input type="hidden" name="title" value="{{ $slider['title'] ?? 'Home Main Banners' }}">
        <input type="hidden" name="status" value="1">

        <div id="tab-slider-desktop" class="tab-content space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="admin-card space-y-4">
                    <div class="flex items-center justify-between border-b border-slate/10 pb-3">
                        <h2 class="font-bold text-slate">مدیریت اسلایدهای دسکتاپ</h2>
                        <button type="button" class="admin-btn py-1.5" data-add-slide="desktop"><span class="material-icons text-sm">add</span>افزودن اسلاید</button>
                    </div>
                    <div class="space-y-3" data-slides-list="desktop"></div>

                    <div class="pt-4 border-t border-slate/10 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate">تنظیمات نمایشی دسکتاپ</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold">وضعیت نمایش دسکتاپ</span>
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">افکت جابجایی</label>
                                <select class="admin-input text-xs" data-config-desktop="effect">
                                    <option value="slide">Slide (ساده)</option>
                                    <option value="fade">Fade (محو شدن)</option>
                                    <option value="cube">Cube (مکعبی)</option>
                                    <option value="coverflow">Coverflow (سه بعدی)</option>
                                    <option value="flip">Flip (ورق زدن)</option>
                                    <option value="cards">Cards (کارتی)</option>
                                    <option value="creative">Creative (خلاقانه)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">فاصله بین اسلایدها (px)</label>
                                <input type="number" step="5" class="admin-input text-xs admin-ltr" data-config-desktop="spaceBetween" placeholder="0">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">نشانگر (Pagination)</label>
                                <select class="admin-input text-xs" data-config-desktop="pagination.type">
                                    <option value="none">غیرفعال (None)</option>
                                    <option value="bullets">نقطه‌ای</option>
                                    <option value="dynamic_bullets">نقطه‌ای پویا</option>
                                    <option value="fraction">کسری</option>
                                    <option value="progressbar">نوار پیشرفت</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">محل نشانگر (Position)</label>
                                <select class="admin-input text-xs" data-config-desktop="pagination.position">
                                    <option value="bottom-4">پایین (پیش‌فرض)</option>
                                    <option value="bottom-8">پایین (فاصله بیشتر)</option>
                                    <option value="top-4">بالا</option>
                                    <option value="left-4">چپ</option>
                                    <option value="right-4">راست</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">سرعت جابجایی (میلی‌ثانیه)</label>
                                <input type="number" step="100" class="admin-input text-xs admin-ltr" data-config-desktop="speed" placeholder="مثلا: 600">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">تاخیر پخش خودکار (ms)</label>
                                <input type="number" step="500" class="admin-input text-xs admin-ltr" data-config-desktop="autoplayDelay" placeholder="مثلا: 3000">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">چرخش بی‌نهایت</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">پخش خودکار</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">نمایش فلش‌ها</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">توقف هنگام نگه داشتن ماوس</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin-card space-y-4">
                    <h2 class="font-bold text-slate border-b border-slate/10 pb-3">پیش‌نمایش زنده دسکتاپ</h2>
                    <div class="swiper rounded-xl border border-slate/30 bg-black/5 p-2 swiper-admin-preview" data-slider-preview-desktop>
                        <div class="swiper-wrapper"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate/10">
                        <button type="submit" class="admin-btn admin-btn-primary w-full justify-center"><span class="material-icons">save</span>ذخیره تمامی تغییرات اسلایدر</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-slider-mobile" class="tab-content hidden space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="admin-card space-y-4">
                    <div class="flex items-center justify-between border-b border-slate/10 pb-3">
                        <h2 class="font-bold text-slate">مدیریت اسلایدهای موبایل</h2>
                        <button type="button" class="admin-btn py-1.5" data-add-slide="mobile"><span class="material-icons text-sm">add</span>افزودن اسلاید</button>
                    </div>
                    <div class="space-y-3" data-slides-list="mobile"></div>

                    <div class="pt-4 border-t border-slate/10 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate">تنظیمات نمایشی موبایل</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold">وضعیت نمایش موبایل</span>
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">افکت جابجایی</label>
                                <select class="admin-input text-xs" data-config-mobile="effect">
                                    <option value="slide">Slide (ساده)</option>
                                    <option value="fade">Fade (محو شدن)</option>
                                    <option value="cube">Cube (مکعبی)</option>
                                    <option value="coverflow">Coverflow (سه بعدی)</option>
                                    <option value="flip">Flip (ورق زدن)</option>
                                    <option value="cards">Cards (کارتی)</option>
                                    <option value="creative">Creative (خلاقانه)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">فاصله بین اسلایدها (px)</label>
                                <input type="number" step="5" class="admin-input text-xs admin-ltr" data-config-mobile="spaceBetween" placeholder="0">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">نشانگر (Pagination)</label>
                                <select class="admin-input text-xs" data-config-mobile="pagination.type">
                                    <option value="none">غیرفعال (None)</option>
                                    <option value="bullets">نقطه‌ای</option>
                                    <option value="dynamic_bullets">نقطه‌ای پویا</option>
                                    <option value="fraction">کسری</option>
                                    <option value="progressbar">نوار پیشرفت</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">محل نشانگر (Position)</label>
                                <select class="admin-input text-xs" data-config-mobile="pagination.position">
                                    <option value="bottom-4">پایین (پیش‌فرض)</option>
                                    <option value="bottom-8">پایین (فاصله بیشتر)</option>
                                    <option value="top-4">بالا</option>
                                    <option value="left-4">چپ</option>
                                    <option value="right-4">راست</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">سرعت (ms)</label>
                                <input type="number" step="100" class="admin-input text-xs admin-ltr" data-config-mobile="speed" placeholder="600">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60 px-1">تاخیر (ms)</label>
                                <input type="number" step="500" class="admin-input text-xs admin-ltr" data-config-mobile="autoplayDelay" placeholder="3000">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-2">
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">چرخش بی‌نهایت</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">پخش خودکار</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">نمایش فلش‌ها</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-[11px] font-bold">قابلیت کشیدن (Draggable)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin-card space-y-4">
                    <h2 class="font-bold text-slate border-b border-slate/10 pb-3">پیش‌نمایش زنده موبایل</h2>
                    <div class="flex justify-center">
                        <div class="swiper rounded-xl border border-slate/30 bg-black/5 p-2 w-[320px] swiper-admin-preview" data-slider-preview-mobile>
                            <div class="swiper-wrapper"></div>
                            <div class="swiper-button-next"></div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate/10">
                        <button type="submit" class="admin-btn admin-btn-primary w-full justify-center"><span class="material-icons">save</span>ذخیره تمامی تغییرات اسلایدر</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('dash.admin.modules.home-banners-categories.save', ['authkey' => $authkey]) }}" method="POST" data-home-banners-categories-root>
    @csrf
    <input type="hidden" name="banners_json" value='@json($banners_categories["banners"] ?? [])' data-banners-json>
    <input type="hidden" name="categories_json" value='@json($banners_categories["categories"] ?? [])' data-categories-json>

    <div id="tab-banners" class="tab-content hidden space-y-4">
        <div class="admin-card">
            <div class="flex items-center justify-between mb-5 border-b border-slate/10 pb-3">
                <h2 class="font-bold text-slate !mb-0">مدیریت بنرهای تبلیغاتی</h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold">وضعیت نمایش بنرها</span>
                    <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                </div>
            </div>
            <div class="bg-primary/5 p-4 rounded-lg mb-6 border border-primary/10">
                <p class="text-xs text-slate-500 leading-6">در این بخش می‌توانید دو بنر اصلی صفحه را مدیریت کنید. برای بهترین نمایش از تصاویر با عرض یکسان استفاده کنید.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                @for($i = 0; $i < 2; $i++)
                    <div class="admin-card is-surface space-y-4">
                        <h3 class="text-xs font-bold text-primary">بنر شماره {{ $i + 1 }}</h3>
                        <div class="space-y-3">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold px-1">تصویر بنر</label>
                                <div class="flex">
                                    <input id="banner-img-{{$i}}" class="flex-grow rounded-r-lg border border-l-0 border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="مسیر تصویر" data-banner-index="{{ $i }}" data-banner-field="image">
                                    <button type="button" class="admin-btn admin-admin-btn-secondary !rounded-r-none px-4" data-file-picker data-file-picker-multi="0" data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}" data-files-url="/uploads" data-cdn-base-url="{{ $fileManagerSettings['cdn_base_url'] ?? '' }}" data-file-pick-target="#banner-img-{{$i}}">Browse</button>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold px-1">لینک مقصد</label>
                                <input class="admin-input admin-ltr" placeholder="https://..." data-banner-index="{{ $i }}" data-banner-field="link">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold px-1">متن جایگزین (Alt)</label>
                                <input class="admin-input" placeholder="توضیح تصویر" data-banner-index="{{ $i }}" data-banner-field="alt">
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <div class="mt-6 pt-4 border-t border-slate/10">
                <button type="submit" class="admin-btn admin-btn-primary px-10"><span class="material-icons">save</span>ذخیره تمامی تغییرات (بنر و دسته‌بندی)</button>
            </div>
        </div>
    </div>

    <div id="tab-categories" class="tab-content hidden space-y-6">
        <div>
            {{-- We need to handle the new categories_top and categories_bottom structure here --}}
            <div class="grid gap-6">
                {{-- Category Section TOP --}}
                <div class="admin-card space-y-5">
                    <div class="flex items-center justify-between border-b border-slate/10 pb-3">
                        <div class="flex items-center gap-3">
                            <h2 class="font-bold text-slate">بخش اول دسته‌بندی‌ها (بعد از اسلایدر)</h2>
                            <label class="admin-switch !w-9 !h-5"><input type="checkbox" id="auto-save-toggle" class="admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                        <button type="button" class="admin-btn py-1.5" data-add-cat-item="top"><span class="material-icons text-sm">add</span>افزودن آیتم</button>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-xs font-bold px-1">عنوان این بخش:</label>
                            <input name="categories_top[title]" value="{{ $banners_categories['categories_top']['title'] ?? '' }}" class="admin-input">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold px-1">نحوه نمایش:</label>
                            <select name="categories_top[view_type]" class="admin-input" onchange="const c=this.closest('.admin-card'); c.querySelectorAll('.grid-cols-opt').forEach(x=>x.classList.toggle('hidden', this.value !== 'grid')); c.querySelector('.slider-opt').classList.toggle('hidden', this.value !== 'slider')">
                                <option value="grid" @selected(($banners_categories['categories_top']['view_type'] ?? 'grid') === 'grid')>شبکه‌ای (Grid)</option>
                                <option value="slider" @selected(($banners_categories['categories_top']['view_type'] ?? '') === 'slider')>اسلایدر (Slider)</option>
                            </select>
                        </div>
                        <div class="space-y-1 grid-cols-opt {{ ($banners_categories['categories_top']['view_type'] ?? 'grid') !== 'grid' ? 'hidden' : '' }}">
                            <label class="text-xs font-bold px-1">تعداد در هر ردیف (ستون):</label>
                            <input type="number" name="categories_top[grid_cols]" value="{{ $banners_categories['categories_top']['grid_cols'] ?? 6 }}" class="admin-input">
                        </div>
                        <div class="space-y-1 grid-cols-opt {{ ($banners_categories['categories_top']['view_type'] ?? 'grid') !== 'grid' ? 'hidden' : '' }}">
                            <label class="text-xs font-bold px-1">تعداد ردیف (سطر):</label>
                            <input type="number" name="categories_top[grid_rows]" value="{{ $banners_categories['categories_top']['grid_rows'] ?? 1 }}" class="admin-input">
                        </div>
                        <div class="flex flex-wrap items-center gap-y-2 gap-x-6 py-2 md:col-span-2">
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-xs font-bold">نمایش عنوان بخش</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-xs font-bold">نمایش دکمه‌های ناوبری</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-xs font-bold">نمایش نشانگر (Pagination)</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-primary/5 p-4 rounded-lg border border-primary/10 mb-4">
                        <p class="text-[10px] text-slate/70 leading-5">نکته: در حالت اسلایدر، تعداد آیتم‌ها در دسکتاپ بر اساس "تعداد در هر ردیف (ستون)" تنظیم می‌شود. برای موبایل به صورت خودکار ۲.۲ آیتم نمایش داده می‌شود.</p>
                    </div>
                    <div id="cat-items-top" class="space-y-3">
                        @foreach(($banners_categories['categories_top']['items'] ?? []) as $idx => $item)
                            <div class="admin-card is-surface !p-3 grid gap-3 md:grid-cols-[40px_200px_1fr_1fr_40px] items-end cat-row">
                                <div class="h-[38px] flex items-center justify-center cursor-move text-slate/30 hover:text-primary cat-drag-handle">
                                    <span class="material-icons">drag_indicator</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] opacity-60 font-bold">تصویر (آیکون)</label>
                                    <div class="flex">
                                        <input id="cat-top-img-{{$idx}}" name="categories_top[items][{{$idx}}][image]" value="{{$item['image']}}" class="flex-grow rounded-r-lg border border-l-0 border-slate p-2 text-xs dark:bg-slate-800 admin-ltr">
                                        <button type="button" class="admin-btn admin-admin-btn-secondary !rounded-r-none !p-2 px-3" data-file-picker data-file-picker-multi="0" data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}" data-files-url="/uploads" data-cdn-base-url="{{ $fileManagerSettings['cdn_base_url'] ?? '' }}" data-file-pick-target="#cat-top-img-{{$idx}}"><span class="material-icons !text-sm">folder</span></button>
                                    </div>
                                </div>
                                <div class="space-y-1"><label class="text-[10px] opacity-60 font-bold">عنوان</label><input name="categories_top[items][{{$idx}}][title]" value="{{$item['title']}}" class="admin-input text-xs"></div>
                                <div class="space-y-1"><label class="text-[10px] opacity-60 font-bold">لینک</label><input name="categories_top[items][{{$idx}}][link]" value="{{$item['link']}}" class="admin-input text-xs admin-ltr"></div>
                                <button type="button" class="remove-cat-row h-[38px] text-danger hover:bg-danger/10 rounded"><span class="material-icons">delete</span></button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Category Section BOTTOM --}}
                <div class="admin-card space-y-5">
                    <div class="flex items-center justify-between border-b border-slate/10 pb-3">
                        <div class="flex items-center gap-3">
                            <h2 class="font-bold text-slate">بخش دوم دسته‌بندی‌ها (بعد از بنرها)</h2>
                            <label class="admin-switch !w-9 !h-5"><input type="checkbox" id="auto-save-toggle" class="admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                        <button type="button" class="admin-btn py-1.5" data-add-cat-item="bottom"><span class="material-icons text-sm">add</span>افزودن آیتم</button>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-xs font-bold px-1">عنوان این بخش:</label>
                            <input name="categories_bottom[title]" value="{{ $banners_categories['categories_bottom']['title'] ?? '' }}" class="admin-input">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold px-1">نحوه نمایش:</label>
                            <select name="categories_bottom[view_type]" class="admin-input" onchange="const c=this.closest('.admin-card'); c.querySelectorAll('.grid-cols-opt').forEach(x=>x.classList.toggle('hidden', this.value !== 'grid')); c.querySelector('.slider-opt').classList.toggle('hidden', this.value !== 'slider')">
                                <option value="grid" @selected(($banners_categories['categories_bottom']['view_type'] ?? 'grid') === 'grid')>شبکه‌ای (Grid)</option>
                                <option value="slider" @selected(($banners_categories['categories_bottom']['view_type'] ?? '') === 'slider')>اسلایدر (Slider)</option>
                            </select>
                        </div>
                        <div class="space-y-1 grid-cols-opt {{ ($banners_categories['categories_bottom']['view_type'] ?? 'grid') !== 'grid' ? 'hidden' : '' }}">
                            <label class="text-xs font-bold px-1">تعداد در هر ردیف (ستون):</label>
                            <input type="number" name="categories_bottom[grid_cols]" value="{{ $banners_categories['categories_bottom']['grid_cols'] ?? 6 }}" class="admin-input">
                        </div>
                        <div class="space-y-1 grid-cols-opt {{ ($banners_categories['categories_bottom']['view_type'] ?? 'grid') !== 'grid' ? 'hidden' : '' }}">
                            <label class="text-xs font-bold px-1">تعداد ردیف (سطر):</label>
                            <input type="number" name="categories_bottom[grid_rows]" value="{{ $banners_categories['categories_bottom']['grid_rows'] ?? 1 }}" class="admin-input">
                        </div>
                        <div class="flex flex-wrap items-center gap-y-2 gap-x-6 py-2 md:col-span-2">
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-xs font-bold">نمایش عنوان بخش</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-xs font-bold">نمایش دکمه‌های ناوبری</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="admin-switch !w-9 !h-5"><input type="checkbox" class="auto-save-toggle admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                                <span class="text-xs font-bold">نمایش نشانگر (Pagination)</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-primary/5 p-4 rounded-lg border border-primary/10 mb-4">
                        <p class="text-[10px] text-slate/70 leading-5">نکته: در حالت اسلایدر، تعداد آیتم‌ها در دسکتاپ بر اساس "تعداد در هر ردیف (ستون)" تنظیم می‌شود. برای موبایل به صورت خودکار ۲.۲ آیتم نمایش داده می‌شود.</p>
                    </div>
                    <div id="cat-items-bottom" class="space-y-3">
                        @foreach(($banners_categories['categories_bottom']['items'] ?? []) as $idx => $item)
                            <div class="admin-card is-surface !p-3 grid gap-3 md:grid-cols-[40px_200px_1fr_1fr_40px] items-end cat-row">
                                <div class="h-[38px] flex items-center justify-center cursor-move text-slate/30 hover:text-primary cat-drag-handle">
                                    <span class="material-icons">drag_indicator</span>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] opacity-60 font-bold">تصویر (آیکون)</label>
                                    <div class="flex">
                                        <input id="cat-bot-img-{{$idx}}" name="categories_bottom[items][{{$idx}}][image]" value="{{$item['image']}}" class="flex-grow rounded-r-lg border border-l-0 border-slate p-2 text-xs dark:bg-slate-800 admin-ltr">
                                        <button type="button" class="admin-btn admin-admin-btn-secondary !rounded-r-none !p-2 px-3" data-file-picker data-file-picker-multi="0" data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}" data-files-url="/uploads" data-cdn-base-url="{{ $fileManagerSettings['cdn_base_url'] ?? '' }}" data-file-pick-target="#cat-bot-img-{{$idx}}"><span class="material-icons !text-sm">folder</span></button>
                                    </div>
                                </div>
                                <div class="space-y-1"><label class="text-[10px] opacity-60 font-bold">عنوان</label><input name="categories_bottom[items][{{$idx}}][title]" value="{{$item['title']}}" class="admin-input text-xs"></div>
                                <div class="space-y-1"><label class="text-[10px] opacity-60 font-bold">لینک</label><input name="categories_bottom[items][{{$idx}}][link]" value="{{$item['link']}}" class="admin-input text-xs admin-ltr"></div>
                                <button type="button" class="remove-cat-row h-[38px] text-danger hover:bg-danger/10 rounded"><span class="material-icons">delete</span></button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="admin-card">
                    <button type="submit" class="admin-btn admin-btn-primary px-10"><span class="material-icons">save</span>ذخیره تمامی تنظیمات بنر و دسته‌بندی</button>
                </div>
            </div>
        </div>
    </div>

    </form>


    {{-- Slide Modal --}}
    <dialog class="admin-dialog w-[min(100vw-16px,720px)] max-w-[720px]" data-slide-modal>
        <form method="dialog" class="admin-dialog-body space-y-4">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title">افزودن / ویرایش اسلاید</h3>
                <button type="button" class="admin-toggle inline-flex" data-close-slide-modal><span class="material-icons">close</span></button>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold px-1">تصویر اسلاید</label>
                <div class="flex">
                    <input id="slider-modal-image" class="flex-grow rounded-r-lg border border-l-0 border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="مسیر تصویر" data-slide-field="image">
                    <button type="button" class="admin-btn admin-admin-btn-secondary !rounded-r-none px-4" data-file-picker data-file-picker-multi="0" data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}" data-files-url="/uploads" data-cdn-base-url="{{ $fileManagerSettings['cdn_base_url'] ?? '' }}" data-file-pick-target="#slider-modal-image">Browse</button>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold px-1">عنوان (اختیاری)</label>
                <input class="admin-input" placeholder="عنوان" data-slide-field="title">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold px-1">توضیح کوتاه</label>
                <textarea class="admin-input" rows="2" placeholder="توضیح کوتاه" data-slide-field="description"></textarea>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold px-1">لینک مقصد</label>
                <input class="admin-input admin-ltr" placeholder="https://..." data-slide-field="link">
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold px-1">ترتیب نمایش</label>
                    <input type="number" min="0" class="admin-input" placeholder="ترتیب" data-slide-field="sort_order">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold px-1">نوع اسلاید</label>
                    <select class="admin-input" data-slide-field="slide_type">
                        <option value="image">تصویر تمام‌عرض</option>
                        <option value="banner">بنر دارای محتوا</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-between bg-slate/5 p-3 rounded-lg border border-slate/10">
                <span class="text-xs font-bold">وضعیت انتشار اسلاید</span>
                <label class="admin-switch"><input type="checkbox" data-slide-field="is_active" class="admin-switch-input" checked><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
            </div>
            <div class="admin-dialog-actions pt-4 border-t">
                <button type="button" class="admin-btn admin-admin-btn-secondary" data-close-slide-modal>انصراف</button>
                <button type="button" class="admin-btn admin-btn-primary px-6" data-save-slide>ذخیره اسلاید</button>
            </div>
        </form>
    </dialog>
</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-home-items-hub.js'])
