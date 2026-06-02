<x-layouts.auth title="ورود">
    <h1 class="mb-8 text-center text-2xl font-black text-slate dark:text-white">خوش آمدید</h1>

    <form action="{{ route('auth.login.verify') }}" method="POST" class="space-y-5">
        @csrf

        <div class="space-y-1">
            <label class="text-xs font-bold text-slate pr-2">موبایل یا ایمیل</label>
            <input type="text" name="identifier" dir="auto" class="w-full rounded-squircle border border-slate-100 bg-slate-50 p-4 text-sm font-medium text-slate transition-all focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="example@mail.com" value="{{ old('identifier') }}" />
        </div>

        <div class="space-y-1">
            <label class="text-xs font-bold text-slate pr-2">رمز عبور</label>
            <input type="password" name="password" class="w-full rounded-squircle border border-slate-100 bg-slate-50 p-4 text-sm font-medium text-slate transition-all focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="••••••••" />
        </div>

        <x-security.challenge-options :challenge="$challenge" />

        <label class="flex items-center gap-3 cursor-pointer group">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="rounded border-slate-100 text-primary focus:ring-primary dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" />
            <span class="text-xs font-bold text-slate group-hover:text-slate transition-colors">مرا به خاطر بسپار (۱ روز)</span>
        </label>

        <x-session-errors/>

        <button type="submit" class="btn-primary w-full py-4 shadow-lg shadow-primary/30 text-base">ورود به حساب</button>
    </form>

    <div class="mt-8 flex items-center justify-center gap-6 text-xs font-bold">
        <a href="{{ route('auth.register.step1') }}" class="text-primary hover:underline">ایجاد حساب کاربری</a>
        <span class="h-4 w-px bg-slate-50"></span>
        <a href="{{ route('auth.forget') }}" class="text-slate hover:text-slate transition-colors">فراموشی رمز عبور</a>
    </div>
</x-layouts.auth>
