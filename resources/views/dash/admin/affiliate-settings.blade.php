<x-layouts.admin-dashboard title="مدیریت افیلیت" :helpModuleKey="'affiliate'">
    @php($authkey = request()->route('authkey'))

    <style>
        .persian-date-input {
            border-radius: 0.5rem;
            border-color: #cbd5e1;
            padding: 0.5rem;
            width: 100%;
            direction: rtl;
            font-family: inherit;
            font-size: 0.875rem;
        }
        .dark .persian-date-input {
            background-color: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }
        .dark .persian-date-input::placeholder {
            color: #94a3b8;
        }
        .affiliate-datepicker-popup {
            animation: fadeIn 0.15s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت سیستم افیلیت</h1>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="home-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-dashboard">
                <span class="material-icons text-base">dashboard</span>
                <span>داشبورد</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-settings">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات</span>
            </button>
        </div>
    </div>

    <div id="tab-settings" class="tab-content space-y-6">
        <form action="{{ route('dash.admin.affiliate.settings.save', ['authkey' => $authkey]) }}" method="POST" class="admin-card space-y-4">
            @csrf
            <div class="flex items-center gap-3 mb-2">
                <div class="rounded-full bg-primary/10 p-2 text-primary"><span class="material-icons">shopping_cart</span></div>
                <h2 class="font-bold text-slate">تنظیمات اتصال به افیلیت باسلام</h2>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-1">
                    <label class="text-xs font-bold px-1">Merchant ID</label>
                    <input name="merchant_id" value="{{ $settings['merchant_id'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="48817">
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold px-1">Access Token</label>
                    <input name="access_token" value="{{ $settings['access_token'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="eyJ0eXAiOiJKV1QiLCJhbG...">
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-xs font-bold px-1">URL Prefix</label>
                    <input name="url_prefix" value="{{ $settings['url_prefix'] ?? 'https://a.bslm.ir/api/v1/tracking/click/' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="https://a.bslm.ir/api/v1/tracking/click/">
                </div>
            </div>
            <div class="pt-2">
                <button class="admin-btn admin-btn-primary"><span class="material-icons">save</span>ذخیره تنظیمات باسلام</button>
            </div>
        </form>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="admin-card space-y-4">
                <div class="flex items-center gap-3 border-b border-slate/10 pb-3">
                    <span class="material-icons text-primary">settings_backup_restore</span>
                    <h3 class="font-bold text-sm">پشتیبان‌گیری و بازگردانی تنظیمات</h3>
                </div>
                <div class="space-y-3">
                    <div class="p-3 bg-slate/5 rounded-lg">
                        <p class="text-[10px] text-slate/70 mb-3 leading-5">خروجی گرفتن از تنظیمات اتصال باسلام (Merchant ID، Token، Prefix)</p>
                        <a href="{{ route('dash.admin.affiliate.export', ['authkey' => $authkey, 'type' => 'basalam']) }}" class="admin-btn admin-btn-secondary w-full justify-center">
                            <span class="material-icons text-sm">file_download</span>دانلود فایل تنظیمات
                        </a>
                    </div>
                    <form action="{{ route('dash.admin.affiliate.import', ['authkey' => $authkey, 'type' => 'basalam']) }}" method="POST" enctype="multipart/form-data" class="p-3 border border-dashed border-slate/20 rounded-lg space-y-3">
                        @csrf
                        <label class="text-[10px] font-bold opacity-60">بازگردانی تنظیمات:</label>
                        <input type="file" name="file" required class="w-full text-xs text-slate opacity-70">
                        <button type="submit" class="admin-btn admin-btn-primary w-full justify-center">
                            <span class="material-icons text-sm">file_upload</span>ایمپورت تنظیمات
                        </button>
                    </form>
                </div>
            </div>

            <div class="admin-card space-y-4">
                <div class="flex items-center gap-3 border-b border-slate/10 pb-3">
                    <span class="material-icons text-primary">cloud_sync</span>
                    <h3 class="font-bold text-sm">پشتیبان‌گیری و بازگردانی داده‌ها</h3>
                </div>
                <div class="space-y-3">
                    <div class="p-3 bg-slate/5 rounded-lg">
                        <p class="text-[10px] text-slate/70 mb-3 leading-5">خروجی گرفتن از تمامی لینک‌ها و آمار کلیک روزانه.</p>
                        <a href="{{ route('dash.admin.affiliate.links.export', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary w-full justify-center">
                            <span class="material-icons text-sm">file_download</span>دانلود بکاپ داده‌ها (JSON)
                        </a>
                    </div>
                    <form action="{{ route('dash.admin.affiliate.links.import', ['authkey' => $authkey]) }}" method="POST" enctype="multipart/form-data" class="p-3 border border-dashed border-slate/20 rounded-lg space-y-3">
                        @csrf
                        <label class="text-[10px] font-bold opacity-60">وارد کردن دسته‌ای داده‌ها:</label>
                        <input type="file" name="file" required class="w-full text-xs text-slate opacity-70">
                        <button type="submit" class="admin-btn admin-btn-primary w-full justify-center">
                            <span class="material-icons text-sm">file_upload</span>ایمپورت داده‌ها
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-dashboard" class="tab-content hidden space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="admin-card !p-5 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold opacity-60 uppercase tracking-wider">کل لینک‌ها</span>
                        <span class="rounded-lg bg-primary/10 p-2 text-primary"><span class="material-icons text-lg">link</span></span>
                    </div>
                    <p class="text-2xl font-black text-slate">{{ number_format($links->total()) }}</p>
                    <p class="text-[10px] opacity-50 mt-1">تعداد کل لینک‌های ثبت شده</p>
                </div>
            </div>
            <div class="admin-card !p-5 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-success/5 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold opacity-60 uppercase tracking-wider">لینک‌های فعال</span>
                        <span class="rounded-lg bg-success/10 p-2 text-success"><span class="material-icons text-lg">check_circle</span></span>
                    </div>
                    <p class="text-2xl font-black text-slate">{{ number_format($links->where('status', 'active')->count()) }}</p>
                    <p class="text-[10px] opacity-50 mt-1">در حال کار</p>
                </div>
            </div>
            <div class="admin-card !p-5 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-warning/5 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold opacity-60 uppercase tracking-wider">مجموع کلیک‌ها</span>
                        <span class="rounded-lg bg-warning/10 p-2 text-warning"><span class="material-icons text-lg">touch_app</span></span>
                    </div>
                    <p class="text-2xl font-black text-slate">{{ number_format($links->sum(fn($l) => $l->click_count ?? 0)) }}</p>
                    <p class="text-[10px] opacity-50 mt-1">کلیک از ابتدای ثبت</p>
                </div>
            </div>
            <div class="admin-card !p-5 relative overflow-hidden group">
                <div class="absolute inset-0 bg-gradient-to-br from-danger/5 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold opacity-60 uppercase tracking-wider">خطاها</span>
                        <span class="rounded-lg bg-danger/10 p-2 text-danger"><span class="material-icons text-lg">error_outline</span></span>
                    </div>
                    <p class="text-2xl font-black text-slate">{{ number_format($links->where('status', 'error')->count()) }}</p>
                    <p class="text-[10px] opacity-50 mt-1">لینک‌های با مشکل</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 admin-card">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate flex items-center gap-2">
                        <span class="material-icons text-primary">show_chart</span>
                        عملکرد کلیک‌ها
                    </h3>
                    <form action="{{ route('dash.admin.affiliate.settings', ['authkey' => $authkey]) }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="tab" value="tab-dashboard">
                        <div class="shamsi-datepicker-popup">
                            <input type="text" name="start_date" value="{{ $startDate }}" placeholder="از تاریخ" class="persian-date-input rounded-lg border border-slate p-2 text-xs dark:bg-slate-800 w-[110px]">
                        </div>
                        <span class="text-slate/30">—</span>
                        <div class="shamsi-datepicker-popup">
                            <input type="text" name="end_date" value="{{ $endDate }}" placeholder="تا تاریخ" class="persian-date-input rounded-lg border border-slate p-2 text-xs dark:bg-slate-800 w-[110px]">
                        </div>
                        <select name="group_by" class="rounded-lg border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white">
                            <option value="day" @selected($groupBy === 'day')>روزانه</option>
                            <option value="week" @selected($groupBy === 'week')>هفتگی</option>
                            <option value="month" @selected($groupBy === 'month')>ماهانه</option>
                        </select>
                        <button type="submit" class="rounded-lg bg-primary/10 p-2 text-primary hover:bg-primary/20 transition-colors">
                            <span class="material-icons text-sm">filter_alt</span>
                        </button>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead>
                            <tr class="border-b border-slate/10">
                                <th class="pb-3 text-[10px] font-bold opacity-50 uppercase tracking-wider text-center">بازه</th>
                                <th class="pb-3 text-[10px] font-bold opacity-50 uppercase tracking-wider text-center">باسلام</th>
                                <th class="pb-3 text-[10px] font-bold opacity-50 uppercase tracking-wider text-center">دیجی‌کالا</th>
                                <th class="pb-3 text-[10px] font-bold opacity-50 uppercase tracking-wider text-center">مجموع</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate/5">
                            @forelse($dailyStats as $stat)
                                <tr class="hover:bg-slate/5 transition-colors">
                                    <td class="py-3 text-center font-bold text-xs">{{ $stat['date'] }}</td>
                                    <td class="py-3 text-center">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-primary">
                                            {{ number_format($stat['basalam']) }}
                                            @if(isset($stat['basalam_prev']) && $stat['basalam_prev'] > 0)
                                                <span class="text-[9px] {{ ($stat['basalam'] - $stat['basalam_prev']) >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ ($stat['basalam'] - $stat['basalam_prev']) >= 0 ? '↑' : '↓' }} {{ abs(round((($stat['basalam'] - $stat['basalam_prev']) / $stat['basalam_prev']) * 100)) }}%
                                                </span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-danger">
                                            {{ number_format($stat['digikala']) }}
                                            @if(isset($stat['digikala_prev']) && $stat['digikala_prev'] > 0)
                                                <span class="text-[9px] {{ ($stat['digikala'] - $stat['digikala_prev']) >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ ($stat['digikala'] - $stat['digikala_prev']) >= 0 ? '↑' : '↓' }} {{ abs(round((($stat['digikala'] - $stat['digikala_prev']) / $stat['digikala_prev']) * 100)) }}%
                                                </span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-3 text-center font-black text-xs">{{ number_format($stat['basalam'] + $stat['digikala']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-slate opacity-40 text-xs">آماری در این بازه یافت نشد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-card">
                <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
                    <span class="material-icons text-primary">leaderboard</span>
                    توزیع کلیک‌ها
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-slate">باسلام</span>
                            <span class="text-xs font-black text-primary">
                                {{ number_format($links->where('store', 'basalam')->sum(fn($l) => $l->click_count ?? 0)) }}
                                ({{ $links->total() > 0 ? round(($links->where('store', 'basalam')->sum(fn($l) => $l->click_count ?? 0) / max($links->sum(fn($l) => $l->click_count ?? 0), 1)) * 100) : 0 }}%)
                            </span>
                        </div>
                        <div class="h-2 rounded-full bg-slate/10 overflow-hidden">
                            <div class="h-full rounded-full bg-primary transition-all duration-500" style="width: {{ $links->total() > 0 ? round(($links->where('store', 'basalam')->sum(fn($l) => $l->click_count ?? 0) / max($links->sum(fn($l) => $l->click_count ?? 0), 1)) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-medium text-slate">دیجی‌کالا</span>
                            <span class="text-xs font-black text-danger">
                                {{ number_format($links->where('store', 'digikala')->sum(fn($l) => $l->click_count ?? 0)) }}
                                ({{ $links->total() > 0 ? round(($links->where('store', 'digikala')->sum(fn($l) => $l->click_count ?? 0) / max($links->sum(fn($l) => $l->click_count ?? 0), 1)) * 100) : 0 }}%)
                            </span>
                        </div>
                        <div class="h-2 rounded-full bg-slate/10 overflow-hidden">
                            <div class="h-full rounded-full bg-danger transition-all duration-500" style="width: {{ $links->total() > 0 ? round(($links->where('store', 'digikala')->sum(fn($l) => $l->click_count ?? 0) / max($links->sum(fn($l) => $l->click_count ?? 0), 1)) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate/10 mt-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center p-3 rounded-xl bg-primary/5">
                                <p class="text-lg font-black text-primary">{{ number_format($links->where('store', 'basalam')->count()) }}</p>
                                <p class="text-[10px] opacity-50">لینک باسلام</p>
                            </div>
                            <div class="text-center p-3 rounded-xl bg-danger/5">
                                <p class="text-lg font-black text-danger">{{ number_format($links->where('store', 'digikala')->count()) }}</p>
                                <p class="text-[10px] opacity-50">لینک دیجی‌کالا</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <form action="{{ route('dash.admin.affiliate.settings', ['authkey' => $authkey]) }}" method="GET" class="flex flex-wrap items-center gap-3 mb-4">
                <input type="hidden" name="tab" value="tab-dashboard">
                <input type="text" name="q" value="{{ $q }}" placeholder="جستجو شناسه یا اسلاگ..." class="rounded border border-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <select name="store" class="rounded border border-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <option value="all" @selected(($store ?? 'all') === 'all')>همه فروشگاه‌ها</option>
                    <option value="basalam" @selected(($store ?? '') === 'basalam')>باسلام</option>
                    <option value="digikala" @selected(($store ?? '') === 'digikala')>دیجی‌کالا</option>
                </select>
                <select name="sort" class="rounded border border-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
                    <option value="clicks" @selected(($sort ?? '') === 'clicks')>بیشترین کلیک</option>
                    <option value="product_asc" @selected(($sort ?? '') === 'product_asc')>شناسه (صعودی)</option>
                    <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
                </select>
                <button class="admin-btn justify-center"><span class="material-icons">search</span>جستجو</button>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-slate/5 text-slate opacity-70">
                        <tr>
                            <th class="p-3 font-bold text-center">وضعیت</th>
                            <th class="p-3 font-bold text-center">فروشگاه</th>
                            <th class="p-3 font-bold text-center">شناسه محصول</th>
                            <th class="p-3 font-bold text-center">اسلاگ</th>
                            <th class="p-3 font-bold text-center">کلیک</th>
                            <th class="p-3 font-bold text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate/10">
                        @forelse($links as $link)
                            <tr class="hover:bg-slate/5 transition-colors">
                                <td class="p-3 text-center">
                                    <span class="inline-flex items-center gap-1 text-xs {{ $link->status === 'active' ? 'text-success' : 'text-danger' }}">
                                        <span class="material-icons text-[12px]">{{ $link->status === 'active' ? 'check_circle' : 'cancel' }}</span>
                                        {{ $link->status === 'active' ? 'فعال' : 'خطا' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="text-[10px] px-2 py-0.5 rounded font-bold {{ $link->store === 'digikala' ? 'bg-danger/10 text-danger' : 'bg-primary/10 text-primary' }}">
                                        {{ $link->store === 'digikala' ? 'دیجی‌کالا' : 'باسلام' }}
                                    </span>
                                </td>
                                <td class="p-3 text-center font-mono text-xs">{{ $link->product_id }}</td>
                                <td class="p-3 text-center font-mono text-xs opacity-60">go/{{ $link->slug }}</td>
                                <td class="p-3 text-center font-bold">{{ number_format($link->click_count ?? 0) }}</td>
                                <td class="p-3 text-center">
                                    <form action="{{ route('dash.admin.affiliate.links.delete', ['authkey' => $authkey, 'link' => $link->id]) }}" method="POST" data-admin-confirm="آیا از حذف این لینک مطمئن هستید؟">
                                        @csrf @method('DELETE')
                                        <button class="admin-btn admin-btn-danger !p-1.5" title="حذف"><span class="material-icons !text-base">delete</span></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="p-6 text-center text-slate opacity-50">موردی یافت نشد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $links->links() }}</div>
        </div>
    </div>


</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-affiliate-settings.js'])
