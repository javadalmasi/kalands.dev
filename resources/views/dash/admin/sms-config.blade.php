<x-layouts.admin-dashboard title="تنظیمات پیامک">
    @php($authkey = request()->route('authkey'))
    <x-admin.page-header title="تنظیمات پیامک ملی‌پیامک" />
    <form action="{{ route('dash.admin.sms.config.save', ['authkey' => $authkey]) }}" method="POST" class="admin-card grid gap-3">
        @csrf
        <input name="endpoint" value="{{ $settings['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp/{token}' }}" placeholder="API Endpoint" class="admin-input">
        <input name="api_token" value="{{ $settings['api_token'] ?? '' }}" placeholder="API Token" class="admin-input">
        <input name="sender_number" value="{{ $settings['sender_number'] ?? '' }}" placeholder="Sender Number (اختیاری)" class="admin-input">
        <button class="admin-btn"><span class="material-icons">save</span>ذخیره</button>
    </form>
    <form action="{{ route('dash.admin.sms.config.test', ['authkey' => $authkey]) }}" method="POST" class="admin-card mt-4 grid gap-3">
        @csrf
        <input name="to" placeholder="شماره موبایل تست (09...)" class="admin-input">
        <input name="message" value="SMS test from admin panel" placeholder="متن تست" class="admin-input">
        <button class="admin-btn admin-btn-secondary"><span class="material-icons">send</span>ارسال تست</button>
    </form>
</x-layouts.admin-dashboard>
