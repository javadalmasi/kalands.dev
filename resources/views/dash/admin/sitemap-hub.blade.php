<x-layouts.admin-dashboard title="مدیریت سایت مپ">
    @php
        $authkey = request()->route('authkey');
    @endphp
    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت سایت مپ</h1>
        <div class="flex items-center gap-3">
            @if($isRunning)
                <span class="flex items-center gap-2 px-4 py-2 rounded-xl bg-warning/10 text-warning font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-warning animate-pulse"></span>
                    در حال پردازش...
                </span>
            @else
                <span class="flex items-center gap-2 px-4 py-2 rounded-xl bg-success/10 text-success font-bold text-xs">
                    <span class="w-2 h-2 rounded-full bg-success"></span>
                    غیرفعال
                </span>
            @endif
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="sitemap-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-overview">
                <span class="material-icons text-base">map</span>
                <span>وضعیت و آمار</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-logs">
                <span class="material-icons text-base">history</span>
                <span>گزارش اجراها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-actions">
                <span class="material-icons text-base">play_arrow</span>
                <span>اجرا و مدیریت</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <div id="tab-overview" class="tab-content space-y-6">
        @if($currentRun)
            <div class="admin-card">
                <h2 class="mb-4 font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">timeline</span>
                    اجرای فعلی
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate">شناسه اجرا</span>
                        <span class="font-mono text-xs admin-ltr">{{ $currentRun->run_id }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate">شروع</span>
                        <span class="admin-ltr text-xs">{{ $currentRun->started_at }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate">نوع</span>
                        <span class="text-xs">{{ $currentRun->force_mode ? 'بازسازی کامل' : 'افزایشی' }}</span>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-slate">پیشرفت</span>
                            <span class="font-bold text-primary">{{ $currentRun->progress }}%</span>
                        </div>
                        <div class="w-full h-3 rounded-full bg-slate/20 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-l from-primary to-secondary transition-all duration-500" style="width: {{ $currentRun->progress }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                        <div class="text-center p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                            <div class="text-2xl font-bold text-primary">{{ number_format($currentRun->processed_products) }}</div>
                            <div class="text-[10px] text-slate mt-1">محصول پردازش شده</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                            <div class="text-2xl font-bold text-primary">{{ $currentRun->total_products ? number_format($currentRun->total_products) : '...' }}</div>
                            <div class="text-[10px] text-slate mt-1">کل محصولات</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                            <div class="text-2xl font-bold text-primary">{{ $currentRun->total_chunks }}</div>
                            <div class="text-[10px] text-slate mt-1">فایل‌های ساخته شده</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                            <div class="text-2xl font-bold text-primary">{{ number_format($currentRun->total_chunks * 50000) }}</div>
                            <div class="text-[10px] text-slate mt-1">URL ایندکس شده</div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="admin-card">
                <div class="text-center py-12">
                    <span class="material-icons text-5xl text-slate/50 mb-4">map</span>
                    <p class="text-slate text-sm">هیچ اجرایی در حال انجام نیست.</p>
                    <p class="text-slate/60 text-xs mt-1">برای شروع تولید سایت مپ به تب "اجرا و مدیریت" مراجعه کنید.</p>
                </div>
            </div>
        @endif

        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">description</span>
                فایل‌های سایت مپ
            </h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm p-3 rounded-xl bg-slate/10 dark:bg-white/5">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-base text-primary">insert_link</span>
                        <span>Sitemap Index</span>
                    </div>
                    <div>
                        @if($sitemapIndexExists)
                            <a href="{{ $sitemapIndexUrl }}" target="_blank" rel="noopener" class="text-xs text-primary underline admin-ltr hover:text-primary/80 transition-colors">{{ $sitemapIndexUrl }}</a>
                        @else
                            <span class="text-xs text-danger">وجود ندارد</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm p-3 rounded-xl bg-slate/10 dark:bg-white/5">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-base text-primary">folder</span>
                        <span>فایل‌های سایت مپ (chunks)</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate">{{ number_format($chunkCount) }} فایل .xml.gz</span>
                        @if($chunkCount > 0)
                            <button onclick="toggleChunkList()" class="text-xs text-primary underline hover:text-primary/80 transition-colors">نمایش لیست</button>
                        @endif
                    </div>
                </div>
                @if($separateStores && ($dkChunkCount > 0 || $bsChunkCount > 0))
                <div class="flex items-center gap-4 pr-4">
                    @if($dkChunkCount > 0)
                    <span class="text-[10px] text-slate flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                        دیجی‌کالا: {{ number_format($dkChunkCount) }} فایل
                    </span>
                    @endif
                    @if($bsChunkCount > 0)
                    <span class="text-[10px] text-slate flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-warning"></span>
                        باسلام: {{ number_format($bsChunkCount) }} فایل
                    </span>
                    @endif
                </div>
                @endif
                @if($chunkCount > 0 && isset($chunkFileUrls))
                    <div id="chunk-list" class="hidden space-y-1 pr-4 max-h-48 overflow-y-auto custom-scrollbar">
                        @foreach($chunkFileUrls as $url)
                            <div class="text-xs text-slate admin-ltr">
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-primary/70 hover:text-primary underline transition-colors">{{ $url }}</a>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="flex items-center justify-between text-sm p-3 rounded-xl bg-slate/10 dark:bg-white/5">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-base text-primary">inventory_2</span>
                        <span>کل فضای اشغالی</span>
                    </div>
                    <span class="text-xs text-slate admin-ltr">{{ $totalSize }}</span>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">history</span>
                آخرین اجراها
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                    <h3 class="font-bold mb-3 flex items-center gap-2 text-sm">
                        <span class="material-icons text-base text-success">check_circle</span>
                        آخرین اجرای موفق
                    </h3>
                    @if($lastCompletedRun)
                        <div class="space-y-2 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span>شناسه</span>
                                <span class="admin-ltr font-mono">{{ $lastCompletedRun->run_id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>شروع</span>
                                <span class="admin-ltr">{{ $lastCompletedRun->started_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>پایان</span>
                                <span class="admin-ltr">{{ $lastCompletedRun->completed_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
                            </div>
                            @if($lastCompletedRun->started_at && $lastCompletedRun->completed_at)
                            <div class="flex items-center justify-between">
                                <span>مدت</span>
                                <span class="admin-ltr">{{ $lastCompletedRun->started_at->diffInSeconds($lastCompletedRun->completed_at) }} ثانیه</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span>محصولات</span>
                                <span>{{ number_format($lastCompletedRun->processed_products) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>فایل‌ها</span>
                                <span>{{ $lastCompletedRun->total_chunks }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>نوع</span>
                                <span>{{ $lastCompletedRun->force_mode ? 'بازسازی کامل' : 'افزایشی' }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate/60">تاکنون هیچ اجرای موفقی ثبت نشده است.</p>
                    @endif
                </div>
                <div class="p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                    <h3 class="font-bold mb-3 flex items-center gap-2 text-sm">
                        <span class="material-icons text-base text-danger">error</span>
                        آخرین اجرای ناموفق
                    </h3>
                    @if($lastFailedRun)
                        <div class="space-y-2 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span>شناسه</span>
                                <span class="admin-ltr font-mono">{{ $lastFailedRun->run_id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>شروع</span>
                                <span class="admin-ltr">{{ $lastFailedRun->started_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
                            </div>
                            @if($lastFailedRun->error_message)
                            <div class="bg-danger/5 border border-danger/20 p-2 rounded mt-1">
                                <p class="text-[11px] text-danger font-medium leading-5">خطا: {{ $lastFailedRun->error_message }}</p>
                            </div>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-slate/60">هیچ اجرای ناموفقی ثبت نشده است.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">timer</span>
                مدت زمان اجراها
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                    <h3 class="font-bold mb-3 flex items-center gap-2 text-sm">
                        <span class="material-icons text-base text-primary">cached</span>
                        بازسازی کامل
                    </h3>
                    @if($lastCompletedForce && $forceDuration !== null)
                        <div class="space-y-2 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span>شناسه اجرا</span>
                                <span class="admin-ltr font-mono">{{ $lastCompletedForce->run_id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>زمان شروع</span>
                                <span class="admin-ltr">{{ $lastCompletedForce->started_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>زمان پایان</span>
                                <span class="admin-ltr">{{ $lastCompletedForce->completed_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-base font-bold">
                                <span>مدت زمان</span>
                                <span class="admin-ltr text-primary">{{ floor($forceDuration / 60) }} دقیقه و {{ $forceDuration % 60 }} ثانیه</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>محصولات پردازش شده</span>
                                <span>{{ number_format($lastCompletedForce->processed_products) }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate/60">تاکنون بازسازی کاملی انجام نشده است.</p>
                    @endif
                </div>
                <div class="p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                    <h3 class="font-bold mb-3 flex items-center gap-2 text-sm">
                        <span class="material-icons text-base text-primary">trending_up</span>
                        اجرای افزایشی
                    </h3>
                    @if($lastCompletedIncremental && $incrementalDuration !== null)
                        <div class="space-y-2 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span>شناسه اجرا</span>
                                <span class="admin-ltr font-mono">{{ $lastCompletedIncremental->run_id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>زمان شروع</span>
                                <span class="admin-ltr">{{ $lastCompletedIncremental->started_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>زمان پایان</span>
                                <span class="admin-ltr">{{ $lastCompletedIncremental->completed_at->format('Y-m-d H:i:s') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-base font-bold">
                                <span>مدت زمان</span>
                                <span class="admin-ltr text-primary">{{ $incrementalDuration >= 60 ? floor($incrementalDuration / 60).' دقیقه و ' : '' }}{{ $incrementalDuration % 60 }} ثانیه</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>محصولات پردازش شده</span>
                                <span>{{ number_format($lastCompletedIncremental->processed_products) }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate/60">تاکنون اجرای افزایشی انجام نشده است.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">schedule</span>
                وضعیت زمان‌بندی
            </h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-sm p-3 rounded-xl bg-slate/10 dark:bg-white/5">
                    <span>وضعیت محدودیت زمانی</span>
                    <span class="{{ $scheduleEnabled ? 'text-success' : 'text-slate' }} text-xs font-bold">
                        {{ $scheduleEnabled ? 'فعال' : 'غیرفعال (همیشه مجاز)' }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm p-3 rounded-xl bg-slate/10 dark:bg-white/5">
                    <span>بازه مجاز (به وقت تهران)</span>
                    <span class="admin-ltr text-xs font-bold">{{ str_pad($scheduleStart, 2, '0', STR_PAD_LEFT) }}:00 — {{ str_pad($scheduleEnd, 2, '0', STR_PAD_LEFT) }}:00</span>
                </div>
                <div class="flex items-center justify-between text-sm p-3 rounded-xl bg-slate/10 dark:bg-white/5">
                    <span>ساعت فعلی تهران</span>
                    <span class="admin-ltr text-xs font-bold">{{ $nowTehran->format('H:i') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm p-3 rounded-xl bg-slate/10 dark:bg-white/5">
                    <span>در بازه مجاز</span>
                    <span class="{{ $inScheduleWindow ? 'text-success' : 'text-danger' }} text-xs font-bold">
                        {{ $inScheduleWindow ? 'بله' : 'خیر' }}
                    </span>
                </div>
                @if(!$inScheduleWindow && $scheduleEnabled)
                <div class="bg-warning/5 border border-warning/20 p-2 rounded">
                    <p class="text-[11px] text-warning">scheduler در بازه غیرمجاز از اجرا خودداری می‌کند.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div id="tab-logs" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="material-icons text-primary">history</span>گزارش ۵۰ اجرای آخر</div>
                <span class="text-[10px] font-normal text-slate">برای مشاهده جزئیات کلیک کنید</span>
            </h2>
            <div class="space-y-3 max-h-[700px] overflow-y-auto pr-2 custom-scrollbar">
                @forelse($lastRuns as $run)
                    <div class="rounded-xl border border-slate bg-slate/10 p-4 hover:bg-slate/20 transition-all cursor-pointer group dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10" onclick="toggleLogMeta(this)">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="material-icons text-sm text-slate">schedule</span>
                                <span class="text-xs font-bold admin-ltr">{{ $run->started_at?->format('Y-m-d H:i:s') ?? '—' }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $run->status === 'completed' ? 'bg-success text-white' : ($run->status === 'running' ? 'bg-warning text-white' : 'bg-danger text-white') }}">
                                {{ $run->status === 'completed' ? 'موفق' : ($run->status === 'running' ? 'در حال اجرا' : 'ناموفق') }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-[11px] text-slate mb-1">
                            <span class="material-icons text-xs">info</span>
                            <span>
                                @if($run->status === 'completed')
                                    {{ number_format($run->processed_products) }} محصول در {{ $run->total_chunks }} فایل — {{ $run->force_mode ? 'بازسازی کامل' : 'افزایشی' }}
                                @elseif($run->status === 'running')
                                    {{ number_format($run->processed_products) }} محصول پردازش شده از {{ $run->total_products ? number_format($run->total_products) : '?' }}
                                @else
                                    خطا: {{ Str::limit($run->error_message, 100) }}
                                @endif
                            </span>
                        </div>

                        @if($run->error_message)
                            <div class="bg-danger/5 border border-danger/20 p-2 rounded mt-2">
                                <p class="text-[11px] text-danger font-medium leading-5">پیام خطا: {{ $run->error_message }}</p>
                            </div>
                        @endif

                        <div class="log-meta hidden mt-4 pt-4 border-t border-slate/50 dark:border-white/10">
                            <p class="text-[11px] font-bold text-primary mb-2 flex items-center gap-1">
                                <span class="material-icons text-xs">task_alt</span>
                                جزئیات اجرا:
                            </p>
                            <ul class="space-y-1 text-[10px] text-slate-700 dark:text-slate-300">
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    شناسه اجرا: <span class="admin-ltr font-mono">{{ $run->run_id }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    محصولات کل: {{ number_format($run->total_products ?? 0) }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    محصولات پردازش شده: {{ number_format($run->processed_products) }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    تعداد فایل‌ها: {{ $run->total_chunks }}
                                </li>
                                @if($run->completed_at)
                                    <li class="flex items-center gap-2">
                                        <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                        پایان: <span class="admin-ltr">{{ $run->completed_at->format('Y-m-d H:i:s') }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate/60 text-sm">
                        هنوز هیچ اجرایی ثبت نشده است.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div id="tab-actions" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">play_arrow</span>
                اجرای تولید سایت مپ
            </h2>
            <p class="text-sm text-slate mb-6">
                با کلیک روی دکمه زیر، فرآیند تولید سایت مپ به صورت خودکار در صف پردازش قرار می‌گیرد.
                فقط محصولات جدید یا بروزرسانی شده پردازش خواهند شد.
            </p>
            <div class="flex flex-wrap gap-4">
                <form action="{{ route('dash.admin.sitemap.trigger', ['authkey' => $authkey]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="mode" value="incremental">
                    <button type="submit" class="admin-btn admin-btn-primary px-6 shadow-lg shadow-primary/20" {{ $isRunning ? 'disabled' : '' }}>
                        <span class="material-icons">play_arrow</span>
                        شروع پردازش افزایشی
                    </button>
                </form>
                <form action="{{ route('dash.admin.sitemap.trigger', ['authkey' => $authkey]) }}" method="POST" onsubmit="return confirm('آیا از بازسازی کامل سایت مپ اطمینان دارید؟ تمام فایل‌های قبلی حذف و از نو ساخته می‌شوند.')">
                    @csrf
                    <input type="hidden" name="mode" value="force">
                    <button type="submit" class="admin-btn admin-btn-danger px-6" {{ $isRunning ? 'disabled' : '' }}>
                        <span class="material-icons">restart_alt</span>
                        بازسازی کامل
                    </button>
                </form>
            </div>
            @if($isRunning)
                <p class="text-xs text-warning mt-4 flex items-center gap-1">
                    <span class="material-icons text-xs">warning</span>
                    در حال حاضر یک فرآیند در حال اجراست. پس از اتمام آن می‌توانید اجرای جدیدی را شروع کنید.
                </p>
            @endif
        </div>

        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">query_stats</span>
                تخمین زمان پردازش
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                    <h3 class="font-bold mb-3 flex items-center gap-2 text-sm">
                        <span class="material-icons text-base text-primary">cached</span>
                        بازسازی کامل
                    </h3>
                    @if($estForceSeconds !== null)
                        @php
                            $estForceMin = (int) floor($estForceSeconds / 60);
                            $estForceSec = $estForceSeconds % 60;
                        @endphp
                        <div class="space-y-2 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span>تخمین زمان</span>
                                <span class="admin-ltr text-primary font-bold text-base">{{ $estForceMin }} دقیقه و {{ $estForceSec }} ثانیه</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>تعداد محصولات فعال</span>
                                <span>{{ number_format($totalActive) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>تعداد فایل‌های تخمینی</span>
                                <span>{{ number_format((int) ceil($totalActive / 50000)) }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate/60">پس از حداقل یک بار اجرا، تخمین نشان داده می‌شود.</p>
                    @endif
                </div>
                <div class="p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                    <h3 class="font-bold mb-3 flex items-center gap-2 text-sm">
                        <span class="material-icons text-base text-primary">trending_up</span>
                        اجرای افزایشی
                    </h3>
                    @if($estIncrementalSeconds !== null)
                        @php
                            $estIncMin = (int) floor($estIncrementalSeconds / 60);
                            $estIncSec = $estIncrementalSeconds % 60;
                        @endphp
                        <div class="space-y-2 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span>تخمین زمان</span>
                                <span class="admin-ltr text-primary font-bold text-base">{{ $estIncMin > 0 ? $estIncMin.' دقیقه و ' : '' }}{{ $estIncSec }} ثانیه</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>محصولات آماده پردازش</span>
                                <span>{{ number_format($pendingProducts) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>از کل محصولات فعال</span>
                                <span>{{ number_format($totalActive) }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-slate/60">پس از حداقل یک بار اجرا، تخمین نشان داده می‌شود.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">tune</span>
                تنظیمات ماژول
            </h2>
            <form action="{{ route('dash.admin.sitemap.settings', ['authkey' => $authkey]) }}" method="POST" class="max-w-lg">
                @csrf
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-base text-primary">store</span>
                            جداسازی فروشگاه‌ها
                        </h3>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="hidden" name="separate_stores" value="0">
                            <input type="checkbox" name="separate_stores" value="1" class="w-4 h-4 rounded border-slate/30 text-primary focus:ring-primary" {{ $separateStores ? 'checked' : '' }}>
                            <span class="text-sm text-slate">جداسازی سایت مپ دیجی‌کالا و باسلام</span>
                        </label>
                        <p class="text-[11px] text-slate/60 mt-1 pr-6">
                            در حالت پیش‌فرض همه محصولات در یک فایل سایت مپ مخلوط هستند. با فعال‌سازی، فایل‌های مجزا برای هر فروشگاه ساخته می‌شود.
                        </p>
                    </div>

                    <hr class="border-slate/10">

                    <div>
                        <h3 class="text-sm font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-base text-primary">schedule</span>
                            زمان‌بندی اجرا
                        </h3>
                        <p class="text-xs text-slate mb-4">
                            بازه ساعتی (به وقت تهران) که scheduler مجاز به شروع فرآیند جدید سایت مپ است.
                        </p>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="hidden" name="schedule_enabled" value="0">
                                    <input type="checkbox" name="schedule_enabled" value="1" class="w-4 h-4 rounded border-slate/30 text-primary focus:ring-primary" {{ $scheduleEnabled ? 'checked' : '' }}>
                                    <span class="text-sm text-slate">فعال کردن محدودیت زمانی</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-slate mb-2">شروع بازه</label>
                                    <select name="schedule_start" class="admin-input w-full">
                                        @for($h = 0; $h <= 23; $h++)
                                            <option value="{{ $h }}" {{ $scheduleStart === $h ? 'selected' : '' }}>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate mb-2">پایان بازه</label>
                                    <select name="schedule_end" class="admin-input w-full">
                                        @for($h = 0; $h <= 23; $h++)
                                            <option value="{{ $h }}" {{ $scheduleEnd === $h ? 'selected' : '' }}>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <p class="text-[11px] text-slate/60">مثال: شروع ۱ و پایان ۶ به معنای بازه ۰۱:۰۰ تا ۰۵:۵۹ است. scheduler فقط در این ساعات اجرای جدید شروع می‌کند.</p>
                        </div>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="tab-help" class="tab-content space-y-6 hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای ماژول سایت مپ</h2>
                    </div>
                    <section id="doc-howto">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">info</span>
                            نحوه عملکرد
                        </h3>
                        <p class="text-sm text-slate leading-7">این ماژول به صورت هوشمند و افزایشی کار می‌کند. در هر بار اجرا، فقط محصولاتی که:</p>
                        <ul class="list-disc list-inside space-y-1 mt-2 text-xs text-slate">
                            <li>جدید اضافه شده‌اند (sitemapped_at null)</li>
                            <li>بروزرسانی شده‌اند (updated_at > sitemapped_at)</li>
                        </ul>
                        <p class="mt-2 text-sm text-slate leading-7">در صف پردازش قرار می‌گیرند. محصولات پردازش نشده در فایل‌های ۵۰٬۰۰۰ تایی gzip شده ذخیره می‌شوند.</p>
                    </section>
                    <hr class="border-slate/10">
                    <section id="doc-schedule">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">schedule</span>
                            زمان‌بندی
                        </h3>
                        <p class="text-sm text-slate leading-7">فرآیند تولید سایت مپ هر ۵ دقیقه یکبار توسط scheduler لاراول بررسی می‌شود. در صورت نبود فرآیند فعال و قرار داشتن در بازه زمانی مجاز، اجرای جدید شروع می‌شود.</p>
                        <p class="mt-2 text-sm text-slate leading-7">بازه زمانی مجاز در تب "اجرا و مدیریت" قابل تنظیم است. پیشنهاد می‌شود در ساعات خلوت سایت (مثلاً ۱ تا ۶ بامداد به وقت تهران) تنظیم شود.</p>
                    </section>
                    <hr class="border-slate/10">
                    <section id="doc-rebuild">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">restart_alt</span>
                            بازسازی کامل
                        </h3>
                        <p class="text-sm text-slate leading-7">اگر نیاز به بازسازی کامل همه فایل‌های سایت مپ دارید (مثلاً بعد از تغییرات عمده در ساختار URLها)، از گزینه "بازسازی کامل" در تب "اجرا و مدیریت" استفاده کنید. این کار تمام فایل‌های قبلی را حذف کرده و از نو می‌سازد.</p>
                    </section>
                    <hr class="border-slate/10">
                    <section id="doc-files">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">description</span>
                            ساختار فایل‌ها
                        </h3>
                        <ul class="list-disc list-inside space-y-1 mt-2 text-xs text-slate">
                            <li><span class="admin-ltr font-mono">/sitemap.xml</span> — ایندکس اصلی سایت مپ</li>
                            <li><span class="admin-ltr font-mono">/sitemaps/sitemap-{run}-{chunk}.xml.gz</span> — فایل‌های سایت مپ فشرده شده</li>
                            <li>هر فایل حداکثر ۵۰٬۰۰۰ URL دارد</li>
                            <li>تعداد کل فایل‌ها بستگی به تعداد محصولات فعال دارد</li>
                            <li>فقط محصولات فعال (is_active = true) ایندکس می‌شوند</li>
                        </ul>
                    </section>
                </div>
            </div>
            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-howto" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نحوه عملکرد</a>
                        <a href="#doc-schedule" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">زمان‌بندی</a>
                        <a href="#doc-rebuild" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">بازسازی کامل</a>
                        <a href="#doc-files" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">ساختار فایل‌ها</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
    <script>
        document.querySelectorAll('#sitemap-tabs button').forEach(btn => {
            btn.addEventListener('click', function() {
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

        function toggleLogMeta(el) {
            const meta = el.querySelector('.log-meta');
            if (meta) meta.classList.toggle('hidden');
        }

        function toggleChunkList() {
            document.getElementById('chunk-list').classList.toggle('hidden');
        }

    </script>
    @endpush
</x-layouts.admin-dashboard>
