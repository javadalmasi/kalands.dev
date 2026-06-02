@props(['title' => 'Admin Dashboard'])

@php
    $authkey = request()->route('authkey') ?? auth('admin')->user()->dashboard_authkey;
    $admin = auth('admin')->user()?->load('roles.permissions');
    $navGroups = [
        [
            'label' => 'داشبورد',
            'icon' => 'dashboard',
            'route' => 'dash.admin.index',
            'active' => 'dash.admin.index',
        ],
        [
            'label' => 'آنالیزور',
            'icon' => 'analytics',
            'route' => 'dash.admin.modules.show',
            'params' => ['moduleKey' => 'analytics'],
            'active' => 'dash.admin.modules.show',
            'permission' => 'analytics.view',
        ],
        [
            'label' => 'کاربران',
            'icon' => 'group',
            'children' => [
                ['label' => 'افزودن کاربر', 'route' => 'dash.admin.users.create', 'active' => 'dash.admin.users.create', 'permission' => 'users.create'],
                ['label' => 'مدیریت کاربران', 'route' => 'dash.admin.users', 'active' => 'dash.admin.users', 'permission' => 'users.view'],
                ['label' => 'مدیریت ادمین‌ها', 'route' => 'dash.admin.admins', 'active' => 'dash.admin.admins*', 'permission' => 'admins.view'],
                ['label' => 'مدیریت دسترسی‌ها', 'route' => 'dash.admin.roles', 'active' => 'dash.admin.roles*', 'permission' => 'roles.view'],
            ],
        ],
        [
            'label' => 'محصولات',
            'icon' => 'inventory_2',
            'children' => [
                ['label' => 'مدیریت محصولات', 'route' => 'dash.admin.products', 'active' => 'dash.admin.products', 'permission' => 'products.view'],
                ['label' => 'بررسی محصولات غیرفعال', 'route' => 'dash.admin.products.checker', 'active' => 'dash.admin.products.checker*', 'permission' => 'products.check'],
            ],
        ],
        [
            'label' => 'ماژول‌ها',
            'icon' => 'settings',
            'route' => 'dash.admin.modules',
            'active' => 'dash.admin.modules',
        ],
    ];

    // Filter navGroups based on permissions
    $navGroups = collect($navGroups)->filter(function($group) use ($admin) {
        if (isset($group['permission']) && !$admin->hasPermission($group['permission'])) {
            return false;
        }
        if (isset($group['children'])) {
            $group['children'] = collect($group['children'])->filter(function($child) use ($admin) {
                return !isset($child['permission']) || $admin->hasPermission($child['permission']);
            })->values()->toArray();

            return count($group['children']) > 0;
        }
        return true;
    })->values()->toArray();
@endphp

