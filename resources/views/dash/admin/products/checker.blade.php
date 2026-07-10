<x-layouts.admin-dashboard title="بررسی محصولات غیرفعال" helpModuleKey="product_checker">
    <div id="checker-root" class="admin-card" data-authkey="{{ $authkey }}" data-ids-url="{{ route('dash.admin.products.digikala_ids', ['authkey' => $authkey]) }}" data-check-url="{{ route('dash.admin.products.check_api', ['authkey' => $authkey]) }}">
        <x-admin.page-header
            title="بررسی وضعیت وب‌سرویس محصولات"
            description="این ابزار تمامی محصولات دیجی‌کالا را در دسته‌های 10 تایی از طریق Multi-Curl بررسی کرده و در صورت غیرفعال بودن در مبدا، آن‌ها را در سیستم نیز غیرفعال می‌کند."
        >
            <x-slot:actions>
                <a href="{{ route('dash.admin.products', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary">
                    <span class="material-icons !text-base">arrow_forward</span>
                    بازگشت به لیست
                </a>
            </x-slot:actions>
        </x-admin.page-header>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
            <div class="admin-card border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900/50 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">کل محصولات</p>
                        <p class="mt-2 text-2xl font-bold text-slate dark:text-white" id="stat-total">0</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                        <span class="material-icons">inventory_2</span>
                    </div>
                </div>
            </div>

            <div class="admin-card border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900/50 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">بررسی شده</p>
                        <p class="mt-2 text-2xl font-bold text-slate dark:text-white" id="stat-processed">0</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-icons">check_circle</span>
                    </div>
                </div>
            </div>

            <div class="admin-card border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900/50 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">غیرفعال شناسایی شده</p>
                        <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-500" id="stat-inactive">0</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <span class="material-icons">warning</span>
                    </div>
                </div>
            </div>

            <div class="admin-card border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900/50 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">نرخ موفقیت</p>
                        <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-500" id="stat-success-rate">0%</p>
                    </div>
                    <div class="w-12 h-12 rounded-lg bg-green-500/10 text-green-500 flex items-center justify-center">
                        <span class="material-icons">trending_up</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: Control Panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="admin-card border border-slate-100 bg-slate-50 dark:border-slate-800 dark:bg-slate-900 p-6">
                    <h3 class="mb-4 font-bold text-slate dark:text-slate-100">پنل کنترل</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2">اندازه هر دسته</label>
                            <div class="flex gap-2">
                                <select id="batch-size" class="admin-input flex-1 !py-2 !px-3 !text-sm" disabled>
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button id="start-btn" class="admin-btn w-full justify-center gap-2 py-3">
                                <span class="material-icons">play_circle</span>
                                <span>شروع عملیات بررسی</span>
                            </button>

                            <button id="stop-btn" class="admin-btn admin-btn-danger mt-3 w-full justify-center gap-2 hidden">
                                <span class="material-icons">stop_circle</span>
                                توقف عملیات
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Progress -->
                <div class="admin-card border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900/50 p-6">
                    <h3 class="mb-4 font-bold text-slate dark:text-slate-100">پیشرفت</h3>
                    <div class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800 mb-3">
                        <div id="progress-bar" class="h-full bg-primary transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            <span id="progress-text">0</span>%
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            ETA: <span id="eta-text">-</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: Log Console -->
            <div class="lg:col-span-2">
                <div class="flex h-full flex-col admin-card border border-slate-100 bg-slate-950 dark:border-slate-800 p-4 font-mono text-xs text-green-400 shadow-inner">
                    <div class="mb-2 flex items-center justify-between border-b border-white/10 pb-2 text-[10px] uppercase tracking-widest text-slate-500">
                        <span>خروجی عملیات (Logs)</span>
                        <button id="clear-logs-btn" class="hover:text-white transition-colors">پاکسازی</button>
                    </div>
                    <div id="log-container" class="flex-1 overflow-y-auto space-y-1">
                        <div class="py-10 text-center text-slate-700">منتظر شروع عملیات...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inactive Products Table (hidden by default) -->
        <div id="results-section" class="mt-6 hidden">
            <div class="admin-card border border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900/50 p-6">
                <h3 class="mb-4 font-bold text-slate dark:text-slate-100 flex items-center gap-2">
                    <span class="material-icons text-amber-500">warning</span>
                    <span>محصولات غیرفعال شناسایی‌شده</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400 font-normal">(<span id="inactive-count">0</span> محصول)</span>
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-800">
                                <th class="text-right py-3 px-4 font-bold text-slate-700 dark:text-slate-300">شناسه محصول</th>
                                <th class="text-right py-3 px-4 font-bold text-slate-700 dark:text-slate-300">وضعیت</th>
                                <th class="text-right py-3 px-4 font-bold text-slate-700 dark:text-slate-300">زمان بررسی</th>
                            </tr>
                        </thead>
                        <tbody id="inactive-products-tbody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/admin-products-checker.js'])
</x-layouts.admin-dashboard>
