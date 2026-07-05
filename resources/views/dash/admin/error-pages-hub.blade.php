<x-layouts.admin-dashboard title="مدیریت صفحات خطا" :helpModuleKey="'error_pages'">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="admin-page-title !mb-0">مدیریت صفحات خطا</h1>
            <p class="text-sm text-slate-500 mt-1">مدیریت لینک‌های کمکی و آیکون‌های نمایش داده شده در پایین صفحات خطا</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('error-pages-form').submit()" class="admin-btn admin-btn-primary flex items-center gap-2 h-11 px-6">
                <i class="material-icons !text-lg">save</i>
                <span>ذخیره تمامی تغییرات</span>
            </button>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="error-pages-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-links">
                <span class="material-icons text-base">link</span>
                <span>لینک‌های کمکی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-settings">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات ظاهری</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <form id="error-pages-form" action="{{ route('dash.admin.error_pages.save', ['authkey' => request()->route('authkey')]) }}" method="POST">
        @csrf

        <!-- Links Tab -->
        <div id="tab-links" class="tab-content space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($links as $index => $link)
                <div class="admin-card !p-5 border border-slate-200 dark:border-white/10 bg-slate-50/50 dark:bg-white/5 relative overflow-hidden group/card">
                    <div class="absolute top-0 right-0 h-1 w-full bg-primary/20"></div>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary text-white text-xs shadow-lg shadow-primary/20">{{ $index + 1 }}</span>
                            لینک شماره {{ $index + 1 }}
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 px-1">عنوان لینک</label>
                            <input type="text" name="links[{{ $index }}][title]" value="{{ $link['title'] }}" class="admin-input !h-11 !rounded-xl w-full px-4" placeholder="مثلا: گوشی موبایل">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 px-1">آدرس (URL)</label>
                            <input type="text" name="links[{{ $index }}][url]" value="{{ $link['url'] }}" class="admin-input !h-11 !rounded-xl w-full admin-ltr text-left px-4" placeholder="مثلا: /result/mobile-phone">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 px-1">آیکون</label>
                            <div class="flex gap-2">
                                <div class="h-11 w-11 flex-shrink-0 flex items-center justify-center bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 text-slate-500 dark:text-slate-400 rounded-xl shadow-sm">
                                    <span class="material-symbols-outlined icon-preview-{{ $index }}">{{ $link['icon'] }}</span>
                                </div>
                                <input type="text" id="icon-input-{{ $index }}" name="links[{{ $index }}][icon]" value="{{ $link['icon'] }}" class="admin-input !h-11 !rounded-xl flex-1 px-4 cursor-default" readonly>
                                <button type="button" onclick="openIconPicker({{ $index }})" class="admin-btn !h-11 !rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 hover:bg-slate-200 dark:hover:bg-white/10 transition-all px-4">
                                    <span class="material-symbols-outlined !text-lg text-slate-500">search</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Settings Tab -->
        <div id="tab-settings" class="tab-content hidden">
            <div class="admin-card is-surface max-w-2xl">
                <h3 class="font-bold text-slate mb-6 flex items-center gap-2">
                    <span class="material-icons text-primary">grid_view</span>
                    تنظیمات چیدمان
                </h3>

                <div class="space-y-6">
                    <div class="p-4 rounded-xl bg-slate/5 border border-slate/10">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">تعداد آیکون‌ها در هر ردیف (دسکتاپ)</label>
                        <div class="grid grid-cols-3 gap-4">
                            @foreach([4, 5, 6] as $count)
                                <label class="relative flex items-center justify-center p-4 rounded-xl border-2 cursor-pointer transition-all {{ ($settings['icons_per_row'] ?? 4) == $count ? 'border-primary bg-primary/5 text-primary' : 'border-slate/10 hover:border-slate/30 text-slate/60' }}">
                                    <input type="radio" name="settings[icons_per_row]" value="{{ $count }}" class="sr-only" {{ ($settings['icons_per_row'] ?? 4) == $count ? 'checked' : '' }}>
                                    <span class="text-xl font-black">{{ $count }}</span>
                                    <span class="text-[10px] absolute bottom-1">ستون</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-primary/80 font-bold mt-4 flex items-center gap-1">
                            <span class="material-symbols-outlined !text-xs">info</span>
                            توجه: پس از تغییر تعداد ستون‌ها، باید دکمه ذخیره را بزنید تا تعداد فیلدهای مدیریت لینک در تب قبل بروزرسانی شود.
                        </p>
                        <p class="text-[10px] text-slate/50 mt-2">این تنظیم نحوه نمایش لینک‌های کمکی را در دسکتاپ مشخص می‌کند. در موبایل بصورت خودکار ۲ ستونه نمایش داده می‌شوند.</p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول صفحات خطا</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-links">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">link</span>
                                لینک‌های کمکی
                            </h3>
                            <p>لینک‌های کمکی در صفحات خطا (۴۰۴، ۵۰۰ و ...) نمایش داده می‌شوند. می‌توانید تعداد و محتوای این لینک‌ها را تنظیم کنید.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">تنظیمات قابل ویرایش</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>عنوان لینک (نمایش در دکمه)</li>
                                    <li>آدرس URL مقصد</li>
                                    <li>آیکون (Material Symbols)</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-settings">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">settings</span>
                                تنظیمات چیدمان
                            </h3>
                            <p>در این بخش می‌توانید تعداد ستون‌های نمایش در دسکتاپ را تنظیم کنید.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">نکات مهم</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>در موبایل تصویر به صورت خودکار ۲ ستونه نمایش داده می‌شود</li>
                                    <li>تغییرات پس از ذخیره نهایی در سایت اعمال می‌شود</li>
                                    <li>حداکثر ۲۰ لینک قابل نمایش در هر صفحه خطا است</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-icons">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">photo_library</span>
                                انتخاب آیکون
                            </h3>
                            <p>برای انتخاب آیکون می‌توانید از فیلد جستجوی انتخاب‌گر آیکون استفاده کنید. از آیکون‌های Material Symbols استفاده می‌شود.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">راهنمای انتخاب آیکون</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>کافی است نام انگلیسی آیکون را جستجو کنید (مثلا: phone, home, cart)</li>
                                    <li>کلیک بر روی آیکون مورد نظر آن را انتخاب می‌کند</li>
                                    <li>آیکون انتخابی در فیلد مربوطه نمایش داده می‌شود</li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-links" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">لینک‌های کمکی</a>
                        <a href="#doc-settings" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تنظیمات چیدمان</a>
                        <a href="#doc-icons" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">انتخاب آیکون</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    <!-- Icon Picker Modal -->
    <dialog id="icon-picker-modal" class="admin-dialog backdrop:bg-black/50 overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-white/10 flex items-center justify-between">
            <h3 class="font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">insert_emoticon</span>
                انتخاب آیکون (Material Symbols)
            </h3>
            <button onclick="this.closest('dialog').close()" class="text-slate-400 hover:text-slate-600 flex items-center justify-center h-8 w-8 rounded-lg hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                <span class="material-symbols-outlined !text-xl">close</span>
            </button>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-white/5 border-b border-slate-100 dark:border-white/10">
            <div class="relative group">
                <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input type="text" id="icon-search" class="admin-input !h-11 !rounded-xl w-full pr-10" placeholder="جستجو بین آیکون‌ها (مثلا: phone, home, cart...)" oninput="filterIcons(this.value)">
            </div>
        </div>
        <div class="p-6 max-h-[400px] overflow-y-auto custom-scrollbar">
            <div id="icons-grid" class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-4 text-center">
                <!-- Icons rendered here -->
            </div>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-white/5 flex justify-end gap-2 border-t border-slate-100 dark:border-white/10">
            <button onclick="this.closest('dialog').close()" class="admin-btn !h-11 px-8 !rounded-xl bg-white border border-slate-200 dark:bg-slate-800 dark:border-white/10 text-slate-700 dark:text-slate-300">بستن</button>
        </div>
    </dialog>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-error-pages.js', 'resources/js/admin-error-pages-hub.js'])
