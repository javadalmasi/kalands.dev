<x-layouts.admin-dashboard title="آنالیزور">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="admin-page-title !mb-1">آنالیزور</h1>
            <p class="text-sm text-slate/60">گزارش حرفه‌ای بازدیدها، اهداف، دستگاه‌ها، کشورها و رفتار کاربران</p>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="analytics-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-success text-success flex items-center gap-2" data-tab-target="tab-overview" data-filterable>
                <span class="material-icons text-base">dashboard</span>
                <span>نمای کلی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-live">
                <span class="material-icons text-base">sensors</span>
                <span>کاربران زنده</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-reports" data-filterable>
                <span class="material-icons text-base">public</span>
                <span>گزارش‌ها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-content" data-filterable>
                <span class="material-icons text-base">inventory_2</span>
                <span>محتوا و محصولات</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-search" data-filterable>
                <span class="material-icons text-base">search</span>
                <span>جستجوها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-goals" data-filterable>
                <span class="material-icons text-base">touch_app</span>
                <span>اهداف</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-users" data-filterable>
                <span class="material-icons text-base">person_search</span>
                <span>کاربران</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-errors" data-filterable>
                <span class="material-icons text-base">bug_report</span>
                <span>خطاها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-maintenance">
                <span class="material-icons text-base">cleaning_services</span>
                <span>نگهداری داده‌ها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-settings">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <div id="analytics-hub-root"
         data-report-url="{{ route('dash.admin.analytics_report', ['authkey' => $authkey]) }}"
         data-user-activity-url="{{ route('dash.admin.analytics_user_details', ['authkey' => $authkey, 'userId' => ':id']) }}">
        <div class="admin-card mb-6" id="analytics-filter-card">
            <form data-analytics-filters>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-7" id="analytics-filter-primary">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate">از تاریخ</label>
                        <div data-shamsi-datepicker="from">
                            <input type="text" name="from" placeholder="۱۴۰۳/۰۱/۰۱" autocomplete="off" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate">تا تاریخ</label>
                        <div data-shamsi-datepicker="to">
                            <input type="text" name="to" placeholder="۱۴۰۳/۰۱/۰۱" autocomplete="off" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate">نوع گزارش</label>
                        <select name="period" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            <option value="day">روزانه</option>
                            <option value="week">هفتگی</option>
                            <option value="month">ماهانه</option>
                        </select>
                    </div>
                    <div class="space-y-1 flex items-end gap-2">
                        <button type="button" id="analytics-filter-toggle" class="admin-btn admin-btn-secondary h-10 px-3 shrink-0 whitespace-nowrap text-xs" title="فیلترهای بیشتر">
                            <span class="material-icons text-base" id="analytics-filter-toggle-icon">expand_more</span>
                            <span id="analytics-filter-toggle-text">بیشتر</span>
                        </button>
                    </div>
                    <div class="space-y-1 xl:col-span-2">
                        <label class="text-xs font-bold text-slate">جستجو</label>
                        <div class="flex gap-2">
                            <input type="search" name="search" placeholder="مسیر، عنوان، کشور..." class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            <button class="admin-btn !bg-success !text-white h-10 px-3 shrink-0" type="submit">
                                <span class="material-icons text-base">filter_alt</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 mt-4 hidden" id="analytics-filter-secondary">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate">کشور</label>
                        <select name="country" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            <option value="">همه</option>
                            <option value="IR" data-flag="🇮🇷">🇮🇷 ایران</option>
                            <option value="US" data-flag="🇺🇸">🇺🇸 آمریکا</option>
                            <option value="CA" data-flag="🇨🇦">🇨🇦 کانادا</option>
                            <option value="GB" data-flag="🇬🇧">🇬🇧 انگلیس</option>
                            <option value="DE" data-flag="🇩🇪">🇩🇪 آلمان</option>
                            <option value="FR" data-flag="🇫🇷">🇫🇷 فرانسه</option>
                            <option value="IT" data-flag="🇮🇹">🇮🇹 ایتالیا</option>
                            <option value="ES" data-flag="🇪🇸">🇪🇸 اسپانیا</option>
                            <option value="NL" data-flag="🇳🇱">🇳🇱 هلند</option>
                            <option value="RU" data-flag="🇷🇺">🇷🇺 روسیه</option>
                            <option value="TR" data-flag="🇹🇷">🇹🇷 ترکیه</option>
                            <option value="AE" data-flag="🇦🇪">🇦🇪 امارات</option>
                            <option value="SA" data-flag="🇸🇦">🇸🇦 عربستان</option>
                            <option value="IQ" data-flag="🇮🇶">🇮🇶 عراق</option>
                            <option value="CN" data-flag="🇨🇳">🇨🇳 چین</option>
                            <option value="JP" data-flag="🇯🇵">🇯🇵 ژاپن</option>
                            <option value="KR" data-flag="🇰🇷">🇰🇷 کره</option>
                            <option value="IN" data-flag="🇮🇳">🇮🇳 هند</option>
                            <option value="PK" data-flag="🇵🇰">🇵🇰 پاکستان</option>
                            <option value="AU" data-flag="🇦🇺">🇦🇺 استرالیا</option>
                            <option value="BR" data-flag="🇧🇷">🇧🇷 برزیل</option>
                            <option value="MX" data-flag="🇲🇽">🇲🇽 مکزیک</option>
                            <option value="ZA" data-flag="🇿🇦">🇿🇦 آفریقای جنوبی</option>
                            <option value="EG" data-flag="🇪🇬">🇪🇬 مصر</option>
                            <option value="NG" data-flag="🇳🇬">🇳🇬 نیجریه</option>
                            <option value="TH" data-flag="🇹🇭">🇹🇭 تایلند</option>
                            <option value="MY" data-flag="🇲🇾">🇲🇾 مالزی</option>
                            <option value="SG" data-flag="🇸🇬">🇸🇬 سنگاپور</option>
                            <option value="ID" data-flag="🇮🇩">🇮🇩 اندونزی</option>
                            <option value="DZ" data-flag="🇩🇿">🇩🇿 الجزایر</option>
                            <option value="MA" data-flag="🇲🇦">🇲🇦 مراکش</option>
                            <option value="AR" data-flag="🇦🇷">🇦🇷 آرژانتین</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate">دستگاه</label>
                        <select name="device_type" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            <option value="">همه</option>
                            <option value="desktop">دسکتاپ</option>
                            <option value="mobile">موبایل</option>
                            <option value="tablet">تبلت</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate">فعالیت</label>
                        <select name="activity" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            <option value="">همه</option>
                            <option value="pageview">بازدید صفحه</option>
                            <option value="goal">تحقق هدف</option>
                            <option value="error">خطا</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div id="tab-overview" class="analytics-tab-content space-y-6">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">بازدید امروز</p>
                    <p class="text-2xl font-bold" data-stat="today">...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">بازدید ۷ روز اخیر</p>
                    <p class="text-2xl font-bold" data-stat="week">...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">بازدید ۳۰ روز اخیر</p>
                    <p class="text-2xl font-bold" data-stat="month">...</p>
                </div>
                <div class="admin-card text-center border-success/40">
                    <p class="text-xs text-slate opacity-60 mb-1">کاربران زنده</p>
                    <p class="text-2xl font-bold text-success" data-stat="live">...</p>
                </div>
            </div>

            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                    <span class="material-icons text-success">timeline</span>
                    <span>فعالیت کاربران در بازه انتخابی</span>
                </h3>
                <div data-list="overview-activity" class="grid gap-3 md:grid-cols-3">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>

            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                    <span class="material-icons text-success">show_chart</span>
                    <span>نمودار بازدید با تاریخ</span>
                </h3>
                <div class="min-h-72 flex items-center justify-center bg-slate/5 border border-dashed border-slate/20" data-chart="overview">
                    <p class="text-sm opacity-50">در حال بارگذاری نمودار...</p>
                </div>
            </div>
        </div>

        <dialog id="user-activity-modal" class="admin-dialog">
            <div class="admin-dialog-body !p-0">
                <div class="admin-dialog-head p-4">
                    <h3 class="admin-dialog-title flex items-center gap-2">
                        <span class="material-icons text-success">history</span>
                        <span id="activity-modal-title">فعالیت کاربر</span>
                    </h3>
                    <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle !w-8 !h-8 !rounded-lg border-none hover:bg-slate/10">
                        <span class="material-icons text-sm">close</span>
                    </button>
                </div>
                <div class="overflow-y-auto max-h-[70vh] p-4 custom-scrollbar" id="activity-modal-content">
                    <p class="text-center py-10 opacity-50">در حال دریافت اطلاعات...</p>
                </div>
            </div>
        </dialog>

        <div id="tab-live" class="analytics-tab-content hidden space-y-6">
            <div class="admin-card !p-0 overflow-hidden border-success/20">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-slate/10 bg-slate/5">
                    <h3 class="font-bold text-slate flex items-center gap-2">
                        <span class="material-icons text-success">public</span>
                        <span>نقشه زنده بازدیدها</span>
                    </h3>
                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-950">
                        <button type="button" data-live-map-mode="2d" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-xs font-black text-white shadow-sm ring-1 ring-emerald-500/20 transition-all dark:bg-emerald-400 dark:text-slate-950 dark:ring-emerald-300/30">
                            <span class="material-icons text-base">map</span>
                            <span>2D Map</span>
                        </button>
                        <button type="button" data-live-map-mode="3d" class="flex items-center gap-2 rounded-lg px-6 py-2.5 text-xs font-black text-slate-600 transition-all hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                            <span class="material-icons text-base">language</span>
                            <span>3D Globe</span>
                        </button>
                    </div>
                </div>
                <div id="live-map-container" class="relative bg-slate/5 h-[600px] lg:h-[720px] w-full">
                    <div data-map="live" class="w-full h-full">
                        <div class="flex flex-col items-center justify-center h-full gap-4 opacity-50">
                            <div class="w-12 h-12 border-4 border-success/20 border-t-success rounded-full animate-spin"></div>
                            <p class="text-sm">در حال بارگذاری نقشه زنده...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="admin-card">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="font-bold text-slate flex items-center gap-2">
                        <span class="material-icons text-success">sensors</span>
                        <span>کاربران فعال در چند دقیقه اخیر</span>
                    </h3>
                    <span class="text-xs text-slate/60">به‌روزرسانی خودکار هر ۳۰ ثانیه</span>
                </div>
                <div data-live-list class="space-y-2">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>
        </div>

        <div id="tab-reports" class="analytics-tab-content hidden space-y-6">
            <div class="admin-card !p-0 overflow-hidden border-success/20">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-slate/10 bg-slate/5">
                    <h3 class="font-bold text-slate flex items-center gap-2">
                        <span class="material-icons text-success">public</span>
                        <span>نقشه جهانی بازدیدها</span>
                    </h3>
                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-950">
                        <button type="button" data-live-map-mode="2d" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-xs font-black text-white shadow-sm ring-1 ring-emerald-500/20 transition-all dark:bg-emerald-400 dark:text-slate-950 dark:ring-emerald-300/30">
                            <span class="material-icons text-base">map</span>
                            <span>2D Map</span>
                        </button>
                        <button type="button" data-live-map-mode="3d" class="flex items-center gap-2 rounded-lg px-6 py-2.5 text-xs font-black text-slate-600 transition-all hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                            <span class="material-icons text-base">language</span>
                            <span>3D Globe</span>
                        </button>
                    </div>
                </div>
                <div id="reports-map-container" class="relative bg-slate/5 h-[600px] lg:h-[720px] w-full">
                    <div data-map="reports" class="w-full h-full">
                        <div class="flex flex-col items-center justify-center h-full gap-4 opacity-50">
                            <div class="w-12 h-12 border-4 border-success/20 border-t-success rounded-full animate-spin"></div>
                            <p class="text-sm">در حال بارگذاری نقشه...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">flag</span>
                        <span>بازدید بر اساس کشور</span>
                    </h3>
                    <div data-list="countries" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">source</span>
                        <span>منابع ترافیک</span>
                    </h3>
                    <div data-list="referrers" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">devices</span>
                        <span>نوع دستگاه</span>
                    </h3>
                    <div data-list="device-types" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">devices</span>
                        <span>برند دستگاه‌ها</span>
                    </h3>
                    <div data-list="device-brands" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">language</span>
                        <span>مرورگرها</span>
                    </h3>
                    <div data-list="browsers" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">developer_board</span>
                        <span>سیستم‌عامل‌ها</span>
                    </h3>
                    <div data-list="platforms" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">search</span>
                        <span>موتورهای جستجو</span>
                    </h3>
                    <div data-list="search-engines" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">campaign</span>
                        <span>UTM Source</span>
                    </h3>
                    <div data-list="utm-sources" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">layers</span>
                        <span>UTM Medium</span>
                    </h3>
                    <div data-list="utm-mediums" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">flag</span>
                        <span>UTM Campaign</span>
                    </h3>
                    <div data-list="utm-campaigns" class="space-y-2 min-h-[200px]">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card md:col-span-2 xl:col-span-4">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-success">timeline</span>
                        <span>فعالیت‌ها</span>
                    </h3>
                    <div data-list="activities" class="grid gap-3 md:grid-cols-3">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-content" class="analytics-tab-content hidden space-y-6">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 text-sm border-b border-slate/10 pb-2">صفحات پربازدید</h3>
                    <div data-list="pages" class="space-y-3">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 text-sm border-b border-slate/10 pb-2">محصولات پربازدید</h3>
                    <div data-list="products" class="space-y-3">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 text-sm border-b border-slate/10 pb-2">دسته‌بندی‌های پربازدید</h3>
                    <div data-list="categories" class="space-y-3">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 text-sm border-b border-slate/10 pb-2">فروشندگان پربازدید</h3>
                    <div data-list="sellers" class="space-y-3">
                        <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-search" class="analytics-tab-content hidden space-y-6">
            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                    <span class="material-icons text-success">search</span>
                    <span>پر سرچ‌ترین کلمات سایت</span>
                </h3>
                <div data-list="keywords" class="space-y-1">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>
        </div>

        <div id="tab-goals" class="analytics-tab-content hidden space-y-6">
            <div class="grid gap-6 md:grid-cols-2 mb-6">
                <div class="admin-card border-r-4 border-success">
                    <h3 class="font-bold text-slate mb-4">کلیک‌های Digikala</h3>
                    <div class="flex items-end justify-between">
                        <p class="text-3xl font-black" data-goal-today="tr_dk">...</p>
                        <div class="text-xs font-bold" data-goal-delta="tr_dk"></div>
                    </div>
                    <p class="text-[10px] text-slate opacity-60 mt-2">مقایسه با روز گذشته</p>
                </div>
                <div class="admin-card border-r-4 border-amber-500">
                    <h3 class="font-bold text-slate mb-4">کلیک‌های Basalam</h3>
                    <div class="flex items-end justify-between">
                        <p class="text-3xl font-black" data-goal-today="tr_bs">...</p>
                        <div class="text-xs font-bold" data-goal-delta="tr_bs"></div>
                    </div>
                    <p class="text-[10px] text-slate opacity-60 mt-2">مقایسه با روز گذشته</p>
                </div>
            </div>

            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                    <span class="material-icons text-success">emoji_events</span>
                    <span>آمار کلی اهداف ۳۰ روز اخیر</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-slate/5 border-b border-slate/10">
                            <tr>
                                <th class="p-4 font-bold">نام هدف</th>
                                <th class="p-4 font-bold text-center">تعداد تحقق</th>
                            </tr>
                        </thead>
                        <tbody data-goals-table class="divide-y divide-slate/5">
                            <tr>
                                <td colspan="2" class="p-10 text-center opacity-50">در حال بارگذاری...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tab-users" class="analytics-tab-content hidden space-y-6">
            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                    <span class="material-icons text-success">person_search</span>
                    <span>کاربران لاگین‌شده پربازدید</span>
                </h3>
                <div data-list="users" class="space-y-2">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>
        </div>

        <div id="tab-errors" class="analytics-tab-content hidden space-y-6">
            <div class="admin-card">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="font-bold text-slate flex items-center gap-2">
                        <span class="material-icons text-danger">bug_report</span>
                        <span>Error Tracker</span>
                    </h3>
                    <span class="text-sm font-bold text-danger" data-errors-total>...</span>
                </div>
                <div data-errors-list class="space-y-3">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>
        </div>

        <div id="tab-maintenance" class="analytics-tab-content hidden space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="admin-card">
                    <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                        <span class="material-icons text-danger">auto_delete</span>
                        <span>پاکسازی پیشرفته داده‌ها</span>
                    </h3>
                    <p class="text-xs text-slate/60 mb-6">در این بخش می‌توانید داده‌های قدیمی یا غیرضروری را برای آزاد کردن فضای دیتابیس حذف کنید. این عملیات غیرقابل بازگشت است.</p>

                    <form action="{{ route('dash.admin.analytics_prune', ['authkey' => $authkey]) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate px-1">نوع داده</label>
                            <select name="type" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="errors">فقط گزارش‌های خطا</option>
                                <option value="all">تمامی رویدادها (بازدید، هدف، خطا)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate px-1">بازه زمانی</label>
                            <select name="older_than" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="all">همه زمان‌ها</option>
                                <option value="30">قدیمی‌تر از یک ماه</option>
                                <option value="90">قدیمی‌تر از ۳ ماه</option>
                                <option value="180">قدیمی‌تر از ۶ ماه</option>
                                <option value="365">قدیمی‌تر از یک سال</option>
                            </select>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="admin-btn !bg-danger !text-white w-full justify-center" onclick="return confirm('آیا از حذف این داده‌ها اطمینان دارید؟ این عملیات غیرقابل بازگشت است.')">
                                <span class="material-icons">delete_forever</span>
                                شروع پاکسازی
                            </button>
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="admin-card">
                        <h3 class="font-bold text-slate mb-3 flex items-center gap-2">
                            <span class="material-icons text-success">download</span>
                            <span>Export آمار</span>
                        </h3>
                        <p class="text-xs text-slate/60 mb-4">تنظیمات و آمار تجمیعی برای انتقال یا بکاپ خروجی JSON می‌گیرد.</p>
                        <a href="{{ route('dash.admin.analytics_export', ['authkey' => $authkey]) }}" class="admin-btn !bg-success/10 !text-success !border-success/20 justify-center w-full">
                            <span class="material-icons">file_download</span>
                            دریافت فایل JSON
                        </a>
                    </div>
                    <div class="admin-card">
                        <h3 class="font-bold text-slate mb-3 flex items-center gap-2">
                            <span class="material-icons text-success">upload</span>
                            <span>Import آمار</span>
                        </h3>
                        <form action="{{ route('dash.admin.analytics_import', ['authkey' => $authkey]) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <input type="file" name="analytics_file" accept="application/json,.json" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" required>
                            <button class="admin-btn !bg-success !text-white justify-center w-full">
                                <span class="material-icons">file_upload</span>
                                درون‌ریزی فایل
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-settings" class="analytics-tab-content hidden space-y-6">
            <form action="{{ route('dash.admin.analytics_settings_save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
                @csrf
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                        <div class="flex items-center gap-3">
                            <div class="rounded bg-success/10 p-2 text-success">
                                <span class="material-icons">analytics</span>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate">فعالسازی آنالیزور</h2>
                                <p class="text-xs text-slate/60 mt-1">ثبت رویدادها و نمایش گزارش‌ها در پنل ادمین</p>
                            </div>
                        </div>
                        <label class="admin-switch"><input type="checkbox" name="enabled" value="1" class="admin-switch-input" @checked(old('enabled', $settings['enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                    </div>

                    <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                        <div class="flex items-center gap-3">
                            <div class="rounded bg-success/10 p-2 text-success">
                                <span class="material-icons">code</span>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate">فعالسازی اسکریپت رهگیری</h2>
                                <p class="text-xs text-slate/60 mt-1">ارسال pageview و goal با fetch سبک</p>
                            </div>
                        </div>
                        <label class="admin-switch"><input type="checkbox" name="tracking_script_enabled" value="1" class="admin-switch-input" @checked(old('tracking_script_enabled', $settings['tracking_script_enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                    </div>

                    <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                        <div class="flex items-center gap-3">
                            <div class="rounded bg-danger/10 p-2 text-danger">
                                <span class="material-icons">bug_report</span>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate">فعالسازی Error Tracker</h2>
                                <p class="text-xs text-slate/60 mt-1">ثبت خطاهای سمت کاربر با محدودیت تعداد برای کاهش مصرف منابع</p>
                            </div>
                        </div>
                        <label class="admin-switch"><input type="checkbox" name="error_tracking_enabled" value="1" class="admin-switch-input" @checked(old('error_tracking_enabled', $settings['error_tracking_enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-sm font-bold px-1 text-slate">TTL کش گزارش‌ها (ثانیه)</label>
                            <input type="number" name="report_cache_seconds" min="10" max="3600" value="{{ old('report_cache_seconds', $settings['report_cache_seconds'] ?? 60) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-bold px-1 text-slate">پنجره کاربران زنده (دقیقه)</label>
                            <input type="number" name="live_user_window_minutes" min="1" max="60" value="{{ old('live_user_window_minutes', $settings['live_user_window_minutes'] ?? 5) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-bold px-1 text-slate">نگهداری رویداد خام (روز)</label>
                            <input type="number" name="raw_event_retention_days" min="1" max="365" value="{{ old('raw_event_retention_days', $settings['raw_event_retention_days'] ?? 90) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-bold px-1 text-slate">نگهداری آمار تجمیعی (روز)</label>
                            <input type="number" name="stats_retention_days" min="30" max="1825" value="{{ old('stats_retention_days', $settings['stats_retention_days'] ?? 365) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" required>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate/10">
                        <button class="admin-btn !bg-success !text-white w-full sm:w-auto px-10 h-12 justify-center">
                            <span class="material-icons">save</span>
                            ذخیره تنظیمات
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

        </div>
    </div>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول آنالیزور</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>ماژول آنالیزور، سامانه گزارش حرفه‌ای بازدیدها و رفتار کاربران در پلتفرم kalands.ir است. این ماژول با ثبت رویدادهای صفحه‌ای، اهداف (Goals)، کلیک‌های افیلیت و خطاهای سمت کاربر، تحلیل کامل‌تری از عملکرد سایت ارائه می‌دهد.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-tracking">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">code</span>
                                اسکریپت رهگیری
                            </h3>
                            <p>اسکریپت رهگیری به صورت خودکار در تمامی صفحات سایت بارگذاری می‌شود و رویدادهای زیر را ثبت می‌کند:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>Pageview:</b> شمارش بازدید هر صفحه</li>
                                <li><b>Goal:</b> ثبت تحقق اهداف تعریف شده (کلیک روی دکمه، ارسال فرم و ...)</li>
                                <li><b>Error:</b> ثبت خطاهای جاوااسکریپت سمت کاربر</li>
                            </ul>
                            <p class="mt-3">اسکریپت با استفاده از <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">fetch</code> سبک ارسال داده می‌کند و تاثیر منفی بر سرعت صفحه ندارد.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-goals">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">touch_app</span>
                                اهداف (Goals)
                            </h3>
                            <p>اهداف برای ثبت رویدادهای مهم سایت تعریف می‌شوند. در این پلتفرم اهداف زیر به صورت پیش‌فرض فعال هستند:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>کلیک دیجی‌کالا:</b> هر کلیک روی لینک‌های افیلیت دیجی‌کالا</li>
                                <li><b>کلیک باسلام:</b> هر کلیک روی لینک‌های افیلیت باسلام</li>
                            </ul>
                            <p class="mt-3">تانش اهداف با مقایسه روزانه با روز قبل نمایش داده می‌شود.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-live">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">sensors</span>
                                کاربران زنده
                            </h3>
                            <p>این بخش بازدیدکنندگان فعال در چند دقیقه اخیر را و به صورت خودکار هر ۳۰ ثانیه به‌روز می‌شود. روی نقشه زنده می‌توانید mکان تقریبی کاربران فعال را مشاهده کنید.</p>
                            <p class="mt-3">پنجره زمانی کاربران زنده قابل تنظیم است (پیش‌فرض ۵ دقیقه).</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-retention">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">schedule</span>
                                نگهداری داده‌ها
                            </h3>
                            <p>برای مدیریت مصرف فضای دیتابیس، می‌توانید دوره نگهداری داده‌های خام و آمار تجمیعی را تنظیم کنید:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>رویداد خام:</b> داده‌های اولیه هر بازدید (پیش‌فرض ۹۰ روز)</li>
                                <li><b>آمار تجمیعی:</b> گزارش‌های دوره‌ای محاسبه شده (پیش‌فرض ۳۶۵ روز)</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-export">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">download</span>
                                خروجی و ورودی داده‌ها
                            </h3>
                            <p>امکان خروجی گرفتن از تنظیمات و آمار تجمیعی به صورت JSON و درون‌ریزی مجدد آن‌ها فراهم است. این قابلیت برای migrate داده‌ها بین محیط‌های توسعه و تولید مفید است.</p>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-tracking" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">اسکریپت رهگیری</a>
                        <a href="#doc-goals" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">اهداف</a>
                        <a href="#doc-live" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">کاربران زنده</a>
                        <a href="#doc-retention" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نگهداری داده‌ها</a>
                        <a href="#doc-export" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">خروجی و ورودی</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    @vite(['resources/js/admin-analytics.js', 'resources/js/admin-analytics-hub.js'])
</x-layouts.admin-dashboard>
