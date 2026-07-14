<x-layouts.admin-dashboard title="تنظیمات ایمیل" :helpModuleKey="'email_settings'">
@php
    $authkey = request()->route('authkey');
    $driver  = $mailCfg['mailer'] ?? 'smtp';
    if (! in_array($driver, ['smtp', 'sendmail'])) {
        $driver = 'smtp';
    }
    $isAsync = ! in_array($queueConnection, ['sync', 'null']);
@endphp

<x-admin.page-header title="تنظیمات ایمیل">
    <x-slot:actions>
        <button type="submit" form="mail-config-form"
            class="admin-btn admin-btn-primary px-6 shadow-lg shadow-primary/20">
            <span class="material-icons">save</span>ذخیره تنظیمات
        </button>
    </x-slot:actions>
</x-admin.page-header>

{{-- ══════════════════════════════════════════════════════
     وضعیت صف
══════════════════════════════════════════════════════ --}}
<div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl
    {{ $isAsync ? 'bg-success/8 border border-success/20' : 'bg-amber-500/8 border border-amber-500/20' }}">
    <span class="material-icons text-base {{ $isAsync ? 'text-success' : 'text-amber-500' }}">
        {{ $isAsync ? 'done_all' : 'warning_amber' }}
    </span>
    <div class="flex-1">
        <p class="text-xs font-bold {{ $isAsync ? 'text-success' : 'text-amber-600 dark:text-amber-400' }}">
            {{ $isAsync ? 'ایمیل‌ها از طریق صف ارسال می‌شوند' : 'صف غیرفعال — ارسال همزمان (sync)' }}
        </p>
        <p class="text-[10px] text-slate/60 mt-0.5">
            درایور صف فعال: <span class="font-mono">{{ $queueConnection }}</span>
            &nbsp;—&nbsp;
            برای تغییر به
            <a href="{{ route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'queues']) }}"
               class="font-bold underline underline-offset-2">ماژول صف‌ها</a>
            بروید.
        </p>
    </div>
</div>

