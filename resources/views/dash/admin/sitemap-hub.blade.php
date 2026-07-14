<x-layouts.admin-dashboard title="مدیریت سایت مپ" :helpModuleKey="'sitemap'">
    @php
        $authkey = request()->route('authkey');
    @endphp

    <x-admin.page-header title="مدیریت سایت مپ">
        <x-slot:actions>
            @if($isRunning)
                <span class="flex items-center gap-2 px-4 py-2 rounded-xl bg-warning/10 text-warning font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-warning animate-pulse"></span>
                    در حال بازسازی...
                </span>
            @else
                <span class="flex items-center gap-2 px-4 py-2 rounded-xl {{ $mode === 'auto' ? 'bg-success/10 text-success' : 'bg-slate/10 text-slate' }} font-bold text-xs">
                    <span class="w-2 h-2 rounded-full {{ $mode === 'auto' ? 'bg-success' : 'bg-slate' }}"></span>
                    {{ $mode === 'auto' ? 'آماده (خودکار)' : 'خاموش' }}
                </span>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="sitemap-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-overview">
            <span class="material-icons text-base">map</span>
            <span>وضعیت و آمار</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-logs">
            <span class="material-icons text-base">history</span>
            <span>گزارش اجراها</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-settings">
            <span class="material-icons text-base">settings</span>
            <span>تنظیمات و کنترل</span>
        </button>
    </x-admin.tab-bar>

    {{-- ================= OVERVIEW ================= --}}
    <div id="tab-overview" class="tab-content space-y-6">
        @if($currentRun)
            <div class="admin-card">
                <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate/10">
                    <h2 class="font-bold flex items-center gap-2">
                        <span class="material-icons text-primary">timeline</span>
                        بازسازی فعلی
                    </h2>
                    <span class="text-[10px] text-slate/60">محاسبه‌ی متادیتای شاردها</span>
                </div>
                <div class="rounded-xl border border-primary/20 bg-primary/[0.04] p-4 space-y-2.5 text-xs text-slate">
                    <div class="flex items-center justify-between">
                        <span class="text-slate/60">شناسه</span>
                        <span class="admin-ltr font-mono text-[11px]" data-sitemap-stat="run-id">{{ $currentRun->run_id }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate/60">شروع</span>
                        <span class="admin-ltr">{{ persianDateTime($currentRun->started_at) }}</span>
                    </div>
                    <div class="pt-2.5 mt-2.5 border-t border-primary/10">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-slate/60">پیشرفت</span>
                            <span class="font-bold text-primary"><span data-sitemap-stat="progress">{{ $currentRun->progress }}</span>%</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-slate/20 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-l from-primary to-secondary transition-all duration-500" data-sitemap-stat="progress-bar" style="width: {{ $currentRun->progress }}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2.5 mt-2.5 border-t border-primary/10">
                        <span class="text-slate/60">محصولات پردازش شده</span>
                        <span class="font-bold"><span data-sitemap-stat="processed">{{ number_format($currentRun->processed_products) }}</span> / {{ number_format($currentRun->total_products) }}</span>
                    </div>
                </div>
            </div>
        @endif

        <div class="admin-card">
            <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate/10">
                <h2 class="font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">insights</span>
                    آمار کلی
                </h2>
                <span class="text-[10px] text-slate/60">بروزرسانی هر ۶۰ ثانیه</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <span class="material-icons text-primary text-lg mb-1">inventory_2</span>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="active-products">{{ number_format($activeProducts) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">محصولات فعال</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <span class="material-icons text-primary text-lg mb-1">link</span>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="total-urls">{{ number_format($totalUrls) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">URL در sitemap</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <span class="material-icons text-primary text-lg mb-1">description</span>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="shard-count">{{ number_format($shardCount) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">زیرـsitemap</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <span class="material-icons text-primary text-lg mb-1">tag</span>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="generation">{{ number_format($activeGeneration) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">نسل فعال</div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between p-2 rounded-lg bg-slate/10 text-[10px] text-slate/50">
                <span>آخرین بازسازی: <span class="admin-ltr">{{ $lastBuildAt ? persianDateTime($lastBuildAt) : 'هرگز' }}</span></span>
                <span>هر فایل حداکثر {{ number_format(config('sitemap.urls_per_shard')) }} URL</span>
            </div>
        </div>

        <div class="admin-card !p-0 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate/10">
                <h2 class="font-bold flex items-center gap-2 text-sm">
                    <span class="material-icons text-primary">account_tree</span>
                    فایل‌های sitemap
                </h2>
                @if($hasSitemap)
                    <a href="{{ $indexUrl }}" target="_blank" rel="noopener" class="text-xs font-bold text-primary hover:underline admin-ltr">sitemap.xml</a>
                @else
                    <span class="text-[10px] text-danger">هنوز ساخته نشده</span>
                @endif
            </div>
            <div class="px-5 py-4">
                @if($shardCount > 0)
                    <div class="rounded-xl border border-slate/10 overflow-hidden">
                        <div class="divide-y divide-slate/10 max-h-80 overflow-y-auto custom-scrollbar">
                            @foreach($shards as $shard)
                                <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate/5 transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                        <span class="material-icons text-sm text-slate/40 shrink-0">description</span>
                                        <span class="text-[11px] text-slate/70 admin-ltr truncate">product-sitemap{{ $shard['index'] }}.xml</span>
                                        <span class="text-[10px] text-slate/40 shrink-0">({{ number_format($shard['url_count']) }} URL)</span>
                                    </div>
                                    <a href="{{ $shard['url'] }}" target="_blank" rel="noopener" class="text-primary/60 hover:text-primary p-1 shrink-0" title="باز کردن">
                                        <span class="material-icons text-sm">open_in_new</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-slate/60 text-sm">هنوز sitemap ساخته نشده است. از تب «تنظیمات و کنترل» یک بازسازی شروع کنید.</div>
                @endif
                <div class="mt-4 flex items-start gap-2 text-[11px] text-slate/50 leading-6">
                    <span class="material-icons text-xs text-primary/50 mt-0.5">info</span>
                    <span>خروجی به‌صورت XML داینامیک سرو می‌شود (بدون فایل استاتیک). ایندکس در <span class="admin-ltr">/sitemap.xml</span> به زیرـsitemapها اشاره می‌کند و هر کدام هنگام درخواست از دیتابیس ساخته و کش می‌شود. بازسازی فقط متادیتای مرزها را به‌روز می‌کند و بدون داون‌تایم جایگزین می‌شود.</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= LOGS ================= --}}
    <div id="tab-logs" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="font-bold flex items-center gap-2">
                        <span class="material-icons text-primary">history</span>
                        گزارش ۵۰ اجرای آخر
                    </h2>
                    <p class="text-[10px] text-slate mt-1">{{ $totalRuns }} اجرا · {{ $completedRuns }} موفق</p>
                </div>
                <button type="button"
                    onclick="document.getElementById('sitemap-clean-logs-modal').showModal()"
                    class="admin-btn admin-btn-secondary border-danger/20 text-danger hover:bg-danger/10 text-xs">
                    <span class="material-icons text-sm">delete_sweep</span>
                    حذف لاگ‌ها
                </button>
            </div>
            <div class="space-y-3 max-h-[700px] overflow-y-auto pr-2 custom-scrollbar">
                @forelse($lastRuns as $run)
                    <div class="rounded-xl border border-slate bg-slate/10 p-4 dark:border-white/5 dark:bg-white/5">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="material-icons text-sm text-slate">schedule</span>
                                <span class="text-xs font-bold admin-ltr">{{ persianDateTime($run->started_at) }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $run->status === 'completed' ? 'bg-success text-white' : ($run->status === 'running' ? 'bg-warning text-white' : 'bg-danger text-white') }}">
                                {{ $run->status === 'completed' ? 'موفق' : ($run->status === 'running' ? 'در حال اجرا' : 'ناموفق') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-slate mb-1">
                            <span class="material-icons text-xs">info</span>
                            <span>
                                {{ number_format($run->processed_products) }} محصول پردازش شد
                                @if($run->completed_at && $run->started_at)
                                    — {{ $run->started_at->diffInSeconds($run->completed_at) }} ثانیه
                                @endif
                            </span>
                        </div>
                        @if($run->error_message)
                            <div class="bg-danger/5 border border-danger/20 p-2 rounded mt-2">
                                <p class="text-[11px] text-danger font-medium leading-5">{{ $run->error_message }}</p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-slate/60 text-sm">هنوز هیچ اجرایی ثبت نشده است.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= SETTINGS ================= --}}
    <div id="tab-settings" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">bolt</span>
                کنترل دستی
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <form action="{{ route('dash.admin.sitemap.rebuild', ['authkey' => $authkey]) }}" method="POST">
                    @csrf
                    <button type="submit" onclick="return confirm('بازسازی سایت‌مپ شروع شود؟')"
                        class="admin-btn admin-btn-primary w-full justify-center" {{ $isRunning ? 'disabled' : '' }}>
                        <span class="material-icons">restart_alt</span>
                        بازسازی سایت‌مپ
                    </button>
                    <p class="text-[10px] text-slate/50 mt-1.5 leading-5">مرز شاردها دوباره محاسبه و نسل جدید بدون داون‌تایم جایگزین می‌شود.</p>
                </form>
                @if($isRunning)
                    <form action="{{ route('dash.admin.sitemap.stop', ['authkey' => $authkey]) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('فرآیند در حال اجرا متوقف شود؟')"
                            class="admin-btn admin-btn-secondary border-warning/20 text-warning hover:bg-warning/10 w-full justify-center">
                            <span class="material-icons">stop_circle</span>
                            توقف فرآیند فعلی
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">tune</span>
                تنظیمات ماژول
            </h2>
            <form action="{{ route('dash.admin.sitemap.settings', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
                @csrf
                <label class="flex items-center justify-between gap-4 rounded-xl border border-slate/10 bg-slate/[0.04] p-4 cursor-pointer">
                    <span>
                        <span class="block text-sm font-bold">حالت خودکار</span>
                        <span class="block text-[11px] text-slate/60 mt-1">در حالت خودکار، زمان‌بند سیستم در فواصل تعیین‌شده به‌صورت خودکار سایت‌مپ را بازسازی می‌کند.</span>
                    </span>
                    <span class="admin-switch">
                        <input type="hidden" name="mode" value="off">
                        <input type="checkbox" name="mode" value="auto" class="admin-switch-input" {{ $mode === 'auto' ? 'checked' : '' }}>
                        <span class="admin-switch-track"></span>
                        <span class="admin-switch-ball"></span>
                    </span>
                </label>

                <div>
                    <label class="text-xs font-bold text-slate/70 mb-2 block">فاصله‌ی بازسازی خودکار (ساعت)</label>
                    <input type="number" name="rebuild_interval_hours" value="{{ $rebuildIntervalHours }}" min="1" max="720" class="admin-input max-w-xs">
                    <p class="text-[10px] text-slate/50 mt-1.5 leading-5">
                        آخرین بازسازی: {{ $lastBuildAt ? persianDateTime($lastBuildAt) : 'هرگز' }}
                        @if($rebuildDue)<span class="text-warning font-bold">— موعد فرا رسیده</span>@endif
                    </p>
                </div>

                <button type="submit" class="admin-btn admin-btn-primary">
                    <span class="material-icons">save</span>
                    ذخیره تنظیمات
                </button>
            </form>
        </div>

        <div class="admin-card">
            <h2 class="text-sm font-bold mb-3 flex items-center gap-2 text-danger">
                <span class="material-icons text-base">dangerous</span>
                بازنشانی کامل
            </h2>
            <p class="text-xs text-slate/60 mb-3 leading-6">تمام شاردها و کش سایت‌مپ حذف و pointer نسل صفر می‌شود. تا زمانی که بازسازی جدید انجام نشود، <span class="admin-ltr">/sitemap.xml</span> در دسترس نخواهد بود.</p>
            <button type="button"
                onclick="document.getElementById('sitemap-reset-modal').showModal()"
                class="admin-btn admin-btn-secondary border-danger/20 text-danger hover:bg-danger/10">
                <span class="material-icons">restart_alt</span>
                بازنشانی کامل
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('#sitemap-tabs button').forEach(btn => {
            btn.addEventListener('click', function () {
                const target = this.dataset.tabTarget;
                document.querySelectorAll('#sitemap-tabs button').forEach(b => {
                    b.classList.remove('border-primary', 'text-primary', 'font-bold');
                    b.classList.add('text-slate', 'font-medium');
                });
                this.classList.add('border-primary', 'text-primary', 'font-bold');
                this.classList.remove('text-slate', 'font-medium');
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.add('hidden'));
                document.getElementById(target).classList.remove('hidden');
            });
        });

        function formatNumber(value) {
            return new Intl.NumberFormat('fa-IR').format(Number(value || 0));
        }

        function setStat(key, value) {
            document.querySelectorAll('[data-sitemap-stat="' + key + '"]').forEach(el => { el.textContent = value; });
        }

        function refreshSitemapStatus() {
            fetch('{{ route('dash.admin.sitemap.status', ['authkey' => $authkey]) }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (!data) return;
                    setStat('active-products', formatNumber(data.active_products));
                    setStat('total-urls', formatNumber(data.total_urls));
                    setStat('shard-count', formatNumber(data.shard_count));
                    setStat('generation', formatNumber(data.active_generation));

                    if (data.current_run) {
                        setStat('processed', formatNumber(data.current_run.processed_products));
                        setStat('progress', data.current_run.progress);
                        document.querySelectorAll('[data-sitemap-stat="progress-bar"]').forEach(el => {
                            el.style.width = data.current_run.progress + '%';
                        });
                    } else if (!data.is_running && document.querySelector('[data-sitemap-stat="run-id"]')) {
                        window.location.reload();
                    }
                })
                .catch(() => {});
        }

        window.setInterval(refreshSitemapStatus, 60000);
    </script>
    @endpush

    <dialog id="sitemap-reset-modal" class="admin-dialog w-[min(100vw-32px,450px)]">
        <div class="admin-dialog-body">
            <div class="flex items-start gap-4 p-2">
                <div class="w-12 h-12 rounded-full bg-danger/10 text-danger flex items-center justify-center shrink-0">
                    <span class="material-icons !text-2xl">warning</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-slate-800 dark:text-white mb-2">بازنشانی کامل سایت‌مپ</h3>
                    <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">تمام شاردها و کش حذف می‌شوند و <span class="admin-ltr">/sitemap.xml</span> تا بازسازی بعدی در دسترس نخواهد بود.</p>
                    <p class="text-xs leading-6 text-danger mt-2 font-bold">این عملیات قابل بازگشت نیست. ادامه می‌دهید؟</p>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button type="button" onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <form method="POST" action="{{ route('dash.admin.sitemap.reset', ['authkey' => $authkey]) }}" class="inline">
                    @csrf
                    <button type="submit" class="admin-btn bg-danger text-white hover:bg-danger/90 px-8">تایید و بازنشانی</button>
                </form>
            </div>
        </div>
    </dialog>

    <dialog id="sitemap-clean-logs-modal" class="admin-dialog w-[min(100vw-32px,450px)]">
        <div class="admin-dialog-body">
            <div class="flex items-start gap-4 p-2">
                <div class="w-12 h-12 rounded-full bg-danger/10 text-danger flex items-center justify-center shrink-0">
                    <span class="material-icons !text-2xl">delete_sweep</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-slate-800 dark:text-white mb-2">حذف لاگ‌های سایت‌مپ</h3>
                    <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">تمام رکوردهای گزارش اجرا (به‌جز اجرای در حال انجام) حذف می‌شوند.</p>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button type="button" onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <form method="POST" action="{{ route('dash.admin.sitemap.clean-logs', ['authkey' => $authkey]) }}" class="inline">
                    @csrf
                    <button type="submit" class="admin-btn bg-danger text-white hover:bg-danger/90 px-8">حذف لاگ‌ها</button>
                </form>
            </div>
        </div>
    </dialog>
</x-layouts.admin-dashboard>
