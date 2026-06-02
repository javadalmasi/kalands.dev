<x-layouts.auth title="بازیابی رمز عبور" back="auth.login">
    <h1 class="mb-6 text-center text-xl font-medium text-slate dark:text-white">بازیابی رمز عبور</h1>

    <p class="mb-4 text-sm text-slate dark:text-slate-200">
        ایمیل یا شماره موبایل را وارد کنید. کد ۶ رقمی برای شما ارسال می‌شود.
    </p>

    <form action="{{ route('auth.forget.send') }}" method="POST" class="space-y-4">
        @csrf

        <label class="relative block rounded-squircle border border-slate shadow-base dark:border-white/5">
            <input type="text" name="identifier" dir="auto" class="peer w-full rounded-squircle border-none bg-transparent p-4 text-left text-slate placeholder-transparent focus:outline-none focus:ring-0 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="موبایل یا ایمیل" value="{{ old('identifier') }}" />
            <span class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white px-2 py-0.5 text-sm text-slate transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-focus:top-0 peer-focus:text-sm dark:bg-slate dark:text-slate-200">
                موبایل یا ایمیل
            </span>
        </label>

        <x-security.challenge-options :challenge="$challenge" />

        <x-session-errors/>
        <x-session-messages/>

        <button type="submit" class="btn-primary w-full py-3">ارسال کد ۶ رقمی</button>
    </form>
</x-layouts.auth>
