@php
    $adminGuard = auth()->guard('admin');
    $webGuard = auth()->guard('web');
    $isAdmin = $adminGuard->check();
    $isUser = $webGuard->check();
    $isLoggedIn = $isAdmin || $isUser;
    $currentUser = $isAdmin ? $adminGuard->user() : ($isUser ? $webGuard->user() : null);
    $currentGuard = $isAdmin ? 'admin' : ($isUser ? 'web' : null);
    $adminDashKey = session('dashboard.admin.authkey') ?? ($isAdmin ? $currentUser?->dashboard_authkey : null);
    $adminDashAvailable = $isAdmin && !empty($adminDashKey);
    $userDashKey = session('dashboard.user.authkey') ?? ($isUser ? $currentUser?->dashboard_authkey : null);
    $userDashAvailable = $isUser && !empty($userDashKey);
    $megaMenuConfig = app(\App\Repositories\SettingsRepository::class)->get('megamenu.config', []);
    $visitorPattern = app(\App\Services\VisitorIntelligenceService::class)->getConfig()['robots_pattern'] ?? '';
@endphp
<script>
    window.megaMenuConfig = @json($megaMenuConfig);
    window.visitorPattern = @json($visitorPattern);
</script>
<header class="fixed left-0 right-0 top-0 z-40" id="main-header">
    <div class="border-b border-slate-100 bg-white dark:border-white/10 dark:bg-slate-900">
        <!-- Header Desktop -->
        <div class="hidden md:block">
            <div class="container relative z-30 flex max-w-[1680px] items-center justify-between gap-x-8 py-4">
                <!-- logo -->
                <div class="shrink-0 flex items-center gap-3">
                    <a href="{{route('index')}}" class="block">
                        <picture>
                            <source type="image/avif" srcset="{{ asset('/assets/images/logo.avif') }}">
                            <source type="image/png" srcset="{{ asset('/assets/images/logo.png') }}">
                            <source type="image/webp" srcset="{{ asset('/assets/images/logo.webp') }}">
                            <img width="160" height="40" loading="lazy" decoding="async" alt="کالندز"
                                 class="h-9 w-auto" src="{{ asset('/assets/images/logo.svg') }}">
                        </picture>
                    </a>
                    <button id="desktopMegamenuWrapper" type="button" class="hidden items-center gap-2 rounded-squircle border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-primary hover:text-primary dark:border-white/10 dark:text-slate-200 lg:flex">
                        <svg class="h-5 w-5"><use xlink:href="#menu"/></svg>
                        <span>دسته‌بندی‌ها</span>
                    </button>
                </div>

                <!-- Search -->
                <div class="relative flex-1 max-w-2xl" id="desktopHeaderSearchBase">
                    <form action="/result/" method="get">
                        <div id="desktopHeaderSearchWrapper"
                             class="group flex items-center gap-3 rounded-squircle bg-slate-50 px-4 py-2.5 transition-all focus-within:bg-white focus-within:ring-4 focus-within:ring-primary/10 dark:bg-white/5 dark:focus-within:bg-slate-800">
                            <svg class="h-5 w-5 text-slate-400 transition-colors group-focus-within:text-primary">
                                <use xlink:href="#search"/>
                            </svg>
                            <input id="desktopHeaderSearch" class="w-full border-none bg-transparent p-0 text-sm font-medium text-slate-700 outline-none placeholder:text-slate-400 focus:ring-0 dark:bg-transparent dark:text-white dark:border-white/10" placeholder="جستجو در کالندز..." type="text" name="q" autocomplete="off" />
                            <span class="hidden lg:block text-[10px] px-1.5 py-0.5 rounded bg-slate-200 dark:bg-white/10 text-slate-500 dark:text-slate-400 font-bold whitespace-nowrap uppercase">Ctrl + K</span>
                        </div>
                    </form>
                    <div id="desktopHeaderSearchResult"
                         class="absolute inset-x-0 top-full -mt-2 hidden overflow-hidden rounded-b-squircle border border-t-0 border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900 z-50">
                        <div class="max-h-[450px] overflow-y-auto py-3 pt-5">
                            <div id="desktopAutocompleteResults" class="space-y-1 px-3"></div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-x-4">
                    <div class="flex items-center gap-1 p-1 rounded-full bg-slate-50 dark:bg-white/5">
                        <div class="cursor-pointer p-2 rounded-full text-slate-500 hover:bg-white hover:text-primary transition-all dark:hover:bg-slate-800" id="toggleThemeDesktop">
                            <svg class="h-5 w-5" id="themeIconDesktop"><use xlink:href="#moon"/></svg>
                        </div>
                    </div>
                    
                    <div class="h-8 w-px bg-slate-200 dark:bg-white/10"></div>

                    @if($isLoggedIn)
                        <div class="relative">
                            <button class="flex items-center gap-2 group" data-dropdown-toggle="dropdownAccountDesktop">
                                <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm dark:border-slate-800">
                                    <img src="{{ asset('assets/images/user.png') }}" class="h-full w-full object-cover" alt="user">
                                </div>
                                <div class="hidden xl:block text-right">
                                    <p class="text-xs font-bold text-slate-700 dark:text-white">{{ $currentUser->name }}</p>
                                    <p class="text-[10px] text-slate-400 uppercase tracking-tighter">@if($isAdmin)پنل مدیریت@elseحساب کاربری@endif</p>
                                </div>
                            </button>
                            <div class="z-10 !ml-5 hidden w-64 overflow-hidden rounded-squircle border border-slate-100 bg-white shadow-2xl dark:border-white/5 dark:bg-slate-900" id="dropdownAccountDesktop">
                                <div class="p-4 bg-slate-50 dark:bg-white/5 flex items-center gap-3">
                                    <img src="{{ asset('assets/images/user.png') }}" class="h-12 w-12 rounded-full" alt="user">
                                    <div>
                                        <p class="text-sm font-black text-slate-800 dark:text-white">{{ $currentUser->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $currentUser->email }}</p>
                                    </div>
                                </div>
                                <ul class="p-2 space-y-1">
                                    @if($isAdmin)
                                        <li>
                                            <a class="flex items-center gap-3 rounded-squircle px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-primary/5 hover:text-primary dark:text-slate-300 transition-all"
                                               href="{{ $adminDashAvailable ? route('dash.admin.index', ['authkey' => $adminDashKey]) : route('index') }}">
                                                <svg class="h-5 w-5"><use xlink:href="#user"/></svg>
                                                <span>پنل مدیریت</span>
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a class="flex items-center gap-3 rounded-squircle px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-primary/5 hover:text-primary dark:text-slate-300 transition-all"
                                               href="{{ $userDashAvailable ? route('dash.user.index', ['authkey' => $userDashKey]) : route('index') }}">
                                                <svg class="h-5 w-5"><use xlink:href="#user"/></svg>
                                                <span>پنل کاربری</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="flex items-center gap-3 rounded-squircle px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-primary/5 hover:text-primary dark:text-slate-300 transition-all"
                                               href="{{ $userDashAvailable ? route('dash.user.likes', ['authkey' => $userDashKey]) : route('index') }}">
                                                <svg class="h-5 w-5"><use xlink:href="#heart"/></svg>
                                                <span>علاقه‌مندی‌ها</span>
                                            </a>
                                        </li>
                                    @endif
                                    <li class="pt-2 mt-2 border-t border-slate-100 dark:border-white/5">
                                        <form action="{{ route('auth.logout') }}" method="POST">
                                            @csrf
                                            <button class="flex w-full items-center gap-3 rounded-squircle px-3 py-2.5 text-sm font-bold text-warning hover:bg-warning/10 transition-all">
                                                <svg class="h-5 w-5"><use xlink:href="#exit"/></svg>
                                                <span>خروج از حساب</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('auth.login') }}" class="btn-primary px-6 py-2.5 text-sm shadow-lg shadow-primary/20">
                            ورود | ثبت‌نام
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Mobile Header -->
        <div class="md:hidden">
            <div class="container flex h-16 items-center justify-between">
                <button onclick="openNav()" class="p-2 text-slate-600 dark:text-white">
                    <svg class="h-6 w-6"><use xlink:href="#menu"/></svg>
                </button>
                <a href="{{route('index')}}">
                    <img src="{{ asset('/assets/images/logo.svg') }}" class="h-7 w-auto" alt="logo">
                </a>
                @if($isLoggedIn)
                    <a href="{{ $isAdmin && $adminDashAvailable ? route('dash.admin.index', ['authkey' => $adminDashKey]) : ($userDashAvailable ? route('dash.user.index', ['authkey' => $userDashKey]) : route('index')) }}" class="p-2 text-slate-600 dark:text-white">
                        <svg class="h-6 w-6"><use xlink:href="#user"/></svg>
                    </a>
                @else
                    <a href="{{route('auth.login')}}" class="p-2 text-slate-600 dark:text-white">
                        <svg class="h-6 w-6"><use xlink:href="#user"/></svg>
                    </a>
                @endif
            </div>
            <div class="container pb-4">
                <div class="relative" id="mobileHeaderSearchBase">
                    <form action="/result/" method="get">
                        <div id="mobileHeaderSearchWrapper" class="flex items-center gap-3 rounded-squircle bg-slate-50 px-4 py-2.5 dark:bg-white/5 transition-all">
                            <svg class="h-5 w-5 text-slate-400"><use xlink:href="#search"/></svg>
                            <input id="mobileHeaderSearch" class="w-full border-none bg-transparent p-0 text-sm font-medium text-slate-700 outline-none dark:bg-transparent dark:text-white dark:border-white/10" placeholder="جستجو در کالندز..." type="text" name="q" autocomplete="off" />
                        </div>
                    </form>
                    <div id="mobileHeaderSearchResult"
                         class="absolute inset-x-0 top-full -mt-2 hidden overflow-hidden rounded-b-squircle border border-t-0 border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900 z-50">
                        <div class="max-h-[400px] overflow-y-auto py-3 pt-5">
                            <div id="mobileAutocompleteResults" class="space-y-1 px-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<div id="header-overlay" class="fixed inset-0 z-30 hidden bg-black/35"></div>
