<x-layouts.admin-dashboard title="مدیریت Object Cache" :helpModuleKey="'object_cache'">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت Object Cache</h1>
        <div class="flex items-center gap-2 text-xs">
            <span class="px-3 py-1.5 rounded-lg bg-slate/5 border border-slate/10 font-bold">
                درایور فعلی: {{ $drivers[$currentDriver] ?? $currentDriver }}
            </span>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="object-cache-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-config">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-test">
                <span class="material-icons text-base">network_check</span>
                <span>تست اتصال</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-status">
                <span class="material-icons text-base">fact_check</span>
                <span>وضعیت درایورها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-purge">
                <span class="material-icons text-base">cleaning_services</span>
                <span>پاکسازی کش</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-items">
                <span class="material-icons text-base">inventory_2</span>
                <span>مشاهده آیتم‌ها</span>
            </button>
</div>
    </div>

    <div id="tab-config" class="tab-content space-y-6">
        <form action="{{ route('dash.admin.object-cache.settings.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">storage</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات درایور کش</h2>
                            <p class="text-xs text-slate/60 mt-1">تغییر درایور و پیشوند object cache لاراول</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-bold block mb-2">درایور Object Cache</label>
                        <select name="driver" id="cache-driver-select" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            @foreach($drivers as $driverKey => $driverLabel)
                                <option value="{{ $driverKey }}" @selected($selectedDriver === $driverKey)>{{ $driverLabel }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate/50 mt-1.5">درایور پیش‌فرض env: {{ $drivers[$currentDriver] ?? $currentDriver }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">پیشوند (Prefix)</label>
                        <input type="text" name="prefix" value="{{ $settings['prefix'] ?? $currentPrefix }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr text-left font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="{{ $currentPrefix }}">
                        <p class="text-[11px] text-slate/50 mt-1.5">پیشوند فعلی: <code class="bg-slate/10 px-1 rounded">{{ $currentPrefix }}</code></p>
                    </div>
                </div>

                <div id="redis-connection-config" class="{{ str_starts_with($selectedDriver, 'redis') ? '' : 'hidden' }}">
                    <div class="mt-6 pt-6 border-t border-slate/10">
                        <h3 class="font-bold text-sm text-slate mb-4 flex items-center gap-2">
                            <span class="material-icons text-primary">settings_ethernet</span>
                            تنظیمات اتصال Redis
                        </h3>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="text-xs font-bold block mb-1.5">نحوه اتصال</label>
                                <select name="redis_scheme" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                                    <option value="tcp" @selected(($redisConfig['scheme'] ?? 'tcp') === 'tcp')>TCP (پورت)</option>
                                    <option value="unix" @selected(($redisConfig['scheme'] ?? '') === 'unix')>Unix Socket</option>
                                </select>
                            </div>
                            <div id="redis-host-field">
                                <label class="text-xs font-bold block mb-1.5">هاست (Host)</label>
                                <input type="text" name="redis_host" value="{{ $redisConfig['host'] ?? '127.0.0.1' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="127.0.0.1">
                            </div>
                            <div id="redis-port-field">
                                <label class="text-xs font-bold block mb-1.5">پورت (Port)</label>
                                <input type="text" name="redis_port" value="{{ $redisConfig['port'] ?? '6379' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="6379">
                            </div>
                            <div id="redis-socket-field" class="hidden">
                                <label class="text-xs font-bold block mb-1.5">مسیر Socket</label>
                                <input type="text" name="redis_socket" value="{{ $redisConfig['socket'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="/var/run/redis/redis.sock">
                            </div>
                            <div>
                                <label class="text-xs font-bold block mb-1.5">رمز عبور (اختیاری)</label>
                                <input type="password" name="redis_password" value="{{ $redisConfig['password'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="مقداردهی نشده">
                            </div>
                            <div>
                                <label class="text-xs font-bold block mb-1.5">دیتابیس</label>
                                <input type="text" name="redis_database" value="{{ $redisConfig['database'] ?? '1' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="1">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="memcached-connection-config" class="{{ $selectedDriver === 'memcached' ? '' : 'hidden' }}">
                    <div class="mt-6 pt-6 border-t border-slate/10">
                        <h3 class="font-bold text-sm text-slate mb-4 flex items-center gap-2">
                            <span class="material-icons text-primary">dns</span>
                            تنظیمات اتصال Memcached
                        </h3>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div>
                                <label class="text-xs font-bold block mb-1.5">هاست (Host)</label>
                                <input type="text" name="memcached_host" value="{{ $memcachedConfig['host'] ?? '127.0.0.1' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="127.0.0.1">
                            </div>
                            <div>
                                <label class="text-xs font-bold block mb-1.5">پورت (Port)</label>
                                <input type="text" name="memcached_port" value="{{ $memcachedConfig['port'] ?? '11211' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="11211">
                            </div>
                            <div>
                                <label class="text-xs font-bold block mb-1.5">مسیر Socket (جایگزین)</label>
                                <input type="text" name="memcached_socket" value="{{ $memcachedConfig['socket'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 text-sm ltr font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="/var/run/memcached/memcached.sock">
                                <p class="text-[10px] text-slate/50 mt-1">در صورت مقداردهی، Socket جایگزین Host/Port می‌شود</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10">
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4 mb-4 text-xs text-amber-700 font-bold flex items-center gap-2">
                        <span class="material-icons text-base">info</span>
                        تغییرات در فایل <code class="bg-amber-500/20 px-1 rounded">.env</code> و runtime اعمال می‌شود.
                    </div>
                    <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </form>

        <div class="admin-card is-surface">
            <div class="flex items-center gap-3 mb-4">
                <div class="rounded-full bg-info/10 p-2 text-info">
                    <span class="material-icons">info</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate">راهنما</h3>
                </div>
            </div>
            <div class="text-xs text-slate/70 space-y-2">
                <p><strong class="text-slate">فایل (File):</strong> مناسب برای محیط توسعه، نیازی به سرویس جانبی ندارد.</p>
                <p><strong class="text-slate">Redis:</strong> پرسرعت، مناسب برای تولید، نیاز به سرور Redis دارد. پشتیبانی از Unix Socket.</p>
                <p><strong class="text-slate">Memcached:</strong> پرسرعت، مناسب برای تولید، نیاز به سرور Memcached دارد. پشتیبانی از Unix Socket.</p>
                <p><strong class="text-slate">دیتابیس (Database):</strong> کش در دیتابیس ذخیره می‌شود، مناسب برای بارهای کم.</p>
                <p class="text-amber-600 font-bold mt-2">تغییر درایور در فایل <code class="bg-amber-600/10 px-1 rounded">.env</code> اعمال شده و ماندگار است.</p>
            </div>
        </div>
    </div>

    <div id="tab-test" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">network_check</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">تست اتصال به Object Cache</h2>
                        <p class="text-xs text-slate/60 mt-1">بررسی صحت اتصال به درایور کش فعال</p>
                    </div>
                </div>
            </div>

            <div id="test-result" class="mb-4 hidden">
                <div class="p-4 rounded-lg text-sm font-bold flex items-center gap-3" id="test-result-content"></div>
            </div>

            <button type="button" id="test-cache-btn" class="admin-btn admin-btn-primary px-10 h-12 justify-center" data-url="{{ route('dash.admin.object-cache.test', ['authkey' => $authkey]) }}">
                <span class="material-icons">play_arrow</span>
                شروع تست اتصال
            </button>
        </div>

        <div class="admin-card is-surface">
            <div class="flex items-center gap-3 mb-4">
                <div class="rounded-full bg-info/10 p-2 text-info">
                    <span class="material-icons">info</span>
                </div>
                <div>
                    <h3 class="font-bold text-sm text-slate">جزئیات درایور فعلی</h3>
                </div>
            </div>
            <div class="text-xs space-y-2">
                <div class="flex items-center gap-2"><span class="font-bold text-slate w-28">درایور:</span><code class="bg-slate/10 px-2 py-0.5 rounded font-mono">{{ $currentDriver }}</code></div>
                <div class="flex items-center gap-2"><span class="font-bold text-slate w-28">پیشوند:</span><code class="bg-slate/10 px-2 py-0.5 rounded font-mono">{{ $currentPrefix }}</code></div>
                <div class="flex items-center gap-2"><span class="font-bold text-slate w-28">CACHE_STORE:</span><code class="bg-slate/10 px-2 py-0.5 rounded font-mono">{{ config('cache.default') }}</code></div>
            </div>
        </div>
    </div>

    <div id="tab-status" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">fact_check</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">وضعیت مدها و درایورهای مختلف</h2>
                        <p class="text-xs text-slate/60 mt-1">بررسی نصب بودن اکستنشن‌ها و وضعیت اتصال</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach($driverStatus as $key => $info)
                    <div class="p-4 rounded-xl border {{ $selectedDriver === $key ? 'border-primary/30 bg-primary/5' : 'border-slate/10 bg-slate/5' }} flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $info['installed'] ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-600' }}">
                                <span class="material-icons">{{ $info['installed'] ? 'check_circle' : 'cancel' }}</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate flex items-center gap-2">
                                    {{ $info['label'] }}
                                    @if($selectedDriver === $key)
                                        <span class="px-2 py-0.5 rounded text-[10px] bg-primary text-white uppercase">Active</span>
                                    @endif
                                </h3>
                                <p class="text-[10px] {{ $info['installed'] ? 'text-emerald-600/70' : 'text-rose-600/70' }}">
                                    {{ $info['installed'] ? 'Extension installed' : 'Extension NOT installed' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-left ltr">
                            @if($info['installed'])
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold {{ $info['connected'] ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $info['connected'] ? 'CONNECTED' : 'DISCONNECTED' }}
                                    </span>
                                    <div class="w-2 h-2 rounded-full {{ $info['connected'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></div>
                                </div>
                            @else
                                <span class="text-[10px] text-slate/40">N/A</span>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="p-4 rounded-xl border border-slate/10 bg-slate/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-emerald-500/10 text-emerald-600">
                            <span class="material-icons">check_circle</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate">File System</h3>
                            <p class="text-[10px] text-emerald-600/70">Always available</p>
                        </div>
                    </div>
                    <div class="text-left ltr">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-emerald-600">READY</span>
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-purge" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-danger/10 p-2 text-danger">
                        <span class="material-icons">cleaning_services</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">پاکسازی کامل Object Cache</h2>
                        <p class="text-xs text-slate/60 mt-1">تمام آیتم‌های ذخیره شده در کش حذف خواهند شد</p>
                    </div>
                </div>
            </div>

            <div class="bg-danger/5 border border-danger/20 rounded-lg p-6 text-center">
                <span class="material-icons text-5xl text-danger/40 block mb-3">warning</span>
                <p class="text-sm font-bold text-danger mb-2">هشدار: پاکسازی کش</p>
                <p class="text-xs text-slate/70 mb-6 max-w-lg mx-auto">
                    با این عملیات تمام داده‌های کش شده در سرور حذف می‌شوند. این شامل کش تنظیمات، کش محصولات و سایر داده‌های موقت می‌باشد. پس از پاکسازی، سیستم ممکن است تا چند لحظه کندتر عمل کند تا داده‌ها دوباره کش شوند.
                </p>
                <form action="{{ route('dash.admin.object-cache.purge', ['authkey' => $authkey]) }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-btn admin-btn-danger px-10 h-12 justify-center mx-auto" data-admin-confirm="آیا از پاکسازی کامل object cache اطمینان دارید؟ این عملیات غیرقابل بازگشت است.">
                        <span class="material-icons">delete_forever</span>
                        پاکسازی کامل کش
                    </button>
                </form>
            </div>
        </div>

        @if($currentDriver === 'file')
            <div class="admin-card is-surface">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-warning/10 p-2 text-warning">
                        <span class="material-icons">folder</span>
                    </div>
                    <div>
                        <p class="text-xs text-slate/70">مسیر ذخیره‌سازی درایور فایل: <code class="bg-slate/10 px-1 rounded font-mono">storage/framework/cache/data</code></p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div id="tab-items" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">inventory_2</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">مشاهده آیتم‌های کش</h2>
                        <p class="text-xs text-slate/60 mt-1">جستجو و مدیریت آیتم‌های ذخیره شده در object cache</p>
                    </div>
                </div>
                <div class="text-xs font-bold opacity-60" id="items-count">
                    @if(!empty($cacheItems)) تعداد: {{ count($cacheItems) }} @endif
                </div>
            </div>

            @if($canList)
                <form method="GET" class="mb-4" id="search-items-form">
                    <input type="hidden" name="tab" value="tab-items">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate/40 text-base">search</span>
                            <input type="text" name="search" value="{{ $search }}" class="w-full rounded-lg border border-slate p-2.5 pr-10 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="جستجوی کلید کش...">
                        </div>
                        <button class="admin-btn h-[42px] px-6">
                            <span class="material-icons">search</span>
                            جستجو
                        </button>
                        @if($search)
                            <a href="{{ route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'object_cache']) }}?tab=tab-items" class="admin-btn admin-btn-secondary h-[42px] px-4">
                                <span class="material-icons">close</span>
                                پاک کردن فیلتر
                            </a>
                        @endif
                    </div>
                </form>

                @if(!empty($cacheItems))
                    <div class="overflow-hidden rounded-lg border border-slate/10">
                        <table class="w-full text-right text-sm">
                            <thead class="bg-slate/5 border-b border-slate/10">
                                <tr>
                                    <th class="p-3 font-bold">#</th>
                                    <th class="p-3 font-bold">کلید (Key)</th>
                                    <th class="p-3 font-bold text-center">عملیات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate/5" id="cache-items-tbody">
                                @foreach($cacheItems as $index => $item)
                                    <tr class="hover:bg-slate/5 transition-colors cache-item-row">
                                        <td class="p-3 text-slate/50 font-mono text-[10px]">{{ $loop->iteration }}</td>
                                        <td class="p-3 font-mono text-xs ltr break-all">{{ $item }}</td>
                                        <td class="p-3 text-center">
                                            <form action="{{ route('dash.admin.object-cache.item.delete', ['authkey' => $authkey]) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $item }}">
                                                <button type="submit" class="admin-btn admin-btn-danger !py-1.5 !px-3 text-[10px]" data-admin-confirm="آیا از حذف آیتم {{ $item }} اطمینان دارید؟">
                                                    <span class="material-icons !text-sm">delete</span>
                                                    حذف
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($search)
                    <div class="admin-card text-center py-10 opacity-50">
                        <span class="material-icons text-4xl block mb-2">search_off</span>
                        هیچ آیتمی با کلید "{{ $search }}" یافت نشد.
                    </div>
                @else
                    <div class="admin-card text-center py-10 opacity-50">
                        <span class="material-icons text-4xl block mb-2">inventory_2</span>
                        برای مشاهده آیتم‌های کش، جستجو کنید.
                    </div>
                @endif
            @else
                <div class="admin-card text-center py-10">
                    <span class="material-icons text-4xl block mb-2 text-warning">info</span>
                    <p class="text-sm font-bold text-slate mb-1">مرور آیتم‌ها پشتیبانی نمی‌شود</p>
                    <p class="text-xs text-slate/60">درایور <strong>{{ $drivers[$currentDriver] ?? $currentDriver }}</strong> از مرور آیتم‌های کش پشتیبانی نمی‌کند. می‌توانید از تب پاکسازی کش برای حذف تمام آیتم‌ها استفاده کنید.</p>
                </div>
            @endif
        </div>
    </div>

</x-layouts.admin-dashboard>
@vite(['resources/js/admin-object-cache-hub.js'])
