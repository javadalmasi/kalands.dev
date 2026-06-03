<x-layouts.admin-dashboard title="ماژول جامع ارتباطی">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">ماژول جامع ارتباطی</h1>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="comm-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="smtp-general">
                <span class="material-icons text-base">mail_outline</span>
                <span>SMTP عمومی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="smtp-transactional">
                <span class="material-icons text-base">speed</span>
                <span>SMTP تراکنشی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="sms-panel">
                <span class="material-icons text-base">sms</span>
                <span>پنل پیامک</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="test-hub">
                <span class="material-icons text-base">science</span>
                <span>تست و عیب‌یابی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <div id="smtp-general" class="tab-content space-y-4">
        <div class="admin-card">
            <div class="flex items-center gap-2 mb-5 border-b border-slate/20 pb-3 dark:border-white/5">
                <span class="material-icons text-primary">public</span>
                <h2 class="font-bold text-slate">پیکربندی SMTP عمومی</h2>
            </div>
            <form action="{{ route('dash.admin.smtp.general.save', ['authkey' => $authkey]) }}" method="POST" class="grid gap-5 md:grid-cols-2">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">میزبان (Host)</label>
                    <input name="host" value="{{ $settings['smtp_general']['host'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="smtp.gmail.com">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">پورت (Port)</label>
                    <input name="port" type="number" value="{{ $settings['smtp_general']['port'] ?? '587' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">نام کاربری (Username)</label>
                    <input name="username" value="{{ $settings['smtp_general']['username'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">کلمه عبور (Password)</label>
                    <input name="password" type="password" value="{{ $settings['smtp_general']['password'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">نوع رمزنگاری (Encryption)</label>
                    <select name="encryption" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10">
                        <option value="" @selected(!($settings['smtp_general']['encryption'] ?? null))>بدون رمزنگاری</option>
                        <option value="tls" @selected(($settings['smtp_general']['encryption'] ?? '') === 'tls')>TLS</option>
                        <option value="ssl" @selected(($settings['smtp_general']['encryption'] ?? '') === 'ssl')>SSL</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">ایمیل فرستنده (Sender Email)</label>
                    <input name="sender_email" value="{{ $settings['smtp_general']['sender_email'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-bold text-slate/70 px-1">نام فرستنده (Sender Name)</label>
                    <input name="sender_name" value="{{ $settings['smtp_general']['sender_name'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10">
                </div>
                <div class="admin-actions md:col-span-2 mt-4">
                    <button class="admin-btn admin-btn-primary px-8"><span class="material-icons">save</span>ذخیره تنظیمات عمومی</button>
                </div>
            </form>
        </div>
    </div>

    <div id="smtp-transactional" class="tab-content hidden space-y-4">
        <div class="admin-card">
            <div class="flex items-center gap-2 mb-5 border-b border-slate/20 pb-3 dark:border-white/5">
                <span class="material-icons text-primary">auto_awesome</span>
                <h2 class="font-bold text-slate">پیکربندی SMTP تراکنشی</h2>
            </div>
            <div class="bg-primary/10 border-r-4 border-primary p-4 rounded mb-6 flex gap-3 items-start">
                <span class="material-icons text-primary mt-0.5">info</span>
                <p class="text-xs text-slate leading-6 font-medium">از این بخش برای ایمیل‌های حساس مانند "بازیابی رمز عبور" استفاده می‌شود. در صورت خالی بودن، سیستم به تنظیمات SMTP عمومی رجوع می‌کند.</p>
            </div>
            <form action="{{ route('dash.admin.smtp.transactional.save', ['authkey' => $authkey]) }}" method="POST" class="grid gap-5 md:grid-cols-2">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">میزبان (Host)</label>
                    <input name="host" value="{{ $settings['smtp_transactional']['host'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="smtp.mailtrap.io">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">پورت (Port)</label>
                    <input name="port" type="number" value="{{ $settings['smtp_transactional']['port'] ?? '587' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">نام کاربری (Username)</label>
                    <input name="username" value="{{ $settings['smtp_transactional']['username'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">کلمه عبور (Password)</label>
                    <input name="password" type="password" value="{{ $settings['smtp_transactional']['password'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">ایمیل فرستنده (Sender Email)</label>
                    <input name="sender_email" value="{{ $settings['smtp_transactional']['sender_email'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">نام فرستنده (Sender Name)</label>
                    <input name="sender_name" value="{{ $settings['smtp_transactional']['sender_name'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10">
                </div>
                <div class="admin-actions md:col-span-2 mt-4">
                    <button class="admin-btn admin-btn-primary px-8"><span class="material-icons">save</span>ذخیره تنظیمات تراکنشی</button>
                </div>
            </form>
        </div>
    </div>

    <div id="sms-panel" class="tab-content hidden space-y-4">
        <div class="admin-card">
            <div class="flex items-center gap-2 mb-5 border-b border-slate/20 pb-3 dark:border-white/5">
                <span class="material-icons text-primary">sms_failed</span>
                <h2 class="font-bold text-slate">پیکربندی پنل پیامک</h2>
            </div>
            <form action="{{ route('dash.admin.sms.config.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-5">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">آدرس وب‌سرویس (Endpoint)</label>
                    <input name="endpoint" value="{{ $settings['sms']['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">توکن امنیتی (API Token)</label>
                    <input name="api_token" type="password" value="{{ $settings['sms']['api_token'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">شماره فرستنده (شماره اختصاصی)</label>
                    <input name="sender_number" value="{{ $settings['sms']['sender_number'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr">
                </div>
                <div class="admin-actions mt-4">
                    <button class="admin-btn admin-btn-primary px-8"><span class="material-icons">save</span>ذخیره تنظیمات پیامک</button>
                </div>
            </form>
        </div>
    </div>

    <div id="test-hub" class="tab-content hidden space-y-6">
        <div class="admin-card">
            <div class="flex items-center justify-between mb-5 border-b border-slate/20 pb-3 dark:border-white/5">
                <div class="flex items-center gap-2">
                    <span class="material-icons text-primary">bookmark_added</span>
                    <h2 class="font-bold text-slate">مقادیر پیش‌فرض برای تست‌های مکرر</h2>
                </div>
                @if(($settings['test_defaults']['email'] ?? '') || ($settings['test_defaults']['phone'] ?? ''))
                    <form action="{{ route('dash.admin.communication.defaults.save', ['authkey' => $authkey]) }}" method="POST" data-admin-confirm="آیا از حذف اطلاعات پیش‌فرض اطمینان دارید؟">
                        @csrf
                        <input type="hidden" name="email" value="">
                        <input type="hidden" name="phone" value="">
                        <button class="text-[10px] text-danger font-bold hover:underline flex items-center gap-1">
                            <span class="material-icons text-xs">delete_sweep</span>
                            حذف اطلاعات پیش‌فرض
                        </button>
                    </form>
                @endif
            </div>
            <form action="{{ route('dash.admin.communication.defaults.save', ['authkey' => $authkey]) }}" method="POST" class="grid gap-5 md:grid-cols-2">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">ایمیل تستی پیش‌فرض (جهت ویرایش تغییر دهید)</label>
                    <input name="email" value="{{ $settings['test_defaults']['email'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="test@example.com">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/70 px-1">شماره تماس تستی پیش‌فرض (جهت ویرایش تغییر دهید)</label>
                    <input name="phone" value="{{ $settings['test_defaults']['phone'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="09120000000">
                </div>
                <div class="admin-actions md:col-span-2">
                    <button class="admin-btn admin-btn-secondary px-6"><span class="material-icons">save</span>ثبت و ویرایش مقادیر ثابت</button>
                </div>
            </form>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="admin-card">
                <div class="flex items-center gap-2 mb-4 border-b border-slate/20 pb-2 dark:border-white/5">
                    <span class="material-icons text-primary text-base">send</span>
                    <h3 class="font-bold text-slate text-sm">ارسال ایمیل آزمایشی</h3>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-[11px] font-bold text-slate/60 mb-1 block px-1">نوع ارسال:</label>
                        <select id="test-email-type" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10">
                            <option value="general">از طریق درگاه SMTP عمومی</option>
                            <option value="transactional">از طریق درگاه SMTP تراکنشی</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate/60 mb-1 block px-1">ایمیل مقصد:</label>
                        <input id="test-email-target" value="{{ $settings['test_defaults']['email'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="recipient@example.com">
                    </div>
                    <button type="button" onclick="runEmailTest()" id="email-test-btn" class="admin-btn admin-btn-primary w-full justify-center py-3">ارسال ایمیل تست</button>
                </div>
            </div>

            <div class="admin-card">
                <div class="flex items-center gap-2 mb-4 border-b border-slate/20 pb-2 dark:border-white/5">
                    <span class="material-icons text-primary text-base">phonelink_ring</span>
                    <h3 class="font-bold text-slate text-sm">ارسال پیامک آزمایشی</h3>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="text-[11px] font-bold text-slate/60 mb-1 block px-1">شماره مقصد:</label>
                        <input id="test-sms-target" value="{{ $settings['test_defaults']['phone'] ?? '' }}" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 admin-ltr" placeholder="0912xxxxxxx">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate/60 mb-1 block px-1">متن پیام:</label>
                        <textarea id="test-sms-message" class="w-full rounded-lg border border-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10" rows="2">Kalands Integrated Communication Hub - SMS Test</textarea>
                    </div>
                    <button type="button" onclick="runSmsTest()" id="sms-test-btn" class="admin-btn admin-btn-primary w-full justify-center py-3">ارسال پیامک تست</button>
                </div>
            </div>
        </div>

        <div id="test-log-card" class="admin-card hidden">
            <div class="flex items-center justify-between mb-4 border-b border-slate/20 pb-2 dark:border-white/5">
                <div class="flex items-center gap-2">
                    <span class="material-icons text-primary">receipt_long</span>
                    <h3 class="font-bold text-slate">خروجی و لاگ آخرین تست</h3>
                </div>
                <button onclick="document.getElementById('test-log-card').classList.add('hidden')" class="text-slate hover:text-danger flex"><span class="material-icons">close</span></button>
            </div>
            <div id="test-status-badge" class="mb-4 px-4 py-1.5 rounded-lg text-xs font-bold inline-block"></div>
            <div id="test-error-msg" class="text-danger text-sm font-bold mb-4 bg-danger/5 p-3 rounded-lg border border-danger/10 hidden"></div>
            <div class="rounded-lg bg-black/90 p-4 border border-white/5">
                <p class="text-[10px] text-slate-500 mb-2 font-mono uppercase tracking-widest">Server Response & Logs:</p>
                <pre id="test-full-log" class="admin-ltr text-green-400 overflow-x-auto text-[11px] max-h-[400px] whitespace-pre-wrap font-mono custom-scrollbar"></pre>
            </div>
        </div>
    </div>

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول جامع ارتباطی</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>ماژول جامع ارتباطی، سامانه یکپارچه ارسال ایمیل و پیامک در پلتفرم kalands.ir است. این ماژول شامل تنظیمات SMTP دو سطحی (عمومی و تراکنشی)، پنل پیامک ملی‌پیامک و قابلیت تست مستقیم کانکشن‌ها می‌باشد.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-smtp">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">mail</span>
                                سیستم SMTP
                            </h3>
                            <p>این سامانه دو بخش SMTP عمومی و SMTP تراکنشی دارد:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>SMTP عمومی:</b> برای ایمیل‌های عمومی مانند خبرنامه، اطلاعیه‌ها و پیامک‌های تایید حساب استفاده می‌شود.</li>
                                <li><b>SMTP تراکنشی:</b> برای ایمیل‌های حساس مانند بازیابی رمز عبور، فعال‌سازی حساب و ایمیل‌های احراز هویت استفاده می‌شود. در صورت خالی بودن، به SMTP عمومی رجوع می‌شود.</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-sms">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">sms</span>
                                سیستم پیامک
                            </h3>
                            <p>سیستم پیامک با پنل ملی‌پیامک ادغام شده است. برای استفاده از این سرویس، نیاز به توکن API و شماره اختصاصی دارید.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">ویژگی‌های کلیدی</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>ارسال کد تایید برای ثبت‌نام و بازیابی رمز عبور</li>
                                    <li>قابلیت تنظیم Endpoint دلخواه برای سایر پنل‌ها</li>
                                    <li>تست مستقیم از پنل برای اطمینان از دریافت پیامک</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-testing">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">science</span>
                                تست و عیب‌یابی
                            </h3>
                            <p>در تب تست و عیب‌یابی، می‌توانید مقادیر پیش‌فرض را ذخیره کنید تا در تست‌های دیگر سیستم استفاده شوند. همچنین می‌توانید ایمیل یا پیامک تستی بفرستید تا کانکشن را بررسی کنید.</p>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-smtp" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">سیستم SMTP</a>
                        <a href="#doc-sms" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">سامانه پیامک</a>
                        <a href="#doc-testing" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تست و عیب‌یابی</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-communication.js'])
