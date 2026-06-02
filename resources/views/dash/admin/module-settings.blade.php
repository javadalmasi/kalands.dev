<x-layouts.admin-dashboard :title="$module['label']">
    @php($authkey = request()->route('authkey'))
    @php($moduleSettings = $settings[$moduleKey] ?? [])

    <div class="mb-4">
        <a href="{{ route('dash.admin.modules', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary">
            <span class="material-icons">arrow_forward</span>
            بازگشت به ماژول‌ها
        </a>
    </div>

    <div class="admin-card space-y-4">
        <div class="flex items-start gap-3">
            <div class="rounded-full bg-blue/10 p-2 text-blue"><span class="material-symbols-outlined">{{ $module['icon'] }}</span></div>
            <div>
                <h2 class="text-base font-semibold">{{ $module['label'] }}</h2>
                <p class="mt-1 text-sm text-slate">{{ $module['description'] }}</p>
            </div>
        </div>

        @if($moduleKey === 'smtp_general' || $moduleKey === 'smtp_transactional')
            <form action="{{ route($moduleKey === 'smtp_general' ? 'dash.admin.smtp.general.save' : 'dash.admin.smtp.transactional.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <input name="host" value="{{ old('host', $moduleSettings['host'] ?? '') }}" placeholder="Host" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="port" value="{{ old('port', $moduleSettings['port'] ?? '') }}" placeholder="Port" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="username" value="{{ old('username', $moduleSettings['username'] ?? '') }}" placeholder="Username" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="password" value="{{ old('password', $moduleSettings['password'] ?? '') }}" placeholder="Password" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <select name="encryption" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                        <option value="">بدون رمزنگاری</option>
                        <option value="tls" @selected(old('encryption', $moduleSettings['encryption'] ?? '') === 'tls')>TLS</option>
                        <option value="ssl" @selected(old('encryption', $moduleSettings['encryption'] ?? '') === 'ssl')>SSL</option>
                    </select>
                    <input name="sender_email" value="{{ old('sender_email', $moduleSettings['sender_email'] ?? '') }}" placeholder="Sender Email" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="sender_name" value="{{ old('sender_name', $moduleSettings['sender_name'] ?? '') }}" placeholder="Sender Name" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                </div>
                <div class="admin-actions"><button class="admin-btn" type="submit"><span class="material-icons">save</span>ذخیره</button></div>
            </form>
            <form action="{{ route($moduleKey === 'smtp_general' ? 'dash.admin.smtp.general.test' : 'dash.admin.smtp.transactional.test', ['authkey' => $authkey]) }}" method="POST" class="space-y-3 border-t border-slate/50 pt-3">
                @csrf
                <input name="to" type="email" placeholder="ایمیل تست" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <div class="admin-actions"><button class="admin-btn admin-btn-secondary" type="submit"><span class="material-icons">send</span>ارسال تست</button></div>
            </form>
        @elseif($moduleKey === 'file_manager')
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h3 class="font-semibold text-slate">مرور فایل‌ها</h3>
                    <button type="button" class="admin-btn admin-btn-secondary" data-admin-refresh-explorer><span class="material-icons">refresh</span>بازخوانی</button>
                </div>
                <div class="space-y-3" data-module-explorer data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}" data-file-url-base="/uploads" data-cdn-base-url="{{ $moduleSettings['cdn_base_url'] ?? '' }}" data-explorer-path="">
                    <div class="rounded-squircle border border-slate/70 bg-white/5 p-4 text-sm text-slate">در حال بارگذاری فایل‌ها...</div>
                </div>
            </div>
        @elseif($moduleKey === 'home_items_management')
            <div class="flex justify-center py-10">
                <a href="{{ route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'home_items_management']) }}" class="admin-btn">
                    <span class="material-icons">open_in_new</span>
                    ورود به صفحه مدیریت محتوای صفحه اصلی
                </a>
            </div>
        @elseif($moduleKey === 'sms')
            <form action="{{ route('dash.admin.sms.config.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-3">
                @csrf
                <input name="endpoint" value="{{ old('endpoint', $moduleSettings['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp/{token}') }}" placeholder="API Endpoint" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <input name="api_token" value="{{ old('api_token', $moduleSettings['api_token'] ?? '') }}" placeholder="API Token" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <input name="sender_number" value="{{ old('sender_number', $moduleSettings['sender_number'] ?? '') }}" placeholder="Sender Number" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <div class="admin-actions"><button class="admin-btn" type="submit"><span class="material-icons">save</span>ذخیره</button></div>
            </form>
            <form action="{{ route('dash.admin.sms.config.test', ['authkey' => $authkey]) }}" method="POST" class="space-y-3 border-t border-slate/50 pt-3">
                @csrf
                <input name="to" placeholder="شماره موبایل تست (09...)" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <input name="message" value="SMS test from admin panel" placeholder="متن تست" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <div class="admin-actions"><button class="admin-btn admin-btn-secondary" type="submit"><span class="material-icons">send</span>ارسال تست</button></div>
            </form>
        @elseif($moduleKey === 'contact')
            <form action="{{ route('dash.admin.contact.page.info.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-3">
                @csrf
                <input name="title" value="{{ old('title', $moduleSettings['title'] ?? 'اطلاعات تماس') }}" placeholder="عنوان" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <textarea name="description" rows="4" placeholder="توضیحات" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">{{ old('description', $moduleSettings['description'] ?? '') }}</textarea>
                <div class="grid gap-3 md:grid-cols-2">
                    <input name="phone" value="{{ old('phone', $moduleSettings['phone'] ?? '') }}" placeholder="Phone" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="email" value="{{ old('email', $moduleSettings['email'] ?? '') }}" placeholder="Email" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="address" value="{{ old('address', $moduleSettings['address'] ?? '') }}" placeholder="Address" class="rounded border border-slate bg-slate p-2 md:col-span-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="work_hours" value="{{ old('work_hours', $moduleSettings['work_hours'] ?? '') }}" placeholder="Work hours" class="rounded border border-slate bg-slate p-2 md:col-span-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                </div>
                <div class="admin-actions"><button class="admin-btn" type="submit"><span class="material-icons">save</span>ذخیره</button></div>
            </form>
        @elseif($moduleKey === 'affiliate')
            <form action="{{ route('dash.admin.affiliate.settings.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <input name="merchant_id" value="{{ old('merchant_id', $moduleSettings['merchant_id'] ?? '') }}" placeholder="Merchant ID" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="access_token" value="{{ old('access_token', $moduleSettings['access_token'] ?? '') }}" placeholder="Access Token" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="url_prefix" value="{{ old('url_prefix', $moduleSettings['url_prefix'] ?? 'https://a.bslm.ir/api/v1/tracking/click/') }}" placeholder="URL Prefix" class="rounded border border-slate bg-slate p-2 md:col-span-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <input name="cache_ttl_minutes" value="{{ old('cache_ttl_minutes', $moduleSettings['cache_ttl_minutes'] ?? 120) }}" placeholder="Cache TTL" class="rounded border border-slate bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                </div>
                <div class="admin-actions"><button class="admin-btn" type="submit"><span class="material-icons">save</span>ذخیره</button></div>
            </form>
        @elseif($moduleKey === 'queues')
            <div class="flex justify-center py-10">
                <a href="{{ route('dash.admin.queues', ['authkey' => $authkey]) }}" class="admin-btn">
                    <span class="material-icons">open_in_new</span>
                    ورود به صفحه مدیریت صف‌ها و گزارشات
                </a>
            </div>
        @elseif($moduleKey === 'email_templates')
            <a href="{{ route('dash.admin.email.templates', ['authkey' => $authkey]) }}" class="admin-btn"><span class="material-icons">open_in_new</span>ورود به صفحه تمپلیت ایمیل</a>
        @else
            <div class="rounded border border-slate bg-slate p-3 text-sm text-slate">تنظیمات این ماژول از صفحات تخصصی قبلی قابل مدیریت است.</div>
        @endif
    </div>

</x-layouts.admin-dashboard>
