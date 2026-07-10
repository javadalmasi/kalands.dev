<x-layouts.admin-dashboard title="مدیریت افیلیت" :helpModuleKey="'affiliate'">
    @php($authkey = request()->route('authkey'))

    <style>
        .persian-date-input {
            background: var(--adm-input-bg);
            border: 1px solid var(--adm-input-border);
            border-radius: 6px;
            color: var(--adm-fg);
            padding: 0.4rem 0.75rem;
            width: 100%;
            direction: rtl;
            font-family: inherit;
            font-size: 0.8125rem;
            outline: none;
        }
        .persian-date-input::placeholder { color: var(--adm-fg-3); }
        .persian-date-input:focus {
            border-color: rgba(16, 185, 129, 0.4);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.08);
        }
        .affiliate-datepicker-popup { animation: fadeIn 0.15s ease; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <x-admin.page-header title="مدیریت سیستم افیلیت" description="مدیریت لینک‌های افیلیت، تنظیمات اتصال و آمار کلیک" />

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex overflow-x-auto whitespace-nowrap admin-tab-bar" id="home-tabs">
            <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-dashboard">
                <span class="material-icons !text-[17px]">dashboard</span>
                <span>داشبورد</span>
            </button>
            <button class="admin-tab-btn text-slate-500 font-medium" data-tab-target="tab-settings">
                <span class="material-icons !text-[17px]">settings</span>
                <span>تنظیمات</span>
            </button>
        </div>
    </div>

    <div id="tab-settings" class="tab-content space-y-6">
        <form action="{{ route('dash.admin.affiliate.settings.save', ['authkey' => $authkey]) }}" method="POST" class="admin-card space-y-4">
            @csrf
            <div class="flex items-center gap-3 pb-3 mb-1" style="border-bottom: 1px solid var(--adm-border)">
                <div class="rounded-lg p-2" style="background: var(--adm-accent-bg)">
                    <span class="material-icons !text-[17px]" style="color: var(--admin-primary)">shopping_cart</span>
                </div>
                <h2 class="font-semibold text-sm" style="color: var(--adm-fg)">تنظیمات اتصال به افیلیت باسلام</h2>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color: var(--adm-fg-2)">Merchant ID</label>
                    <input name="merchant_id" value="{{ $settings['merchant_id'] ?? '' }}" class="admin-input admin-ltr" placeholder="48817">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1.5" style="color: var(--adm-fg-2)">Access Token</label>
                    <input name="access_token" value="{{ $settings['access_token'] ?? '' }}" class="admin-input admin-ltr" placeholder="eyJ0eXAiOiJKV1QiLCJhbG...">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium mb-1.5" style="color: var(--adm-fg-2)">URL Prefix</label>
                    <input name="url_prefix" value="{{ $settings['url_prefix'] ?? 'https://a.bslm.ir/api/v1/tracking/click/' }}" class="admin-input admin-ltr" placeholder="https://a.bslm.ir/api/v1/tracking/click/">
                </div>
            </div>
            <div class="pt-1">
                <button class="admin-btn admin-btn-primary"><span class="material-icons">save</span>ذخیره تنظیمات</button>
            </div>
        </form>

        <div class="grid gap-4 md:grid-cols-2">
            <x-admin.action-card title="پشتیبان‌گیری تنظیمات" description="خروجی از تنظیمات اتصال باسلام (Merchant ID، Token، Prefix)" icon="settings_backup_restore" variant="info">
                <x-slot:body>
                    <a href="{{ route('dash.admin.affiliate.export', ['authkey' => $authkey, 'type' => 'basalam']) }}" class="admin-btn admin-btn-secondary w-full justify-center">
                        <span class="material-icons">file_download</span>دانلود تنظیمات
                    </a>
                    <form action="{{ route('dash.admin.affiliate.import', ['authkey' => $authkey, 'type' => 'basalam']) }}" method="POST" enctype="multipart/form-data" class="space-y-2 mt-2">
                        @csrf
                        <input type="file" name="file" required class="w-full text-xs" style="color: #6b7280">
                        <button type="submit" class="admin-btn admin-btn-primary w-full justify-center">
                            <span class="material-icons">file_upload</span>ایمپورت تنظیمات
                        </button>
                    </form>
                </x-slot:body>
            </x-admin.action-card>

            <x-admin.action-card title="پشتیبان‌گیری داده‌ها" description="خروجی از تمامی لینک‌ها و آمار کلیک روزانه" icon="cloud_sync" variant="success">
                <x-slot:body>
                    <a href="{{ route('dash.admin.affiliate.links.export', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary w-full justify-center">
                        <span class="material-icons">file_download</span>دانلود بکاپ (JSON)
                    </a>
                    <form action="{{ route('dash.admin.affiliate.links.import', ['authkey' => $authkey]) }}" method="POST" enctype="multipart/form-data" class="space-y-2 mt-2">
                        @csrf
                        <input type="file" name="file" required class="w-full text-xs" style="color: #6b7280">
                        <button type="submit" class="admin-btn admin-btn-primary w-full justify-center">
                            <span class="material-icons">file_upload</span>ایمپورت داده‌ها
                        </button>
                    </form>
                </x-slot:body>
            </x-admin.action-card>
        </div>
    </div>

    <div id="tab-dashboard" class="tab-content hidden space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-admin.stat-card title="کل لینک‌ها" :value="number_format($links->total())" icon="link" color="primary" />
            <x-admin.stat-card title="لینک‌های فعال" :value="number_format($links->where('status', 'active')->count())" icon="check_circle" color="success" />
            <x-admin.stat-card title="مجموع کلیک‌ها" :value="number_format($links->sum(fn($l) => $l->click_count ?? 0))" icon="touch_app" color="warning" />
            <x-admin.stat-card title="خطاها" :value="number_format($links->where('status', 'error')->count())" icon="error_outline" color="danger" />
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
                            <input type="text" name="start_date" value="{{ $startDate }}" placeholder="از تاریخ" class="admin-input admin-ltr !w-[110px] !min-h-[32px] text-xs">
                        </div>
                        <span style="color: rgba(255,255,255,0.2)">—</span>
                        <div class="shamsi-datepicker-popup">
                            <input type="text" name="end_date" value="{{ $endDate }}" placeholder="تا تاریخ" class="admin-input admin-ltr !w-[110px] !min-h-[32px] text-xs">
                        </div>
                        <select name="group_by" class="admin-input !w-auto !min-h-[32px] text-xs">
                            <option value="day" @selected($groupBy === 'day')>روزانه</option>
                            <option value="week" @selected($groupBy === 'week')>هفتگی</option>
                            <option value="month" @selected($groupBy === 'month')>ماهانه</option>
                        </select>
                        <button type="submit" class="admin-toggle inline-flex" style="width:32px;height:32px" title="اعمال فیلتر">
                            <span class="material-icons !text-base" style="color:#10b981">filter_alt</span>
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
                <input type="text" name="q" value="{{ $q }}" placeholder="جستجو شناسه یا اسلاگ..." class="admin-input">
                <select name="store" class="admin-input">
                    <option value="all" @selected(($store ?? 'all') === 'all')>همه فروشگاه‌ها</option>
                    <option value="basalam" @selected(($store ?? '') === 'basalam')>باسلام</option>
                    <option value="digikala" @selected(($store ?? '') === 'digikala')>دیجی‌کالا</option>
                </select>
                <select name="sort" class="admin-input">
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
