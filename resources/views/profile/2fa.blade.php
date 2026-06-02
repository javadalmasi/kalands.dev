<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <h1 class="mb-6 text-xl font-black text-slate-800 dark:text-white">احراز هویت دو مرحله‌ای (2FA)</h1>

            @if(!auth()->user()->two_factor_secret)
                <div class="rounded-squircle border border-primary/20 bg-primary/5 p-6 text-center">
                    <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full bg-primary text-white shadow-lg shadow-primary/30">
                        <span class="material-icons !text-3xl !leading-none block">qr_code_2</span>
                    </div>
                    <p class="mb-1 text-sm font-black text-slate-800 dark:text-white">اسکن کد QR</p>
                    <p class="mb-6 text-xs text-slate-500">کد زیر را در اپلیکیشن Google Authenticator اسکن کنید.</p>

                    @if(!empty($qr_code))
                        <div class="inline-block rounded-2xl border-4 border-white bg-white p-4 shadow-xl dark:border-slate-800 dark:bg-slate-800">
                            <img src="{{ $qr_code }}" alt="2FA QR" class="mx-auto h-48 w-48">
                        </div>
                    @else
                        <div class="rounded-squircle border border-warning/20 bg-warning/5 p-4 text-xs font-bold text-warning">
                            تولید کد QR با خطا مواجه شد. از کلید دستی زیر استفاده کنید.
                        </div>
                    @endif

                    <div class="mt-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">کلید دستی (Setup Key)</p>
                        <p class="mt-2 inline-block rounded-lg bg-slate-100 px-4 py-2 font-mono text-sm font-bold text-slate-700 dark:bg-white/5 dark:text-slate-300">{{ $setup_secret ?? '-' }}</p>
                    </div>
                </div>

                <form action="{{ route('dash.user.2fa.enable', ['authkey' => $authkey]) }}" method="POST" class="mt-8 space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 pr-2">کد ۶ رقمی اپلیکیشن</label>
                        <input name="otp" maxlength="6" class="w-full text-center tracking-[1em] font-black rounded-squircle border border-slate-200 bg-white p-4 text-lg text-slate-700 outline-none focus:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10" type="text" placeholder="000000">
                    </div>
                    <button class="btn-primary w-full py-4 text-sm font-black shadow-lg shadow-primary/30">فعال‌سازی نهایی احراز هویت</button>
                </form>
            @else
                <div class="rounded-squircle border border-success/20 bg-success/5 p-8 text-center">
                    <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full bg-success text-white shadow-lg shadow-success/30">
                        <span class="material-icons !text-3xl !leading-none block">verified_user</span>
                    </div>
                    <p class="text-lg font-black text-success">احراز هویت دو مرحله‌ای فعال است</p>
                    <p class="mt-2 text-sm text-slate-500">حساب کاربری شما در حال حاضر با بالاترین سطح امنیت محافظت می‌شود.</p>
                </div>

                @if(auth()->user()->two_factor_recovery_codes)
                    <div class="mt-8 rounded-squircle border border-slate-100 bg-slate-50/50 p-6 dark:border-white/5 dark:bg-white/5">
                        <p class="mb-4 text-xs font-black text-slate-700 dark:text-white flex items-center gap-2">
                            <span class="material-icons !text-sm text-primary">key</span>
                            کدهای بازیابی (Recovery Codes)
                        </p>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                            @foreach(auth()->user()->two_factor_recovery_codes as $code)
                                <span class="rounded bg-white p-2 text-center font-mono text-xs font-bold shadow-sm dark:bg-slate-800 dark:text-slate-300 border border-slate-100 dark:border-white/5">{{ $code }}</span>
                            @endforeach
                        </div>
                        <p class="mt-4 text-[10px] text-warning font-bold">⚠️ این کدها را در جای امن نگهداری کنید. در صورت گم شدن گوشی، تنها راه بازیابی حساب شما این کدها هستند.</p>
                    </div>
                @endif

                <form action="{{ route('dash.user.2fa.disable', ['authkey' => $authkey]) }}" method="POST" class="mt-8">
                    @csrf
                    <button class="flex w-full items-center justify-center gap-2 rounded-squircle bg-warning/10 px-4 py-4 text-sm font-black text-warning transition-all hover:bg-warning hover:text-white active:scale-95">
                        <span class="material-icons !text-base">no_encryption</span>
                        غیرفعال‌سازی احراز هویت دو مرحله‌ای
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.profile>
