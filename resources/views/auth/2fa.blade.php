<x-layouts.auth>
    <form
        action="{{ route('auth.2fa.verify') }}"
        method="POST"
        class="flex flex-col items-center gap-4"
    >
        @csrf

        <h1>احراز هویت دو مرحله ای</h1>
        <p class="text-sm text-slate text-center">وارد نرم افزار Authenticator خود شوید و کد دریافتی را وارد کنید</p>
        <label
            for="phone_email"
            class="relative block rounded-squircle border border-slate shadow-base dark:border-white/5"
        >
            <input type="text" id="code" name="code" dir="auto" class="peer w-full rounded-squircle border-none bg-transparent p-4 text-left text-slate placeholder-transparent focus:outline-none focus:ring-0 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="کد تایید" />

            <span
                class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white px-2 py-0.5 text-sm text-slate transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-focus:top-0 peer-focus:text-sm dark:bg-slate dark:text-slate-200"
            >
                  کد تایید
                </span>
        </label>
        @if($errors->any())
            <p class="h-5 text-sm text-warning dark:text-warning">
                {{ $errors->first() }}
            </p>
        @endif

        <button class="btn-primary px-2 py-1">
            تایید
        </button>
    </form>
</x-layouts.auth>
