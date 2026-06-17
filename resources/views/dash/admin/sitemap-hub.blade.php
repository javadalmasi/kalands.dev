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
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-periodic">
                <span class="material-icons text-base">schedule</span>
                <span>بازسازی دوره‌ای</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-queue">
                <span class="material-icons text-base">queue</span>
                <span>مدیریت صف</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-actions">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات و کنترل</span>
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
                <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate/10">
                    <h2 class="font-bold flex items-center gap-2">
                        <span class="material-icons text-primary">timeline</span>
                        اجرای فعلی
                    </h2>
                    <span class="text-[10px] text-slate/60">در حال اجرا</span>
                </div>
                <div class="rounded-xl border border-primary/20 bg-primary/[0.04] p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-icons text-primary text-lg">play_circle</span>
                        <h3 class="font-bold text-sm">جزئیات اجرا</h3>
                    </div>
                    <div class="space-y-2.5 text-xs text-slate">
                        <div class="flex items-center justify-between">
                            <span class="text-slate/60">شناسه</span>
                            <span class="admin-ltr font-mono text-[11px]">{{ $currentRun->run_id }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate/60">شروع</span>
                            <span class="admin-ltr">{{ persianDateTime($currentRun->started_at) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate/60">نوع</span>
                            <span>چرخه یکپارچه خودکار</span>
                        </div>
                        <div class="pt-2.5 mt-2.5 border-t border-primary/10">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-slate/60">پیشرفت</span>
                                <span class="font-bold text-primary">{{ $currentRun->progress }}%</span>
                            </div>
                            <div class="w-full h-2.5 rounded-full bg-slate/20 overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-l from-primary to-secondary transition-all duration-500" style="width: {{ $currentRun->progress }}%"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2.5 mt-2.5 border-t border-primary/10">
                            <span class="text-slate/60">محصولات پردازش شده</span>
                            <span class="font-bold">{{ number_format($currentRun->processed_products) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate/60">کل محصولات</span>
                            <span class="font-bold">{{ $currentRun->total_products ? number_format($currentRun->total_products) : '...' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate/60">فایل‌های ساخته شده</span>
                            <span class="font-bold">{{ $currentRun->total_chunks }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate/60">URL ایندکس شده</span>
                            <span class="font-bold">{{ number_format($currentRun->total_chunks * 50000) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="admin-card">
                <div class="text-center py-12">
                    <span class="material-icons text-5xl text-slate/50 mb-4">map</span>
                    <p class="text-slate text-sm">در حال حاضر اجرای فعالی دیده نمی‌شود.</p>
                    <p class="text-slate/60 text-xs mt-1">در حالت خودکار، چرخه‌ی بعدی تولید به‌صورت خودکار شروع می‌شود.</p>
                </div>
            </div>
        @endif

        <div class="admin-card !p-0 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate/10">
                <h2 class="font-bold flex items-center gap-2 text-sm">
                    <span class="material-icons text-primary">description</span>
                    فایل‌های سایت‌مپ
                </h2>
                <span class="text-[10px] text-slate/60 bg-slate/10 px-3 py-1 rounded-full">چرخه یکپارچه</span>
            </div>
            <div class="px-5 py-4">
                <div class="grid grid-cols-3 gap-4 mb-5">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate/10">
                        <span class="material-icons text-primary text-2xl">folder_zip</span>
                        <div>
                            <div class="text-lg font-bold admin-ltr">{{ number_format($chunkCount) }}</div>
                            <div class="text-[10px] text-slate/60">فایل gzip</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate/10">
                        <span class="material-icons text-primary text-2xl">storage</span>
                        <div>
                            <div class="text-lg font-bold admin-ltr">{{ $totalSize }}</div>
                            <div class="text-[10px] text-slate/60">فضای اشغالی</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate/10">
                        <span class="material-icons text-2xl {{ $sitemapIndexExists ? 'text-success' : 'text-danger' }}">insert_link</span>
                        <div>
                            @if($sitemapIndexExists)
                                <a href="{{ $sitemapIndexUrl }}" target="_blank" rel="noopener" class="text-sm font-bold text-primary hover:underline admin-ltr">sitemap.xml</a>
                            @else
                                <div class="text-sm font-bold text-danger">—</div>
                            @endif
                            <div class="text-[10px] text-slate/60">فایل ایندکس</div>
                        </div>
                    </div>
                </div>
                @if($separateStores && ($dkChunkCount > 0 || $bsChunkCount > 0))
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-[10px] text-slate/60 ml-2">تفکیک فروشگاه:</span>
                    @if($dkChunkCount > 0)
                    <span class="text-[11px] text-slate flex items-center gap-1.5 bg-slate/10 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                        دیجی‌کالا {{ number_format($dkChunkCount) }}
                    </span>
                    @endif
                    @if($bsChunkCount > 0)
                    <span class="text-[11px] text-slate flex items-center gap-1.5 bg-slate/10 px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-warning"></span>
                        باسلام {{ number_format($bsChunkCount) }}
                    </span>
                    @endif
                </div>
                @endif
                @if($chunkCount > 0 && isset($chunkFileUrls))
                <div class="rounded-xl border border-slate/10 overflow-hidden">
                    <button onclick="toggleChunkList()" class="w-full flex items-center justify-between px-4 py-3 text-xs text-slate/70 hover:text-slate hover:bg-slate/5 transition-colors">
                        <span class="flex items-center gap-2">
                            <span class="material-icons text-sm" id="chunk-toggle-icon">expand_more</span>
                            <span id="chunk-toggle-text" data-show-label="{{ number_format($chunkCount) }} فایل — نمایش لیست">{{ number_format($chunkCount) }} فایل — نمایش لیست</span>
                        </span>
                        <span class="text-[10px] text-slate/40">برای کپی کلیک کنید</span>
                    </button>
                    <div id="chunk-list" class="hidden divide-y divide-slate/10 max-h-56 overflow-y-auto custom-scrollbar border-t border-slate/10">
                        @foreach($chunkFileUrls as $url)
                        <div class="flex items-center justify-between px-4 py-2.5 hover:bg-slate/5 transition-colors group">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <span class="material-icons text-sm text-slate/40 shrink-0">description</span>
                                <span class="text-[11px] text-slate/70 admin-ltr truncate group-hover:text-slate transition-colors" title="{{ basename($url) }}">{{ basename($url) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="text-[10px] text-primary/60 hover:text-primary transition-colors p-1" title="باز کردن">
                                    <span class="material-icons text-sm">open_in_new</span>
                                </a>
                                <button onclick="copyToClipboard('{{ $url }}', this)" class="text-[10px] text-slate/40 hover:text-primary transition-colors p-1" title="کپی لینک">
                                    <span class="material-icons text-sm">content_copy</span>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                <div class="mt-4 flex items-start gap-2 text-[11px] text-slate/50 leading-6">
                    <span class="material-icons text-xs text-primary/50 mt-0.5">info</span>
                    <span>تولید دائمی: هر batch شامل ۱٬۰۰۰ URL و هر ۵۰ batch یک فایل gzip با حداکثر ۵۰٬۰۰۰ URL می‌سازد. چرخه‌ها بازتولید کامل هستند تا هیچ آیتم تکراری در خروجی نهایی وجود نداشته باشد.</span>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate/10">
                <h2 class="font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">history</span>
                    آخرین اجراها
                </h2>
                <span class="text-[10px] text-slate/60">{{ $totalRuns }} اجرا · {{ $totalRunsCompleted }} موفق</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-success/20 bg-success/[0.04] p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-icons text-success text-lg">check_circle</span>
                        <h3 class="font-bold text-sm">آخرین اجرای موفق</h3>
                    </div>
                    @if($lastCompletedRun)
                        <div class="space-y-2.5 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span class="text-slate/60">شناسه</span>
                                <span class="admin-ltr font-mono text-[11px]">{{ $lastCompletedRun->run_id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate/60">زمان</span>
                                <span class="admin-ltr">{{ persianDateTime($lastCompletedRun->started_at) }}</span>
                            </div>
                            @if($lastCompletedRun->started_at && $lastCompletedRun->completed_at)
                            <div class="flex items-center justify-between">
                                <span class="text-slate/60">مدت</span>
                                <span class="admin-ltr font-bold text-success">{{ $lastCompletedRun->started_at->diffInSeconds($lastCompletedRun->completed_at) }} ثانیه</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between pt-2.5 mt-2.5 border-t border-success/10">
                                <span class="text-slate/60">محصولات</span>
                                <span class="font-bold">{{ number_format($lastCompletedRun->processed_products) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate/60">فایل‌ها</span>
                                <span class="font-bold">{{ $lastCompletedRun->total_chunks }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6 text-slate/50 text-xs">تاکنون هیچ اجرای موفقی ثبت نشده است.</div>
                    @endif
                </div>
                <div class="rounded-xl border border-danger/20 bg-danger/[0.04] p-4">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-icons text-danger text-lg">error</span>
                        <h3 class="font-bold text-sm">آخرین اجرای ناموفق</h3>
                    </div>
                    @if($lastFailedRun)
                        <div class="space-y-2.5 text-xs text-slate">
                            <div class="flex items-center justify-between">
                                <span class="text-slate/60">شناسه</span>
                                <span class="admin-ltr font-mono text-[11px]">{{ $lastFailedRun->run_id }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate/60">زمان</span>
                                <span class="admin-ltr">{{ persianDateTime($lastFailedRun->started_at) }}</span>
                            </div>
                            @if($lastFailedRun->error_message)
                            <div class="mt-3 p-2.5 rounded-lg bg-danger/10 border border-danger/20">
                                <p class="text-[11px] text-danger leading-5">{{ $lastFailedRun->error_message }}</p>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-6 text-slate/50 text-xs">هیچ اجرای ناموفقی ثبت نشده است.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate/10">
                <h2 class="font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">speed</span>
                    آمار عملکرد
                </h2>
                <span class="text-[10px] text-slate/60">بر اساس ۱۰ اجرای آخر</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">history_toggle_off</span>
                    </div>
                    @if($lastCompletedForce && $forceDuration !== null)
                        @php
                            $mins = floor($forceDuration / 60);
                            $secs = $forceDuration % 60;
                        @endphp
                        <div class="text-lg font-bold text-primary admin-ltr">{{ $mins > 0 ? $mins.'m' : '' }}{{ $secs }}s</div>
                        <div class="text-[9px] text-slate/60 mt-0.5">آخرین مدت اجرا</div>
                    @else
                        <div class="text-lg font-bold text-slate/40">—</div>
                        <div class="text-[9px] text-slate/60 mt-0.5">مدت اجرا</div>
                    @endif
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">avg_pace</span>
                    </div>
                    @if($avgSpeed !== null)
                        <div class="text-lg font-bold text-primary admin-ltr">{{ number_format($avgSpeed, 1) }}</div>
                        <div class="text-[9px] text-slate/60 mt-0.5">میانگین سرعت (item/s)</div>
                    @else
                        <div class="text-lg font-bold text-slate/40">—</div>
                        <div class="text-[9px] text-slate/60 mt-0.5">میانگین سرعت</div>
                    @endif
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">inventory</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr">{{ number_format($totalActive) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">محصولات فعال</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">pending</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr">{{ number_format($pendingProducts) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">در انتظار پردازش</div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate/10 dark:bg-white/5 text-xs">
                    <span class="text-slate/60">نرخ لحظه‌ای</span>
                    <span class="admin-ltr font-bold" data-sitemap-stat="current-rate">{{ $currentRate }}/10</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate/10 dark:bg-white/5 text-xs">
                    <span class="text-slate/60">batch در ساعت</span>
                    <span class="admin-ltr font-bold" data-sitemap-stat="batches-per-hour">{{ number_format($currentBatchesPerHour) }}</span>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate/10 dark:bg-white/5 text-xs">
                    <span class="text-slate/60">فاصله batch</span>
                    <span class="admin-ltr font-bold" data-sitemap-stat="delay-seconds">{{ number_format($currentDelaySeconds) }}s</span>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate/10">
                <h2 class="font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">insights</span>
                    وضعیت لحظه‌ای تولید
                </h2>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 text-[10px] {{ $mode === 'auto' ? 'text-success' : 'text-slate/40' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $mode === 'auto' ? 'bg-success animate-pulse' : 'bg-slate/40' }}"></span>
                        {{ $mode === 'auto' ? 'فعال' : 'خاموش' }}
                    </span>
                    <span class="text-[10px] text-slate/40">|</span>
                    <span class="text-[10px] text-slate/60">بروزرسانی هر ۶۰ ثانیه</span>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3" id="sitemap-live-status">
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="flex items-center gap-1">
                            <span class="material-icons text-primary text-lg">switch_access_shortcut</span>
                        </span>
                    </div>
                    <div class="text-lg font-bold {{ $mode === 'auto' ? 'text-success' : 'text-slate/40' }}">{{ $mode === 'auto' ? 'فعال' : 'خاموش' }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">وضعیت ماژول</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">speed</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="current-rate">{{ $currentRate }}/10</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">نرخ ساعت فعلی</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">dynamic_feed</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="batches-per-hour">{{ number_format($currentBatchesPerHour) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">batch در ساعت</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">hourglass_bottom</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="delay-seconds">{{ number_format($currentDelaySeconds) }}s</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">فاصله بین batchها</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">boundary</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="max-batches">{{ number_format($maxBatchesPerHour) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">سقف تنظیم شده</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">pending</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="pending-products">{{ number_format($pendingProducts) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">محصولات باقی‌مانده</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">folder_zip</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="chunk-count">{{ number_format($chunkCount) }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">فایل‌های gzip</div>
                </div>
                <div class="text-center p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="flex items-center justify-center gap-1 mb-1">
                        <span class="material-icons text-primary text-lg">play_circle</span>
                    </div>
                    <div class="text-lg font-bold text-primary admin-ltr" data-sitemap-stat="current-run">{{ $currentRun ? $currentRun->progress.'%' : '&#8212;' }}</div>
                    <div class="text-[9px] text-slate/60 mt-0.5">اجرای فعلی</div>
                </div>
            </div>
            <div class="flex items-center justify-between p-2 rounded-lg bg-slate/10 text-[10px] text-slate/50">
                <span>آخرین بروزرسانی شمارش: <span class="admin-ltr" data-sitemap-stat="counts-updated-at">{{ $countsUpdatedAt ? persianDateTime($countsUpdatedAt) : '—' }}</span></span>
                <span>هر batch = ۱٬۰۰۰ محصول</span>
            </div>
        </div>
    </div>

    <div id="tab-logs" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h2 class="font-bold flex items-center gap-2">
                        <span class="material-icons text-primary">history</span>
                        گزارش ۵۰ اجرای آخر
                    </h2>
                    <p class="text-[10px] text-slate mt-1">برای مشاهده جزئیات هر اجرا روی همان ردیف کلیک کنید.</p>
                </div>
                <button type="button"
                    onclick="document.getElementById('sitemap-clean-logs-modal').showModal()"
                    class="admin-btn admin-btn-secondary border-danger/20 text-danger hover:bg-danger/10 text-xs">
                    <span class="material-icons text-sm">delete_sweep</span>
                    حذف همه لاگ‌ها
                </button>
            </div>
            <div class="space-y-3 max-h-[700px] overflow-y-auto pr-2 custom-scrollbar">
                @forelse($lastRuns as $run)
                    <div class="rounded-xl border border-slate bg-slate/10 p-4 hover:bg-slate/20 transition-all cursor-pointer group dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10" onclick="toggleLogMeta(this)">
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
                                @if($run->status === 'completed')
                                    {{ number_format($run->processed_products) }} محصول — {{ $run->rebuild_type === 'full' ? 'بازسازی کامل' : 'افزایشی' }}
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
                                @if($run->version)
                                    <li class="flex items-center gap-2">
                                        <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                        نسخه: <span class="admin-ltr font-mono">{{ $run->version }}</span>
                                    </li>
                                @endif
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    نوع اجرا: {{ $run->rebuild_type === 'full' ? 'بازسازی کامل' : 'افزایشی' }}
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
                                        پایان: <span class="admin-ltr">{{ persianDateTime($run->completed_at) }}</span>
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

    <div id="tab-periodic" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5 pb-4 border-b border-slate/10">
                <div>
                    <h2 class="font-bold flex items-center gap-2">
                        <span class="material-icons text-primary">schedule</span>
                        بازسازی دوره‌ای سایت‌مپ
                    </h2>
                    <p class="text-xs text-slate/60 mt-1 leading-6">بازسازی کامل در نسخه جدید ساخته می‌شود و تا پایان ساخت، sitemap فعلی دست‌نخورده می‌ماند.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $periodicRebuildEnabled ? 'bg-success text-white' : 'bg-slate/20 text-slate' }}">
                    {{ $periodicRebuildEnabled ? 'فعال' : 'غیرفعال' }}
                </span>
            </div>

            <form action="{{ route('dash.admin.sitemap.rebuild-settings', ['authkey' => $authkey]) }}" method="POST" class="space-y-5">
                @csrf
                <label class="flex items-center justify-between gap-4 rounded-xl border border-slate/10 bg-slate/[0.04] p-4 cursor-pointer">
                    <span>
                        <span class="block text-sm font-bold">فعال‌سازی بازسازی دوره‌ای</span>
                        <span class="block text-[11px] text-slate/60 mt-1">در موعد تعیین‌شده، Queue یک full rebuild نسخه‌بندی‌شده شروع می‌کند.</span>
                    </span>
                    <span class="admin-switch">
                        <input type="hidden" name="periodic_rebuild_enabled" value="0">
                        <input type="checkbox" name="periodic_rebuild_enabled" value="1" class="admin-switch-input" {{ $periodicRebuildEnabled ? 'checked' : '' }}>
                        <span class="admin-switch-track"></span>
                        <span class="admin-switch-ball"></span>
                    </span>
                </label>

                <div>
                    <label class="text-xs font-bold text-slate/70 mb-2 block">پریود بازسازی کامل</label>
                    <select name="periodic_rebuild_days" class="admin-input max-w-xs">
                        @foreach([30, 45, 60, 75, 90] as $days)
                            <option value="{{ $days }}" {{ (int) $periodicRebuildDays === $days ? 'selected' : '' }}>{{ $days }} روز</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                        <div class="text-[10px] text-slate/60 mb-1">نسخه فعال</div>
                        <div class="font-bold admin-ltr text-primary">{{ $currentVersion }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                        <div class="text-[10px] text-slate/60 mb-1">آخرین full rebuild</div>
                        <div class="font-bold text-xs">{{ $lastFullRebuildAt ? persianDateTime($lastFullRebuildAt) : 'ثبت نشده' }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                        <div class="text-[10px] text-slate/60 mb-1">گروه‌های کامل</div>
                        <div class="font-bold admin-ltr text-primary">{{ number_format($completeGroups) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                        <div class="text-[10px] text-slate/60 mb-1">URL در draft</div>
                        <div class="font-bold admin-ltr text-primary">{{ number_format($draftUrlsInGroups) }}</div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate/10 bg-slate/[0.04] p-3 text-xs text-slate/70 leading-6">
                    اجرای افزایشی فقط وقتی شروع می‌شود که حداقل
                    <span class="admin-ltr font-bold text-primary">{{ number_format($pendingNeededForNextGroup) }}</span>
                    محصول جدید برای تکمیل گروه ۵۰٬۰۰۰تایی بعدی آماده باشد. گروه ناقص تا قبل از تکمیل شدن وارد sitemap.xml نمی‌شود.
                </div>

                @if($shouldDoFullRebuild)
                    <div class="rounded-xl border border-warning/20 bg-warning/10 p-3 text-xs text-warning leading-6">
                        موعد بازسازی کامل رسیده است؛ در اجرای بعدی Queue، full rebuild به صورت خودکار شروع می‌شود.
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات
                    </button>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">view_list</span>
                    گروه‌های نسخه فعال
                </h2>
                <form action="{{ route('dash.admin.sitemap.force-full-rebuild', ['authkey' => $authkey]) }}" method="POST">
                    @csrf
                    <button type="submit"
                        onclick="return confirm('بازسازی کامل سایت‌مپ در نسخه جدید شروع شود؟')"
                        class="admin-btn admin-btn-secondary border-primary/20 text-primary hover:bg-primary/10 text-xs"
                        {{ $isRunning ? 'disabled' : '' }}>
                        <span class="material-icons text-sm">restart_alt</span>
                        شروع full rebuild
                    </button>
                </form>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div class="p-3 rounded-xl bg-slate/10">
                    <div class="text-[10px] text-slate/60">کل گروه‌ها</div>
                    <div class="text-lg font-bold admin-ltr">{{ number_format($totalGroups) }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate/10">
                    <div class="text-[10px] text-slate/60">کامل و داخل sitemap.xml</div>
                    <div class="text-lg font-bold admin-ltr text-success">{{ number_format($completeGroups) }}</div>
                </div>
                <div class="p-3 rounded-xl bg-slate/10">
                    <div class="text-[10px] text-slate/60">ناقص و draft</div>
                    <div class="text-lg font-bold admin-ltr text-warning">{{ number_format($incompleteGroups) }}</div>
                </div>
            </div>
            <div class="mb-4 rounded-xl border border-primary/10 bg-primary/[0.04] p-3 text-xs text-slate/70 leading-6">
                ظرفیت باقی‌مانده تا sitemap کامل بعدی:
                <span class="admin-ltr font-bold text-primary">{{ number_format($pendingNeededForNextGroup) }}</span>
                URL
            </div>
            <div class="rounded-xl border border-slate/10 overflow-hidden">
                <div class="max-h-72 overflow-y-auto custom-scrollbar divide-y divide-slate/10">
                    @forelse($activeGroups as $group)
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-2 p-3 text-xs items-center">
                            <span class="font-bold admin-ltr">#{{ $group->group_index }}</span>
                            <span class="admin-ltr truncate md:col-span-2">{{ $group->filename }}</span>
                            <span class="admin-ltr">{{ number_format($group->url_count) }} / 50,000</span>
                            <span class="{{ $group->is_complete ? 'text-success' : 'text-warning' }} font-bold">
                                {{ $group->is_complete ? 'کامل' : 'draft' }}
                            </span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-slate/60">هنوز گروهی برای نسخه فعال ثبت نشده است.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div id="tab-queue" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-5 pb-4 border-b border-slate/10">
                <div>
                    <h2 class="font-bold flex items-center gap-2">
                        <span class="material-icons text-primary">queue</span>
                        اتصال به مدیریت صف‌ها
                    </h2>
                    <p class="text-xs text-slate/60 mt-1 leading-6">شروع خودکار sitemap از وب‌سرویس صف انجام می‌شود و jobها روی صف تنظیم‌شده اجرا می‌شوند.</p>
                </div>
                <a href="{{ route('dash.admin.queues', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary text-xs">
                    <span class="material-icons text-sm">open_in_new</span>
                    ماژول صف‌ها
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="text-[10px] text-slate/60 mb-1">نام صف sitemap</div>
                    <div class="font-bold admin-ltr text-primary">{{ $sitemapQueueName }}</div>
                </div>
                <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="text-[10px] text-slate/60 mb-1">jobهای sitemap در صف</div>
                    <div class="font-bold admin-ltr text-primary">{{ number_format($pendingSitemapJobs) }}</div>
                </div>
                <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="text-[10px] text-slate/60 mb-1">وب‌سرویس صف</div>
                    <div class="font-bold {{ ($queueSettings['webservice_enabled'] ?? true) ? 'text-success' : 'text-danger' }}">
                        {{ ($queueSettings['webservice_enabled'] ?? true) ? 'فعال' : 'غیرفعال' }}
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-primary/[0.06] border border-primary/10">
                    <div class="text-[10px] text-slate/60 mb-1">آخرین اجرای صف</div>
                    <div class="font-bold text-xs">{{ $lastQueueRun?->executed_at ? persianDateTime($lastQueueRun->executed_at) : 'ثبت نشده' }}</div>
                </div>
            </div>
            <div class="mt-4 rounded-xl border border-slate/10 bg-slate/[0.04] p-3 text-xs text-slate/70 leading-6">
                Cron یا وب‌سرویس مدیریت صف، ابتدا شمار محصولات sitemap را به‌روز می‌کند؛ اگر ماژول فعال باشد و اجرای فعالی وجود نداشته باشد، job افزایشی یا full rebuild دوره‌ای را در صف قرار می‌دهد.
            </div>
        </div>
    </div>

    <div id="tab-actions" class="tab-content space-y-6 hidden">
        <div class="admin-card">
            <h2 class="mb-4 font-bold flex items-center gap-2">
                <span class="material-icons text-primary">tune</span>
                تنظیمات ماژول
            </h2>
            <form action="{{ route('dash.admin.sitemap.settings', ['authkey' => $authkey]) }}" method="POST" class="w-full">
                @csrf
                <div class="space-y-6 w-full">
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
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold flex items-center gap-2">
                                <span class="material-icons text-base text-primary">speed</span>
                                نرخ تولید ساعتی
                            </h3>
                            <label class="admin-switch">
                                <input type="hidden" name="mode" value="off">
                                <input type="checkbox" name="mode" value="auto" class="admin-switch-input" {{ $mode === 'auto' ? 'checked' : '' }} onchange="this.previousElementSibling.disabled = this.checked;">
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>

                        <div class="bg-slate/10 rounded-xl p-4 mb-4">
                            <div class="flex items-center justify-between text-xs text-slate/70 mb-3">
                                <span class="flex items-center gap-1">
                                    <span class="material-icons text-sm text-primary">schedule</span>
                                    نرخ ۰-۱۰ برای هر ساعت از شبانه‌روز
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-icons text-sm text-primary">touch_app</span>
                                    کلیک کنید تا مقدار تغییر کند
                                </span>
                            </div>
                            <div class="crawlChartContainer">
                                <div class="crawlChartInner">
                                    <div class="crawlYAxis">
                                        @for($w = 10; $w >= 0; $w--)
                                            <span class="crawlYTick">{{ $w }}</span>
                                        @endfor
                                    </div>
                                    <div class="crawlChartBody">
                                        <div class="crawlChartGrid" id="sitemap-rate-chart">
                                            @for($w = 10; $w >= 1; $w--)
                                                <div class="crawlRow" data-weight="{{ $w }}">
                                                    @for($h = 0; $h < 24; $h++)
                                                        @php($currentWeight = $hourlyRates[$h] ?? 1)
                                                        <button type="button"
                                                            class="crawlCell {{ $currentWeight >= $w ? 'selected' : '' }}"
                                                            data-engine="sitemap"
                                                            data-hour="{{ $h }}"
                                                            data-weight="{{ $w }}"
                                                            aria-label="نرخ {{ $w }} برای ساعت {{ $h }}"
                                                            tabindex="-1"></button>
                                                    @endfor
                                                </div>
                                            @endfor
                                        </div>
                                        <div class="crawlXAxis">
                                            @for($h = 0; $h < 24; $h++)
                                                <span class="crawlXLabel {{ $currentTehranHour === $h ? 'text-primary font-bold' : '' }}" data-sitemap-x-hour="{{ $h }}">{{ $h }}</span>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="hourly_rates" id="sitemap-hourly-rates-input" value="{{ json_encode($hourlyRates) }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate/10">
                                <span class="material-icons text-base text-primary/50">speed</span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-primary admin-ltr">{{ $currentRate }}/10</div>
                                    <div class="text-[9px] text-slate/50 truncate">نرخ فعلی</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate/10">
                                <span class="material-icons text-base text-primary/50">dynamic_feed</span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-primary admin-ltr">{{ number_format($currentBatchesPerHour) }}</div>
                                    <div class="text-[9px] text-slate/50 truncate">batch در ساعت</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-slate/10">
                                <span class="material-icons text-base text-primary/50">hourglass_bottom</span>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-primary admin-ltr">{{ number_format($currentDelaySeconds) }}s</div>
                                    <div class="text-[9px] text-slate/50 truncate">فاصله batch</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate/10 bg-slate/[0.03] p-3">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                                <label class="text-[10px] font-bold text-slate/60 whitespace-nowrap">سقف batch/h</label>
                                <input type="number" name="max_batches_per_hour" value="{{ $maxBatchesPerHour }}" min="1" max="3600" class="admin-input !w-20 text-center text-xs">
                                <span class="text-[10px] text-slate/50 leading-5">هر ۱۰۰۰ محصول = ۱ batch. پیش‌فرض ۶۰ batch/h = ۶۰٬۰۰۰ محصول/ساعت. محدوده پیشنهادی ۳۰-۱۲۰.</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات
                    </button>

                    <hr class="border-slate/10">

                    <div>
                        <h3 class="text-sm font-bold mb-3 flex items-center gap-2 text-danger">
                            <span class="material-icons text-base">dangerous</span>
                            بازنشانی کامل
                        </h3>
                        <p class="text-xs text-slate/60 mb-3 leading-6">با کلیک روی دکمه زیر، تمام فایل‌های سایت‌مپ موجود حذف، صف‌های در حال اجرا خالی و فرآیند تولید از نو شروع می‌شود.</p>
                        <button type="button"
                            onclick="document.getElementById('sitemap-reset-modal').showModal()"
                            class="admin-btn admin-btn-secondary border-danger/20 text-danger hover:bg-danger/10">
                            <span class="material-icons">restart_alt</span>
                            بازنشانی و شروع مجدد
                        </button>
                    </div>
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
                        <p class="text-sm text-slate leading-7">این ماژول به صورت افزایشی و گروه‌بندی‌شده کار می‌کند. در اجرای افزایشی، فقط محصولاتی پردازش می‌شوند که:</p>
                        <ul class="list-disc list-inside space-y-1 mt-2 text-xs text-slate">
                            <li>جدید اضافه شده‌اند (sitemapped_at null)</li>
                            <li>برای تکمیل گروه ۵۰٬۰۰۰تایی بعدی به حد نصاب رسیده‌اند</li>
                        </ul>
                        <p class="mt-2 text-sm text-slate leading-7">گروه ناقص در draft نگه‌داری می‌شود و تا زمانی که به ۵۰٬۰۰۰ URL نرسد داخل sitemap.xml قرار نمی‌گیرد. به‌روزرسانی کامل URLهای قبلی با بازسازی دوره‌ای انجام می‌شود.</p>
                    </section>
                    <hr class="border-slate/10">
                    <section id="doc-schedule">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">schedule</span>
                            زمان‌بندی
                        </h3>
                        <p class="text-sm text-slate leading-7">فرآیند تولید سایت مپ توسط ماژول مدیریت صف‌ها شروع می‌شود. در صورت نبود اجرای فعال، وجود محصول آماده پردازش و مثبت بودن نرخ ساعت فعلی، اجرای جدید در صف قرار می‌گیرد.</p>
                        <p class="mt-2 text-sm text-slate leading-7">این ماژول بازه مجاز/غیرمجاز ندارد؛ نرخ هر ساعت از ۰ تا ۱۰ تعیین می‌کند batchهای ۱۰۰۰تایی با چه فاصله‌ای اجرا شوند.</p>
                    </section>
                    <hr class="border-slate/10">
                    <section id="doc-rebuild">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">restart_alt</span>
                            بازسازی دوره‌ای
                        </h3>
                        <p class="text-sm text-slate leading-7">در بازسازی کامل، نسخه جدید پشت صحنه ساخته می‌شود. نسخه قبلی و sitemap.xml فعلی تا پایان ساخت حفظ می‌شوند و بعد از کامل شدن نسخه جدید، فایل‌های نسخه قبلی حذف و sitemap.xml با آدرس فایل‌های جدید جایگزین می‌شود.</p>
                    </section>
                    <hr class="border-slate/10">
                    <section id="doc-files">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">description</span>
                            ساختار فایل‌ها
                        </h3>
                        <ul class="list-disc list-inside space-y-1 mt-2 text-xs text-slate">
                            <li><span class="admin-ltr font-mono">/sitemap.xml</span> — ایندکس اصلی سایت مپ</li>
                            <li><span class="admin-ltr font-mono">/sitemaps/sitemap-{run}-g{group}.xml.gz</span> — فایل‌های سایت مپ فشرده شده</li>
                            <li>هر فایل حداکثر ۵۰٬۰۰۰ URL دارد (۵۰ batch هزار تایی)</li>
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
                        <a href="#doc-rebuild" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">بازسازی دوره‌ای</a>
                        <a href="#doc-files" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">ساختار فایل‌ها</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    @push('styles')
    <style>
        .crawlChartContainer {
            direction: ltr;
            overflow-x: auto;
        }
        .crawlChartInner {
            display: flex;
            gap: 4px;
        }
        .crawlYAxis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            flex-shrink: 0;
            gap: 2px;
        }
        .crawlYTick {
            font-size: 8px;
            font-weight: 700;
            color: var(--color-slate, #64748b);
            line-height: 1;
            display: flex;
            align-items: center;
        }
        .crawlChartBody {
            flex: 1;
            min-width: 0;
        }
        .crawlChartGrid {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .crawlRow {
            display: flex;
            gap: 2px;
        }
        .crawlCell {
            flex: 1;
            aspect-ratio: 1;
            border: 1px solid transparent;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.1s ease;
            background: rgba(100, 116, 139, 0.08);
            border-color: rgba(100, 116, 139, 0.15);
            min-width: 0;
        }
        .crawlCell:hover {
            border-color: var(--color-primary, #3b82f6);
            background: rgba(59, 130, 246, 0.2);
        }
        .crawlCell.selected {
            background: var(--color-primary, #3b82f6);
            border-color: var(--color-primary, #3b82f6);
            opacity: 0.85;
        }
        .crawlCell.selected:hover {
            opacity: 1;
        }
        .crawlCell[data-weight="10"].selected { background: #1e40af; border-color: #1e40af; }
        .crawlCell[data-weight="9"].selected { background: #2563eb; border-color: #2563eb; }
        .crawlCell[data-weight="8"].selected { background: #3b82f6; border-color: #3b82f6; }
        .crawlCell[data-weight="7"].selected { background: #60a5fa; border-color: #60a5fa; }
        .crawlCell[data-weight="6"].selected { background: #93c5fd; border-color: #93c5fd; }
        .crawlCell[data-weight="5"].selected { background: #a5b4fc; border-color: #a5b4fc; }
        .crawlCell[data-weight="4"].selected { background: #c7d2fe; border-color: #c7d2fe; }
        .crawlCell[data-weight="3"].selected { background: #ddd6fe; border-color: #ddd6fe; }
        .crawlCell[data-weight="2"].selected { background: #ede9fe; border-color: #ede9fe; }
        .crawlCell[data-weight="1"].selected { background: #f5f3ff; border-color: #f5f3ff; }
        .dark .crawlCell[data-weight="5"].selected { background: #6366f1; border-color: #6366f1; }
        .dark .crawlCell[data-weight="4"].selected { background: #818cf8; border-color: #818cf8; }
        .dark .crawlCell[data-weight="3"].selected { background: #a5b4fc; border-color: #a5b4fc; }
        .dark .crawlCell[data-weight="2"].selected { background: #c7d2fe; border-color: #c7d2fe; }
        .dark .crawlCell[data-weight="1"].selected { background: #ddd6fe; border-color: #ddd6fe; }
        .crawlXAxis {
            display: flex;
            gap: 2px;
            margin-top: 2px;
        }
        .crawlXLabel {
            flex: 1;
            text-align: center;
            font-size: 7px;
            font-weight: 600;
            color: var(--color-slate, #64748b);
            min-width: 0;
        }
    </style>
    @endpush

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
            const list = document.getElementById('chunk-list');
            const icon = document.getElementById('chunk-toggle-icon');
            const text = document.getElementById('chunk-toggle-text');
            const hidden = list.classList.toggle('hidden');
            icon.textContent = hidden ? 'expand_more' : 'expand_less';
            text.textContent = hidden ? text.dataset.showLabel || '{{ number_format($chunkCount) }} فایل — نمایش لیست' : 'پنهان کردن لیست';
        }

        function copyToClipboard(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const icon = btn.querySelector('.material-icons');
                const orig = icon.textContent;
                icon.textContent = 'check';
                btn.classList.add('text-success');
                setTimeout(() => {
                    icon.textContent = orig;
                    btn.classList.remove('text-success');
                }, 1500);
            }).catch(() => {});
        }

        document.querySelectorAll('.crawlCell[data-engine="sitemap"]').forEach(cell => {
            cell.addEventListener('click', function(e) {
                e.preventDefault();
                const weight = parseInt(this.dataset.weight, 10);
                const hour = this.dataset.hour;
                const hourCells = document.querySelectorAll('.crawlCell[data-engine="sitemap"][data-hour="' + hour + '"]');

                if (this.classList.contains('selected') && weight === 1) {
                    hourCells.forEach(c => c.classList.remove('selected'));
                } else {
                    hourCells.forEach(c => {
                        const w = parseInt(c.dataset.weight, 10);
                        c.classList.toggle('selected', w <= weight);
                    });
                }

                updateSitemapWeights();
            });
        });

        function updateSitemapWeights() {
            const input = document.getElementById('sitemap-hourly-rates-input');
            if (!input) return;
            const weights = [];
            for (let h = 0; h < 24; h++) {
                const cells = document.querySelectorAll('.crawlCell[data-engine="sitemap"][data-hour="' + h + '"]');
                let maxSelected = 0;
                cells.forEach(c => {
                    const w = parseInt(c.dataset.weight, 10);
                    if (c.classList.contains('selected') && w > maxSelected) {
                        maxSelected = w;
                    }
                });
                weights[h] = maxSelected;
            }

            input.value = JSON.stringify(weights);
        }

        updateSitemapWeights();

        function formatNumber(value) {
            return new Intl.NumberFormat('fa-IR').format(Number(value || 0));
        }

        function setSitemapStat(key, value) {
            document.querySelectorAll('[data-sitemap-stat="' + key + '"]').forEach(el => {
                el.textContent = value;
            });
        }

        function refreshSitemapStatus() {
            fetch('{{ route('dash.admin.sitemap.status', ['authkey' => $authkey]) }}', {
                headers: { 'Accept': 'application/json' },
            })
                .then(response => response.ok ? response.json() : null)
                .then(data => {
                    if (!data) return;

                    setSitemapStat('current-rate', data.current_rate + '/10');
                    setSitemapStat('batches-per-hour', formatNumber(data.batches_per_hour));
                    setSitemapStat('max-batches', formatNumber(data.max_batches_per_hour) + ' batch/h');
                    setSitemapStat('delay-seconds', formatNumber(data.delay_seconds) + 's');
                    setSitemapStat('pending-products', formatNumber(data.pending_products));
                    setSitemapStat('counts-updated-at', data.counts_updated_at || '—');
                    setSitemapStat('chunk-count', formatNumber(data.chunk_count));
                    setSitemapStat('current-run', data.current_run ? (data.current_run.progress + '%') : 'idle');

                    document.querySelectorAll('[data-sitemap-x-hour]').forEach(label => {
                        const isCurrent = parseInt(label.dataset.sitemapXHour, 10) === data.current_hour;
                        label.classList.toggle('text-primary', isCurrent);
                        label.classList.toggle('font-bold', isCurrent);
                    });
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
                    <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">
                        تمام فایل‌های gzip، ایندکس‌ها و متادیتای سایت‌مپ حذف خواهند شد. صف‌های در حال اجرا خالی شده و فرآیند تولید از نو آغاز می‌شود.
                    </p>
                    <p class="text-xs leading-6 text-slate-500 dark:text-slate-400 mt-2 font-bold">این عملیات قابل بازگشت نیست. ادامه می‌دهید؟</p>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button type="button" onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <form method="POST" action="{{ route('dash.admin.sitemap.reset', ['authkey' => $authkey]) }}" class="inline" id="sitemap-reset-form">
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
                    <h3 class="font-black text-slate-800 dark:text-white mb-2">حذف همه لاگ‌های سایت‌مپ</h3>
                    <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">
                        با تایید این عملیات، تمام رکوردهای گزارش اجرای سایت‌مپ حذف می‌شوند.
                        فایل‌های sitemap و گروه‌های ساخته‌شده تغییری نمی‌کنند.
                    </p>
                    <p class="text-xs leading-6 text-danger mt-2 font-bold">
                        این عملیات قابل بازگشت نیست و همه لاگ‌ها، از جمله لاگ اجرای جاری، حذف می‌شوند.
                    </p>
                    <form method="POST" action="{{ route('dash.admin.sitemap.clean-logs', ['authkey' => $authkey]) }}" class="mt-4" id="sitemap-clean-logs-form">
                        @csrf
                    </form>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button type="button" onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <button type="submit" form="sitemap-clean-logs-form" class="admin-btn bg-danger text-white hover:bg-danger/90 px-8">حذف همه لاگ‌ها</button>
            </div>
        </div>
    </dialog>
</x-layouts.admin-dashboard>
