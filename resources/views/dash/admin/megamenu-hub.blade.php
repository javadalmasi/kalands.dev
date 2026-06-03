<x-layouts.admin-dashboard title="مدیریت مگا منو">
    @push('head')
        <meta name="authkey" content="{{ request()->route('authkey') }}">
    @endpush

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت مگا منو</h1>
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-xs font-bold bg-white/5 border border-slate/10 px-3 py-2 rounded-lg cursor-pointer hover:bg-white/10 transition-colors">
                <label class="admin-switch !w-9 !h-5"><input type="checkbox" id="auto-save-toggle" class="admin-switch-input"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                ذخیره خودکار
            </label>
            <button onclick="window.saveMenuConfig()" class="admin-btn admin-btn-primary flex items-center gap-2 shadow-lg shadow-primary/20">
                <i class="material-icons !text-lg">save</i>
                <span>ذخیره تمامی تغییرات</span>
            </button>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="megamenu-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-editor">
                <span class="material-icons text-base">edit_note</span>
                <span>ویرایشگر منو</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-broken-links">
                <span class="material-icons text-base">link_off</span>
                <span>تست لینک‌های شکسته</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-settings">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات عمومی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
            <div class="flex-grow"></div>
            <button onclick="window.addGroup()" class="px-6 py-4 text-sm font-bold text-emerald-600 hover:bg-emerald-50 transition-colors flex items-center gap-2 border-r border-slate/10">
                <span class="material-icons text-base">add_circle</span>
                <span>افزودن گروه اصلی</span>
            </button>
        </div>
    </div>

    <div id="tab-editor" class="tab-content space-y-6">
        <div class="admin-card">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-white/10">
                <div class="flex flex-wrap items-center gap-4 flex-1">
                    <button onclick="window.openGroupSelector()" class="admin-input flex items-center justify-between min-w-[280px] bg-slate-50 dark:bg-white/5 cursor-pointer group !h-11">
                        <span id="current-group-label" class="font-bold text-slate-700 dark:text-white">انتخاب گروه اصلی برای ویرایش...</span>
                        <span class="material-icons text-slate-400 group-hover:text-primary transition-colors">expand_more</span>
                    </button>
                    <div id="group-actions" class="hidden flex items-center gap-2">
                        <button onclick="window.editCurrentGroup()" class="p-2 text-slate-400 hover:text-primary transition-colors" title="ویرایش نام گروه">
                            <span class="material-icons !text-lg">edit</span>
                        </button>
                        <button onclick="window.removeCurrentGroup()" class="p-2 text-slate-400 hover:text-red-500 transition-colors" title="حذف گروه">
                            <span class="material-icons !text-lg">delete</span>
                        </button>
                    </div>

                    <div class="h-8 w-px bg-slate-200 dark:bg-white/10 mx-2 hidden md:block"></div>

                    <div class="flex items-center gap-2 flex-1 min-w-[300px] bg-slate-50 dark:bg-white/5 p-1 rounded-xl border border-slate-100 dark:border-white/10 h-11">
                        <div class="relative flex-1 h-full">
                            <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 !text-lg">search</span>
                            <input type="text" id="global-menu-search" oninput="window.handleGlobalSearch(this.value)" class="admin-input !border-0 !bg-transparent w-full pr-10 pl-10 h-full !text-xs" placeholder="جستجوی سراسری در تمام سطوح...">
                            <button onclick="document.getElementById('global-menu-search').value='*'; window.handleGlobalSearch('*')" class="absolute left-2 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center bg-primary/10 text-primary !rounded-lg hover:bg-primary hover:text-white transition-all shadow-sm" title="Quick Search (*)">
                                <span class="material-icons !text-base">bolt</span>
                            </button>
                        </div>
                        <div class="w-px h-6 bg-slate-200 dark:bg-white/10 mx-1"></div>
                        <select id="global-menu-filter" onchange="window.handleGlobalFilter(this.value)" class="admin-input !border-0 !bg-transparent !w-auto h-full !text-xs !px-4 cursor-pointer">
                            <option value="all">همه وضعیت‌ها</option>
                            <option value="inactive">فقط غیرفعال‌ها</option>
                            <option value="desktop_only">فقط دسکتاپ</option>
                            <option value="mobile_only">فقط موبایل</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="megamenu-builder-root" class="flex gap-4 min-h-[600px]">
                <div class="flex-1 flex items-center justify-center border-2 border-dashed border-slate-200 dark:border-white/5 admin-card bg-slate-50/50 dark:bg-white/5">
                    <div class="text-center space-y-4">
                        <div class="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="material-icons !text-4xl">account_tree</span>
                        </div>
                        <h3 class="font-black text-slate-700 dark:text-white">ویرایشگر بصری مگا منو</h3>
                        <p class="text-xs text-slate-500 max-w-xs mx-auto leading-6">ابتدا یکی از گروه‌های اصلی (تب‌ها) را از منوی بالا انتخاب کنید تا زیرمجموعه‌های آن برای ویرایش نمایش داده شود.</p>
                        <button onclick="window.openGroupSelector()" class="admin-btn admin-btn-primary mt-4">
                            <span class="material-icons">touch_app</span>
                            انتخاب گروه
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-broken-links" class="tab-content hidden space-y-6">
        <div class="admin-card">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-amber-500/10 p-2 text-amber-600">
                        <span class="material-icons">link_off</span>
                    </div>
                    <div>
                        <h2 class="font-black text-slate">بررسی لینک‌های شکسته</h2>
                        <p class="text-[11px] text-slate/50 mt-1">شناسایی خودکار صفحات حذف شده یا آدرس‌های اشتباه در منو</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div id="broken-links-bulk-actions" class="hidden flex items-center gap-2 p-1.5 bg-slate-50 dark:bg-white/5 rounded-xl border border-slate-100 dark:border-white/10">
                        <span class="text-[10px] font-bold px-2 text-slate-500">عملیات دسته‌جمعی شکسته:</span>
                        <button onclick="window.bulkActionBrokenLinks('disable_selected')" class="admin-btn !py-1 !text-[10px] bg-amber-500 text-white">غیرفعال‌سازی انتخابی</button>
                        <button onclick="window.bulkActionBrokenLinks('delete_selected')" class="admin-btn !py-1 !text-[10px] bg-red-500 text-white">حذف انتخابی</button>
                        <div class="w-px h-4 bg-slate-200 dark:bg-white/10 mx-1"></div>
                        <button onclick="window.bulkActionBrokenLinks('disable_all')" class="admin-btn !py-1 !text-[10px] border-amber-500 text-amber-500 hover:bg-amber-500 hover:text-white">غیرفعال‌سازی همه شکسته</button>
                        <button onclick="window.bulkActionBrokenLinks('delete_all')" class="admin-btn !py-1 !text-[10px] border-red-500 text-red-500 hover:bg-red-500 hover:text-white">حذف همه شکسته</button>
                    </div>
                    <button onclick="window.startLinkTest()" id="start-test-btn" class="admin-btn admin-btn-primary px-8">
                        <span class="material-icons">play_circle</span>
                        شروع عملیات بررسی
                    </button>
                </div>
            </div>

            <div id="test-progress" class="hidden mb-8 max-w-2xl mx-auto bg-slate-50 dark:bg-white/5 p-6 admin-card border border-slate-100 dark:border-white/10">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400">پیشرفت عملیات</span>
                    <span id="progress-percent" class="text-sm font-black text-primary">0%</span>
                </div>
                <div class="w-full h-3 bg-white dark:bg-slate-800 rounded-full overflow-hidden shadow-inner p-0.5 border border-slate-200 dark:border-white/5">
                    <div id="progress-bar" class="h-full bg-primary rounded-full transition-all duration-300 relative overflow-hidden">
                         <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                    </div>
                </div>
                <p id="test-status-msg" class="text-[10px] text-center mt-4 text-slate-400">در حال دریافت و بررسی تمامی لینک‌های موجود در مگا منو...</p>
            </div>

            <div id="broken-links-container" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 h-[500px] content-start overflow-y-auto custom-scrollbar p-2">
                <div class="col-span-full flex flex-col items-center justify-center py-20 opacity-30">
                    <span class="material-icons !text-6xl mb-4">search_off</span>
                    <p class="text-sm font-bold text-slate-500">گزارشی برای نمایش وجود ندارد</p>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-settings" class="tab-content hidden space-y-6">
        <div class="admin-card">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">settings</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">تنظیمات مگا منو</h2>
                        <p class="text-xs text-slate/60 mt-1">پیکربندی کلی رفتار مگا منو در سایت</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="window.exportMenuConfig()" class="admin-btn bg-slate-100 dark:bg-white/5 hover:bg-primary hover:text-white transition-all">
                        <span class="material-icons">file_download</span>
                        <span>خروجی JSON</span>
                    </button>
                    <button onclick="document.getElementById('import-json-input').click()" class="admin-btn bg-slate-100 dark:bg-white/5 hover:bg-emerald-500 hover:text-white transition-all">
                        <span class="material-icons">file_upload</span>
                        <span>وارد کردن JSON</span>
                    </button>
                    <input type="file" id="import-json-input" class="hidden" accept=".json" onchange="window.importMenuConfig(event)">
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-500">مشاهده و ویرایش مستقیم ساختار JSON</label>
                    <span class="text-[10px] text-amber-500 font-bold">* تغییرات در اینجا بلافاصله روی ویرایشگر اعمال می‌شود.</span>
                </div>
                <textarea id="megamenu-json-viewer" class="admin-input w-full h-[500px] font-mono text-[11px] leading-5 !p-4 !admin-card" dir="ltr" spellcheck="false" oninput="window.handleJsonViewerInput(this.value)"></textarea>
            </div>
        </div>
    </div>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول مگا منو</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-editor">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">edit_note</span>
                                ویرایشگر منو
                            </h3>
                            <p>ویرایشگر بصری مگا منو به شما امکان می‌دهد ساختار کامل منوی سایت را از طریق یک رابط کاربری کشیدن و رها کردن مدیریت کنید.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">نکات کلیدی</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>گروه‌های اصلی (تب‌ها) در هدر سایت ظاهر می‌شوند</li>
                                    <li>زیرمنوها تا سه سطح تو در تو پشورش دارند</li>
                                    <li>استفاده از جستجوی سراسری برای یافتن سریع آیتم‌ها</li>
                                    <li>فیلترهای وضعیت برای نمایش فقط موارد غیرفعال</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-broken-links">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">link_off</span>
                                تست لینک‌های شکسته
                            </h3>
                            <p>این بخش به‌صورت خودکار لینک‌های منو را بررسی می‌کند و گزارش می‌دهد کدام لینک‌ها دچار خطا یا حذف شده‌اند.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">عملیات‌های دسته‌جمعی</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>غیرفعال‌سازی انتخابی: لینک‌های انتخابی را غیرفعال می‌کند</li>
                                    <li>حذف انتخابی: لینک‌های انتخابی را از ساختار منو حذف می‌کند</li>
                                    <li>غیرفعال‌سازی همه شکسته: تمام لینک‌های خطا را غیرفعال می‌کند</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-settings">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">settings</span>
                                تنظیمات عمومی
                            </h3>
                            <p>در این بخش می‌توانید ساختار JSON منو را مستقیماً مشاهده و ویرایش کنید.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">ویرایش مستقیم JSON</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>تغییرات در اینجا بلافاصله در ویرایشگر اعمال می‌شود</li>
                                    <li>دانلود خروجی JSON برای پشتیبان‌گیری</li>
                                    <li>وارد کردن JSON برای بازیابی تنظیمات قبلی</li>
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
                        <a href="#doc-editor" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">ویرایشگر منو</a>
                        <a href="#doc-broken-links" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تست لینک‌های شکسته</a>
                        <a href="#doc-settings" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تنظیمات عمومی</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    {{-- Dialog Modals --}}
    <dialog id="group-form-modal" class="admin-dialog w-[min(100vw-32px,500px)]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons text-primary">edit</span>
                    <span id="group-modal-title">افزودن گروه اصلی</span>
                </h3>
                <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle"><span class="material-icons">close</span></button>
            </div>
            <div class="space-y-4 py-4">
                <div>
                    <label class="block text-xs font-bold mb-2">عنوان گروه</label>
                    <input type="text" id="group-title-input" class="admin-input w-full !rounded-xl !h-11" placeholder="مثلا: محصولات، خدمات و...">
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <button id="group-modal-submit" class="admin-btn admin-btn-primary">ثبت و ذخیره</button>
            </div>
        </div>
    </dialog>

    <dialog id="item-form-modal" class="admin-dialog w-[min(100vw-32px,500px)]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons text-primary">edit</span>
                    <span id="item-modal-title">ویرایش آیتم</span>
                </h3>
                <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle"><span class="material-icons">close</span></button>
            </div>
            <div class="space-y-4 py-4">
                <div>
                    <label class="block text-xs font-bold mb-2">عنوان</label>
                    <input type="text" id="item-title-input" class="admin-input w-full !rounded-xl !h-11" placeholder="عنوان آیتم...">
                </div>
                <div id="item-href-container">
                    <label class="block text-xs font-bold mb-2">لینک (URL)</label>
                    <input type="text" id="item-href-input" class="admin-input w-full admin-ltr !rounded-xl !h-11" placeholder="https://example.com/...">
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <button id="item-modal-submit" class="admin-btn admin-btn-primary">ثبت و ذخیره</button>
            </div>
        </div>
    </dialog>

    <dialog id="megamenu-alert-modal" class="admin-dialog w-[min(100vw-32px,450px)]">
        <div class="admin-dialog-body">
            <div class="flex items-start gap-4 p-2">
                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-icons !text-2xl">info</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-slate-800 dark:text-white mb-2">توجه</h3>
                    <p id="megamenu-alert-message" class="text-xs leading-6 text-slate-500 dark:text-slate-400"></p>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button onclick="this.closest('dialog').close()" class="admin-btn admin-btn-primary px-8">متوجه شدم</button>
            </div>
        </div>
    </dialog>

    <dialog id="megamenu-confirm-modal" class="admin-dialog w-[min(100vw-32px,450px)]">
        <div class="admin-dialog-body">
            <div class="flex items-start gap-4 p-2">
                <div class="w-12 h-12 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                    <span class="material-icons !text-2xl">help_outline</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-slate-800 dark:text-white mb-2">تایید عملیات</h3>
                    <p id="megamenu-confirm-message" class="text-xs leading-6 text-slate-500 dark:text-slate-400"></p>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button id="megamenu-confirm-cancel" class="admin-btn admin-btn-secondary">انصراف</button>
                <button id="megamenu-confirm-submit" class="admin-btn admin-btn-primary px-8">تایید</button>
            </div>
        </div>
    </dialog>

    <dialog id="group-selector-modal" class="admin-dialog w-[min(100vw-32px,800px)]">
        <div class="admin-dialog-body space-y-6">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons text-primary">grid_view</span>
                    <span>انتخاب گروه اصلی (تب منو)</span>
                </h3>
                <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle"><span class="material-icons">close</span></button>
            </div>

            <div class="relative mb-4">
                <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input type="text" oninput="window.filterModalGroups(this.value)" class="admin-input w-full pr-10 !rounded-xl !h-11" placeholder="جستجو در گروه‌ها...">
            </div>

            <div id="modal-groups-list" class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[450px] overflow-y-auto custom-scrollbar p-1">
            </div>

            <div class="pt-4 border-t border-slate/10 flex justify-between items-center">
                <p class="text-[10px] text-slate-400 italic">* گروه‌های اصلی تب‌های اول منو در هدر سایت هستند.</p>
                <button onclick="this.closest('dialog').close()" class="admin-btn bg-white border border-slate-200 dark:bg-slate-800">انصراف</button>
            </div>
        </div>
    </dialog>


</x-layouts.admin-dashboard>
<script>window.menuData = @json($menuConfig);</script>
@vite(['resources/js/admin-megamenu.js', 'resources/js/admin-megamenu-hub.js', 'resources/css/admin-megamenu-hub.css'])
