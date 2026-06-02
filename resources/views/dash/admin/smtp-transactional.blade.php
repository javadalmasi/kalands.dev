<x-layouts.admin-dashboard title="SMTP تراکنشی">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">SMTP تراکنشی</h1>
    <form action="{{ route('dash.admin.smtp.transactional.save', ['authkey' => $authkey]) }}" method="POST" class="admin-card grid gap-3 md:grid-cols-2">
        @csrf
        <input name="host" value="{{ $settings['host'] ?? '' }}" placeholder="Host" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <input name="port" value="{{ $settings['port'] ?? '' }}" placeholder="Port" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <input name="username" value="{{ $settings['username'] ?? '' }}" placeholder="Username" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <input name="password" value="{{ $settings['password'] ?? '' }}" placeholder="Password" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <select name="encryption" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <option value="tls" @selected(($settings['encryption'] ?? '') === 'tls')>TLS</option>
            <option value="ssl" @selected(($settings['encryption'] ?? '') === 'ssl')>SSL</option>
        </select>
        <input name="sender_email" value="{{ $settings['sender_email'] ?? '' }}" placeholder="Sender Email" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <input name="sender_name" value="{{ $settings['sender_name'] ?? '' }}" placeholder="Sender Name" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <button class="admin-btn"><span class="material-icons">save</span>ذخیره</button>
    </form>
    <form action="{{ route('dash.admin.smtp.transactional.test', ['authkey' => $authkey]) }}" method="POST" class="admin-card mt-4 grid gap-3 md:grid-cols-2">
        @csrf
        <input name="to" type="email" placeholder="ایمیل تست" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <button class="admin-btn admin-btn-secondary"><span class="material-icons">send</span>ارسال تست</button>
    </form>
</x-layouts.admin-dashboard>
