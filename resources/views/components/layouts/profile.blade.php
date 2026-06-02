@php
    $authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey;
    $menuItems = [
        ['route' => 'dash.user.index', 'label' => 'داشبورد'],
        ['route' => 'dash.user.settings', 'label' => 'تنظیمات'],
        ['route' => 'dash.user.bookmarks', 'label' => 'بوکمارک‌ها'],
        ['route' => 'dash.user.likes', 'label' => 'پسندها'],
        ['route' => 'dash.user.comments', 'label' => 'نظرات'],
        ['route' => 'dash.user.tickets.index', 'label' => 'تیکت‌ها'],
        ['route' => 'dash.user.2fa.show', 'label' => 'احراز دو مرحله‌ای'],
    ];
@endphp
<x-layouts.app title="{{ $title ?? 'داشبورد کاربر' }}">
    <main class="flex-grow bg-slate pb-14 pt-28 md:pt-36 dark:bg-black">
        <div class="container">
            <x-session-messages/>
            <x-validation-errors/>

            <div class="mb-4 flex items-center justify-between rounded-squircle border border-slate-200 bg-white p-3 shadow-sm dark:border-white/10 dark:bg-slate-900 lg:hidden">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/images/user.png') }}" class="h-10 w-10 rounded-full" alt="">
                    <div class="min-w-0">
                        <div class="line-clamp-1 text-sm font-bold text-slate-700 dark:text-white">{{ auth()->user()->name }}</div>
                        <div class="line-clamp-1 text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->phone ?? auth()->user()->email }}</div>
                    </div>
                </div>
                <button id="profile-mobile-menu-open" type="button" class="rounded-squircle border border-slate-200 p-2 text-slate-500 dark:border-white/10 dark:text-slate-400" aria-label="menu">
                    <svg class="h-6 w-6">
                        <use xlink:href="#menu"></use>
                    </svg>
                </button>
            </div>

            <div id="profile-mobile-overlay" class="fixed inset-0 z-40 hidden bg-black/40 lg:hidden"></div>
            <aside id="profile-mobile-drawer" class="fixed inset-y-0 right-0 z-50 w-72 translate-x-full overflow-y-auto border-l border-slate-200 bg-white p-4 shadow-2xl transition-transform duration-300 dark:border-white/10 dark:bg-slate-900 lg:hidden">
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-4 dark:border-white/5">
                    <div>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-500">{{ auth()->user()->phone ?? auth()->user()->email }}</p>
                    </div>
                    <button id="profile-mobile-menu-close" type="button" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-white/5" aria-label="close menu">
                        <svg class="h-5 w-5">
                            <use xlink:href="#close"></use>
                        </svg>
                    </button>
                </div>
                <ul class="space-y-1">
                    @foreach($menuItems as $item)
                        <x-profile.sidebar-item href="{{ route($item['route'], ['authkey' => $authkey]) }}" label="{{ $item['label'] }}" :active="request()->routeIs($item['route'])"/>
                    @endforeach
                </ul>
                <form action="{{ route('auth.logout') }}" method="POST" class="mt-4">
                    @csrf
                    <button class="flex w-full items-center justify-center gap-2 rounded-squircle bg-warning px-3 py-3 text-sm font-bold text-white shadow-lg shadow-warning/20 transition-all active:scale-95">
                        <svg class="h-5 w-5"><use xlink:href="#exit"/></svg>
                        <span>خروج از حساب</span>
                    </button>
                </form>
            </aside>

            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 lg:col-span-3">
                    <div class="sticky top-32 hidden w-full overflow-hidden rounded-squircle border border-slate-100 bg-white shadow-sm dark:border-white/5 dark:bg-slate-900 lg:block">
                        <div class="p-6">
                            <div class="mb-6 flex items-center gap-4 border-b border-slate-100 pb-6 dark:border-white/5">
                                <img src="{{ asset('assets/images/user.png') }}" class="h-14 w-14 rounded-full border-2 border-primary/20 p-0.5" alt="">
                                <div class="min-w-0">
                                    <div class="line-clamp-1 text-base font-black text-slate-800 dark:text-white">{{ auth()->user()->name }}</div>
                                    <div class="line-clamp-1 text-xs text-slate-500">{{ auth()->user()->phone ?? auth()->user()->email }}</div>
                                </div>
                            </div>
                            <ul class="space-y-1">
                                @foreach($menuItems as $item)
                                    <x-profile.sidebar-item href="{{ route($item['route'], ['authkey' => $authkey]) }}" label="{{ $item['label'] }}" :active="request()->routeIs($item['route'])"/>
                                @endforeach
                            </ul>
                            <form action="{{ route('auth.logout') }}" method="POST" class="mt-6 pt-6 border-t border-slate-100 dark:border-white/5">
                                @csrf
                                <button class="flex w-full items-center justify-center gap-2 rounded-squircle bg-warning/10 px-3 py-3 text-sm font-bold text-warning transition-all hover:bg-warning hover:text-white active:scale-95">
                                    <svg class="h-5 w-5"><use xlink:href="#exit"/></svg>
                                    <span>خروج از حساب</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>
    </main>

    <script>
        (function () {
            const drawer = document.getElementById('profile-mobile-drawer');
            const overlay = document.getElementById('profile-mobile-overlay');
            const openButton = document.getElementById('profile-mobile-menu-open');
            const closeButton = document.getElementById('profile-mobile-menu-close');

            if (!drawer || !overlay || !openButton || !closeButton) {
                return;
            }

            const openDrawer = function () {
                drawer.classList.remove('translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const closeDrawer = function () {
                drawer.classList.add('translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            openButton.addEventListener('click', openDrawer);
            closeButton.addEventListener('click', closeDrawer);
            overlay.addEventListener('click', closeDrawer);

            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) {
                    closeDrawer();
                }
            });
        })();
    </script>
</x-layouts.app>