<form action="{{ route('dash.admin.mail.config.save', ['authkey' => $authkey]) }}" method="POST" id="mail-config-form">
    @csrf
    <input type="hidden" name="mailer" id="mailer-input" value="{{ $driver }}">

    {{-- ── Driver picker ── --}}
    <div class="admin-card mb-5">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary">dns</span>
            <div>
                <h2 class="font-bold text-slate dark:text-white text-sm">درایور ارسال ایمیل</h2>
                <p class="text-[11px] text-slate/50 dark:text-white/40 mt-0.5">نحوه ارسال ایمیل از سرور را انتخاب کنید</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3" id="driver-picker">
            {{-- SMTP --}}
            <button type="button" data-driver="smtp"
                class="driver-card flex items-center gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer text-start
                       {{ $driver === 'smtp' ? 'border-primary bg-primary/8 text-primary' : 'border-slate/15 dark:border-white/8 text-slate/60 dark:text-white/50 hover:border-primary/40' }}">
                <span class="material-icons text-2xl shrink-0">dns</span>
                <div>
                    <p class="text-xs font-bold">SMTP</p>
                    <p class="text-[10px] opacity-70 mt-0.5">سرور SMTP اختصاصی (Gmail، Postmark، ...)</p>
                </div>
            </button>

            {{-- Sendmail --}}
            <button type="button" data-driver="sendmail"
                class="driver-card flex items-center gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer text-start
                       {{ $driver === 'sendmail' ? 'border-primary bg-primary/8 text-primary' : 'border-slate/15 dark:border-white/8 text-slate/60 dark:text-white/50 hover:border-primary/40' }}">
                <span class="material-icons text-2xl shrink-0">terminal</span>
                <div>
                    <p class="text-xs font-bold">Sendmail</p>
                    <p class="text-[10px] opacity-70 mt-0.5">PHP mail از طریق sendmail سرور</p>
                </div>
            </button>
        </div>
    </div>

    {{-- ── SMTP fields ── --}}
    <div id="fields-smtp" data-driver-fields="smtp"
         class="{{ $driver !== 'smtp' ? 'hidden' : '' }} admin-card mb-5">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary">cable</span>
            <div>
                <h2 class="font-bold text-slate dark:text-white text-sm">پیکربندی SMTP</h2>
                <p class="text-[11px] text-slate/50 dark:text-white/40 mt-0.5">اتصال مستقیم به سرور ایمیل از طریق پروتکل SMTP</p>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            <div class="space-y-1.5 md:col-span-2">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">میزبان (Host)</label>
                <input name="host" value="{{ $mailCfg['host'] ?? '' }}"
                    class="admin-input admin-ltr" placeholder="smtp.example.com">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">پورت (Port)</label>
                <input name="port" id="smtp-port" type="number" value="{{ $mailCfg['port'] ?? '587' }}"
                    class="admin-input admin-ltr">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">رمزنگاری</label>
                <select name="encryption" id="smtp-encryption" class="admin-input">
                    <option value=""   @selected(!($mailCfg['encryption'] ?? null))>بدون رمزنگاری (port 25)</option>
                    <option value="tls" @selected(($mailCfg['encryption'] ?? '') === 'tls')>STARTTLS (port 587)</option>
                    <option value="ssl" @selected(($mailCfg['encryption'] ?? '') === 'ssl')>SSL / TLS (port 465)</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">نام کاربری</label>
                <input name="username" value="{{ $mailCfg['username'] ?? '' }}"
                    class="admin-input admin-ltr">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">کلمه عبور</label>
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

        <div class="mt-4 flex items-center justify-between p-4 rounded-xl bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
            <div>
                <p class="text-xs font-bold text-slate dark:text-white">تأیید گواهی TLS (Verify Peer)</p>
                <p class="text-[10px] text-slate/50 dark:text-white/40 mt-0.5">در صورت استفاده از گواهی self-signed غیرفعال کنید</p>
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
    <div id="fields-sendmail" data-driver-fields="sendmail"
         class="{{ $driver !== 'sendmail' ? 'hidden' : '' }} admin-card mb-5">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary">terminal</span>
            <div>
                <h2 class="font-bold text-slate dark:text-white text-sm">پیکربندی Sendmail</h2>
                <p class="text-[11px] text-slate/50 dark:text-white/40 mt-0.5">ارسال از طریق برنامه sendmail سرور محلی</p>
            </div>
        </div>
        <div class="space-y-1.5">
            <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">مسیر Sendmail (اختیاری)</label>
            <input name="sendmail_path" value="{{ $mailCfg['sendmail_path'] ?? '' }}"
                class="admin-input admin-ltr font-mono"
                placeholder="/usr/sbin/sendmail -bs -i">
            <p class="text-[10px] text-slate/50 dark:text-white/40 px-1 mt-1">
                در صورت خالی بودن از مقدار
                <code class="font-mono bg-slate/10 dark:bg-white/5 px-1 rounded">/usr/sbin/sendmail -bs -i</code>
                استفاده می‌شود.
            </p>
        </div>
    </div>

    {{-- ── اطلاعات فرستنده ── --}}
    <div class="admin-card mb-5">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary">alternate_email</span>
            <div>
                <h2 class="font-bold text-slate dark:text-white text-sm">اطلاعات فرستنده</h2>
                <p class="text-[11px] text-slate/50 dark:text-white/40 mt-0.5">در تمام ایمیل‌های ارسالی نمایش داده می‌شود</p>
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">آدرس ایمیل فرستنده</label>
                <input name="sender_email" value="{{ $mailCfg['sender_email'] ?? '' }}"
                    class="admin-input admin-ltr" placeholder="no-reply@yourdomain.com">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">نام فرستنده</label>
                <input name="sender_name" value="{{ $mailCfg['sender_name'] ?? '' }}"
                    class="admin-input" placeholder="فروشگاه کالندز">
            </div>
        </div>
    </div>

    {{-- ── خلاصه وضعیت ── --}}
    <div class="admin-card mb-5">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary text-base">monitor_heart</span>
            <h3 class="font-bold text-slate dark:text-white text-sm">خلاصه پیکربندی فعال</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
                <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">درایور</p>
                <p class="text-xs font-mono font-bold text-slate dark:text-white uppercase">{{ $driver }}</p>
            </div>
            @if($driver === 'smtp')
                <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
                    <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">Host</p>
                    <p class="text-xs font-mono font-bold text-slate dark:text-white truncate">{{ $mailCfg['host'] ?? '—' }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
                    <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">Port</p>
                    <p class="text-xs font-mono font-bold text-slate dark:text-white">{{ $mailCfg['port'] ?? '—' }}</p>
                </div>
                <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
                    <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">Encryption</p>
                    <p class="text-xs font-mono font-bold text-slate dark:text-white">{{ strtoupper($mailCfg['encryption'] ?? 'none') }}</p>
                </div>
            @else
                <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5 col-span-3">
                    <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">وضعیت</p>
                    <p class="text-xs font-mono font-bold text-slate dark:text-white">پیکربندی‌شده</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── تست ارسال ایمیل ── --}}
    <div class="admin-card mb-5">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary text-base">send</span>
            <div>
                <h3 class="font-bold text-slate dark:text-white text-sm">تست ارسال ایمیل</h3>
                <p class="text-[10px] text-slate/50 dark:text-white/40 mt-0.5">
                    برای اطمینان از صحت تنظیمات، یک ایمیل آزمایشی ارسال کنید.
                    {{ $isAsync ? 'ایمیل از طریق صف ('.$queueConnection.') ارسال می‌شود.' : 'ارسال همزمان (sync).' }}
                </p>
            </div>
        </div>
        <div class="flex gap-3 items-end">
            <div class="flex-1 space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">ایمیل مقصد</label>
                <input id="test-email-target" type="email"
                    class="admin-input admin-ltr" placeholder="recipient@example.com">
            </div>
            <button type="button" id="email-test-btn"
                data-url="{{ route('dash.admin.mail.config.test', ['authkey' => $authkey]) }}"
                class="admin-btn admin-btn-secondary px-6 gap-2 shrink-0">
                <span class="material-icons text-base">send</span><span>ارسال تست</span>
            </button>
        </div>

        {{-- نتیجه تست --}}
        <div id="test-result-card" class="hidden mt-4 rounded-xl bg-[#0d1117] border border-white/5 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-2.5 border-b border-white/5 bg-white/2">
                <div id="test-result-badge"></div>
                <button type="button" onclick="document.getElementById('test-result-card').classList.add('hidden')"
                    class="text-white/30 hover:text-white/70 transition-colors">
                    <span class="material-icons text-base">close</span>
                </button>
            </div>
            <pre id="test-result-msg" class="admin-ltr p-4 overflow-x-auto max-h-80 custom-scrollbar leading-5"></pre>
        </div>
    </div>

</form>

@vite(['resources/js/admin-email-settings.js'])
</x-layouts.admin-dashboard>