<div id="desktopMegamenu" class="fixed inset-x-0 top-[65px] pt-[15px] z-40 hidden">
    <div class="container max-w-[1400px]">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900 max-h-[calc(100vh-120px)] overflow-hidden flex flex-col">
            <div class="grid gap-8 lg:grid-cols-[260px_1fr] overflow-hidden h-full">
                <ul id="mega-menu-parents" class="h-full space-y-1 overflow-y-auto custom-scrollbar pe-4 border-e border-slate-100 dark:border-white/5">
                    <li class="rounded-squircle px-3 py-2 text-sm text-slate-500 dark:text-slate-300">در حال بارگذاری منو...</li>
                </ul>
                <div id="mega-menu-childs" class="h-full overflow-y-auto custom-scrollbar ps-4">
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                        <div class="h-12 animate-pulse rounded-squircle bg-slate-100 dark:bg-white/10"></div>
                        <div class="h-12 animate-pulse rounded-squircle bg-slate-100 dark:bg-white/10"></div>
                        <div class="h-12 animate-pulse rounded-squircle bg-slate-100 dark:bg-white/10"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="overlay" onclick="closeNav()" class="fixed inset-0 z-50 hidden bg-black/40"></div>
<button id="closeBtn" onclick="closeNav()" class="fixed left-4 top-4 z-[60] hidden rounded-full bg-white/90 p-2 text-slate-800 shadow-xl dark:bg-slate-800 dark:text-white">
    <svg class="h-6 w-6"><use xlink:href="#close"/></svg>
