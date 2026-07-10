<x-layouts.admin-dashboard title="ماژول جامع ارتباطی" :helpModuleKey="'communication_hub'">
@php
    $authkey  = request()->route('authkey');
    $mailCfg  = $settings['mail_config']   ?? [];
    $smsCfg   = $settings['sms']           ?? [];
    $defaults = $settings['test_defaults'] ?? [];
    $driver   = $mailCfg['mailer'] ?? 'smtp';
    // Normalize: if somehow mailgun was saved before, reset to smtp
    if (!in_array($driver, ['smtp', 'sendmail', 'log'])) { $driver = 'smtp'; }
@endphp

<x-admin.page-header title="ماژول جامع ارتباطی" />

{{-- ──────────────────────── Tabs ──────────────────────── --}}
<x-admin.tab-bar id="comm-tabs">
    <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold flex items-center gap-2"
            data-tab-target="tab-settings">
        <span class="material-icons text-base">settings</span><span>تنظیمات ایمیل</span>
    </button>
    <button class="admin-tab-btn flex items-center gap-2"
            data-tab-target="tab-sms">
        <span class="material-icons text-base">sms</span><span>پیامک</span>
    </button>
    <button class="admin-tab-btn flex items-center gap-2"
            data-tab-target="tab-debug">
        <span class="material-icons text-base">bug_report</span><span>تست و دیباگ</span>
    </button>
</x-admin.tab-bar>

