<x-layouts.auth title="ثبت‌نام - مرحله دوم" back="auth.register.step1">
    <h1 class="mb-6 text-center text-xl font-medium text-slate dark:text-white">ثبت‌نام - مرحله دوم</h1>

    <div class="mb-4 rounded-squircle border border-slate-100 dark:border-white/5 p-3 text-sm text-slate dark:border-white/5 dark:text-white/80">
        <p>نام: {{ $stepOne['first_name'] ?? '-' }} {{ $stepOne['last_name'] ?? '' }}</p>
        <p>شناسه: {{ $stepOne['identifier'] ?? '-' }}</p>
    </div>

    <form action="{{ route('auth.register.step2.store') }}" method="POST" class="space-y-4">
        @csrf

        <label class="block text-sm text-slate dark:text-white/80">
            تم پیش‌فرض
            <select name="theme_preference" class="mt-1 w-full rounded-squircle border border-slate-100 bg-transparent px-3 py-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="light" @selected(old('theme_preference', $stepTwo['theme_preference'] ?? 'light') === 'light')>روشن</option>
                <option value="dark" @selected(old('theme_preference', $stepTwo['theme_preference'] ?? '') === 'dark')>تیره</option>
            </select>
        </label>

        <label class="block text-sm text-slate dark:text-white/80">
            توضیح کوتاه پروفایل
            <textarea
                name="profile_bio"
                rows="3"
                class="mt-1 w-full rounded-squircle border border-slate-100 bg-transparent px-3 py-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700"
            >{{ old('profile_bio', $stepTwo['profile_bio'] ?? '') }}</textarea>
        </label>

        <label class="flex items-center gap-2 text-sm text-slate dark:text-white/80">
            <input type="checkbox" name="marketing_opt_in" value="1" @checked(old('marketing_opt_in', $stepTwo['marketing_opt_in'] ?? false)) />
            دریافت اطلاع‌رسانی‌ها
        </label>

        <x-security.challenge-options :challenge="$challenge" />

        <x-session-errors/>

        <div class="grid grid-cols-2 gap-2">
            <button
                type="submit"
                formaction="{{ route('auth.register.back') }}"
                class="rounded-squircle border border-slate-100 dark:border-white/5 px-4 py-2 text-sm text-slate dark:border-white/20 dark:text-white"
            >
                بازگشت
            </button>
            <button type="submit" class="btn-primary py-2">ایجاد حساب</button>
        </div>
    </form>
</x-layouts.auth>
