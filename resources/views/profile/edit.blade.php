<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <div class="mb-8 flex flex-col items-start justify-between gap-y-4">
                <h1 class="text-xl font-black text-slate-800 dark:text-white">ویرایش حساب کاربری</h1>
                <div class="h-1 w-12 rounded-full bg-primary"></div>
            </div>
            <form action="{{ route('dash.user.settings.update', ['authkey' => $authkey]) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 gap-4">
                    <x-form.input
                        name="first_name"
                        label="نام"
                        value="{{ $user->first_name }}"
                    />
                    <x-form.input
                        name="last_name"
                        label="نام خانوادگی"
                        value="{{ $user->last_name }}"
                    />
                    <x-form.input
                        name="phone"
                        label="شماره تلفن"
                        value="{{ $user->phone }}"
                    />
                    <div class="flex items-center gap-2 pr-2 -mt-2 mb-2">
                        @if($user->phone_verified_at)
                            <span class="flex items-center gap-1 text-[10px] font-black text-primary bg-primary/10 px-2 py-0.5 rounded-full"><svg class="w-3 h-3"><use xlink:href="#check"/></svg> تایید شده</span>
                        @else
                            <span class="flex items-center gap-1 text-[10px] font-black text-warning bg-warning/10 px-2 py-0.5 rounded-full"><svg class="w-3 h-3"><use xlink:href="#warning"/></svg> تایید نشده</span>
                        @endif
                    </div>
                    <x-form.input
                        name="email"
                        label="ایمیل"
                        type="email"
                        value="{{ $user->email }}"
                    />
                    <div class="flex items-center gap-2 pr-2 -mt-2 mb-2">
                        @if($user->email_verified_at)
                            <span class="flex items-center gap-1 text-[10px] font-black text-primary bg-primary/10 px-2 py-0.5 rounded-full"><svg class="w-3 h-3"><use xlink:href="#check"/></svg> تایید شده</span>
                        @else
                            <span class="flex items-center gap-1 text-[10px] font-black text-warning bg-warning/10 px-2 py-0.5 rounded-full"><svg class="w-3 h-3"><use xlink:href="#warning"/></svg> تایید نشده</span>
                        @endif
                    </div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                        تم نمایشی
                        <select id="themePreferenceSelect" name="theme_preference" class="mt-1 w-full rounded-squircle border border-slate-200 bg-white px-3 py-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <option value="light" @selected($user->theme_preference === 'light')>روشن</option>
                            <option value="dark" @selected($user->theme_preference === 'dark')>تیره</option>
                        </select>
                    </label>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                        درباره من (بیو)
                        <textarea name="profile_bio" rows="3" class="mt-1 w-full rounded-squircle border border-slate-200 bg-white px-3 py-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">{{ $user->profile_bio }}</textarea>
                    </label>
                </div>
                <div class="flex justify-end">
                    <button class="btn-primary w-full px-4 py-2 mt-2 md:w-auto">
                        ویرایش حساب کاربری
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <div class="mb-8 flex flex-col items-start justify-between gap-y-4">
                <h1 class="text-xl font-black text-slate-800 dark:text-white">تغییر رمز عبور</h1>
                <div class="h-1 w-12 rounded-full bg-primary"></div>
            </div>
            <form action="{{ route('dash.user.settings.password', ['authkey' => $authkey]) }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 gap-4">
                    <x-form.input
                        name="password"
                        label="رمز عبور جدید"
                        type="password"
                    />
                    <x-form.input
                        name="password_confirmation"
                        label="تکرار رمز عبور جدید"
                        type="password"
                    />
                </div>
                <div class="flex justify-end">
                    <button class="btn-primary w-full px-4 py-2 mt-2 md:w-auto">
                        تغییر رمز عبور
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const themeSelect = document.getElementById('themePreferenceSelect');
            if (!themeSelect) {
                return;
            }

            const applyTheme = function (mode) {
                const normalizedMode = mode === 'dark' ? 'dark' : 'light';
                document.documentElement.classList.toggle('dark', normalizedMode === 'dark');
                localStorage.setItem('theme', normalizedMode);
            };

            const currentTheme = localStorage.getItem('theme');
            if (!currentTheme) {
                applyTheme(themeSelect.value);
            } else {
                themeSelect.value = currentTheme === 'dark' ? 'dark' : 'light';
                applyTheme(themeSelect.value);
            }

            themeSelect.addEventListener('change', function () {
                applyTheme(themeSelect.value);
            });
        })();
    </script>

</x-layouts.profile>
