<x-layouts.admin-dashboard title="آنالیزور" :helpModuleKey="'analytics'">
    <div>
        <h1 class="admin-page-title !mb-1">آنالیزور</h1>
        <p class="text-sm text-slate/60">گزارش حرفه‌ای بازدیدها، اهداف، دستگاه‌ها، کشورها و رفتار کاربران</p>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="analytics-tabs">
            <button class="px-5 py-3.5 text-sm font-bold transition-colors border-b-2 border-success text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-overview">
                <span class="material-icons text-lg">dashboard</span>
                <span class="hidden sm:inline">نمای کلی</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-live">
                <span class="material-icons text-lg">sensors</span>
                <span class="hidden sm:inline">زنده</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-reports">
                <span class="material-icons text-lg">public</span>
                <span class="hidden sm:inline">گزارش‌ها</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-content">
                <span class="material-icons text-lg">inventory_2</span>
                <span class="hidden sm:inline">محتوا</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-goals">
                <span class="material-icons text-lg">touch_app</span>
                <span class="hidden sm:inline">اهداف</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-funnels">
                <span class="material-icons text-lg">filter_alt</span>
                <span class="hidden sm:inline">قیف</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-sessions">
                <span class="material-icons text-lg">timeline</span>
                <span class="hidden sm:inline">جلسات</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-users">
                <span class="material-icons text-lg">manage_search</span>
                <span class="hidden sm:inline">کاربران</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-errors">
                <span class="material-icons text-lg">bug_report</span>
                <span class="hidden sm:inline">خطاها</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-cohort">
                <span class="material-icons text-lg">group_work</span>
                <span class="hidden sm:inline">هم‌گروه</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-compare">
                <span class="material-icons text-lg">compare_arrows</span>
                <span class="hidden sm:inline">مقایسه</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-raw">
                <span class="material-icons text-lg">explore</span>
                <span class="hidden sm:inline">خام</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-maintenance">
                <span class="material-icons text-lg">cleaning_services</span>
                <span class="hidden sm:inline">نگهداری</span>
            </button>
            <button class="px-5 py-3.5 text-sm font-medium transition-colors text-slate hover:text-success flex items-center gap-1.5 shrink-0" data-tab-target="tab-settings">
                <span class="material-icons text-lg">settings</span>
                <span class="hidden sm:inline">تنظیمات</span>
            </button>
        </div>
    </div>

    <div id="analytics-hub-root"
         data-report-url="{{ route('dash.admin.analytics_report', ['authkey' => $authkey]) }}"
         data-user-activity-url="{{ route('dash.admin.analytics_user_details', ['authkey' => $authkey, 'userId' => ':id']) }}"
         data-user-journey-url="{{ route('dash.admin.analytics_user_journey', ['authkey' => $authkey]) }}"
         data-funnel-delete-url="{{ route('dash.admin.analytics_funnels_delete', ['authkey' => $authkey, 'key' => ':key']) }}">
        <div class="admin-card mb-6 !p-0 overflow-hidden hidden" id="analytics-filter-card">
            <form data-analytics-filters>
                <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate/10 bg-slate/5">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-success text-base">filter_alt</span>
                            <p class="text-sm font-bold text-slate" id="analytics-filter-title">فیلترهای گزارش</p>
                        </div>
                        <p class="text-[11px] text-slate/55 mt-1" id="analytics-filter-description">فقط فیلترهای مرتبط با تب فعال نمایش داده می‌شوند.</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" id="analytics-filter-toggle" class="admin-btn admin-btn-secondary h-10 px-3 text-xs">
                            <span class="material-icons text-base" id="analytics-filter-toggle-icon">expand_more</span>
                            <span id="analytics-filter-toggle-text">فیلتر</span>
                        </button>
                        <button class="admin-btn !bg-success !text-white h-10 px-3" type="submit">
                            <span class="material-icons text-base">search</span>
                        </button>
                    </div>
                </div>

                <div class="px-4 py-4 hidden" id="analytics-filter-panel">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4" id="analytics-filter-primary">
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
                        <div class="space-y-1" data-filter-field="search">
                            <label class="text-xs font-bold text-slate">جستجوی آزاد</label>
                            <input type="search" name="search" placeholder="مسیر، عنوان، شهر، مرورگر..." class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
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
                        <div class="space-y-1" data-filter-field="path">
                            <label class="text-xs font-bold text-slate">مسیر / URL</label>
                            <input type="text" name="path" placeholder="/product /result /seller" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="goal_key">
                            <label class="text-xs font-bold text-slate">Goal Key</label>
                            <input type="text" name="goal_key" placeholder="tr_dk / tr_bs / ..." class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="source">
                            <label class="text-xs font-bold text-slate">سورس / Referrer</label>
                            <input type="text" name="source" placeholder="google / direct / instagram" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1" data-filter-field="campaign">
                            <label class="text-xs font-bold text-slate">کمپین / UTM</label>
                            <input type="text" name="campaign" placeholder="spring_sale / cpc" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
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
                    </div>
                    <div class="flex items-center justify-between gap-3 mt-4 pt-4 border-t border-slate/10">
                        <p class="text-[11px] text-slate/50">فیلترها بر اساس تب فعال تغییر می‌کنند و فقط روی همان داده‌های مرتبط تمرکز دارند.</p>
                        <button type="reset" id="analytics-filter-reset" class="admin-btn admin-btn-secondary h-10 px-3 text-xs">
                            <span class="material-icons text-base">restart_alt</span>
                            <span>پاک‌کردن</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

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

        <div id="tab-reports" class="analytics-tab-content hidden space-y-5">
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
                    <span class="material-icons text-success">manage_search</span>
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

        <div id="tab-funnels" class="analytics-tab-content hidden space-y-6">
            <div class="admin-card">
                <div class="flex items-center justify-between gap-4 mb-4">
                    <h3 class="font-bold text-slate flex items-center gap-2">
                        <span class="material-icons text-success">filter_alt</span>
                        <span>قیف‌های تبدیل</span>
                    </h3>
                    <a href="#funnel-form" class="admin-btn !bg-success/10 !text-success !border-success/20 text-xs">
                        <span class="material-icons text-base">add</span>
                        قیف جدید
                    </a>
                </div>
                <div data-list="funnels" class="space-y-4">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>

            <div class="admin-card" id="funnel-form">
                <h3 class="font-bold text-slate mb-4">ایجاد قیف جدید</h3>
                <form action="{{ route('dash.admin.analytics_funnels_save', ['authkey' => $authkey]) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate">شناسه قیف (کلید)</label>
                            <input type="text" name="key" required maxlength="80" placeholder="product_to_cart" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate">نام قیف</label>
                            <input type="text" name="name" required maxlength="200" placeholder="از محصول تا سبد خرید" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate">مراحل قیف (JSON)</label>
                        <textarea name="steps" required rows="4" class="admin-ltr w-full rounded border border-slate p-2 text-sm font-mono dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder='[{"name":"مشاهده محصول"},{"name":"افزودن به سبد"},{"name":"کلیک دیجی‌کالا"}]'></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>فعال</span>
                    </label>
                    <button type="submit" class="admin-btn !bg-success !text-white">
                        <span class="material-icons">save</span>
                        ذخیره قیف
                    </button>
                </form>
            </div>
        </div>

        <div id="tab-sessions" class="analytics-tab-content hidden space-y-6">
            <div class="grid gap-4 md:grid-cols-4">
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">کل جلسات</p>
                    <p class="text-2xl font-bold" data-session-total>...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">نرخ پرش</p>
                    <p class="text-2xl font-bold text-amber-500" data-session-bounce>...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">مدت متوسط</p>
                    <p class="text-2xl font-bold text-info" data-session-duration>...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">صفحات در هر جلسه</p>
                    <p class="text-2xl font-bold" data-session-ppv>...</p>
                </div>
            </div>
            <div class="admin-card">
                <div data-list="sessions-by-device" class="space-y-3">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>
        </div>

        <div id="tab-cohort" class="analytics-tab-content hidden space-y-6">
            <div class="admin-card overflow-x-auto">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                    <span class="material-icons text-success">group_work</span>
                    <span>تحلیل هم‌گروه (Cohort) - نرخ بازگشت</span>
                </h3>
                <div data-cohort-table class="min-w-full">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
            </div>
        </div>

        <div id="tab-compare" class="analytics-tab-content hidden space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">بازه فعلی</p>
                    <p class="text-lg font-bold" data-compare-current-period>...</p>
                    <p class="text-3xl font-bold text-success mt-2" data-compare-current>...</p>
                </div>
                <div class="admin-card text-center">
                    <p class="text-xs text-slate opacity-60 mb-1">بازه قبلی</p>
                    <p class="text-lg font-bold" data-compare-previous-period>...</p>
                    <p class="text-3xl font-bold text-slate mt-2" data-compare-previous>...</p>
                </div>
            </div>
            <div class="admin-card text-center">
                <p class="text-xs text-slate opacity-60 mb-1">تغییرات</p>
                <p class="text-4xl font-black" data-compare-change>...</p>
            </div>
            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4">نمودار مقایسه</h3>
                <div class="min-h-72 flex items-center justify-center bg-slate/5 border border-dashed border-slate/20" data-chart="compare">
                    <p class="text-sm opacity-50">در حال بارگذاری...</p>
                </div>
            </div>
        </div>

        <div id="tab-raw" class="analytics-tab-content hidden space-y-6">
            <div class="flex items-center justify-between gap-4 mb-4">
                <h3 class="font-bold text-slate flex items-center gap-2">
                    <span class="material-icons text-success">explore</span>
                    <span>رویدادهای خام</span>
                </h3>
                <a href="{{ route('dash.admin.analytics_csv_export', ['authkey' => $authkey, 'section' => 'raw']) }}" class="admin-btn !bg-success/10 !text-success !border-success/20 text-xs" target="_blank">
                    <span class="material-icons text-base">file_download</span>
                    خروجی CSV
                </a>
            </div>
            <div class="admin-card">
                <div data-raw-events-filter class="grid gap-4 md:grid-cols-4 mb-4">
                    <input type="text" data-raw-filter="search" placeholder="جستجو..." class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                    <select data-raw-filter="event_type" class="w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                        <option value="">همه رویدادها</option>
                        <option value="pageview">بازدید</option>
                        <option value="goal">هدف</option>
                        <option value="error">خطا</option>
                    </select>
                    <input type="text" data-raw-filter="session_id" placeholder="شناسه جلسه..." class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                    <button class="admin-btn !bg-success !text-white" data-raw-search>
                        <span class="material-icons text-base">search</span>
                        جستجو
                    </button>
                </div>
                <div data-list="raw-events" class="space-y-2">
                    <p class="text-center py-10 opacity-50">در حال بارگذاری...</p>
                </div>
                <div data-raw-pagination class="flex items-center justify-center gap-2 mt-4"></div>
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
                                <option value="sessions">جلسات قدیمی</option>
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

        <div id="tab-settings" class="analytics-tab-content hidden space-y-5">
            <form action="{{ route('dash.admin.analytics_settings_save', ['authkey' => $authkey]) }}" method="POST" class="space-y-5">
                @csrf
                <div class="admin-card">
                    <div class="flex items-center justify-between gap-4 mb-5 pb-4 border-b border-slate/10">
                        <div class="flex items-center gap-3">
                            <div class="rounded bg-success/10 p-2 text-success">
                                <span class="material-icons">analytics</span>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate">تنظیمات عمومی</h2>
                                <p class="text-xs text-slate/60 mt-1">فعالسازی و پیکربندی ماژول آنالیزور</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-slate/5">
                            <div>
                                <p class="text-sm font-bold text-slate">فعالسازی آنالیزور</p>
                                <p class="text-xs text-slate/40">ثبت رویدادها و نمایش گزارش‌ها در پنل ادمین</p>
                            </div>
                            <label class="admin-switch"><input type="checkbox" name="enabled" value="1" class="admin-switch-input" @checked(old('enabled', $settings['enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-slate/5">
                            <div>
                                <p class="text-sm font-bold text-slate">اسکریپت رهگیری</p>
                                <p class="text-xs text-slate/40">ارسال pageview و goal با sendBeacon, fetch, pixel fallback</p>
                            </div>
                            <label class="admin-switch"><input type="checkbox" name="tracking_script_enabled" value="1" class="admin-switch-input" @checked(old('tracking_script_enabled', $settings['tracking_script_enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-slate/5">
                            <div>
                                <p class="text-sm font-bold text-slate">Error Tracker</p>
                                <p class="text-xs text-slate/40">ثبت خطاهای سمت کاربر با محدودیت ۵ خطا در هر نشست</p>
                            </div>
                            <label class="admin-switch"><input type="checkbox" name="error_tracking_enabled" value="1" class="admin-switch-input" @checked(old('error_tracking_enabled', $settings['error_tracking_enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-slate/5">
                            <div>
                                <p class="text-sm font-bold text-slate">Heartbeat</p>
                                <p class="text-xs text-slate/40">ارسال سیگنال هر ۳۰ ثانیه برای محاسبه دقیق مدت جلسه</p>
                            </div>
                            <label class="admin-switch"><input type="checkbox" name="heartbeat_enabled" value="1" class="admin-switch-input" @checked(old('heartbeat_enabled', $settings['heartbeat_enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <p class="text-sm font-bold text-slate">هشدار خودکار (Alerting)</p>
                                <p class="text-xs text-slate/40">بررسی ناهنجاری‌های آماری پس از هر Aggregation</p>
                            </div>
                            <label class="admin-switch"><input type="checkbox" name="alerting_enabled" value="1" class="admin-switch-input" @checked(old('alerting_enabled', $settings['alerting_enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2 mt-5 pt-4 border-t border-slate/10">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate">TTL کش گزارش‌ها (ثانیه)</label>
                            <input type="number" name="report_cache_seconds" min="10" max="3600" value="{{ old('report_cache_seconds', $settings['report_cache_seconds'] ?? 60) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate">پنجره کاربران زنده (دقیقه)</label>
                            <input type="number" name="live_user_window_minutes" min="1" max="60" value="{{ old('live_user_window_minutes', $settings['live_user_window_minutes'] ?? 5) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate">مدت زمان نشست (دقیقه)</label>
                            <input type="number" name="session_timeout_minutes" min="5" max="120" value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? 30) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" required>
                            <p class="text-[10px] text-slate/40">بعد از این مدت بدون فعالیت، جلسه بسته می‌شود</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate">نگهداری رویداد خام (روز)</label>
                            <input type="number" name="raw_event_retention_days" min="1" max="365" value="{{ old('raw_event_retention_days', $settings['raw_event_retention_days'] ?? 90) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" required>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate">نگهداری آمار تجمیعی (روز)</label>
                            <input type="number" name="stats_retention_days" min="30" max="1825" value="{{ old('stats_retention_days', $settings['stats_retention_days'] ?? 365) }}" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" required>
                        </div>
                    </div>

                    <div class="pt-5 border-t border-slate/10 flex justify-between items-center">
                        <button class="admin-btn !bg-success !text-white w-full sm:w-auto px-10 h-11 justify-center">
                            <span class="material-icons">save</span>
                            ذخیره تنظیمات
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    @vite(['resources/js/admin-analytics.js', 'resources/js/admin-analytics-hub.js'])
</x-layouts.admin-dashboard>
