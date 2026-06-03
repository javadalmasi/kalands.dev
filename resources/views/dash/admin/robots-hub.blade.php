<x-layouts.admin-dashboard title="فایل Robots.txt">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت Robots.txt</h1>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="robots-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-editor">
                <span class="material-icons text-base">edit</span>
                <span>ویرایشگر</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-tester">
                <span class="material-icons text-base">biotech</span>
                <span>تستر</span>
            </button>
        </div>
    </div>

    <div id="tab-editor" class="tab-content space-y-6">
        <form action="{{ route('dash.admin.robots.save', ['authkey' => $authkey]) }}" method="POST">
            @csrf
            <div class="admin-card">
                <div class="flex items-center gap-3 mb-4 border-b border-slate/10 pb-4">
                    <div class="rounded bg-primary/10 p-2 text-primary">
                        <span class="material-icons">description</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">محتوای فایل robots.txt</h2>
                        <p class="text-xs text-slate/60 mt-1">این فایل در ریشه سایت (public/robots.txt) قرار دارد.</p>
                    </div>
                </div>

                <div class="space-y-1">
                    <textarea name="content" class="admin-ltr w-full min-h-[400px] rounded border border-slate p-4 text-sm font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" placeholder="User-agent: *...">{{ $content }}</textarea>
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10">
                    <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                        <span class="material-icons">save</span>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="tab-tester" class="tab-content hidden space-y-6">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="md:col-span-1 space-y-6">
                    <div class="admin-card">
                        <h3 class="font-bold text-slate mb-4 pb-2 border-b border-slate/10">تنظیمات تست</h3>
                        
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-sm font-bold text-slate px-1">User-Agent</label>
                                <div class="relative">
                                    <input type="text" id="test-agent" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" value="*">
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <button type="button" onclick="setAgent('*')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">همه (*)</button>
                                        <button type="button" onclick="setAgent('Googlebot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">Googlebot</button>
                                        <button type="button" onclick="setAgent('Bingbot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">Bingbot</button>
                                        <button type="button" onclick="setAgent('YandexBot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">YandexBot</button>
                                        <button type="button" onclick="setAgent('DuckDuckBot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">DuckDuckBot</button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-bold text-slate px-1">آدرس مورد نظر (Path)</label>
                                <input type="text" id="test-path" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" value="/" placeholder="/example-path">
                            </div>

                            <button onclick="runTest()" id="btn-run-test" class="admin-btn admin-btn-primary w-full justify-center h-11">
                                <span class="material-icons">play_arrow</span>
                                اجرای تست
                            </button>
                        </div>
                    </div>

                    <div id="test-summary" class="admin-card hidden">
                        <h3 class="font-bold text-slate mb-2">نتیجه تست:</h3>
                        <div id="test-status-box" class="p-4 rounded text-center font-black text-lg">
                            ---
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="admin-card !p-0 overflow-hidden min-h-[500px]">
                        <div class="bg-slate/5 p-4 border-b border-slate/10 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate">خروجی آنالیز فایل</span>
                            <span class="text-xs text-slate/50">خطوط منطبق با قرمز و سبز مشخص می‌شوند</span>
                        </div>
                        <div id="test-results-output" class="p-4 font-mono text-sm admin-ltr space-y-0.5 overflow-y-auto max-h-[600px]">
                            <div class="py-20 text-center text-slate/40 italic">برای شروع تست، دکمه «اجرای تست» را بزنید.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول مدیریت Robots.txt</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>فایل robots.txt یک فایل متنی ساده است که در ریشه سایت قرار می‌گیرد و دستورالعمل‌هایی برای خزنده‌های وب (مانند Googlebot) ارائه می‌دهد. این فایل به شما کمک می‌کند تا بخش‌های خاصی از سایت را از ایندکس شدن جلوگیری کنید یا دسترسی به منابع خاص را محدود کنید.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-directives">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">code</span>
                                دستورات اصلی
                            </h3>
                            <p>دستورات اصلی robots.txt:</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">دستورات رایج</h4>
                                <ul class="list-disc list-inside space-y-2 mr-4">
                                    <li><b>User-agent:</b> نام خزنده که دستورات برای آن اعمال می‌شود (<code class="bg-slate/10 px-1 rounded">*</code> برای همه)</li>
                                    <li><b>Disallow:</b> مسیری که از ایندکس شدن منع می‌شود (<code class="bg-slate/10 px-1 rounded">/admin/</code>)</li>
                                    <li><b>Allow:</b> مسیری که حتی در صورت وجود Disallow عمومی، مجاز است</li>
                                    <li><b>Crawl-delay:</b> تاخیر بین درخواست‌های خزنده (بر حسب ثانیه)</li>
                                    <li><b>Sitemap:</b> آدرس کامل نقشه سایت برای کمک به ایندکس‌گذاری</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-tester">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">biotech</span>
                                تست robots.txt
                            </h3>
                            <p>ابزار تست برای بررسی اینکه آیا یک خزنده خاص می‌تواند URL مورد نظر شما را ایندکس کند یا خیر:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li>User-Agent: نام خزنده مورد نظر را انتخاب یا وارد کنید</li>
                                <li>آدرس (Path): مسیری که می‌خواهید بررسی کنید</li>
                                <li>نتیجه: مجاز (ALLOWED) یا ممنوع (BLOCKED)</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-tips">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">lightbulb</span>
                                نکات کاربردی
                            </h3>
                            <ul class="list-disc list-inside space-y-2 mr-4">
                                <li>فایل robots.txt همیشه در ریشه سایت (مثلا: <code class="bg-slate/10 px-1 rounded">https://kalands.ir/robots.txt</code>) قابل دسترسی باشد.</li>
                                <li>برای جلوگیری از ایندکس صفحات خاص (مانند پنل ادمین)، از دستور <code class="bg-slate/10 px-1 rounded">Disallow: /admin/</code> استفاده کنید.</li>
                                <li>در صورت تغییر، ممکن است چند روز تا اعمال تغییرات توسط موتورهای جستجو طول بکشد.</li>
                                <li>استفاده از <code class="bg-slate/10 px-1 rounded">Disallow: /</code> کل سایت را از ایندکس شدن منع می‌کند.</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-security">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">security</span>
                                نکات امنیتی
                            </h3>
                            <ul class="list-disc list-inside space-y-2 mr-4">
                                <li>فایل robots.txt برای همه کاربران قابل مشاهده است و نباید اطلاعات حساس در آن ذخیره شود.</li>
                                <li>برای محدود کردن دسترسی به بخش‌های حساس، هم از robots.txt و هم از احراز هویت (Authentication) استفاده کنید.</li>
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
                        <a href="#doc-directives" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">دستورات اصلی</a>
                        <a href="#doc-tester" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تست robots.txt</a>
                        <a href="#doc-tips" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نکات کاربردی</a>
                        <a href="#doc-security" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نکات امنیتی</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-robots-hub.js'])