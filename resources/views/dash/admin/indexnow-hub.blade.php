<x-layouts.admin-dashboard title="مدیریت IndexNow">
    <?php $authkey = request()->route('authkey'); ?>
    <?php $engines = ['bing', 'yandex']; ?>

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت ایندکس‌سازی (IndexNow)</h1>
        <div class="flex items-center gap-3">
            <button type="submit" form="indexnow-settings-form" class="admin-btn admin-btn-primary px-6 shadow-lg shadow-primary/20">
                <span class="material-icons">save</span>
                ذخیره تمامی تنظیمات
            </button>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="indexnow-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-overview">
                <span class="material-icons text-base">dashboard</span>
                <span>وضعیت و آمار</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-bing">
                <span class="material-icons text-base">travel_explore</span>
                <span>تنظیمات بینگ</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-yandex">
                <span class="material-icons text-base">language</span>
                <span>تنظیمات یاندکس</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-logs">
                <span class="material-icons text-base">history</span>
                <span>گزارش اجراها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <form action="{{ route('dash.admin.indexnow.settings.save', ['authkey' => $authkey]) }}" method="POST" id="indexnow-settings-form">
        @csrf

        <div id="tab-overview" class="tab-content space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="admin-card text-center">
                    <span class="material-icons text-3xl text-primary mb-2">checklist</span>
                    <div class="text-2xl font-bold text-primary">{{ number_format($todaySubmitted) }}</div>
                    <div class="text-xs text-slate mt-1">ارسال امروز</div>
                </div>
                <div class="admin-card text-center">
                    <span class="material-icons text-3xl text-primary mb-2">inventory_2</span>
                    <div class="text-2xl font-bold text-primary">{{ number_format($pendingProducts) }}</div>
                    <div class="text-xs text-slate mt-1">محصولات در انتظار ارسال</div>
                </div>
                <div class="admin-card text-center">
                    <span class="material-icons text-3xl text-primary mb-2">database</span>
                    <div class="text-2xl font-bold text-primary">{{ number_format($totalActive) }}</div>
                    <div class="text-xs text-slate mt-1">کل محصولات فعال</div>
                </div>
            </div>

            <div class="admin-card">
                <h2 class="mb-4 font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">schedule</span>
                    زمان‌بندی ارسال روزانه
                </h2>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">سقف ارسال روزانه</label>
                    <input type="number" name="daily_limit" value="{{ $dailyLimit }}" min="100" max="1000000" class="admin-input w-48">
                    <p class="text-[11px] text-slate/60 mt-1">حداکثر تعداد URL ارسالی به هر موتور جستجو در روز</p>
                </div>
            </div>
        </div>

        @foreach($engines as $engine)
        @php
            $engineLabel = $engine === 'bing' ? 'بینگ' : 'یاندکس';
            $engineIcon = $engine === 'bing' ? 'travel_explore' : 'language';
        @endphp
        <div id="tab-{{ $engine }}" class="tab-content space-y-6 hidden">
            <div class="admin-card">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">{{ $engineIcon }}</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات {{ $engineLabel }}</h2>
                            <p class="text-xs text-slate/60 mt-1">مدیریت وریفای و تعیین نرخ ارسال ساعتی</p>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="{{ $engine }}_enabled" value="0">
                        <input type="checkbox" name="{{ $engine }}_enabled" value="1" class="w-4 h-4 rounded border-slate/30 text-primary focus:ring-primary" {{ $enabled[$engine] ? 'checked' : '' }}>
                        <span class="text-sm font-bold {{ $enabled[$engine] ? 'text-success' : 'text-slate' }}">{{ $enabled[$engine] ? 'فعال' : 'غیرفعال' }}</span>
                    </label>
                </div>

                <div class="space-y-6 max-w-xl">
                    <div>
                        <label class="block text-sm font-medium mb-2">کلید وریفای {{ $engineLabel }}</label>
                        <div class="flex gap-2">
                            <input type="text" name="{{ $engine }}_key" id="{{ $engine }}-key-input" value="{{ $keys[$engine] }}" class="admin-input flex-1 admin-ltr text-xs font-mono" placeholder="یک کلید ۳۲ کاراکتری وارد کنید یا تولید کنید">
                            <button type="button" class="admin-btn admin-btn-secondary shrink-0" onclick="regenerateKey('{{ $engine }}')">
                                <span class="material-icons">refresh</span>
                                تولید
                            </button>
                        </div>
                        @if(!empty($keys[$engine]))
                        <p class="text-[11px] text-success mt-2 flex items-center gap-1">
                            <span class="material-icons text-xs">check_circle</span>
                            فایل وریفای در <code class="bg-slate/10 px-1 rounded text-[10px] admin-ltr">/{{ $keys[$engine] }}.txt</code> قرار گرفته است.
                        </p>
                        @else
                        <p class="text-[11px] text-warning mt-2 flex items-center gap-1">
                            <span class="material-icons text-xs">warning</span>
                            برای فعال‌سازی ایندکس‌سازی، یک کلید وریفای تولید یا وارد کنید.
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="admin-card" id="crawl-chart-card-{{ $engine }}">
                <h2 class="mb-4 font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">bar_chart</span>
                    نمودار نرخ ارسال ساعتی - {{ $engineLabel }}
                </h2>
                <p class="text-xs text-slate mb-4">
                    با کلیک روی هر ستون، وزن ارسال برای آن ساعت را مشخص کنید.
                    وزن بیشتر = ارسال محصولات بیشتر در آن ساعت.
                    وزن صفر = عدم ارسال در آن ساعت.
                </p>

                <div class="crawlChartContainer">
                    <div class="crawlChartInner">
                        <div class="crawlYAxis">
                            @for($w = 10; $w >= 0; $w--)
                                <span class="crawlYTick">{{ $w }}</span>
                            @endfor
                        </div>
                        <div class="crawlChartBody">
                            <div class="crawlChartGrid" id="crawl-chart-{{ $engine }}">
                                @for($w = 10; $w >= 1; $w--)
                                    <div class="crawlRow" data-weight="{{ $w }}">
                                        @php
                                            $rowPerHour = $enabled[$engine] ? floor(($w / max(array_sum($weights[$engine]), 1)) * $dailyLimit) : 0;
                                        @endphp
                                        @for($h = 0; $h < 24; $h++)
                                            @php
                                                $currentWeight = $weights[$engine][$h] ?? 1;
                                            @endphp
                                            <button type="button"
                                                class="crawlCell {{ $currentWeight >= $w ? 'selected' : '' }}"
                                                data-engine="{{ $engine }}"
                                                data-hour="{{ $h }}"
                                                data-weight="{{ $w }}"
                                                aria-label="وزن {{ $w }} برای ساعت {{ $h }}"
                                                tabindex="-1"></button>
                                        @endfor
                                    </div>
                                @endfor
                            </div>
                            <div class="crawlXAxis">
                                @for($h = 0; $h < 24; $h++)
                                    <span class="crawlXLabel">{{ $h }}</span>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-4 text-xs text-slate">
                    <span class="flex items-center gap-1">
                        <span class="w-4 h-4 rounded-sm bg-primary"></span>
                        = وزن فعال
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="w-4 h-4 rounded-sm border-2 border-slate/30 bg-transparent"></span>
                        = وزن غیرفعال
                    </span>
                    <span class="flex items-center gap-1">
                        <span class="material-icons text-xs">bar_chart</span>
                        = تعداد تخمینی ارسال در هر ساعت
                    </span>
                </div>
            </div>

            <div class="admin-card">
                <h2 class="mb-4 font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">query_stats</span>
                    آمار ۷ روز گذشته - {{ $engineLabel }}
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="stats-{{ $engine }}">
                    <div class="text-center p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                        <div class="text-2xl font-bold text-primary">—</div>
                        <div class="text-[10px] text-slate mt-1">ارسال شده</div>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                        <div class="text-2xl font-bold text-warning">—</div>
                        <div class="text-[10px] text-slate mt-1">در صف</div>
                    </div>
                    <div class="text-center p-4 rounded-xl bg-slate/10 dark:bg-white/5">
                        <div class="text-2xl font-bold text-danger">—</div>
                        <div class="text-[10px] text-slate mt-1">ناموفق</div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="{{ $engine }}_weights" id="{{ $engine }}-weights-input" value="{{ json_encode($weights[$engine]) }}">
        </div>
        @endforeach
    </form>

    <div id="tab-overview" class="tab-content">
        <div class="admin-card">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold flex items-center gap-2">
                    <span class="material-icons text-primary">play_arrow</span>
                    اجرای دستی
                </h2>
            </div>
            <p class="text-sm text-slate mb-4">یک ساعت خاص را برای پردازش دستی انتخاب کنید.</p>
            <form action="{{ route('dash.admin.indexnow.trigger', ['authkey' => $authkey]) }}" method="POST" class="flex items-center gap-4">
                @csrf
                <select name="hour" class="admin-input w-32">
                    @for($h = 0; $h <= 23; $h++)
                        <option value="{{ $h }}" {{ $h === $currentHour ? 'selected' : '' }}>{{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00</option>
                    @endfor
                </select>
                <button type="submit" class="admin-btn admin-btn-primary">
                    <span class="material-icons">send</span>
                    اجرا
                </button>
            </form>
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
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $run->engine === 'bing' ? 'bg-primary/10 text-primary' : 'bg-warning/10 text-warning' }}">
                                    {{ $run->engine === 'bing' ? 'بینگ' : 'یاندکس' }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $run->status === 'completed' ? 'bg-success text-white' : ($run->status === 'running' ? 'bg-warning text-white' : 'bg-danger text-white') }}">
                                    {{ $run->status === 'completed' ? 'موفق' : ($run->status === 'completed_with_errors' ? 'با خطا' : ($run->status === 'running' ? 'در حال اجرا' : 'ناموفق')) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-[11px] text-slate mb-1">
                            <span class="material-icons text-xs">info</span>
                            <span>ساعت {{ str_pad($run->hour, 2, '0', STR_PAD_LEFT) }}:00 — {{ number_format($run->total_queued) }} در صف / {{ number_format($run->total_submitted) }} ارسال / {{ number_format($run->total_failed) }} ناموفق</span>
                        </div>

                        @if($run->error_message)
                            <div class="bg-danger/5 border border-danger/20 p-2 rounded mt-2">
                                <p class="text-[11px] text-danger font-medium leading-5">خطا: {{ $run->error_message }}</p>
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
                                    شناسه: <span class="admin-ltr font-mono">{{ $run->run_id }}</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    موتور: {{ $run->engine === 'bing' ? 'بینگ' : 'یاندکس' }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    ساعت: {{ str_pad($run->hour, 2, '0', STR_PAD_LEFT) }}:00
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    وضعیت: {{ $run->status === 'completed' ? 'موفق' : ($run->status === 'completed_with_errors' ? 'تکمیل با خطا' : ($run->status === 'running' ? 'در حال اجرا' : 'ناموفق')) }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    در صف: {{ number_format($run->total_queued) }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    ارسال شده: {{ number_format($run->total_submitted) }}
                                </li>
                                <li class="flex items-center gap-2">
                                    <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                    ناموفق: {{ number_format($run->total_failed) }}
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
                    <div class="text-center py-10 opacity-40">
                        <span class="material-icons text-4xl block mb-2">inventory_2</span>
                        <p class="text-xs">هنوز هیچ اجرایی ثبت نشده است.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای ماژول IndexNow</h2>
                    </div>

                    <section id="doc-intro">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">info</span>
                            IndexNow چیست؟
                        </h3>
                        <p class="text-sm text-slate leading-7">پروتکل IndexNow یک استاندارد ساده است که به موتورهای جستجو اجازه می‌دهد از طریق API از تغییرات محتوای وب‌سایت شما مطلع شوند. با استفاده از IndexNow، بلافاصله پس از تغییر محصولات، آدرس آن‌ها را به بینگ و یاندکس اطلاع می‌دهید تا سریع‌تر ایندکس شوند.</p>
                    </section>

                    <hr class="border-slate/10">

                    <section id="doc-verify">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">verified</span>
                            وریفای مالکیت سایت
                        </h3>
                        <p class="text-sm text-slate leading-7">برای استفاده از IndexNow باید مالکیت سایت خود را اثبات کنید. برای این کار:</p>
                        <ol class="list-decimal list-inside mt-3 space-y-2 mr-4 text-sm text-slate leading-7">
                            <li>یک کلید ۳۲ کاراکتری (ترجیحاً تصادفی) تولید یا وارد کنید.</li>
                            <li>فایل <code class="bg-slate/10 px-1.5 py-0.5 rounded">{key}.txt</code> به صورت خودکار در ریشه سایت قرار می‌گیرد.</li>
                            <li>هنگام ارسال درخواست، کلید و محل فایل به موتور جستجو ارسال می‌شود.</li>
                            <li>موتور جستجو فایل را بررسی کرده و مالکیت شما را تأیید می‌کند.</li>
                        </ol>
                    </section>

                    <hr class="border-slate/10">

                    <section id="doc-weights">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">bar_chart</span>
                            نرخ ارسال ساعتی
                        </h3>
                        <p class="text-sm text-slate leading-7">با استفاده از نمودار ستونی می‌توانید برای هر ساعت از شبانه‌روز وزن ارسال مشخصی تعیین کنید. وزن بیشتر به معنای ارسال محصولات بیشتر در آن ساعت است. این قابلیت برای توزیع بار ارسال در ساعات خلوت (مثلاً نیمه‌شب) بسیار مفید است.</p>
                    </section>

                    <hr class="border-slate/10">

                    <section id="doc-limits">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">speed</span>
                            محدودیت‌ها
                        </h3>
                        <ul class="list-disc list-inside mt-3 space-y-2 mr-4 text-sm text-slate leading-7">
                            <li>حداکثر ۱۰٬۰۰۰ URL در هر درخواست</li>
                            <li>حداکثر ۱٬۰۰۰٬۰۰۰ URL در روز (توصیه شده)</li>
                            <li>فقط محصولات فعال (is_active = true) ارسال می‌شوند</li>
                            <li>محصولاتی که قبلاً ارسال شده و بروزرسانی نشده‌اند، دوباره ارسال نمی‌شوند</li>
                        </ul>
                    </section>

                    <hr class="border-slate/10">

                    <section id="doc-schedule">
                        <h3 class="text-base font-bold mb-3 flex items-center gap-2">
                            <span class="material-icons text-primary text-lg">schedule</span>
                            زمان‌بندی
                        </h3>
                        <p class="text-sm text-slate leading-7">پردازش ساعتی توسط cron job یا scheduler لاراول انجام می‌شود. دستور زیر را در crontab تنظیم کنید:</p>
                        <div class="bg-slate-900 text-slate-300 p-4 rounded-lg admin-ltr text-xs border border-white/10 mt-3">
                            <code>0 * * * * php /path/to/artisan indexnow:process-hourly</code>
                        </div>
                        <p class="text-sm text-slate leading-7 mt-4">این دستور هر ساعت یکبار اجرا شده و بر اساس وزن تنظیم شده برای آن ساعت، محصولات را به موتورهای جستجو ارسال می‌کند.</p>
                    </section>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">IndexNow چیست؟</a>
                        <a href="#doc-verify" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">وریفای مالکیت</a>
                        <a href="#doc-weights" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نرخ ارسال ساعتی</a>
                        <a href="#doc-limits" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">محدودیت‌ها</a>
                        <a href="#doc-schedule" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">زمان‌بندی</a>
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
        function toggleLogMeta(el) {
            const meta = el.querySelector('.log-meta');
            if (meta) meta.classList.toggle('hidden');
        }

        document.querySelectorAll('#indexnow-tabs button').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = this.dataset.tabTarget;
                document.querySelectorAll('#indexnow-tabs button').forEach(b => {
                    b.classList.remove('border-primary', 'text-primary', 'font-bold');
                    b.classList.add('text-slate', 'font-medium');
                });
                this.classList.add('border-primary', 'text-primary', 'font-bold');
                this.classList.remove('text-slate', 'font-medium');
                document.querySelectorAll('.tab-content').forEach(tc => tc.classList.add('hidden'));
                document.getElementById(target).classList.remove('hidden');
            });
        });

        var urlParams = new URLSearchParams(window.location.search);
        var activeTab = urlParams.get('tab');
        if (activeTab && document.getElementById(activeTab)) {
            document.querySelector('#indexnow-tabs [data-tab-target="' + activeTab + '"]')?.click();
        }

        ['bing', 'yandex'].forEach(engine => {
            const cells = document.querySelectorAll('.crawlCell[data-engine="' + engine + '"]');
            cells.forEach(cell => {
                cell.addEventListener('click', function(e) {
                    e.preventDefault();
                    const weight = parseInt(this.dataset.weight);
                    const hour = this.dataset.hour;
                    const hourCells = document.querySelectorAll(
                        '.crawlCell[data-engine="' + engine + '"][data-hour="' + hour + '"]'
                    );
                    if (this.classList.contains('selected') && weight === 1) {
                        hourCells.forEach(c => c.classList.remove('selected'));
                    } else {
                        hourCells.forEach(c => {
                            const w = parseInt(c.dataset.weight);
                            c.classList.toggle('selected', w <= weight);
                        });
                    }
                    updateWeights(engine);
                });
            });
        });

        function updateWeights(engine) {
            const weights = [];
            for (let h = 0; h < 24; h++) {
                const cells = document.querySelectorAll(
                    '.crawlCell[data-engine="' + engine + '"][data-hour="' + h + '"]'
                );
                let maxSelected = 0;
                cells.forEach(c => {
                    const w = parseInt(c.dataset.weight);
                    if (c.classList.contains('selected') && w > maxSelected) {
                        maxSelected = w;
                    }
                });
                weights[h] = maxSelected;
            }

            document.getElementById(engine + '-weights-input').value = JSON.stringify(weights);
        }

        document.querySelector('input[name="daily_limit"]')?.addEventListener('input', function() {
            ['bing', 'yandex'].forEach(engine => updateWeights(engine));
        });

        function regenerateKey(engine) {
            const dialog = document.getElementById('admin-confirm-dialog');
            if (!dialog || typeof dialog.showModal !== 'function') {
                if (!confirm('آیا از تولید کلید جدید اطمینان دارید؟ کلید قبلی نامعتبر می‌شود.')) return;
                return doFetchRegenerateKey(engine);
            }

            const titleEl = document.getElementById('admin-confirm-title');
            const msgEl = document.getElementById('admin-confirm-message');
            const submitBtn = document.getElementById('admin-confirm-submit');
            const labelSpan = submitBtn?.querySelector('span:last-child');

            const origTitle = titleEl.textContent;
            const origMsg = msgEl.textContent;
            const origLabel = labelSpan?.textContent;

            titleEl.textContent = 'تولید کلید جدید';
            msgEl.textContent = 'آیا از تولید کلید جدید اطمینان دارید؟ کلید قبلی نامعتبر می‌شود.';
            if (labelSpan) labelSpan.textContent = 'تولید کن';

            function onClose() {
                dialog.removeEventListener('close', onClose);
                titleEl.textContent = origTitle;
                msgEl.textContent = origMsg;
                if (labelSpan) labelSpan.textContent = origLabel;
                if (dialog.returnValue === 'confirm') {
                    doFetchRegenerateKey(engine);
                }
            }

            dialog.addEventListener('close', onClose);
            dialog.showModal();
        }

        function doFetchRegenerateKey(engine) {
            fetch('{{ route("dash.admin.indexnow.key.regenerate", ["authkey" => $authkey]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ engine: engine }),
            }).then(r => r.json()).then(data => {
                if (data.key) {
                    document.getElementById(engine + '-key-input').value = data.key;
                }
            }).catch(function () { showToast('خطا در تولید کلید'); });
        }
    </script>
    @endpush
</x-layouts.admin-dashboard>
