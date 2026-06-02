<x-layouts.admin-dashboard title="مدیریت افیلیت">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت سیستم افیلیت</h1>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="home-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-general">
                <span class="material-icons text-base">settings</span>
                <span>اطلاعات کلی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-basalam">
                <span class="material-icons text-base">shopping_cart</span>
                <span>افیلیت باسلام</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-digikala">
                <span class="material-icons text-base">shopping_bag</span>
                <span>افیلیت دیجی کالا</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-browser">
                <span class="material-icons text-base">history</span>
                <span>مرور لینک‌ها و آمار</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-stats">
                <span class="material-icons text-base">analytics</span>
                <span>آنالیز عملکرد</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-backup">
                <span class="material-icons text-base">backup</span>
                <span>پشتیبان‌گیری</span>
            </button>
        </div>
    </div>

    <div id="tab-general" class="tab-content space-y-6">
        <div class="admin-card space-y-4">
            <h2 class="font-bold text-slate border-b border-slate/10 pb-3">اطلاعات ماژول افیلیت</h2>
            <div class="bg-primary/5 p-4 rounded-xl border border-primary/20 text-sm text-slate leading-7">
                <p>در نسخه جدید، سیستم کشینگ برای دقت ۱۰۰ درصدی آمار حذف شده است.</p>
                <p>تمامی لینک‌ها به صورت <b>Persistent</b> در دیتابیس نگهداری می‌شوند و هر کلیک به صورت مجزا شمارش می‌شود.</p>
                <p class="mt-2">لینک‌های سیستم به فرمت استاندارد <code>/go/{slug}</code> تبدیل شده‌اند.</p>
            </div>
        </div>
    </div>

    <div id="tab-basalam" class="tab-content hidden space-y-6">
        <form action="{{ route('dash.admin.affiliate.settings.save', ['authkey' => $authkey]) }}" method="POST" class="admin-card space-y-4">
            @csrf
            <div class="flex items-center gap-3 mb-2">
                <div class="rounded-full bg-primary/10 p-2 text-primary"><span class="material-icons">link</span></div>
                <h2 class="font-bold text-slate">تنظیمات اتصال به افیلیت باسلام</h2>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs font-bold px-1">Merchant ID</label>
                    <input name="merchant_id" value="{{ $settings['merchant_id'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="Merchant ID">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold px-1">Access Token</label>
                    <input name="access_token" value="{{ $settings['access_token'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="Access Token">
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold px-1">URL Prefix</label>
                    <input name="url_prefix" value="{{ $settings['url_prefix'] ?? 'https://a.bslm.ir/api/v1/tracking/click/' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="https://...">
                </div>
            </div>
            <div class="pt-2">
                <button class="admin-btn admin-btn-primary"><span class="material-icons">save</span>ذخیره تنظیمات باسلام</button>
            </div>
        </form>
    </div>

    <div id="tab-digikala" class="tab-content hidden space-y-6">
        <div class="admin-card space-y-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="rounded-full bg-primary/10 p-2 text-primary"><span class="material-icons">shopping_bag</span></div>
                <h2 class="font-bold text-slate">سیستم افیلیت دیجی کالا</h2>
            </div>
            <div class="bg-primary/5 p-4 rounded-xl border border-primary/20 text-sm text-slate leading-7">
                <p>سیستم افیلیت دیجی کالا در حال حاضر به صورت خودکار از الگوی <b>dgkl.io</b> استفاده می‌کند.</p>
                <p>تمامی کلیک‌ها با شناسه محصول دیجی کالا در دیتابیس ثبت و شمارش می‌شوند.</p>
            </div>
        </div>
    </div>

    <div id="tab-browser" class="tab-content hidden space-y-4">
        <form action="{{ route('dash.admin.affiliate.settings', ['authkey' => $authkey]) }}" method="GET" class="admin-card grid gap-3 md:grid-cols-4">
            <input type="hidden" name="tab" value="tab-browser">
            <input type="text" name="q" value="{{ $q }}" placeholder="جستجو شناسه یا اسلاگ..." class="rounded border border-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <select name="store" class="rounded p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="all" @selected(($store ?? 'all') === 'all')>همه فروشگاه‌ها</option>
                <option value="basalam" @selected(($store ?? '') === 'basalam')>باسلام</option>
                <option value="digikala" @selected(($store ?? '') === 'digikala')>دیجی کالا</option>
            </select>
            <select name="sort" class="rounded p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
                <option value="clicks" @selected(($sort ?? '') === 'clicks')>بیشترین کلیک</option>
                <option value="product_asc" @selected(($sort ?? '') === 'product_asc')>شناسه محصول (صعودی)</option>
                <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
            </select>
            <button class="admin-btn justify-center"><span class="material-icons">search</span>جستجو و فیلتر</button>
        </form>

        <div class="space-y-3">
            @forelse($links as $link)
                <div class="admin-card flex items-center justify-between gap-3">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full {{ $link->store === 'digikala' ? 'bg-danger/10 text-danger' : 'bg-primary/10 text-primary' }} p-3">
                            <span class="material-icons">{{ $link->store === 'digikala' ? 'shopping_bag' : 'shopping_cart' }}</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-bold text-sm">شناسه: {{ $link->product_id }}</p>
                                <span class="text-[10px] px-1.5 py-0.5 rounded {{ $link->store === 'digikala' ? 'bg-danger/10 text-danger' : 'bg-primary/10 text-primary' }} font-bold">
                                    {{ $link->store === 'digikala' ? 'دیجی‌کالا' : 'باسلام' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="inline-flex items-center gap-1 text-[10px] opacity-60">
                                    <span class="material-icons text-[12px]">touch_app</span>
                                    {{ number_format($link->click_count ?? 0) }} کلیک
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] opacity-60">
                                    <span class="material-icons text-[12px]">link</span>
                                    go/{{ $link->slug }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] {{ $link->status === 'active' ? 'text-success' : 'text-danger' }}">
                                    <span class="material-icons text-[12px]">{{ $link->status === 'active' ? 'check_circle' : 'cancel' }}</span>
                                    {{ $link->status === 'active' ? 'فعال' : 'غیرفعال' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <form action="{{ route('dash.admin.affiliate.links.toggle', ['authkey' => $authkey, 'link' => $link->id]) }}" method="POST">
                            @csrf
                            <label class="admin-switch"><input type="checkbox" class="admin-switch-input" @checked($link->status === 'active')><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </form>
                        <form action="{{ route('dash.admin.affiliate.links.delete', ['authkey' => $authkey, 'link' => $link->id]) }}" method="POST" onsubmit="return confirm('آیا از حذف این لینک مطمئن هستید؟')">
                            @csrf
                            @method('DELETE')
                            <button class="admin-btn admin-btn-danger !p-2" title="حذف دائمی"><span class="material-icons text-base">delete</span></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="admin-card text-center py-10 opacity-50">
                    <span class="material-icons text-4xl block mb-2">find_in_page</span>
                    موردی یافت نشد.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $links->links() }}
        </div>
    </div>

    <div id="tab-stats" class="tab-content hidden space-y-6">
        <form action="{{ route('dash.admin.affiliate.settings', ['authkey' => $authkey]) }}" method="GET" class="admin-card grid gap-3 md:grid-cols-4 items-end">
            <input type="hidden" name="tab" value="tab-stats">
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">از تاریخ</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">تا تاریخ</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">تجمیع بر اساس</label>
                <select name="group_by" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800">
                    <option value="day" @selected($groupBy === 'day')>روزانه</option>
                    <option value="week" @selected($groupBy === 'week')>هفتگی</option>
                    <option value="month" @selected($groupBy === 'month')>ماهانه</option>
                </select>
            </div>
            <button class="admin-btn justify-center h-[38px]"><span class="material-icons">filter_alt</span>فیلتر آمار</button>
        </form>

        <div class="admin-card">
            <h2 class="font-bold text-slate mb-6 flex items-center gap-2">
                <span class="material-icons text-primary">show_chart</span>
                گزارش عملکرد کلیک‌ها
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate/5 text-slate opacity-70">
                        <tr>
                            <th class="p-4 font-bold text-center">بازه زمانی</th>
                            <th class="p-4 font-bold text-center">باسلام</th>
                            <th class="p-4 font-bold text-center">دیجی‌کالا</th>
                            <th class="p-4 font-bold text-center">مجموع کلیک</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate/10">
                        @forelse($dailyStats as $stat)
                            <tr class="hover:bg-slate/5 transition-colors">
                                <td class="p-4 text-center font-bold">{{ $stat['date'] }}</td>
                                <td class="p-4 text-center text-primary font-bold">{{ number_format($stat['basalam']) }}</td>
                                <td class="p-4 text-center text-danger font-bold">{{ number_format($stat['digikala']) }}</td>
                                <td class="p-4 text-center font-black">{{ number_format($stat['basalam'] + $stat['digikala']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center text-slate opacity-50">آماری در این بازه یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="tab-backup" class="tab-content hidden space-y-6">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="admin-card space-y-4">
                <div class="flex items-center gap-3 border-b border-slate/10 pb-3">
                    <span class="material-icons text-primary">settings_backup_restore</span>
                    <h3 class="font-bold text-sm">پشتیبان‌گیری تنظیمات (Settings)</h3>
                </div>
                <div class="space-y-3">
                    <div class="p-3 bg-slate/5 rounded-lg">
                        <p class="text-[10px] text-slate/70 mb-3 leading-5">خروجی گرفتن از تنظیمات اتصال به سیستم‌های افیلیت (شناسه مرچنت، توکن و ...)</p>
                        <a href="{{ route('dash.admin.affiliate.export', ['authkey' => $authkey, 'type' => 'basalam']) }}" class="admin-btn admin-btn-secondary w-full justify-center">
                            <span class="material-icons text-sm">file_download</span>
                            دانلود بکاپ تنظیمات باسلام
                        </a>
                    </div>
                    <form action="{{ route('dash.admin.affiliate.import', ['authkey' => $authkey, 'type' => 'basalam']) }}" method="POST" enctype="multipart/form-data" class="p-3 border border-dashed border-slate/20 rounded-lg space-y-3">
                        @csrf
                        <label class="text-[10px] font-bold opacity-60">بازگردانی تنظیمات باسلام:</label>
                        <input type="file" name="file" required class="w-full text-xs text-slate opacity-70">
                        <button class="admin-btn admin-btn-primary w-full justify-center">
                            <span class="material-icons text-sm">file_upload</span>
                            ایمپورت تنظیمات
                        </button>
                    </form>
                </div>
            </div>

            <div class="admin-card space-y-4">
                <div class="flex items-center gap-3 border-b border-slate/10 pb-3">
                    <span class="material-icons text-primary">cloud_sync</span>
                    <h3 class="font-bold text-sm">پشتیبان‌گیری داده‌ها (Links & Stats)</h3>
                </div>
                <div class="space-y-3">
                    <div class="p-3 bg-slate/5 rounded-lg">
                        <p class="text-[10px] text-slate/70 mb-3 leading-5">خروجی گرفتن از تمامی لینک‌های تولید شده (باسلام و دیجی‌کالا) به همراه آمار کلیک روزانه.</p>
                        <a href="{{ route('dash.admin.affiliate.links.export', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary w-full justify-center">
                            <span class="material-icons text-sm">file_download</span>
                            دانلود بکاپ داده‌ها (JSON)
                        </a>
                    </div>
                    <form action="{{ route('dash.admin.affiliate.links.import', ['authkey' => $authkey]) }}" method="POST" enctype="multipart/form-data" class="p-3 border border-dashed border-slate/20 rounded-lg space-y-3">
                        @csrf
                        <label class="text-[10px] font-bold opacity-60">وارد کردن دسته‌ای داده‌ها:</label>
                        <input type="file" name="file" required class="w-full text-xs text-slate opacity-70">
                        <button class="admin-btn admin-btn-primary w-full justify-center">
                            <span class="material-icons text-sm">file_upload</span>
                            ایمپورت داده‌ها
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-affiliate-settings.js'])