{{-- ══════════════════════════════════════════════════════
     TAB 1 : تنظیمات ایمیل
══════════════════════════════════════════════════════ --}}
<div id="tab-settings" class="tab-content">
    <form action="{{ route('dash.admin.mail.config.save', ['authkey' => $authkey]) }}" method="POST" id="mail-config-form">
        @csrf
        <input type="hidden" name="mailer" id="mailer-input" value="{{ $driver }}">

        {{-- ── Driver picker ── --}}
        <div class="admin-card mb-5">
            <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10">
                <span class="material-icons text-primary">dns</span>
                <div>
                    <h2 class="font-bold text-slate text-sm">درایور ارسال ایمیل</h2>
                    <p class="text-[11px] text-slate/50 mt-0.5">نحوه ارسال ایمیل از سرور را انتخاب کنید</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3" id="driver-picker">

                {{-- SMTP --}}
                <button type="button" data-driver="smtp"
                    class="driver-card flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all cursor-pointer text-center
                           {{ $driver === 'smtp' ? 'border-primary bg-primary/8 text-primary' : 'border-slate/15 text-slate/60 hover:border-primary/40' }}">
                    <span class="material-icons text-2xl">dns</span>
                    <div>
                        <p class="text-xs font-bold">SMTP</p>
                        <p class="text-[10px] opacity-70 mt-0.5">سرور SMTP اختصاصی</p>
                    </div>
                </button>

                {{-- Sendmail --}}
                <button type="button" data-driver="sendmail"
                    class="driver-card flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all cursor-pointer text-center
                           {{ $driver === 'sendmail' ? 'border-primary bg-primary/8 text-primary' : 'border-slate/15 text-slate/60 hover:border-primary/40' }}">
                    <span class="material-icons text-2xl">terminal</span>
                    <div>
                        <p class="text-xs font-bold">Sendmail</p>
                        <p class="text-[10px] opacity-70 mt-0.5">PHP mail سرور محلی</p>
                    </div>
                </button>

                {{-- Log --}}
                <button type="button" data-driver="log"
                    class="driver-card flex flex-col items-center gap-2 p-4 rounded-xl border-2 transition-all cursor-pointer text-center
                           {{ $driver === 'log' ? 'border-amber-500 bg-amber-500/8 text-amber-500' : 'border-slate/15 text-slate/60 hover:border-amber-400/40' }}">
                    <span class="material-icons text-2xl">article</span>
                    <div>
                        <p class="text-xs font-bold">Log</p>
                        <p class="text-[10px] opacity-70 mt-0.5">فقط در فایل لاگ</p>
                    </div>
                </button>
            </div>
        </div>

        {{-- ── SMTP fields ── --}}
        <div id="fields-smtp" data-driver-fields="smtp" class="{{ $driver !== 'smtp' ? 'hidden' : '' }} admin-card mb-5">
            <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10">
                <span class="material-icons text-primary">cable</span>
                <div>
                    <h2 class="font-bold text-slate text-sm">پیکربندی SMTP</h2>
                    <p class="text-[11px] text-slate/50 mt-0.5">اتصال مستقیم به سرور ایمیل از طریق پروتکل SMTP</p>
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <div class="space-y-1.5 md:col-span-2">
                    <label class="text-xs font-bold text-slate/60 px-1">میزبان (Host)</label>
                    <input name="host" value="{{ $mailCfg['host'] ?? '' }}"
                        class="admin-input admin-ltr"
                        placeholder="smtp.gmail.com">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/60 px-1">پورت (Port)</label>
                    <input name="port" id="smtp-port" type="number" value="{{ $mailCfg['port'] ?? '587' }}"
                        class="admin-input admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/60 px-1">رمزنگاری (Encryption)</label>
                    <select name="encryption" id="smtp-encryption"
                        class="admin-input">
                        <option value=""  @selected(!($mailCfg['encryption'] ?? null))>بدون رمزنگاری (port 25)</option>
                        <option value="tls" @selected(($mailCfg['encryption'] ?? '') === 'tls')>STARTTLS (port 587)</option>
                        <option value="ssl" @selected(($mailCfg['encryption'] ?? '') === 'ssl')>SSL / TLS (port 465)</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/60 px-1">نام کاربری (Username)</label>
                    <input name="username" value="{{ $mailCfg['username'] ?? '' }}"
                        class="admin-input admin-ltr">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/60 px-1">کلمه عبور (Password)</label>
                    <div class="relative">
                        <input name="password" id="smtp-password" type="password" value="{{ $mailCfg['password'] ?? '' }}"
                            class="admin-input admin-ltr ps-10">
                        <button type="button" data-toggle-password="smtp-password"
                            class="absolute inset-y-0 start-0 px-2.5 text-slate/40 hover:text-primary transition-colors">
                            <span class="material-icons text-base">visibility</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between p-4 rounded-xl bg-slate/5 border border-slate/10">
                <div>
                    <p class="text-xs font-bold text-slate">تأیید گواهی TLS (Verify Peer)</p>
                    <p class="text-[10px] text-slate/50 mt-0.5">در صورت استفاده از گواهی self-signed غیرفعال کنید</p>
                </div>
                <label class="admin-switch">
                    <input type="hidden" name="verify_peer" value="0">
                    <input type="checkbox" name="verify_peer" value="1" class="admin-switch-input"
                        {{ (bool)($mailCfg['verify_peer'] ?? true) ? 'checked' : '' }}>
                    <span class="admin-switch-track"></span>
                    <span class="admin-switch-ball"></span>
                </label>
            </div>
        </div>

        {{-- ── Sendmail fields ── --}}
        <div id="fields-sendmail" data-driver-fields="sendmail" class="{{ $driver !== 'sendmail' ? 'hidden' : '' }} admin-card mb-5">
            <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10">
                <span class="material-icons text-primary">terminal</span>
                <div>
                    <h2 class="font-bold text-slate text-sm">پیکربندی Sendmail</h2>
                    <p class="text-[11px] text-slate/50 mt-0.5">ارسال از طریق برنامه sendmail سرور</p>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 px-1">مسیر Sendmail (اختیاری)</label>
                <input name="sendmail_path" value="{{ $mailCfg['sendmail_path'] ?? '' }}"
                    class="admin-input admin-ltr font-mono"
                    placeholder="/usr/sbin/sendmail -bs -i">
                <p class="text-[10px] text-slate/50 px-1 mt-1">در صورت خالی بودن از مقدار <code class="font-mono bg-slate/10 px-1 rounded">/usr/sbin/sendmail -bs -i</code> استفاده می‌شود.</p>
            </div>
        </div>

        {{-- ── Log info ── --}}
        <div id="fields-log" data-driver-fields="log" class="{{ $driver !== 'log' ? 'hidden' : '' }} admin-card mb-5">
            <div class="flex gap-3 items-start p-4 rounded-xl bg-amber-500/8 border border-amber-500/20">
                <span class="material-icons text-amber-500 mt-0.5 text-base">warning_amber</span>
                <div>
                    <p class="text-xs font-bold text-slate">حالت توسعه — ایمیل‌ها ارسال نمی‌شوند</p>
                    <p class="text-[11px] text-slate/60 mt-1 leading-5">در این حالت تمام ایمیل‌ها به جای ارسال واقعی، در فایل <code class="font-mono bg-slate/10 px-1 rounded">storage/logs/laravel.log</code> نوشته می‌شوند. فقط برای محیط توسعه مناسب است.</p>
                </div>
            </div>
        </div>

        {{-- ── اطلاعات فرستنده (همیشه) ── --}}
        <div class="admin-card mb-5">
            <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10">
                <span class="material-icons text-primary">alternate_email</span>
                <div>
                    <h2 class="font-bold text-slate text-sm">اطلاعات فرستنده</h2>
                    <p class="text-[11px] text-slate/50 mt-0.5">این اطلاعات در تمام ایمیل‌های ارسالی نمایش داده می‌شود</p>
                </div>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/60 px-1">ایمیل فرستنده</label>
                    <input name="sender_email" value="{{ $mailCfg['sender_email'] ?? '' }}"
                        class="admin-input admin-ltr"
                        placeholder="no-reply@yourdomain.com">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate/60 px-1">نام فرستنده</label>
                    <input name="sender_name" value="{{ $mailCfg['sender_name'] ?? '' }}"
                        class="admin-input"
                        placeholder="فروشگاه کالندز">
                </div>
            </div>
        </div>

        <div class="admin-actions">
            <button class="admin-btn admin-btn-primary px-8">
                <span class="material-icons">save</span>ذخیره تنظیمات ایمیل
            </button>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════
     TAB 2 : پیامک
