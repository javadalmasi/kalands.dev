<x-layouts.admin-dashboard title="مدیریت فایل‌ها">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت فایل‌ها</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('dash.admin.modules', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary">
                <span class="material-icons">arrow_forward</span>
                بازگشت به ماژول‌ها
            </a>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="file-manager-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-files">
                <span class="material-icons text-base">folder</span>
                <span>فایل‌ها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-settings">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <div id="tab-files" class="tab-content space-y-4">
        <div class="admin-card">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="font-semibold text-slate flex items-center gap-2">
                    <span class="material-icons text-primary">explore</span>
                    مرور فایل‌ها
                </h3>
                <button type="button" class="admin-btn admin-btn-secondary" data-admin-refresh-explorer>
                    <span class="material-icons">refresh</span>
                    بازخوانی
                </button>
            </div>
            <div class="space-y-3"
                 data-module-explorer
                 data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}"
                 data-file-url-base="/{{ $settings['root_path'] ?? 'uploads' }}"
                 data-cdn-base-url="{{ $settings['cdn_base_url'] ?? '' }}"
                 data-explorer-path="">
                <div class="rounded border border-slate/70 bg-white/5 p-4 text-sm text-slate">در حال بارگذاری فایل‌ها...</div>
            </div>
        </div>
    </div>

    <div id="tab-settings" class="tab-content hidden space-y-6">
        <form action="{{ route('dash.admin.file-manager.settings.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="admin-card">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">storage</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">وضعیت فایل منیجر</h2>
                            <p class="text-xs text-slate/60 mt-1">فعال یا غیرفعال سازی دسترسی به مدیریت فایل‌ها</p>
                        </div>
                    </div>
                    <label class="admin-switch"><input type="checkbox" name="enabled" value="1" class="admin-switch-input" @checked(old('enabled', $settings['enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-2">
                        <label class="text-sm font-bold px-1">مسیر ریشه (Root Path)</label>
                        <input type="text" name="root_path" value="{{ old('root_path', $settings['root_path'] ?? 'uploads') }}"
                               class="w-full rounded-lg border border-slate p-3 dark:bg-slate-800 dark:text-white dark:border-white/10"
                               placeholder="e.g. uploads">
                        <p class="text-[11px] text-slate/50">پوشه‌ای در دایرکتوری public که فایل‌ها در آن ذخیره می‌شوند.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-bold px-1">آدرس پایه CDN (اختیاری)</label>
                        <input type="text" name="cdn_base_url" value="{{ old('cdn_base_url', $settings['cdn_base_url'] ?? '') }}"
                               class="w-full rounded-lg border border-slate p-3 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr"
                               placeholder="https://cdn.example.com">
                        <p class="text-[11px] text-slate/50">در صورت استفاده از CDN، آدرس آن را اینجا وارد کنید.</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10">
                    <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل مدیریت فایل‌ها</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>ماژول مدیریت فایل در پلتفرم kalands.ir، سامانه بارگذاری، مرور و مدیریت فایل‌ها در مسیرهای تعریف شده است. این سامانه با استفاده از API‌های Ajax عملکرد می‌کند و برای سایر ماژول‌ها (مانند اسلایدر و بنر) قابل استفاده است.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-browser">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">folder_open</span>
                                مرور فایل‌ها
                            </h3>
                            <p>در تب فایل‌ها می‌توانید عملیات زیر را انجام دهید:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li>مشاهده فایل‌ها و پوشه‌ها به صورت درختی</li>
                                <li>ایجاد پوشه جدید از طریق دکمه‌ی «ایجاد پوشه»</li>
                                <li>دانلود و حذف فایل‌ها</li>
                                <li>کپی مسیر فایل برای استفاده در سایر بخش‌ها</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-settings">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">settings</span>
                                تنظیمات فایل منیجر
                            </h3>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>مسیر ریشه (Root Path):</b> پوشه‌ای در دایرکتوری <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">public</code> که فایل‌ها در آن ذخیره می‌شوند (مثلاً: <code class="bg-slate/10 px-1.5 py-0.5 rounded text-xs font-mono">uploads</code>)</li>
                                <li><b>آدرس پایه CDN:</b> در صورت استفاده از شبکه توزیع محتوا، آدرس CDN را وارد کنید تا فایل‌ها از طریق CDN بارگذاری شوند</li>
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
                        <a href="#doc-browser" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">مرور فایل‌ها</a>
                        <a href="#doc-settings" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تنظیمات فایل منیجر</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-file-manager.js', 'resources/js/admin-file-manager-hub.js'])
