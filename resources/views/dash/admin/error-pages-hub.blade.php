<x-layouts.admin-dashboard title="مدیریت صفحات خطا" :helpModuleKey="'error_pages'">
    @vite(['resources/js/admin-error-pages.js', 'resources/js/admin-error-pages-hub.js'])

    <x-admin.page-header title="مدیریت صفحات خطا" description="مدیریت لینک‌های کمکی و آیکون‌های نمایش داده شده در پایین صفحات خطا">
        <x-slot:actions>
            <button onclick="document.getElementById('error-pages-form').submit()" class="admin-btn admin-btn-primary flex items-center gap-2 h-11 px-6">
                <i class="material-icons !text-lg">save</i>
                <span>ذخیره تمامی تغییرات</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="error-pages-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-links">
            <span class="material-icons text-base">link</span>
            <span>لینک‌های کمکی</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-settings">
            <span class="material-icons text-base">settings</span>
            <span>تنظیمات ظاهری</span>
        </button>
    </x-admin.tab-bar>

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