══════════════════════════════════════════════════════ --}}
<div id="tab-sms" class="tab-content hidden">
    <div class="admin-card">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10">
            <span class="material-icons text-primary">sms</span>
            <div>
                <h2 class="font-bold text-slate text-sm">پیکربندی پنل پیامک</h2>
                <p class="text-[11px] text-slate/50 mt-0.5">اتصال به سرویس MeliPayamak برای ارسال OTP و اعلان‌ها</p>
            </div>
        </div>
        <form action="{{ route('dash.admin.sms.config.save', ['authkey' => $authkey]) }}" method="POST"
              class="grid gap-5 md:grid-cols-2">
            @csrf
            <div class="space-y-1.5 md:col-span-2">
                <label class="text-xs font-bold text-slate/60 px-1">آدرس وب‌سرویس (Endpoint)</label>
                <input name="endpoint" value="{{ $smsCfg['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp' }}"
                    class="admin-input admin-ltr"
                    placeholder="https://console.melipayamak.com/api/send/otp">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 px-1">توکن امنیتی (API Token)</label>
                <div class="relative">
                    <input name="api_token" id="sms-token" type="password" value="{{ $smsCfg['api_token'] ?? '' }}"
                        class="admin-input admin-ltr ps-10">
                    <button type="button" data-toggle-password="sms-token"
                        class="absolute inset-y-0 start-0 px-2.5 text-slate/40 hover:text-primary transition-colors">
                        <span class="material-icons text-base">visibility</span>
                    </button>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 px-1">شماره فرستنده (اختیاری)</label>
                <input name="sender_number" value="{{ $smsCfg['sender_number'] ?? '' }}"
                    class="admin-input admin-ltr"
                    placeholder="3000xxxxx">
            </div>
            <div class="admin-actions md:col-span-2">
                <button class="admin-btn admin-btn-primary px-6"><span class="material-icons">save</span>ذخیره تنظیمات پیامک</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     TAB 3 : تست و دیباگ
