<x-layouts.admin-dashboard title="آنالیزور" :helpModuleKey="'analytics'">
    <div>
        <h1 class="admin-page-title !mb-1">آنالیزور</h1>
        <p class="text-sm text-slate/60">گزارش حرفه‌ای بازدیدها، اهداف، دستگاه‌ها، کشورها و رفتار کاربران</p>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="analytics-tabs">
            <button class="px-5 py-3.5 text-sm font-bold transition-colors border-b-2 border-success text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-overview">
                <span class="material-icons text-lg">dashboard</span>
                <span class="hidden sm:inline">داشبورد</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-live">
                <span class="material-icons text-lg">sensors</span>
                <span class="hidden sm:inline">زنده</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-analytics">
                <span class="material-icons text-lg">bar_chart</span>
                <span class="hidden sm:inline">آنالیز</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-goals">
                <span class="material-icons text-lg">emoji_events</span>
                <span class="hidden sm:inline">اهداف</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-management">
                <span class="material-icons text-lg">admin_panel_settings</span>
                <span class="hidden sm:inline">مدیریت</span>
            </button>
        </div>
    </div>

    <div id="analytics-hub-root"
         data-report-url="{{ route('dash.admin.analytics_report', ['authkey' => $authkey]) }}"
         data-user-activity-url="{{ route('dash.admin.analytics_user_details', ['authkey' => $authkey, 'userId' => ':id']) }}"
         data-user-journey-url="{{ route('dash.admin.analytics_user_journey', ['authkey' => $authkey]) }}">

        <div class="admin-card mb-6 !p-0 overflow-hidden hidden" id="analytics-filter-card">
            <form data-analytics-filters>
                <div class="px-4 pt-3 pb-1">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-icons text-success text-base">filter_alt</span>
                        <p class="text-sm font-bold text-slate" id="analytics-filter-title">فیلترها</p>
                        <p class="text-[11px] text-slate/50 mr-1" id="analytics-filter-description">فیلترهای مرتبط با تب فعال</p>
                    </div>
                </div>
                <div class="px-4 pb-4" id="analytics-filter-panel">
                    <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-5" id="analytics-filter-primary">
                        <div class="space-y-1" data-filter-field="from">
                            <label class="text-xs font-bold text-slate">از تاریخ</label>
                            <div data-shamsi-datepicker="from">
                                <input type="text" name="from" placeholder="۱۴۰۳/۰۱/۰۱" autocomplete="off" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                        </div>
                        <div class="space-y-1" data-filter-field="to">
                            <label class="text-xs font-bold text-slate">تا تاریخ</label>
                            <div data-shamsi-datepicker="to">
                                <input type="text" name="to" placeholder="۱۴۰۳/۰۱/۰۱" autocomplete="off" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                        </div>
                        <div class="space-y-1" data-filter-field="period">
                            <label class="text-xs font-bold text-slate">بازه تحلیلی</label>
                            <select name="period" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="day">روزانه</option>
                                <option value="week">هفتگی</option>
                                <option value="month">ماهانه</option>
                            </select>
                        </div>
                        <div class="space-y-1" data-filter-field="country">
                            <label class="text-xs font-bold text-slate">کشور</label>
                            <select name="country" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="">همه</option>
                                <option value="IR">🇮🇷 ایران</option>
                                <option value="US">🇺🇸 آمریکا</option>
                                <option value="CA">🇨🇦 کانادا</option>
                                <option value="GB">🇬🇧 انگلیس</option>
                                <option value="DE">🇩🇪 آلمان</option>
                                <option value="FR">🇫🇷 فرانسه</option>
                                <option value="IT">🇮🇹 ایتالیا</option>
                                <option value="ES">🇪🇸 اسپانیا</option>
                                <option value="NL">🇳🇱 هلند</option>
                                <option value="RU">🇷🇺 روسیه</option>
                                <option value="TR">🇹🇷 ترکیه</option>
                                <option value="AE">🇦🇪 امارات</option>
                                <option value="SA">🇸🇦 عربستان</option>
                                <option value="IQ">🇮🇶 عراق</option>
                                <option value="CN">🇨🇳 چین</option>
                                <option value="JP">🇯🇵 ژاپن</option>
                                <option value="KR">🇰🇷 کره</option>
                                <option value="IN">🇮🇳 هند</option>
                                <option value="PK">🇵🇰 پاکستان</option>
                                <option value="AU">🇦🇺 استرالیا</option>
                                <option value="BR">🇧🇷 برزیل</option>
                                <option value="MX">🇲🇽 مکزیک</option>
                                <option value="ZA">🇿🇦 آفریقای جنوبی</option>
                                <option value="EG">🇪🇬 مصر</option>
                                <option value="NG">🇳🇬 نیجریه</option>
                            </select>
                        </div>
                        <div class="space-y-1" data-filter-field="device_type">
                            <label class="text-xs font-bold text-slate">نوع دستگاه</label>
                            <select name="device_type" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="">همه</option>
                                <option value="desktop">دسکتاپ</option>
                                <option value="mobile">موبایل</option>
                                <option value="tablet">تبلت</option>
                            </select>
                        </div>
                        <div class="space-y-1" data-filter-field="activity">
                            <label class="text-xs font-bold text-slate">نوع رویداد</label>
                            <select name="activity" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="">همه</option>
                                <option value="pageview">بازدید صفحه</option>
                                <option value="goal">تحقق هدف</option>
                                <option value="error">خطا</option>
                            </select>
                        </div>
                        <div class="space-y-1" data-filter-field="source">
                            <label class="text-xs font-bold text-slate">سورس / Referrer</label>
                            <input type="text" name="source" placeholder="google / direct / instagram" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="campaign">
                            <label class="text-xs font-bold text-slate">کمپین / UTM</label>
                            <input type="text" name="campaign" placeholder="spring_sale / cpc" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="path">
                            <label class="text-xs font-bold text-slate">مسیر / URL</label>
                            <input type="text" name="path" placeholder="/product /result /seller" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="goal_key">
                            <label class="text-xs font-bold text-slate">Goal Key</label>
                            <input type="text" name="goal_key" placeholder="tr_dk / tr_bs / ..." class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="browser">
                            <label class="text-xs font-bold text-slate">مرورگر</label>
                            <input type="text" name="browser" placeholder="Chrome / Safari / Firefox" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="platform">
                            <label class="text-xs font-bold text-slate">سیستم‌عامل</label>
                            <input type="text" name="platform" placeholder="Windows / Android / iOS" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="session_status">
                            <label class="text-xs font-bold text-slate">وضعیت جلسه</label>
                            <select name="session_status" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="">همه</option>
                                <option value="new">اولین جلسه</option>
                                <option value="returning">بازگشتی</option>
                                <option value="bounce">پرش‌دار</option>
                                <option value="long">بیش از ۵ دقیقه</option>
                            </select>
                        </div>
                        <div class="space-y-1" data-filter-field="search">
                            <label class="text-xs font-bold text-slate">جستجوی آزاد</label>
                            <input type="search" name="search" placeholder="مسیر، عنوان، شهر، مرورگر..." class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-slate/10">
                        <button type="reset" id="analytics-filter-reset" class="admin-btn admin-btn-secondary h-9 px-3 text-xs">
                            <span class="material-icons text-base">restart_alt</span>
                            <span>پاک‌کردن</span>
                        </button>
                        <button class="admin-btn !bg-success !text-white h-9 px-4 text-xs" type="submit">
                            <span class="material-icons text-base">search</span>
                            <span>اعمال فیلتر</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ═══ تب داشبورد ═══ --}}
        <div id="tab-overview" class="analytics-tab-content space-y-5">
            <div class="grid gap-3 sm:gap-4 grid-cols-2 sm:grid-cols-4">
                <div class="admin-card text-center">
                    <p class="text-[10px] text-slate opacity-60 mb-0.5">بازدید امروز</p>
                    <p class="text-xl sm:text-2xl font-bold" data-stat="today">...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-[10px] text-slate opacity-60 mb-0.5">بازدید ۷ روز</p>
                    <p class="text-xl sm:text-2xl font-bold" data-stat="week">...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-[10px] text-slate opacity-60 mb-0.5">بازدید ۳۰ روز</p>
                    <p class="text-xl sm:text-2xl font-bold" data-stat="month">...</p>
                </div>
                <div class="admin-card text-center border-success/40 overflow-hidden relative" id="overview-live-card">
                    <div id="overview-live-progress" class="absolute inset-y-0 right-0 w-full bg-success/10 transition-all duration-700 ease-linear"></div>
                    <div id="overview-live-status" class="absolute inset-0 bg-success/[0.03] transition-colors duration-500"></div>
                    <div class="relative z-10 px-1">
                        <p class="text-[10px] text-slate opacity-60 mb-0.5">کاربران زنده</p>
                        <p class="text-xl sm:text-2xl font-bold text-success" data-stat="live">...</p>
                        <p id="overview-live-delta" class="text-[10px] mt-1 text-slate/60">در انتظار اولین به‌روزرسانی</p>
                    </div>
                </div>
            </div>
            <div class="grid gap-3 sm:gap-4 grid-cols-2 sm:grid-cols-4">
                <div class="admin-card text-center">
                    <span class="material-icons text-lg opacity-30 mb-0.5">person_pin</span>
                    <p class="text-[10px] text-slate opacity-60 mb-0.5">بازدیدکننده یکتا (امروز)</p>
                    <p class="text-xl sm:text-2xl font-bold text-primary" data-stat="today-uniques">...</p>
                </div>
                <div class="admin-card text-center">
                    <span class="material-icons text-lg opacity-30 mb-0.5">group</span>
                    <p class="text-[10px] text-slate opacity-60 mb-0.5">جلسات امروز</p>
                    <p class="text-xl sm:text-2xl font-bold" data-stat="today-sessions">...</p>
                </div>
                <div class="admin-card text-center">
                    <span class="material-icons text-lg opacity-30 mb-0.5">call_split</span>
                    <p class="text-[10px] text-slate opacity-60 mb-0.5">نرخ پرش</p>
                    <p class="text-xl sm:text-2xl font-bold text-amber-500" data-stat="bounce-rate">...</p>
                </div>
                <div class="admin-card text-center">
                    <span class="material-icons text-lg opacity-30 mb-0.5">timer</span>
                    <p class="text-[10px] text-slate opacity-60 mb-0.5">مدت متوسط جلسه</p>
                    <p class="text-xl sm:text-2xl font-bold text-info" data-stat="avg-duration">...</p>
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

        {{-- ═══ تب زنده ═══ --}}
        <div id="tab-live" class="analytics-tab-content hidden space-y-5">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="admin-card border-success/40 overflow-hidden relative" id="live-summary-card">
                    <div id="live-summary-progress" class="absolute inset-y-0 right-0 w-full bg-success/10 transition-all duration-700 ease-linear"></div>
                    <div id="live-summary-status" class="absolute inset-0 bg-success/[0.03] transition-colors duration-500"></div>
                    <div class="relative z-10 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">کاربران زنده</p>
                            <p id="live-summary-users" class="mt-1 text-3xl font-black text-success">0</p>
                            <p id="live-summary-delta" class="text-[10px] mt-1 text-slate/60">در انتظار اولین به‌روزرسانی</p>
                        </div>
                        <span class="material-icons text-success/70 text-3xl">sensors</span>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">بیشترین مرورگر در حال استفاده</p>
                            <div id="live-summary-browser" class="mt-1 flex items-center gap-2 text-info">
                                <span id="live-summary-browser-icon" class="shrink-0"></span>
                                <p id="live-summary-browser-name" class="text-xl font-black truncate">-</p>
                            </div>
                        </div>
                        <span class="material-icons text-info/70 text-3xl">language</span>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">Goal های ۱۰ دقیقه اخیر</p>
                            <p id="live-summary-goals" class="mt-1 text-3xl font-black text-primary">0</p>
                        </div>
                        <span class="material-icons text-primary/70 text-3xl">emoji_events</span>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">غالب‌ترین منبع</p>
                            <p id="live-summary-top-source" class="mt-1 text-lg font-black text-amber-600 dark:text-amber-300 truncate">-</p>
                        </div>
                        <span class="material-icons text-amber-500/70 text-3xl">traffic</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-12">
                <div class="xl:col-span-6 space-y-4 min-w-0">
                    <div class="admin-card !p-0 overflow-hidden border-success/20">
                        <div class="flex items-center justify-between gap-4 p-4 border-b border-slate/10 bg-slate/5">
                            <div>
                                <h3 class="font-bold text-slate flex items-center gap-2">
                                    <span class="material-icons text-success">public</span>
                                    <span>نقشه زنده بازدیدها</span>
                                </h3>
                                <p class="text-xs text-slate/55 mt-1">نمای بلادرنگ پراکندگی کاربران فعال و توزیع کشورها</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 shadow-sm dark:border-slate-700 dark:bg-slate-950">
                                    <button type="button" data-live-map-mode="2d" class="flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-black text-white shadow-sm ring-1 ring-emerald-500/20 transition-all dark:bg-emerald-400 dark:text-slate-950 dark:ring-emerald-300/30">
                                        <span class="material-icons text-sm">map</span>
                                        <span>2D</span>
                                    </button>
                                    <button type="button" data-live-map-mode="3d" class="flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-black text-slate-600 transition-all hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                                        <span class="material-icons text-sm">language</span>
                                        <span>3D</span>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 rounded-xl border border-slate/10 bg-white/70 px-3 py-2 dark:bg-slate-950/70">
                                    <span class="text-xs text-slate/60 hidden sm:inline">به‌روزرسانی هر ۳۰ ثانیه</span>
                                    <span id="live-update-indicator" class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                                </div>
                            </div>
                        </div>
                        <div id="live-map-container" class="relative bg-slate/5 h-[420px] lg:h-[520px] w-full">
                            <div data-map="live" class="w-full h-full">
                                <div class="flex flex-col items-center justify-center h-full gap-4 opacity-50">
                                    <div class="w-12 h-12 border-4 border-success/20 border-t-success rounded-full animate-spin"></div>
                                    <p class="text-sm">در حال بارگذاری نقشه زنده...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">flag</span>
                                    <span>کشورهای فعال</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Top realtime</span>
                            </div>
                            <div id="live-country-breakdown" class="space-y-2 min-h-[180px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">hub</span>
                                    <span>منابع ورود</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Search / UTM / Referrer</span>
                            </div>
                            <div id="live-source-breakdown" class="space-y-2 min-h-[180px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-6 space-y-4 min-w-0">
                    <div class="admin-card !p-0 overflow-hidden border-slate/10">
                        <div class="p-4 border-b border-slate/10 bg-slate/5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-slate flex items-center gap-2">
                                        <span class="material-icons text-success">sensors</span>
                                        <span>کاربران فعال در چند دقیقه اخیر</span>
                                        <span id="live-users-count" class="ml-2 px-2 py-0.5 rounded-full bg-success/10 text-success text-xs font-bold">0</span>
                                    </h3>
                                    <p class="text-xs text-slate/55 mt-1">۱۰ سشن اخیر نمایش داده می‌شود. برای مشاهده جزئیات کامل روی هر آیتم کلیک کنید.</p>
                                </div>
                            </div>
                        </div>
                        <div id="live-users-list" data-live-list class="min-h-[680px] max-h-[760px] overflow-y-visible xl:overflow-y-auto custom-scrollbar p-4 space-y-4">
                            <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">devices</span>
                                    <span>دستگاه‌ها</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">برند / نوع</span>
                            </div>
                            <div id="live-device-breakdown" class="space-y-2 min-h-[170px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">language</span>
                                    <span>مرورگرها</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Browser / Platform</span>
                            </div>
                            <div id="live-browser-breakdown" class="space-y-2 min-h-[170px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <dialog id="live-user-detail-modal" class="admin-dialog w-[99vw] max-w-[99vw] h-[98vh] max-h-[98vh]">
            <div class="admin-dialog-body !p-0">
                <div class="admin-dialog-head p-4 border-b border-slate/10">
                    <h3 class="admin-dialog-title flex items-center gap-2">
                        <span class="material-icons text-success">person_pin</span>
                        <span id="live-user-detail-title">جزئیات کاربر زنده</span>
                    </h3>
                    <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle !w-8 !h-8 !rounded-lg border-none hover:bg-slate/10">
                        <span class="material-icons text-sm">close</span>
                    </button>
                </div>
                <div class="overflow-y-auto h-[calc(98vh-88px)] custom-scrollbar px-1" id="live-user-detail-content">
                    <p class="text-center py-10 opacity-50">در حال دریافت اطلاعات...</p>
                </div>
            </div>
        </dialog>

        {{-- ═══ تب آنالیز (ادغام گزارش‌ها + محتوا) ═══ --}}
        <div id="tab-analytics" class="analytics-tab-content hidden space-y-5">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="admin-card border-success/20 bg-success/[0.03]">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">کاربران زنده</p>
                            <p class="mt-1 text-3xl font-black text-success" data-stat="live">0</p>
                        </div>
                        <span class="material-icons text-success/70 text-3xl">sensors</span>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">کشورهای فعال</p>
                            <p class="mt-1 text-3xl font-black text-info" data-reports-country-count>0</p>
                        </div>
                        <span class="material-icons text-info/70 text-3xl">flag</span>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">مرورگرهای شاخص</p>
                            <p class="mt-1 text-3xl font-black text-primary" data-reports-browser-count>0</p>
                        </div>
                        <span class="material-icons text-primary/70 text-3xl">language</span>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] text-slate/50">سورس‌های شاخص</p>
                            <p class="mt-1 text-3xl font-black text-amber-600 dark:text-amber-300" data-reports-source-count>0</p>
                        </div>
                        <span class="material-icons text-amber-500/70 text-3xl">traffic</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-12">
                <div class="xl:col-span-6 space-y-4 min-w-0">
                    <div class="admin-card !p-0 overflow-hidden border-success/20">
                        <div class="flex items-center justify-between gap-4 p-4 border-b border-slate/10 bg-slate/5">
                            <div>
                                <h3 class="font-bold text-slate flex items-center gap-2">
                                    <span class="material-icons text-success">public</span>
                                    <span>نقشه جهانی بازدیدها</span>
                                </h3>
                                <p class="text-xs text-slate/55 mt-1">پراکندگی جغرافیایی، کشورها و منبع‌های شاخص در بازه انتخابی</p>
                            </div>
                        </div>
                        <div id="reports-map-container" class="relative bg-slate/5 h-[420px] lg:h-[520px] w-full">
                            <div data-map="reports" class="w-full h-full">
                                <div class="flex flex-col items-center justify-center h-full gap-4 opacity-50">
                                    <div class="w-10 h-10 border-4 border-success/20 border-t-success rounded-full animate-spin"></div>
                                    <p class="text-sm">در حال بارگذاری نقشه...</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">flag</span>
                                    <span>کشورها</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Geo mix</span>
                            </div>
                            <div data-list="countries" class="space-y-2 min-h-[180px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">hub</span>
                                    <span>منابع ترافیک</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Referral mix</span>
                            </div>
                            <div data-list="referrers" class="space-y-2 min-h-[180px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-6 space-y-4 min-w-0">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">search</span>
                                    <span>موتورهای جستجو</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Search mix</span>
                            </div>
                            <div data-list="search-engines" class="space-y-2 min-h-[170px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">devices</span>
                                    <span>نوع دستگاه</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Device type</span>
                            </div>
                            <div data-list="device-types" class="space-y-2 min-h-[170px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">smartphone</span>
                                    <span>برند دستگاه</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Brand mix</span>
                            </div>
                            <div data-list="device-brands" class="space-y-2 min-h-[170px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                        <div class="admin-card">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                    <span class="material-icons text-success text-base">language</span>
                                    <span>مرورگرها</span>
                                </h3>
                                <span class="text-[10px] text-slate/50">Browser mix</span>
                            </div>
                            <div data-list="browsers" class="space-y-2 min-h-[170px]">
                                <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <h3 class="font-bold text-slate flex items-center gap-2 text-sm">
                                <span class="material-icons text-success text-base">developer_board</span>
                                <span>سیستم‌عامل</span>
                            </h3>
                            <span class="text-[10px] text-slate/50">Platform mix</span>
                        </div>
                        <div data-list="platforms" class="space-y-2 min-h-[170px]">
                            <p class="text-center py-10 opacity-50 text-xs">در حال بارگذاری...</p>
                        </div>
                    </div>
                </div>
            </div>

            <details class="admin-card group">
                <summary class="font-bold text-slate flex items-center gap-2 text-sm cursor-pointer select-none [&::-webkit-details-marker]:hidden">
                    <span class="material-icons text-success text-lg group-open:rotate-180 transition-transform">expand_more</span>
                    <span class="material-icons text-amber-500">campaign</span>
                    <span>بازاریابی (UTM)</span>
                    <span class="text-[10px] text-slate/40 font-normal">منبع · رسانه · کمپین</span>
                </summary>
                <div class="grid gap-4 md:grid-cols-3 mt-4 border-t border-slate/10 pt-4">
                    <div>
                        <h4 class="text-xs font-bold text-slate/70 mb-3">منبع (Source)</h4>
                        <div data-list="utm-sources" class="space-y-2">
                            <p class="text-center py-6 opacity-50 text-xs">در حال بارگذاری...</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate/70 mb-3">رسانه (Medium)</h4>
                        <div data-list="utm-mediums" class="space-y-2">
                            <p class="text-center py-6 opacity-50 text-xs">در حال بارگذاری...</p>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate/70 mb-3">کمپین (Campaign)</h4>
                        <div data-list="utm-campaigns" class="space-y-2">
                            <p class="text-center py-6 opacity-50 text-xs">در حال بارگذاری...</p>
                        </div>
                    </div>
                </div>
            </details>

            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2 text-sm border-b border-slate/10 pb-2">
                    <span class="material-icons text-success text-lg">timeline</span>
                    <span>فعالیت‌ها</span>
                </h3>
                <div data-list="activities" class="grid gap-3 md:grid-cols-3">
                    <p class="text-center py-8 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
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

        {{-- ═══ تب اهداف ═══ --}}
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

        {{-- ═══ تب مدیریت (ادغام تنظیمات + نگهداری) ═══ --}}
        <div id="tab-management" class="analytics-tab-content hidden space-y-6">

            <form action="{{ route('dash.admin.analytics_settings_save', ['authkey' => $authkey]) }}" method="POST" class="space-y-4">
                @csrf

                <x-admin.form-section title="ردیابی و جمع‌آوری داده" icon="track_changes" description="کنترل اینکه چه داده‌هایی از بازدیدکنندگان جمع‌آوری شود">
                    <x-admin.toggle-row
                        name="enabled"
                        label="فعالسازی آنالیزور"
                        description="ثبت رویدادها و نمایش گزارش‌ها در پنل ادمین"
                        :checked="old('enabled', $settings['enabled'] ?? true)"
                    />
                    <x-admin.toggle-row
                        name="tracking_script_enabled"
                        label="اسکریپت رهگیری"
                        description="ارسال pageview و goal با sendBeacon، fetch و pixel fallback"
                        :checked="old('tracking_script_enabled', $settings['tracking_script_enabled'] ?? true)"
                    />
                    <x-admin.toggle-row
                        name="error_tracking_enabled"
                        label="Error Tracker"
                        description="ثبت خطاهای سمت کاربر — حداکثر ۵ خطا در هر نشست"
                        badge="JS"
                        :checked="old('error_tracking_enabled', $settings['error_tracking_enabled'] ?? true)"
                    />
                    <x-admin.toggle-row
                        name="heartbeat_enabled"
                        label="Heartbeat"
                        description="ارسال سیگنال هر ۳۰ ثانیه برای محاسبه دقیق مدت جلسه"
                        :checked="old('heartbeat_enabled', $settings['heartbeat_enabled'] ?? true)"
                    />
                    <x-admin.toggle-row
                        name="alerting_enabled"
                        label="هشدار خودکار"
                        description="بررسی ناهنجاری‌های آماری پس از هر Aggregation"
                        :checked="old('alerting_enabled', $settings['alerting_enabled'] ?? true)"
                    />
                </x-admin.form-section>

                <x-admin.form-section title="عملکرد و نگهداری" icon="speed" description="تنظیم مقادیر کارایی، زمان‌بندی نشست‌ها و دوره نگهداری داده‌ها">
                    <x-admin.number-field
                        name="report_cache_seconds"
                        label="TTL کش گزارش‌ها"
                        description="مدت زمان cache ماندن نتایج API — کاهش فشار روی دیتابیس"
                        :value="old('report_cache_seconds', $settings['report_cache_seconds'] ?? 60)"
                        min="10" max="3600" unit="ثانیه" required
                    />
                    <x-admin.number-field
                        name="live_user_window_minutes"
                        label="پنجره کاربران زنده"
                        description="بازدیدکنندگانی که در این مدت فعال بوده‌اند زنده شمرده می‌شوند"
                        :value="old('live_user_window_minutes', $settings['live_user_window_minutes'] ?? 5)"
                        min="1" max="60" unit="دقیقه" required
                    />
                    <x-admin.number-field
                        name="session_timeout_minutes"
                        label="مهلت پایان نشست"
                        description="بعد از این مدت بی‌فعالیت، جلسه کاربر بسته می‌شود"
                        :value="old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? 30)"
                        min="5" max="120" unit="دقیقه" required
                    />
                    <x-admin.number-field
                        name="raw_event_retention_days"
                        label="نگهداری رویداد خام"
                        description="رویدادهای قدیمی‌تر از این مدت به‌طور خودکار پاکسازی می‌شوند"
                        :value="old('raw_event_retention_days', $settings['raw_event_retention_days'] ?? 90)"
                        min="1" max="365" unit="روز" required
                    />
                    <x-admin.number-field
                        name="stats_retention_days"
                        label="نگهداری آمار تجمیعی"
                        description="آمار روزانه تجمیع‌شده تا این مدت حفظ می‌شوند"
                        :value="old('stats_retention_days', $settings['stats_retention_days'] ?? 365)"
                        min="30" max="1825" unit="روز" required
                    />
                </x-admin.form-section>

                <div class="flex justify-end">
                    <button type="submit" class="admin-btn !bg-success !text-white px-8 h-10">
                        <span class="material-icons text-base">save</span>
                        ذخیره تنظیمات
                    </button>
                </div>
            </form>

            <div>
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-icons text-slate/40 text-lg">build</span>
                    <h3 class="font-bold text-sm text-slate">نگهداری داده‌ها</h3>
                    <span class="text-[11px] text-slate/40">پاکسازی، بکاپ و بازگردانی</span>
                </div>
                <div class="grid gap-4 md:grid-cols-3">

                    <form action="{{ route('dash.admin.analytics_prune', ['authkey' => $authkey]) }}" method="POST" class="h-full"
                          onsubmit="return confirm('آیا از حذف این داده‌ها اطمینان دارید؟ این عملیات غیرقابل بازگشت است.')">
                        @csrf
                        <x-admin.action-card title="پاکسازی داده‌ها" icon="auto_delete" variant="danger"
                            description="حذف رویدادها و جلسات قدیمی برای آزاد کردن فضای دیتابیس. غیرقابل بازگشت.">
                            <x-slot:body>
                                <x-admin.select-field
                                    name="type"
                                    label="نوع داده"
                                    :options="['errors' => 'فقط گزارش‌های خطا', 'sessions' => 'جلسات قدیمی', 'all' => 'همه رویدادها']"
                                />
                                <x-admin.select-field
                                    name="older_than"
                                    label="بازه زمانی"
                                    :options="['all' => 'همه زمان‌ها', '30' => 'قدیمی‌تر از ۱ ماه', '90' => 'قدیمی‌تر از ۳ ماه', '180' => 'قدیمی‌تر از ۶ ماه', '365' => 'قدیمی‌تر از ۱ سال']"
                                />
                            </x-slot:body>
                            <x-slot:action>
                                <button type="submit" class="admin-btn !bg-danger !text-white w-full justify-center">
                                    <span class="material-icons text-base">delete_forever</span>
                                    شروع پاکسازی
                                </button>
                            </x-slot:action>
                        </x-admin.action-card>
                    </form>

                    <x-admin.action-card title="Export آمار" icon="file_download" variant="success"
                        description="دریافت فایل JSON از تنظیمات و آمار تجمیعی — مناسب برای بکاپ یا انتقال به سرور دیگر.">
                        <x-slot:action>
                            <a href="{{ route('dash.admin.analytics_export', ['authkey' => $authkey]) }}"
                               class="admin-btn !bg-success/10 !text-success !border-success/20 w-full justify-center">
                                <span class="material-icons text-base">download</span>
                                دریافت فایل JSON
                            </a>
                        </x-slot:action>
                    </x-admin.action-card>

                    <form action="{{ route('dash.admin.analytics_import', ['authkey' => $authkey]) }}" method="POST" enctype="multipart/form-data" class="h-full">
                        @csrf
                        <x-admin.action-card title="Import آمار" icon="file_upload" variant="info"
                            description="بارگذاری فایل JSON برای بازگردانی آمار تجمیعی و تنظیمات.">
                            <x-slot:body>
                                <label class="block">
                                    <span class="text-[11px] font-medium text-slate/60 mb-1.5 block">فایل JSON</span>
                                    <input type="file" name="analytics_file" accept="application/json,.json" required
                                        class="block w-full text-xs text-slate/70 dark:text-white/50 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-slate/10 file:text-slate dark:file:bg-white/10 dark:file:text-white/70 hover:file:bg-slate/20 cursor-pointer">
                                </label>
                            </x-slot:body>
                            <x-slot:action>
                                <button type="submit" class="admin-btn !bg-info/10 !text-info !border-info/20 w-full justify-center">
                                    <span class="material-icons text-base">upload</span>
                                    درون‌ریزی فایل
                                </button>
                            </x-slot:action>
                        </x-admin.action-card>
                    </form>

                </div>
            </div>
        </div>

    </div>

    @vite(['resources/js/admin-analytics.js', 'resources/js/admin-analytics-hub.js'])
</x-layouts.admin-dashboard>
