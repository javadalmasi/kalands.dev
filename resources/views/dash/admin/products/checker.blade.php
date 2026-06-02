<x-layouts.admin-dashboard title="بررسی محصولات غیرفعال">
    <div class="admin-card" x-data="productChecker()" data-checker-root data-authkey="{{ $authkey }}" data-ids-url="{{ route('dash.admin.products.digikala_ids', ['authkey' => $authkey]) }}" data-check-url="{{ route('dash.admin.products.check_api', ['authkey' => $authkey]) }}">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate">بررسی وضعیت وب‌سرویس محصولات</h2>
                <p class="mt-1 text-sm text-slate-500">این ابزار تمامی محصولات دیجی‌کالا را در دسته‌های ۱۰ تایی از طریق Multi-Curl بررسی کرده و در صورت غیرفعال بودن در مبدا، آن‌ها را در سیستم نیز غیرفعال می‌کند.</p>
            </div>
            <a href="{{ route('dash.admin.products', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary">
                <span class="material-icons !text-base">arrow_forward</span>
                بازگشت به لیست
            </a>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-1 space-y-6">
                <!-- Control Panel -->
                <div class="admin-card border border-slate-100 bg-slate-50 p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="mb-4 font-bold text-slate">پنل کنترل</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">تعداد کل محصولات دیجی‌کالا:</span>
                            <span class="font-bold text-slate" x-text="totalCount">...</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">بررسی شده:</span>
                            <span class="font-bold text-slate" x-text="processedCount">0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">غیرفعال شناسایی شده:</span>
                            <span class="font-bold text-amber-600 dark:text-amber-500" x-text="inactiveFound">0</span>
                        </div>

                        <div class="pt-4">
                            <button
                                @click="startChecking"
                                :disabled="isWorking || totalCount === 0"
                                class="admin-btn w-full justify-center gap-2 py-3"
                                :class="isWorking ? 'opacity-50 cursor-not-allowed' : ''"
                            >
                                <span class="material-icons" :class="isWorking ? 'animate-spin' : ''" x-text="isWorking ? 'sync' : 'play_circle'"></span>
                                <span x-text="isWorking ? 'در حال پردازش...' : 'شروع عملیات بررسی'"></span>
                            </button>

                            <button
                                x-show="isWorking"
                                @click="stopChecking"
                                class="admin-btn admin-btn-danger mt-3 w-full justify-center gap-2"
                            >
                                <span class="material-icons">stop_circle</span>
                                توقف عملیات
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="admin-card border border-slate-100 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="mb-4 font-bold text-slate">پیشرفت عملیات</h3>
                    <div class="relative h-4 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-full bg-primary transition-all duration-500"
                            :style="`width: ${progress}%`"
                        ></div>
                    </div>
                    <p class="mt-2 text-center text-xs font-bold text-slate" x-text="`${progress}%`"></p>
                </div>
            </div>

            <div class="lg:col-span-2">
                <!-- Log Console -->
                <div class="flex h-full flex-col admin-card border border-slate-100 bg-slate-950 p-4 font-mono text-xs text-green-400 shadow-inner dark:border-slate-800">
                    <div class="mb-2 flex items-center justify-between border-b border-white/10 pb-2 text-[10px] uppercase tracking-widest text-slate-500">
                        <span>خروجی عملیات (Logs)</span>
                        <button @click="logs = []" class="hover:text-white">پاکسازی</button>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-1" id="log-container">
                        <template x-for="(log, index) in logs" :key="index">
                            <div class="flex gap-2">
                                <span class="text-slate-600" x-text="log.time"></span>
                                <span :class="log.type === 'error' ? 'text-red-400' : (log.type === 'success' ? 'text-primary' : 'text-green-400')" x-text="log.message"></span>
                            </div>
                        </template>
                        <div x-show="logs.length === 0" class="py-10 text-center text-slate-700 italic">منتظر شروع عملیات...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-products-checker.js'])
