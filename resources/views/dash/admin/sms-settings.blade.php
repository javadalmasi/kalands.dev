<x-layouts.admin-dashboard title="تنظیمات پیامک" :helpModuleKey="'sms_settings'">
@php
    $authkey   = request()->route('authkey');
    $isAsync   = ! in_array($queueConnection, ['sync', 'null']);
    $hasToken  = ! empty($smsCfg['api_token'] ?? null);
@endphp

<x-admin.page-header title="تنظیمات پیامک">
    <x-slot:actions>
        <button type="submit" form="sms-config-form"
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
            {{ $isAsync ? 'پیامک‌ها از طریق صف ارسال می‌شوند' : 'صف غیرفعال — ارسال همزمان (sync)' }}
        </p>
        <p class="text-[10px] text-slate/60 dark:text-white/40 mt-0.5">
            درایور صف فعال: <span class="font-mono">{{ $queueConnection }}</span>
            &nbsp;—&nbsp;
            برای تغییر به
            <a href="{{ route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'queues']) }}"
               class="font-bold underline underline-offset-2">ماژول صف‌ها</a>
            بروید.
        </p>
    </div>
</div>

<form action="{{ route('dash.admin.sms.config.save', ['authkey' => $authkey]) }}" method="POST" id="sms-config-form">
    @csrf

    {{-- ── پیکربندی API ── --}}
    <div class="admin-card mb-5">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary">sms</span>
            <div>
                <h2 class="font-bold text-slate dark:text-white text-sm">پیکربندی پنل ملی‌پیامک</h2>
                <p class="text-[11px] text-slate/50 dark:text-white/40 mt-0.5">اتصال به سرویس MeliPayamak برای ارسال OTP و اعلان‌ها</p>
            </div>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="space-y-1.5 md:col-span-2">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">آدرس وب‌سرویس (Endpoint)</label>
                <input name="endpoint"
                    value="{{ $smsCfg['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp' }}"
                    class="admin-input admin-ltr"
                    placeholder="https://console.melipayamak.com/api/send/otp">
                <p class="text-[10px] text-slate/50 dark:text-white/40 px-1 mt-1">
                    توکن API به انتهای آدرس اضافه می‌شود:
                    <code class="font-mono bg-slate/10 dark:bg-white/5 px-1 rounded">{endpoint}/{token}</code>
                </p>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">توکن امنیتی (API Token)</label>
                <div class="relative">
                    <input name="api_token" id="sms-token" type="password"
                        value="{{ $smsCfg['api_token'] ?? '' }}"
                        class="admin-input admin-ltr ps-10">
                    <button type="button" data-toggle-password="sms-token"
                        class="absolute inset-y-0 start-0 px-2.5 text-slate/40 hover:text-primary transition-colors">
                        <span class="material-icons text-base">visibility</span>
                    </button>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">شماره فرستنده (اختیاری)</label>
                <input name="sender_number" value="{{ $smsCfg['sender_number'] ?? '' }}"
                    class="admin-input admin-ltr" placeholder="3000xxxxx">
                <p class="text-[10px] text-slate/50 dark:text-white/40 px-1 mt-1">در صورت خالی بودن از شماره پیش‌فرض پنل استفاده می‌شود.</p>
            </div>
        </div>
    </div>

    {{-- ── خلاصه وضعیت ── --}}
    <div class="admin-card mb-5">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary text-base">monitor_heart</span>
            <h3 class="font-bold text-slate dark:text-white text-sm">خلاصه وضعیت</h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
                <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">وضعیت توکن</p>
                <p class="text-xs font-mono font-bold {{ $hasToken ? 'text-success' : 'text-danger' }}">
                    {{ $hasToken ? 'تنظیم شده ✓' : 'تنظیم نشده ✗' }}
                </p>
            </div>
            <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
                <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">Endpoint</p>
                <p class="text-xs font-mono font-bold text-slate dark:text-white truncate">
                    {{ $hasToken ? parse_url($smsCfg['endpoint'] ?? '', PHP_URL_HOST) ?: '—' : '—' }}
                </p>
            </div>
            <div class="p-3 rounded-lg bg-slate/5 dark:bg-white/3 border border-slate/10 dark:border-white/5">
                <p class="text-[10px] text-slate/50 dark:text-white/40 uppercase tracking-widest mb-1">شماره فرستنده</p>
                <p class="text-xs font-mono font-bold text-slate dark:text-white">
                    {{ $smsCfg['sender_number'] ?? 'پیش‌فرض پنل' }}
                </p>
            </div>
        </div>
    </div>

    {{-- ── تست ارسال پیامک ── --}}
    <div class="admin-card mb-5">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate/10 dark:border-white/5">
            <span class="material-icons text-primary text-base">phonelink_ring</span>
            <div>
                <h3 class="font-bold text-slate dark:text-white text-sm">تست ارسال پیامک</h3>
                <p class="text-[10px] text-slate/50 dark:text-white/40 mt-0.5">
                    برای اطمینان از صحت توکن، یک پیامک آزمایشی ارسال کنید.
                    {{ $isAsync ? 'پیامک از طریق صف ('.$queueConnection.') ارسال می‌شود.' : 'ارسال همزمان (sync).' }}
                </p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">شماره مقصد</label>
                <input id="test-sms-target" class="admin-input admin-ltr" placeholder="09120000000">
            </div>
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate/60 dark:text-white/50 px-1">متن پیام / کد OTP</label>
                <input id="test-sms-message" class="admin-input admin-ltr" value="Kalands SMS Test - 12345">
            </div>
        </div>

        <div class="mt-4">
            <button type="button" id="sms-test-btn"
                data-url="{{ route('dash.admin.sms.config.test', ['authkey' => $authkey]) }}"
                class="admin-btn admin-btn-secondary px-6 gap-2">
                <span class="material-icons text-base">phonelink_ring</span><span>ارسال پیامک تست</span>
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

@vite(['resources/js/admin-sms-settings.js'])
</x-layouts.admin-dashboard>