</button>
<aside id="mySidenav" class="fixed right-0 top-0 z-[55] h-screen w-[380px] max-w-[92vw] overflow-hidden border-l border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-slate-900">
    <div id="main-container" class="h-full overflow-y-auto p-4">
        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-4 dark:border-white/10">
            <img src="{{ asset('/assets/images/logo.svg') }}" class="h-8 w-auto" alt="logo">
            <button type="button" onclick="closeNav()" class="rounded-full p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-white/10">
                <svg class="h-5 w-5"><use xlink:href="#close"/></svg>
            </button>
        </div>
        <div id="mobile-menu-actions" class="mb-6 flex items-center justify-between rounded-squircle bg-slate-50 p-3 dark:bg-white/5">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-white shadow-sm dark:border-slate-800">
                    <img src="{{ asset('assets/images/user.png') }}" class="h-full w-full object-cover" alt="user">
                </div>
                <div>
                    @if($isLoggedIn)
                        <p class="text-xs font-bold text-slate-700 dark:text-white">{{ $currentUser->name }}</p>
                        <p class="text-[10px] text-slate-400">@if($isAdmin)پنل مدیریت@elseخوش آمدید@endif</p>
                    @else
                        <p class="text-xs font-bold text-slate-700 dark:text-white">کاربر مهمان</p>
                        <p class="text-[10px] text-slate-400">وارد حساب خود شوید</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="cursor-pointer p-2 rounded-full text-slate-500 hover:bg-white hover:text-primary transition-all dark:hover:bg-slate-800" id="toggleThemeMobile">
                    <svg class="h-5 w-5" id="themeIconMobile"><use xlink:href="#moon"/></svg>
                </div>
            </div>
        </div>
        <ul id="mobile-menu-list" class="space-y-1">
            <!-- Dynamically populated from JS -->
        </ul>
    </div>
    <div id="sub-container" class="absolute inset-0 bg-white p-4 dark:bg-slate-900">
        <button id="mainMenu" type="button" class="mb-3 flex items-center gap-2 rounded-squircle bg-slate-100/50 px-3 py-2 text-sm font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
            <svg class="h-5 w-5"><use xlink:href="#chevron-right"/></svg>
            بازگشت به منوی اصلی
        </button>
        <ul id="sub-container-content" class="h-[calc(100%-2.5rem)] space-y-2 overflow-y-auto custom-scrollbar"></ul>
    </div>
</aside>