<x-layouts.app :title="$title" :clean="true" entryPoint="resources/js/admin-app.js">
    @vite(['resources/js/admin-file-manager.js', 'resources/js/admin-rich-editor-global.js', 'resources/js/admin-search.js'])

    <div id="admin-shell" class="admin-shell">
        <aside id="admin-sidebar" class="admin-sidebar fixed inset-y-0 right-0 z-40 translate-x-full flex flex-col p-4 transition-transform duration-300">
            <div class="mb-8 p-3 rounded-xl bg-primary/10 border border-primary/20">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-lg bg-primary flex items-center justify-center text-white shadow-lg shadow-primary/30">
                        <span class="material-icons">dashboard_customize</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate leading-none">پنل مدیریت</p>
                        <p class="text-[10px] text-primary font-bold mt-1">Kalands Admin</p>
                    </div>
                </div>
            </div>

            <nav class="space-y-2 text-sm">
                @foreach($navGroups as $index => $group)
                    @if(isset($group['route']))
                        @php($groupActive = request()->routeIs($group['active']) && (!isset($group['params']['moduleKey']) || request()->route('moduleKey') === $group['params']['moduleKey']))
                        @php($routeParams = array_merge(['authkey' => $authkey], $group['params'] ?? []))
                        <a href="{{ route($group['route'], $routeParams) }}" class="admin-nav-link w-full {{ $groupActive ? 'active' : '' }}">
                            <span class="flex items-center gap-2">
                                <span class="material-icons admin-nav-icon">{{ $group['icon'] }}</span>
                                <span class="admin-nav-text">{{ $group['label'] }}</span>
                            </span>
                        </a>
                    @else
                        @php($groupActive = collect($group['children'])->contains(fn ($child) => request()->routeIs($child['active'])))
                        <button type="button" class="admin-nav-link w-full justify-between {{ $groupActive ? 'active' : '' }}" data-admin-group-toggle="{{ $index }}">
                            <span class="flex items-center gap-2">
                                <span class="material-icons admin-nav-icon">{{ $group['icon'] }}</span>
                                <span class="admin-nav-text">{{ $group['label'] }}</span>
                            </span>
                            <span class="material-icons admin-nav-text !text-base">expand_more</span>
                        </button>
                        <div class="admin-subnav {{ $groupActive ? 'open' : '' }}" data-admin-group-panel="{{ $index }}">
                            @foreach($group['children'] as $child)
                                @php($subActive = request()->routeIs($child['active']))
                                <a href="{{ route($child['route'], ['authkey' => $authkey]) }}" class="admin-subnav-link {{ $subActive ? 'active' : '' }}">
                                    {{ $child['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </nav>

        </aside>

        <div class="admin-main min-w-0">
            <header class="admin-topbar sticky top-0 z-30 flex items-center justify-between gap-4 px-4 py-3 lg:px-6">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" id="admin-sidebar-toggle" class="admin-toggle inline-flex lg:hidden" aria-label="mobile menu">
                        <span class="material-icons">menu</span>
                    </button>
                    <button type="button" id="admin-theme-toggle" class="admin-toggle inline-flex" aria-label="toggle theme" title="لایت/دارک">
                        <span class="material-icons">brightness_7</span>
                    </button>

                    <div>
                        <h1 class="text-base font-semibold text-slate lg:text-lg">{{ $title }}</h1>
                        <p class="text-xs text-slate">مسیر: <span class="admin-breadcrumb">{{ request()->path() }}</span></p>
                    </div>
                </div>

                <div class="hidden sm:flex flex-1 justify-center px-2 lg:px-4">
                    <div class="relative w-full max-w-[520px]">
                        <div id="admin-header-search-trigger" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 cursor-pointer hover:border-primary/50 transition-colors">
                            <span class="material-icons text-slate-400 !text-lg">search</span>
                            <span class="text-xs text-slate-500 dark:text-slate-300 flex-1">جستجو در سیستم...</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400">Ctrl + K</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 shrink-0">
                    <button type="button" id="admin-notifications-toggle" class="admin-toggle inline-flex relative" title="اعلان‌ها">
                        <span class="material-icons text-slate">notifications</span>
                        @if(($adminNotificationCount ?? 0) > 0)
                            <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-600 text-[10px] font-bold text-white outline outline-2 outline-white dark:outline-slate-900">
                                {{ $adminNotificationCount > 99 ? '99+' : $adminNotificationCount }}
                            </span>
                        @endif
                    </button>

                    <div class="relative group">
                        <button type="button" class="flex items-center gap-3 border-l border-slate/20 pl-4 hover:opacity-80 transition-opacity">
                            <div class="text-right">
                                <p class="text-sm font-bold text-slate">{{ $admin?->full_name ?? $admin?->username ?? 'Admin' }}</p>
                                <p class="admin-user-meta text-[10px] text-slate/60">{{ $admin?->roles->pluck('label')->implode('، ') }}</p>
                            </div>
                            <div class="h-8 w-8 rounded-full bg-primary/10 text-primary flex items-center justify-center border border-primary/20">
                                <span class="material-icons !text-xl">account_circle</span>
                            </div>
                        </button>

                        <div class="absolute left-0 top-full mt-2 w-72 translate-y-2 opacity-0 invisible group-hover:translate-y-0 group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl overflow-hidden text-right" dir="rtl">
                                <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                                    <div class="flex items-center gap-3">
                                        <div class="h-12 w-12 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0 border-2 border-white dark:border-slate-800 shadow-sm">
                                            <span class="material-icons !text-2xl">person</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-slate truncate">{{ $admin?->full_name ?? $admin?->username ?? 'ادمین سیستم' }}</p>
                                            <p class="text-[10px] text-slate-500 mt-0.5 font-bold truncate opacity-80">{{ $admin?->roles->pluck('label')->implode('، ') ?: 'دسترسی محدود' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-2 space-y-1">
                                    @if($admin)
                                    <a href="{{ route('dash.admin.admins', ['authkey' => $authkey]) }}" class="flex items-center gap-3 px-3 py-3 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-primary/5 hover:text-primary rounded-xl transition-all group/item">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center group-hover/item:bg-primary group-hover/item:text-white transition-colors text-slate-500 dark:text-slate-400">
                                            <span class="material-icons !text-lg">settings</span>
                                        </div>
                                        <span>تنظیمات حساب کاربری</span>
                                    </a>
                                    @endif
                                    <div class="h-px bg-slate-100 dark:bg-slate-800 mx-2"></div>
                                    <form action="{{ route('auth.logout') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-3 text-xs font-bold text-danger hover:bg-danger/5 rounded-xl transition-all group/logout text-right">
                                            <div class="w-8 h-8 rounded-lg bg-danger/10 flex items-center justify-center group-hover/logout:bg-danger group-hover/logout:text-white transition-colors text-danger">
                                                <span class="material-icons !text-lg">logout</span>
                                            </div>
                                            <span>خروج از پنل مدیریت</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-content p-4 lg:p-6">
                <x-session-messages/>
                <x-validation-errors/>
                {{ $slot }}
            </main>
        </div>
    </div>
    <div id="admin-toast-wrap" class="admin-toast-wrap" aria-live="polite"></div>

    <dialog id="admin-search-dialog" class="admin-dialog w-[min(100vw-16px,680px)] max-w-[680px]">
        <div class="admin-dialog-body !p-0">
            <div class="p-4 border-b border-white/10 flex items-center gap-3 bg-white dark:bg-slate-900">
                <div class="relative flex-1">
                    <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate/40">search</span>
                    <input type="text" id="admin-search-input" class="admin-dialog-input !pr-10 !pl-4" placeholder="نام ماژول، کاربر یا محصول..." autocomplete="off" data-url="{{ route('dash.admin.search.ajax', ['authkey' => $authkey]) }}">
                </div>
                <select id="admin-search-filter" class="admin-dialog-input !w-auto min-w-[120px]">
                    <option value="all">همه</option>
                    <option value="modules">ماژول‌ها</option>
                    <option value="users">کاربران</option>
                    <option value="products">محصولات</option>
                    <option value="sections">بخش‌ها</option>
                </select>
                <button type="button" class="admin-toggle inline-flex" data-search-close><span class="material-icons">close</span></button>
            </div>
            <div id="admin-search-results" class="max-h-[60vh] overflow-y-auto p-2 bg-white dark:bg-slate-900">
                <div class="p-8 text-center text-slate/40">
                    <span class="material-icons !text-4xl mb-2">search</span>
                    <p class="text-sm">عبارت مورد نظر خود را تایپ کنید...</p>
                </div>
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-500 dark:text-slate-400 flex justify-between items-center px-4">
                <div class="flex gap-4">
                    <span><kbd class="bg-slate-200 dark:bg-white/10 px-1 rounded">↑↓</kbd> برای جابجایی</span>
                    <span><kbd class="bg-slate-200 dark:bg-white/10 px-1 rounded">Enter</kbd> برای انتخاب</span>
                </div>
                <a href="{{ route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'search']) }}" class="text-primary hover:underline">تنظیمات جستجو</a>
            </div>
        </div>
    </dialog>
    <dialog id="admin-confirm-dialog" class="admin-dialog">
        <form method="dialog" class="admin-dialog-body">
            <div class="flex items-start gap-3">
                <div class="mt-1 rounded-full bg-amber-500/10 p-2 text-amber-400">
                    <span class="material-icons">warning</span>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="admin-dialog-title" id="admin-confirm-title">تایید عملیات</h2>
                    <p class="mt-2 text-sm admin-dialog-muted" id="admin-confirm-message">آیا از انجام این عملیات مطمئن هستید؟</p>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button value="cancel" class="admin-btn admin-btn-secondary"><span class="material-icons">close</span><span>انصراف</span></button>
                <button value="confirm" class="admin-btn admin-btn-danger" id="admin-confirm-submit"><span class="material-icons">check_circle</span><span>تایید</span></button>
            </div>
        </form>
    </dialog>
    <dialog id="admin-file-picker-dialog" class="admin-dialog admin-dialog--file-manager">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title">File Manager</h3>
                <button type="button" class="admin-toggle inline-flex" data-file-picker-close><span class="material-icons">close</span></button>
            </div>
            <div class="min-h-0 flex-1 overflow-hidden" data-file-picker-explorer>
                <div class="admin-dialog-surface p-4 text-sm">در حال بارگذاری فایل‌ها...</div>
            </div>
        </div>
    </dialog>
    <dialog id="admin-explorer-input-dialog" class="admin-dialog w-[min(100vw-16px,520px)] max-w-[520px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title" id="admin-explorer-input-title">ورودی</h3>
                <button type="button" class="admin-toggle inline-flex" data-explorer-input-close><span class="material-icons">close</span></button>
            </div>
            <input id="admin-explorer-input-value" class="admin-dialog-input dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <div class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-explorer-input-close><span class="material-icons">close</span><span>انصراف</span></button>
                <button type="button" class="admin-btn" id="admin-explorer-input-submit"><span class="material-icons">check_circle</span><span>ثبت</span></button>
            </div>
        </div>
    </dialog>
    <dialog id="admin-explorer-confirm-dialog" class="admin-dialog w-[min(100vw-16px,520px)] max-w-[520px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title" id="admin-explorer-confirm-title">تایید</h3>
                <button type="button" class="admin-toggle inline-flex" data-explorer-confirm-close><span class="material-icons">close</span></button>
            </div>
            <p id="admin-explorer-confirm-message" class="text-sm admin-dialog-muted">آیا مطمئن هستید؟</p>
            <div class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-explorer-confirm-close><span class="material-icons">close</span><span>انصراف</span></button>
                <button type="button" class="admin-btn admin-btn-danger" id="admin-explorer-confirm-submit"><span class="material-icons">delete</span><span>حذف</span></button>
            </div>
        </div>
    </dialog>
    <dialog id="admin-explorer-preview-dialog" class="admin-dialog admin-dialog--preview">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title" id="admin-explorer-preview-title">Preview</h3>
                <button type="button" class="admin-toggle inline-flex" data-explorer-preview-close><span class="material-icons">close</span></button>
            </div>
            <div id="admin-explorer-preview-body" class="flex-1 overflow-auto grid place-items-center"></div>
            <div class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-explorer-preview-close><span class="material-icons">close</span><span>بستن</span></button>
            </div>
        </div>
    </dialog>
    <dialog id="admin-explorer-move-copy-dialog" class="admin-dialog w-[min(100vw-16px,620px)] max-w-[620px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title">Move / Copy</h3>
                <button type="button" class="admin-toggle inline-flex" data-explorer-move-copy-close><span class="material-icons">close</span></button>
            </div>
            <p class="text-sm admin-dialog-muted">منبع: <span id="admin-explorer-move-copy-source" class="admin-ltr"></span></p>
            <p class="text-sm admin-dialog-muted">مقصد: <span id="admin-explorer-move-copy-target" class="admin-ltr"></span></p>
            <div class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-explorer-move-copy-close><span class="material-icons">close</span><span>انصراف</span></button>
                <button type="button" class="admin-btn admin-btn-secondary" id="admin-explorer-copy-submit"><span class="material-icons">content_copy</span><span>Copy</span></button>
                <button type="button" class="admin-btn" id="admin-explorer-move-submit"><span class="material-icons">drive_file_move</span><span>Move</span></button>
            </div>
        </div>
    </dialog>

    <dialog id="admin-notifications-dialog" class="admin-dialog w-[min(100vw-16px,480px)] max-w-[480px]">
        <div class="admin-dialog-body !p-0">
            <div class="admin-dialog-head p-4 border-b border-white/10 bg-white dark:bg-slate-900">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons">notifications</span>
                    <span>آخرین اعلان‌های بررسی نشده</span>
                </h3>
                <button type="button" class="admin-toggle inline-flex" data-notifications-close><span class="material-icons">close</span></button>
            </div>
            <div class="max-h-[60vh] overflow-y-auto bg-white dark:bg-slate-900">
                @if(empty($adminNotificationGroups))
                    <div class="p-8 text-center">
                        <span class="material-icons text-slate/20 !text-5xl mb-2">notifications_off</span>
                        <p class="text-sm text-slate/60">هیچ اعلان جدیدی وجود ندارد.</p>
                    </div>
                @else
                    @foreach($adminNotificationGroups as $key => $group)
                        <div class="p-4 border-b border-white/5 last:border-0">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2 font-bold text-sm text-slate">
                                    <span class="material-icons !text-lg text-primary">{{ $group['icon'] }}</span>
                                    <span>{{ $group['label'] }}</span>
                                    <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-full text-[10px]">{{ $group['count'] }}</span>
                                </div>
                                <a href="{{ $group['route'] }}" class="text-[10px] text-primary hover:underline">مشاهده همه</a>
                            </div>
                            <div class="space-y-2">
                                @foreach($group['items'] as $item)
                                    <a href="{{ $item['route'] }}" class="block p-2 rounded-lg bg-slate-50 dark:bg-slate-800/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                        <p class="text-xs font-semibold text-slate mb-1 line-clamp-1">{{ $item['title'] }}</p>
                                        <div class="flex items-center justify-between text-[10px] text-slate/60">
                                            <span>{{ $item['meta'] }}</span>
                                            <span>{{ $item['time'] }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="p-3 bg-slate-50 dark:bg-slate-900/50 text-center border-t border-white/10">
                <button type="button" class="admin-btn admin-btn-secondary w-full" data-notifications-close>بستن</button>
            </div>
        </div>
    </dialog>

    <script>
        (function () {
            const shell = document.getElementById('admin-shell');
            const sidebar = document.getElementById('admin-sidebar');
            const toggle = document.getElementById('admin-sidebar-toggle');
            const themeToggle = document.getElementById('admin-theme-toggle');
            const notificationToggle = document.getElementById('admin-notifications-toggle');
            const notificationDialog = document.getElementById('admin-notifications-dialog');
            const toastWrap = document.getElementById('admin-toast-wrap');
            const confirmDialog = document.getElementById('admin-confirm-dialog');
            const confirmTitle = document.getElementById('admin-confirm-title');
            const confirmMessage = document.getElementById('admin-confirm-message');
            const confirmSubmit = document.getElementById('admin-confirm-submit');
            const themeKey = 'admin.theme';
            const desktopMinWidth = 1024;

            // Cleanup frontend theme impacts
            document.documentElement.classList.remove('dark');
            document.body.classList.remove('dark');
            let pendingConfirmForm = null;

            if (!sidebar || !toggle || !shell) {
                return;
            }

            const isDesktop = function () {
                return window.innerWidth >= desktopMinWidth;
            };

            const applyTheme = function (theme) {
                const isLight = theme === 'light';
                shell.classList.remove('admin-light', 'admin-dark');
                shell.classList.add(isLight ? 'admin-light' : 'admin-dark');
                document.documentElement.classList.remove('admin-light', 'admin-dark');
                document.documentElement.classList.add(isLight ? 'admin-light' : 'admin-dark');
                document.documentElement.classList.toggle('dark', !isLight);

                window.dispatchEvent(new CustomEvent('admin-theme-changed', { detail: { theme: isLight ? 'light' : 'dark' } }));
                if (!themeToggle) {
                    return;
                }
                const icon = themeToggle.querySelector('.material-icons');
                if (icon) {
                    icon.textContent = isLight ? 'brightness_2' : 'brightness_7';
                }
            };
            const showToast = function (message) {
                if (!toastWrap) return;
                const item = document.createElement('div');
                item.className = 'admin-toast';
                item.textContent = message;
                toastWrap.appendChild(item);
                setTimeout(function () { item.remove(); }, 2800);
            };
            window.showToast = showToast;

            const syncSidebarByViewport = function () {
                if (isDesktop()) {
                    // Desktop: sidebar must always be visible (minimal/expand only)
                    sidebar.classList.remove('translate-x-full');
                    return;
                }

                // Mobile: default closed
                if (!sidebar.dataset.mobileStateInitialized) {
                    sidebar.classList.add('translate-x-full');
                    sidebar.dataset.mobileStateInitialized = '1';
                }
            };

            applyTheme(localStorage.getItem(themeKey) || 'dark');
            syncSidebarByViewport();

            toggle.addEventListener('click', function () {
                if (isDesktop()) {
                    return;
                }
                sidebar.classList.toggle('translate-x-full');
            });

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    const nextTheme = shell.classList.contains('admin-light') ? 'dark' : 'light';
                    localStorage.setItem(themeKey, nextTheme);
                    applyTheme(nextTheme);
                });
            }
            if (notificationToggle && notificationDialog) {
                notificationToggle.addEventListener('click', function() {
                    if (typeof notificationDialog.showModal === 'function') {
                        notificationDialog.showModal();
                    }
                });
                document.querySelectorAll('[data-notifications-close]').forEach(function(el) {
                    el.addEventListener('click', function() {
                        notificationDialog.close();
                    });
                });
            }
            document.querySelectorAll('[data-admin-group-toggle]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const key = button.getAttribute('data-admin-group-toggle');
                    const panel = document.querySelector('[data-admin-group-panel="' + key + '"]');
                    if (panel) {
                        panel.classList.toggle('open');
                    }
                });
            });

            document.addEventListener('click', function (event) {
                if (window.innerWidth >= 1024) {
                    return;
                }

                const clickedInsideSidebar = sidebar.contains(event.target);
                const clickedToggle = toggle.contains(event.target);

                if (!clickedInsideSidebar && !clickedToggle) {
                    sidebar.classList.add('translate-x-full');
                }
            });

            window.addEventListener('resize', function () {
                syncSidebarByViewport();
            });

            document.querySelectorAll('[data-copy-text]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const text = button.getAttribute('data-copy-text') || '';
                    navigator.clipboard.writeText(text).then(function () {
                        showToast('با موفقیت در کلیپ‌بورد کپی شد.');
                    }, function () {
                        showToast('کپی انجام نشد.');
                    });
                });
            });

            document.addEventListener('submit', function (event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) {
                    return;
                }

                const confirmText = form.getAttribute('data-admin-confirm');
                if (!confirmText || form.dataset.adminConfirmed === '1') {
                    if (form.dataset.adminConfirmed === '1') {
                        delete form.dataset.adminConfirmed;
                    }
                    return;
                }

                event.preventDefault();
                pendingConfirmForm = form;
                if (confirmTitle) {
                    confirmTitle.textContent = form.getAttribute('data-admin-confirm-title') || 'تایید عملیات';
                }
                if (confirmMessage) {
                    confirmMessage.textContent = confirmText;
                }
                if (confirmDialog && typeof confirmDialog.showModal === 'function') {
                    confirmDialog.showModal();
                } else if (window.confirm(confirmText)) {
                    form.dataset.adminConfirmed = '1';
                    form.requestSubmit();
                }
            });

            if (confirmSubmit) {
                confirmSubmit.addEventListener('click', function () {
                    if (!pendingConfirmForm) {
                        return;
                    }
                    const form = pendingConfirmForm;
                    pendingConfirmForm = null;
                    form.dataset.adminConfirmed = '1';
                    if (confirmDialog && confirmDialog.open) {
                        confirmDialog.close();
                    }
                    form.requestSubmit();
                });
            }

            // Keyboard Shortcuts
            document.addEventListener('keydown', function(e) {
                // Alt + T: Toggle Theme
                if (e.altKey && e.key === 't') {
                    e.preventDefault();
                    if (themeToggle) themeToggle.click();
                }
                // Alt + I: Notifications
                if (e.altKey && e.key === 'i') {
                    e.preventDefault();
                    if (notificationToggle) notificationToggle.click();
                }
                // Alt + M: Toggle Sidebar (Mobile)
                if (e.altKey && e.key === 'm') {
                    e.preventDefault();
                    if (toggle) toggle.click();
                }
                // Alt + H: Dashboard Home
                if (e.altKey && e.key === 'h') {
                    e.preventDefault();
                    window.location.href = "{{ route('dash.admin.index', ['authkey' => $authkey]) }}";
                }
                // Ctrl + S: Save/Submit Form
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    const submitBtn = document.querySelector('main.admin-content form button[type="submit"], main.admin-content form button.admin-btn-primary');
                    if (submitBtn) {
                        e.preventDefault();
                        submitBtn.click();
                    }
                }
                // Alt + N: New/Add item
                if (e.altKey && e.key === 'n') {
                    const addBtn = Array.from(document.querySelectorAll('a.admin-btn')).find(el =>
                        el.textContent.includes('افزودن') || el.textContent.includes('جدید')
                    );
                    if (addBtn) {
                        e.preventDefault();
                        addBtn.click();
                    }
                }
                // Esc: Close Notification Dialog (Search handled in its own JS)
                if (e.key === 'Escape') {
                    if (notificationDialog && notificationDialog.open) {
                        notificationDialog.close();
                    }
                }
            });
        })();
    </script>
    @stack('scripts')
</x-layouts.app>