══════════════════════════════════════════════════════ --}}
<div id="tab-debug" class="tab-content hidden space-y-5">

    {{-- وضعیت سیستم --}}
    <div class="admin-card">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate/10">
            <span class="material-icons text-primary text-base">monitor_heart</span>
            <h3 class="font-bold text-slate text-sm">وضعیت سیستم ارتباطی</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="p-3 rounded-lg bg-slate/5 border border-slate/10">
                <p class="text-[10px] text-slate/50 uppercase tracking-widest mb-1">درایور ایمیل</p>
                <p class="text-xs font-mono font-bold text-slate uppercase">{{ $driver }}</p>
            </div>
            @if($driver === 'smtp')
            <div class="p-3 rounded-lg bg-slate/5 border border-slate/10">
                <p class="text-[10px] text-slate/50 uppercase tracking-widest mb-1">Host : Port</p>
                <p class="text-xs font-mono font-bold text-slate truncate">
                    {{ $mailCfg['host'] ?? '—' }} : {{ $mailCfg['port'] ?? '—' }}
                </p>
            </div>
            <div class="p-3 rounded-lg bg-slate/5 border border-slate/10">
                <p class="text-[10px] text-slate/50 uppercase tracking-widest mb-1">Encryption</p>
                <p class="text-xs font-mono font-bold text-slate">{{ strtoupper($mailCfg['encryption'] ?? 'none') }}</p>
            </div>
            @else
            <div class="p-3 rounded-lg bg-slate/5 border border-slate/10 md:col-span-2">
                <p class="text-[10px] text-slate/50 uppercase tracking-widest mb-1">وضعیت</p>
                <p class="text-xs font-mono font-bold {{ $driver === 'log' ? 'text-amber-500' : 'text-slate' }}">
                    {{ $driver === 'log' ? 'حالت توسعه — بدون ارسال واقعی' : 'پیکربندی‌شده' }}
                </p>
            </div>
            @endif
            <div class="p-3 rounded-lg bg-slate/5 border border-slate/10">
                <p class="text-[10px] text-slate/50 uppercase tracking-widest mb-1">SMS Token</p>
                <p class="text-xs font-mono font-bold {{ ($smsCfg['api_token'] ?? null) ? 'text-success' : 'text-danger' }}">
                    {{ ($smsCfg['api_token'] ?? null) ? 'تنظیم شده ✓' : 'تنظیم نشده ✗' }}
                </p>
            </div>
        </div>
    </div>

    {{-- مقادیر پیش‌فرض --}}
    <div class="admin-card">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate/10">
            <div class="flex items-center gap-2">
                <span class="material-icons text-primary text-base">bookmark_added</span>
                <h3 class="font-bold text-slate text-sm">مقادیر پیش‌فرض تست</h3>
            </div>
            @if(($defaults['email'] ?? '') || ($defaults['phone'] ?? ''))
                <form action="{{ route('dash.admin.communication.defaults.save', ['authkey' => $authkey]) }}" method="POST"
                      data-admin-confirm="آیا از حذف اطلاعات پیش‌فرض اطمینان دارید؟">
                    @csrf
                    <input type="hidden" name="email" value=""><input type="hidden" name="phone" value="">
                    <button class="text-[10px] text-danger font-bold hover:underline flex items-center gap-1">
                        <span class="material-icons text-xs">delete_sweep</span>حذف
                    </button>
                </form>
            @endif
        </div>
        <form action="{{ route('dash.admin.communication.defaults.save', ['authkey' => $authkey]) }}" method="POST"
              class="grid gap-4 md:grid-cols-2">
            @csrf
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 px-1">ایمیل تستی پیش‌فرض</label>
                <input name="email" value="{{ $defaults['email'] ?? '' }}"
                    class="admin-input admin-ltr"
                    placeholder="test@example.com">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 px-1">شماره تماس تستی پیش‌فرض</label>
                <input name="phone" value="{{ $defaults['phone'] ?? '' }}"
                    class="admin-input admin-ltr"
                    placeholder="09120000000">
            </div>
            <div class="admin-actions md:col-span-2">
                <button class="admin-btn admin-btn-secondary px-6"><span class="material-icons">save</span>ذخیره مقادیر پیش‌فرض</button>
            </div>
        </form>
    </div>

    {{-- کارت‌های تست --}}
    <div class="grid gap-5 md:grid-cols-2">

        {{-- تست ایمیل --}}
        <div class="admin-card">
            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate/10">
                <span class="material-icons text-primary text-base">send</span>
                <div>
                    <h3 class="font-bold text-slate text-sm">تست ایمیل</h3>
                    <p class="text-[10px] text-slate/50 mt-0.5">درایور فعال: <span class="font-mono uppercase">{{ $driver }}</span></p>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-[11px] font-bold text-slate/60 mb-1.5 block px-1">ایمیل مقصد:</label>
                    <input id="test-email-target" value="{{ $defaults['email'] ?? '' }}"
                        class="admin-input admin-ltr"
                        placeholder="recipient@example.com">
                </div>
                <button type="button" id="email-test-btn"
                    data-url="{{ route('dash.admin.mail.config.test', ['authkey' => $authkey]) }}"
                    class="admin-btn admin-btn-primary w-full justify-center py-3 gap-2">
                    <span class="material-icons text-base">send</span><span>ارسال ایمیل تست</span>
                </button>
            </div>
        </div>

        {{-- تست پیامک --}}
        <div class="admin-card">
            <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate/10">
                <span class="material-icons text-primary text-base">phonelink_ring</span>
                <h3 class="font-bold text-slate text-sm">تست پیامک</h3>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-[11px] font-bold text-slate/60 mb-1.5 block px-1">شماره مقصد:</label>
                    <input id="test-sms-target" value="{{ $defaults['phone'] ?? '' }}"
                        class="admin-input admin-ltr"
                        placeholder="09120000000">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate/60 mb-1.5 block px-1">متن پیام / کد OTP:</label>
                    <textarea id="test-sms-message" rows="2"
                        class="admin-input admin-ltr">Kalands SMS Test - 12345</textarea>
                </div>
                <button type="button" id="sms-test-btn"
                    data-url="{{ route('dash.admin.sms.config.test', ['authkey' => $authkey]) }}"
                    class="admin-btn admin-btn-primary w-full justify-center py-3 gap-2">
                    <span class="material-icons text-base">phonelink_ring</span><span>ارسال پیامک تست</span>
                </button>
            </div>
        </div>
    </div>

    {{-- لاگ دیباگ --}}
    <div id="debug-log-card" class="admin-card hidden">
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate/10">
            <div class="flex items-center gap-2">
                <span class="material-icons text-primary text-base">receipt_long</span>
                <h3 class="font-bold text-slate text-sm">خروجی دیباگ</h3>
            </div>
            <div class="flex items-center gap-2">
                <button id="copy-log-btn" class="flex items-center gap-1 text-[10px] text-slate/60 hover:text-primary font-bold transition-colors">
                    <span class="material-icons text-xs">content_copy</span>کپی
                </button>
                <button onclick="document.getElementById('debug-log-card').classList.add('hidden')"
                    class="flex text-slate/40 hover:text-danger transition-colors">
                    <span class="material-icons text-base">close</span>
                </button>
            </div>
        </div>
        <div id="debug-status-badge" class="mb-4 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold"></div>
        <div id="debug-error-msg" class="text-danger text-xs font-bold mb-4 bg-danger/5 p-3 rounded-lg border border-danger/15 hidden leading-6"></div>
        <div class="rounded-xl bg-[#0d1117] border border-white/5 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2 border-b border-white/5 bg-white/2">
                <span class="text-[9px] font-mono text-white/30 uppercase tracking-widest">Server Response</span>
                <div class="flex gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500/40"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-500/40"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500/40"></span>
                </div>
            </div>
            <pre id="debug-full-log"
                class="admin-ltr p-4 text-green-400 overflow-x-auto text-[11px] max-h-96 whitespace-pre-wrap font-mono custom-scrollbar leading-5"></pre>
        </div>
    </div>

</div>

@vite(['resources/js/admin-communication.js'])
</x-layouts.admin-dashboard>
