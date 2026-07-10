<x-layouts.admin-dashboard title="SMTP عمومی">
    @php($authkey = request()->route('authkey'))
    <x-admin.page-header title="SMTP عمومی" />
    <form action="{{ route('dash.admin.smtp.general.save', ['authkey' => $authkey]) }}" method="POST" class="admin-card grid gap-3 md:grid-cols-2">
        @csrf
        <div class="md:col-span-2">
            <select name="mailer" class="mailer-select admin-input" data-target="general-smtp-fields">
                <option value="smtp" @selected(($settings['mailer'] ?? 'smtp') === 'smtp')>SMTP</option>
                <option value="sendmail" @selected(($settings['mailer'] ?? '') === 'sendmail')>PHP Mail (Sendmail)</option>
            </select>
        </div>
        <div class="smtp-fields md:col-span-2 grid gap-3 md:grid-cols-2" id="general-smtp-fields">
            <input name="host" value="{{ $settings['host'] ?? '' }}" placeholder="Host" class="admin-input">
            <input name="port" value="{{ $settings['port'] ?? '' }}" placeholder="Port" class="admin-input">
            <input name="username" value="{{ $settings['username'] ?? '' }}" placeholder="Username" class="admin-input">
            <input name="password" value="{{ $settings['password'] ?? '' }}" placeholder="Password" class="admin-input">
            <select name="encryption" class="admin-input">
                <option value="" @selected(!($settings['encryption'] ?? null))>بدون رمزنگاری</option>
                <option value="tls" @selected(($settings['encryption'] ?? '') === 'tls')>STARTTLS</option>
                <option value="ssl" @selected(($settings['encryption'] ?? '') === 'ssl')>SSL/TLS</option>
            </select>
            <input name="sender_email" value="{{ $settings['sender_email'] ?? '' }}" placeholder="Sender Email" class="admin-input">
            <input name="sender_name" value="{{ $settings['sender_name'] ?? '' }}" placeholder="Sender Name" class="admin-input">
        </div>
        <button class="admin-btn md:col-span-2"><span class="material-icons">save</span>ذخیره</button>
    </form>
    <form action="{{ route('dash.admin.smtp.general.test', ['authkey' => $authkey]) }}" method="POST" class="admin-card mt-4 grid gap-3 md:grid-cols-2">
        @csrf
        <input name="to" type="email" placeholder="ایمیل تست" class="admin-input">
        <button class="admin-btn admin-btn-secondary"><span class="material-icons">send</span>ارسال تست</button>
    </form>
</x-layouts.admin-dashboard>
