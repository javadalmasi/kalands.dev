<x-layouts.admin-dashboard title="دستورات Artisan">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">دستورات Artisan</h1>
        <div class="flex items-center gap-2 text-xs">
            <span class="px-3 py-1.5 rounded-lg bg-slate/5 border border-slate/10 font-bold">
                <span class="material-icons text-base align-middle ml-1">terminal</span>
                {{ count($commands) }} دستور قابل اجرا
            </span>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="artisan-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-commands">
                <span class="material-icons text-base">terminal</span>
                <span>دستورات</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-logs">
                <span class="material-icons text-base">history</span>
                <span>آخرین اجراها</span>
                @if($recentLogs->isNotEmpty())
                    <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-full text-[10px]">{{ $recentLogs->count() }}</span>
                @endif
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <div id="tab-commands" class="tab-content space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($commands as $cmd)
                <div class="admin-card is-surface cursor-pointer hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 artisan-command-card"
                     data-command="{{ $cmd['command'] }}"
                     data-label="{{ $cmd['label'] }}"
                     data-description="{{ $cmd['description'] }}"
                     data-icon="{{ $cmd['icon'] }}"
                     data-danger="{{ ($cmd['danger'] ?? false) ? 'true' : 'false' }}"
                     data-warning-message="{{ $cmd['warning_message'] ?? '' }}">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center shrink-0 border border-primary/20">
                            <span class="material-icons text-primary">{{ $cmd['icon'] }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-slate text-sm">{{ $cmd['label'] }}</h3>
                            <p class="mt-0.5 text-[11px] text-slate/60 font-mono ltr block">{{ $cmd['command'] }}</p>
                        </div>
                        <div class="shrink-0">
                            <span class="material-icons text-slate/30">chevron_left</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div id="tab-logs" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">history</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">آخرین اجراها</h2>
                        <p class="text-xs text-slate/60 mt-1">۱۵ اجرای آخر به همراه مجری و خروجی</p>
                    </div>
                </div>
            </div>

            @if($recentLogs->isEmpty())
                <div class="text-center py-10 opacity-50">
                    <span class="material-icons text-4xl block mb-2">history</span>
                    <p class="text-sm font-bold text-slate">هنوز دستوری اجرا نشده است.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-slate/10">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-slate/5 border-b border-slate/10">
                            <tr>
                                <th class="p-3 font-bold text-[11px]">دستور</th>
                                <th class="p-3 font-bold text-[11px]">مجری</th>
                                <th class="p-3 font-bold text-[11px]">وضعیت</th>
                                <th class="p-3 font-bold text-[11px]">زمان</th>
                                <th class="p-3 font-bold text-[11px] text-center">خروجی</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate/5">
                            @foreach($recentLogs as $log)
                                <tr class="hover:bg-slate/5 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-slate text-xs">{{ $log->label }}</div>
                                        <div class="text-[10px] text-slate/50 font-mono ltr mt-0.5">{{ $log->command }}</div>
                                    </td>
                                    <td class="p-3 text-xs text-slate">{{ $log->admin_name }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $log->status === 'success' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                            {{ $log->status === 'success' ? 'موفق' : 'ناموفق' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-slate/60 font-mono ltr" dir="ltr">{{ $log->persian_executed_at }}</td>
                                    <td class="p-3 text-center">
                                        @if($log->output)
                                            <button type="button" class="admin-btn !py-1 !px-2 text-[10px] view-log-output" data-output="{{ $log->output }}">
                                                <span class="material-icons !text-sm">visibility</span>
                                                مشاهده
                                            </button>
                                        @else
                                            <span class="text-[10px] text-slate/40">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول دستورات Artisan</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>ماژول دستورات Artisan، دسترسی به اجرای دستورات خط فرمان لاراول را از طریق رابط کاربری وب فراهم می‌کند. این ماژول باعث می‌شود توانایی‌های مدیریت سایت، بهینه‌سازی و تنظیمات سیستم را بدون دسترسی به ترمینال دریافت کنید.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-cache-clear">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">cached</span>
                                پاکسازی کش سیستم
                            </h3>
                            <p>تمام کش‌های ذخیره شده در برنامه (شامل کش تنظیمات، مسیرها، قالب‌ها و سایر داده‌های موقت) را پاک می‌کند. این کار برای اعمال تغییرات جدید پس از به‌روزرسانی کد یا تنظیمات مفید است.</p>
                            <ul class="list-disc list-inside mt-3 space-y-1 mr-4">
                                <li>پس از اجرا، صفحات سایت ممکن است کمی کندتر لود شوند تا کش مجدداً ساخته شود</li>
                                <li>برای دیتابیس‌های بزرگ توصیه می‌شود قبل از اجرا عملیات بکاپ داشته باشید</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-view-cache">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">visibility</span>
                                بازسازی کش قالب‌ها (Blade)
                            </h3>
                            <p>تمام قالب‌های Blade را کامپایل و در حافظه کش ذخیره می‌کند. این کار سرعت بارگذاری صفحات را افزایش می‌دهد. برای استفاده در محیط تولید توصیه می‌شود.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">مرتبط:</h4>
                                <p class="text-xs">مشابه <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs">view:clear</code> که کش قالب‌ها را پاک می‌کند.</p>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-config-cache">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">settings_backup_restore</span>
                                پاکسازی/بازسازی کش تنظیمات
                            </h3>
                            <p>فایل‌های تنظیمات (config) را در فایل کش ذخیره می‌کند تا سرعت لود تنظیمات را افزایش دهد.</p>
                            <ul class="list-disc list-inside mt-3 space-y-1 mr-4">
                                <li><code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">config:clear</code>: کش تنظیمات را پاک می‌کند</li>
                                <li><code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">config:cache</code>: تنظیمات را در کش ذخیره می‌کند</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-route-cache">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">alt_route</span>
                                پاکسازی/بازسازی کش مسیرها
                            </h3>
                            <p>مسیرهای برنامه (routes) را در فایل کش ذخیره می‌کند تا سرعت مسیریابی را افزایش دهد.</p>
                            <ul class="list-disc list-inside mt-3 space-y-1 mr-4">
                                <li><code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">route:clear</code>: کش مسیرها را پاک می‌کند</li>
                                <li><code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">route:cache</code>: مسیرها را در کش ذخیره می‌کند</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-optimize">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">rocket_launch</span>
                                بهینه‌سازی کلی (Optimize)
                            </h3>
                            <p>تمامی کش‌های بهینه‌سازی (config، route، view و event) را به صورت یکجا پاک یا بازسازی می‌کند. این دستور برای عملیات سریع و همزمان چند کش مفید است.</p>
                            <ul class="list-disc list-inside mt-3 space-y-1 mr-4">
                                <li>در محیط توسعه: استفاده از <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">optimize:clear</code> توصیه می‌شود</li>
                                <li>در محیط تولید: استفاده از <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">optimize</code> پس از به‌روزرسانی</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-storage-link">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">link</span>
                                ایجاد لینک نمادین استوریج
                            </h3>
                            <p>لینک نمادین (<code dir="ltr">symlink</code>) از <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">storage/app/public</code> به <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">public/storage</code> ایجاد می‌کند.</p>
                            <ul class="list-disc list-inside mt-3 space-y-1 mr-4">
                                <li>این دستور برای دسترسی به فایل‌های آپلودی ضروری است</li>
                                <li>در صورت وجود پرونده با همان نام، لینک ایجاد نمی‌شود</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-migrate">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">storage</span>
                                اجرای Migrationها
                            </h3>
                            <p>تمامی migrationهای اجرا نشده را به ترتیب بر روی دیتابیس اعمال می‌کند. این دستور برای به‌روزرسانی ساختار دیتابیس استفاده می‌شود.</p>
                            <div class="mt-4 bg-amber-500/10 border border-amber-500/20 rounded-xl p-4">
                                <h4 class="font-bold text-amber-700 mb-2">هشدار مهم:</h4>
                                <p class="text-xs text-amber-700">تغییرات در دیتابیس انجام می‌شود. توصیه می‌شود قبل از اجرا از دیتابیس پشتیبان تهیه کنید.</p>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-migrate-fresh">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-danger text-lg">dangerous</span>
                                ریست و اجرای مجدد Migrationها
                            </h3>
                            <p>تمام جداول دیتابیس را حذف کرده و سپس تمام migrationها را از ابتدا اجرا می‌کند. تمام داده‌ها از بین می‌روند!</p>
                            <div class="mt-4 bg-danger/10 border border-danger/20 rounded-xl p-4">
                                <h4 class="font-bold text-danger mb-2">⚠️ خطرناک:</h4>
                                <p class="text-xs text-danger">تمام داده‌های دیتابیس برای همیشه حذف می‌شوند. این عملیات فقط در محیط توسعه توصیه می‌شود.</p>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-clear-logs">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-warning text-lg">delete_sweep</span>
                                حذف لاگ‌ها
                            </h3>
                            <p>تمام فایل‌های لاگ ذخیره شده در مسیر <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">storage/logs</code> را پاک می‌کند. این کار فضای دیسک را آزاد می‌سازد.</p>
                            <ul class="list-disc list-inside mt-3 space-y-1 mr-4">
                                <li>قبل از حذف، از لاگ‌ها برای عیب‌یابی استفاده کنید</li>
                                <li>لاگ‌های مهم را برای آینده قبلاً ذخیره کنید</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-queue">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">queue</span>
                                مدیریت صف‌ها
                            </h3>
                            <p>عملیات‌های طولانی‌مدت را در صف (Queue) پردازش می‌کند:</p>
                            <ul class="list-disc list-inside mt-3 space-y-1 mr-4">
                                <li><code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">queue:work --stop-when-empty</code>: صف را یک بار پردازش می‌کند و سپس متوقف می‌شود</li>
                                <li><code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">queue:restart</code>: تمام workerهای صف را دوباره راه‌اندازی می‌کند</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-security">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">security</span>
                                نکات امنیتی
                            </h3>
                            <ul class="list-disc list-inside space-y-2 mr-4">
                                <li>دستورات خطرناک (<code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">migrate:fresh</code>، <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">clear_logs</code>) نیاز به رمز عبور دارند</li>
                                <li>تمامی اجراها در لاگ ذخیره می‌شوند (شامل مجری، زمان و خروجی)</li>
                                <li>فقط ادمین‌های مجاز می‌توانند دستورات را اجرا کنند</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-cache-clear" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">پاکسازی کش</a>
                        <a href="#doc-view-cache" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">کش قالب‌ها</a>
                        <a href="#doc-config-cache" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">کش تنظیمات</a>
                        <a href="#doc-route-cache" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">کش مسیرها</a>
                        <a href="#doc-optimize" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">بهینه‌سازی</a>
                        <a href="#doc-storage-link" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">لینک استوریج</a>
                        <a href="#doc-migrate" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">Migrationها</a>
                        <a href="#doc-migrate-fresh" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">ریست Migration</a>
                        <a href="#doc-clear-logs" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">حذف لاگ‌ها</a>
                        <a href="#doc-queue" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">مدیریت صف</a>
                        <a href="#doc-security" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نکات امنیتی</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    <dialog id="artisan-command-modal" class="admin-dialog w-[min(100vw-16px,640px)] max-w-[640px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0">
                        <span class="material-icons text-primary" id="artisan-modal-icon"></span>
                    </div>
                    <div>
                        <h3 class="admin-dialog-title" id="artisan-modal-title"></h3>
                        <p class="text-xs text-slate/60 font-mono ltr" id="artisan-modal-command"></p>
                    </div>
                </div>
                <button type="button" class="admin-toggle inline-flex" data-artisan-modal-close>
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="text-sm text-slate/80 leading-relaxed" id="artisan-modal-description"></div>

            <div id="artisan-modal-output" class="hidden mt-4">
                <h4 class="text-xs font-bold text-slate mb-2 flex items-center gap-1.5">
                    <span class="material-icons text-sm">output</span>
                    خروجی دستور
                </h4>
                <pre id="artisan-modal-output-text" class="bg-slate-900 text-green-400 text-xs p-4 rounded-lg overflow-x-auto max-h-64 ltr leading-relaxed font-mono whitespace-pre-wrap"></pre>
            </div>

            <div id="artisan-modal-password-section" class="hidden">
                <div class="border-t border-slate/10 pt-4 mt-2">
                    <label for="artisan-modal-password-input" class="block text-xs font-bold text-slate mb-1.5">رمز عبور فعلی (برای دستورات خطرناک)</label>
                    <div class="relative">
                        <input type="password" id="artisan-modal-password-input" class="admin-dialog-input w-full text-right pr-10" placeholder="رمز عبور خود را وارد کنید" autocomplete="off">
                        <button type="button" class="absolute left-2 top-1/2 -translate-y-1/2 text-slate/40 hover:text-slate transition-colors" id="artisan-modal-password-toggle" tabindex="-1">
                            <span class="material-icons text-base">visibility_off</span>
                        </button>
                    </div>
                </div>
            </div>

            <div id="artisan-modal-error" class="hidden mt-4 p-4 rounded-lg border text-sm flex items-center gap-3 bg-danger/10 border-danger/20 text-danger">
                <span class="material-icons">error</span>
                <span id="artisan-modal-error-text"></span>
            </div>

            <div id="artisan-modal-actions" class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-artisan-modal-close>
                    <span class="material-icons">close</span>
                    <span>انصراف</span>
                </button>
                <button type="button" class="admin-btn" id="artisan-modal-execute">
                    <span class="material-icons" id="artisan-modal-execute-icon">play_arrow</span>
                    <span id="artisan-modal-execute-text">اجرا</span>
                </button>
            </div>
            <div id="artisan-modal-executing" class="hidden">
                <div class="flex items-center justify-end gap-3 text-sm text-slate/60 pt-4 border-t border-slate/10">
                    <span class="material-icons animate-spin">refresh</span>
                    <span>در حال اجرا...</span>
                </div>
            </div>
        </div>
    </dialog>

    <dialog id="artisan-log-output-modal" class="admin-dialog w-[min(100vw-16px,700px)] max-w-[700px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title">خروجی دستور</h3>
                <button type="button" class="admin-toggle inline-flex" data-log-modal-close>
                    <span class="material-icons">close</span>
                </button>
            </div>
            <pre id="artisan-log-output-text" class="bg-slate-900 text-green-400 text-xs p-4 rounded-lg overflow-x-auto max-h-96 ltr leading-relaxed font-mono whitespace-pre-wrap"></pre>
            <div class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-log-modal-close>
                    <span class="material-icons">close</span>
                    <span>بستن</span>
                </button>
            </div>
        </div>
    </dialog>

</x-layouts.admin-dashboard>
@vite(['resources/js/admin-artisan-commands-hub.js'])