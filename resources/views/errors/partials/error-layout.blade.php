@php
    $configPath = public_path('assets/error-pages/config.json');
    $config = [];
    if (file_exists($configPath)) {
        $config = json_decode(file_get_contents($configPath), true);
    }

    $errorLinks = $config['links'] ?? app(\App\Repositories\SettingsRepository::class)->get('error_pages.links', [
        ['title' => 'گوشی موبایل', 'url' => '/result/mobile-phone', 'icon' => 'smartphone'],
        ['title' => 'لپ‌تاپ', 'url' => '/result/laptop', 'icon' => 'laptop'],
        ['title' => 'تبلت', 'url' => '/result/tablet', 'icon' => 'tablet_mac'],
        ['title' => 'تماس با ما', 'url' => '/contact', 'icon' => 'support_agent'],
    ]);

    $errorSettings = $config['settings'] ?? app(\App\Repositories\SettingsRepository::class)->get('error_pages.settings', [
        'icons_per_row' => 4,
    ]);

    $gridCols = match ((int)($errorSettings['icons_per_row'] ?? 4)) {
        4 => 'sm:grid-cols-4',
        5 => 'sm:grid-cols-5',
        6 => 'sm:grid-cols-6',
        default => 'sm:grid-cols-4',
    };

    $palette = match ((string) ($code ?? '500')) {
        '404' => [
            'color' => 'amber',
            'ping_bg' => 'bg-amber-500/10',
            'icon' => 'search_off',
            'bg_text' => 'text-amber-500/5 dark:text-amber-400/5',
            'title' => 'text-amber-600 dark:text-amber-400',
            'btn' => 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20',
        ],
        '401', '403', '419', '429' => [
            'color' => 'orange',
            'ping_bg' => 'bg-orange-500/10',
            'icon' => match((string)$code) {
                '401' => 'lock_person',
                '403' => 'gpp_maybe',
                '419' => 'update_disabled',
                '429' => 'speed',
                default => 'warning_amber',
            },
            'bg_text' => 'text-orange-500/5 dark:text-orange-400/5',
            'title' => 'text-orange-600 dark:text-orange-400',
            'btn' => 'bg-orange-600 hover:bg-orange-700 shadow-orange-600/20',
        ],
        '503' => [
            'color' => 'blue',
            'ping_bg' => 'bg-blue-500/10',
            'icon' => 'construction',
            'bg_text' => 'text-blue-500/5 dark:text-blue-400/5',
            'title' => 'text-blue-600 dark:text-blue-400',
            'btn' => 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20',
        ],
        default => [
            'color' => 'blue',
            'ping_bg' => 'bg-blue-500/10',
            'icon' => 'error_outline',
            'bg_text' => 'text-blue-500/5 dark:text-blue-400/5',
            'title' => 'text-blue-600 dark:text-blue-400',
            'btn' => 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20',
        ],
    };
@endphp

<div class="relative flex min-h-[70vh] flex-col items-center justify-center overflow-hidden py-12">
    <!-- Huge background status code -->
    <div class="absolute inset-0 -z-10 flex items-center justify-center pointer-events-none select-none">
        <span class="text-[25vw] font-black leading-none {{ $palette['bg_text'] }}">
            {{ $code ?? 'Error' }}
        </span>
    </div>

    <div class="container relative z-10">
        <div class="mx-auto max-w-3xl text-center">
            <!-- Icon with pulsed background -->
            <div class="relative mx-auto mb-8 flex h-24 w-24 items-center justify-center">
                <div class="absolute inset-0 animate-ping rounded-full {{ $palette['ping_bg'] }}"></div>
                <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-xl dark:bg-slate-900 dark:border dark:border-white/5">
                    <span class="material-icons text-5xl {{ $palette['title'] }} !leading-none">{{ $palette['icon'] }}</span>
                </div>
            </div>

            <!-- Content -->
            <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500 dark:bg-white/5">
                <span class="material-icons !text-sm">code</span>
                <span>کد خطا: {{ $code ?? 'Unknown' }}</span>
            </div>
            <h1 class="text-4xl font-black tracking-tight sm:text-5xl {{ $palette['title'] }}">
                {{ $headline ?? 'خطایی رخ داد' }}
            </h1>

            <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">
                {{ $message ?? 'در پردازش درخواست شما مشکلی پیش آمد.' }}
            </p>

            @if((string)($code ?? '') === '404')
                <div class="mt-10 mx-auto max-w-md">
                    <form action="/result/" method="get" class="relative group">
                        <div class="flex items-center gap-3 rounded-2xl bg-white p-2 pr-5 shadow-lg ring-1 ring-slate-200 focus-within:ring-2 focus-within:ring-amber-500/50 dark:bg-slate-900 dark:ring-white/10 dark:focus-within:ring-amber-400/50 transition-all">
                            <span class="material-icons text-slate-400 group-focus-within:text-amber-500">search</span>
                            <input class="w-full border-none bg-transparent p-0 text-sm font-medium text-slate-700 outline-none placeholder:text-slate-400 focus:ring-0 dark:text-white"
                                   placeholder="جستجو در کالندز..."
                                   type="text" name="q" autocomplete="off" />
                            <button type="submit" class="btn-primary py-2 px-6 rounded-xl">بگرد</button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Actions -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a class="flex items-center gap-2 rounded-2xl {{ $palette['btn'] }} px-8 py-3.5 text-sm font-bold text-white transition-all hover:scale-105 active:scale-95" href="{{ route('index') }}">
                    <span class="material-icons text-xl">home</span>
                    بازگشت به صفحه اصلی
                </a>

                @if((string)($code ?? '') === '401')
                    <a class="flex items-center gap-2 rounded-2xl bg-slate-100 px-8 py-3.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" href="{{ route('auth.login') }}">
                        <span class="material-icons text-xl">login</span>
                        ورود به حساب کاربری
                    </a>
                @else
                    <a class="flex items-center gap-2 rounded-2xl bg-slate-100 px-8 py-3.5 text-sm font-bold text-slate-700 transition-all hover:bg-slate-200 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10" href="javascript:history.back()">
                        <span class="material-icons text-xl">arrow_forward</span>
                        بازگشت به صفحه قبل
                    </a>
                @endif
            </div>

            <!-- Helpful links -->
            <div class="mt-16 grid grid-cols-2 gap-4 border-t border-slate-100 pt-8 dark:border-white/5 {{ $gridCols }}">
                @foreach($errorLinks as $link)
                    <a href="{{ $link['url'] }}" class="group flex flex-col items-center gap-2">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 transition-all group-hover:bg-primary group-hover:shadow-lg group-hover:shadow-primary/20 dark:bg-white/5">
                            <span class="material-symbols-outlined text-3xl text-slate-400 group-hover:text-white transition-colors !leading-none">{{ $link['icon'] }}</span>
                        </div>
                        <span class="text-xs font-bold text-slate-500 group-hover:text-slate-800 dark:group-hover:text-white transition-colors">{{ $link['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/error-pages.js'])
