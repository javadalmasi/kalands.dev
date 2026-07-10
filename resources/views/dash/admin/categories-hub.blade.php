<x-layouts.admin-dashboard title="مدیریت دسته‌بندی‌ها" :helpModuleKey="'categories'">
    @push('head')
        <meta name="authkey" content="{{ $authkey }}">
        <style>
            .tree-node-children { display: none; }
            .tree-node-expanded > .tree-node-children { display: block; }
            .tree-node-expanded > .tree-node-header .expand-icon { transform: rotate(-90deg); }
            .expand-icon { transition: transform 0.2s; }
        </style>
    @endpush

    <x-admin.page-header title="مدیریت دسته‌بندی‌ها">
        <x-slot:actions>
            <button onclick="loadData()" class="admin-btn admin-btn-secondary flex items-center gap-2">
                <i class="material-icons !text-lg">refresh</i>
                <span>بروزرسانی لیست‌ها</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="category-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold flex items-center gap-2" data-tab-target="tab-digikala">
            <span class="material-icons text-base">category</span>
            <span>دیجی‌کالا</span>
        </button>
        <button class="admin-tab-btn flex items-center gap-2" data-tab-target="tab-basalam">
            <span class="material-icons text-base">hub</span>
            <span>باسلام</span>
        </button>
        <button class="admin-tab-btn flex items-center gap-2" data-tab-target="tab-snappshop">
            <span class="material-icons text-base">shopping_bag</span>
            <span>اسنپ‌شاپ</span>
        </button>
        <button class="admin-tab-btn flex items-center gap-2" data-tab-target="tab-linked">
            <span class="material-icons text-base">link</span>
            <span>موارد لینک شده</span>
        </button>
        <button class="admin-tab-btn flex items-center gap-2" data-tab-target="tab-operations">
            <span class="material-icons text-base">settings_suggest</span>
            <span>عملیات و ایمپورت</span>
        </button>
        <button class="admin-tab-btn flex items-center gap-2" data-tab-target="tab-settings">
            <span class="material-icons text-base">psychology</span>
            <span>تنظیمات هوش مصنوعی</span>
        </button>
    </x-admin.tab-bar>

    <!-- Digikala Tab -->
    <div id="tab-digikala" class="tab-content space-y-6">
        <div class="admin-card p-6">
            <div id="digikala-tree" class="min-h-[400px]">
                <div class="flex justify-center p-20"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div></div>
            </div>
        </div>
    </div>

    <!-- Basalam Tab -->
    <div id="tab-basalam" class="tab-content hidden space-y-6">
        <div class="admin-card p-6">
            <div id="basalam-tree" class="min-h-[400px]">
                <div class="flex justify-center p-20"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div></div>
            </div>
        </div>
    </div>

    <!-- Linked Mappings Tab -->
    <div id="tab-linked" class="tab-content hidden space-y-6">
        <div class="admin-card p-6">
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100 dark:border-white/10">
                <div>
                    <h2 class="font-bold flex items-center gap-2 text-slate-700 dark:text-white text-lg">
                        <span class="material-icons text-primary">link</span>
                        نگاشت‌های متصل به دیجی‌کالا
                    </h2>
                    <p class="text-[10px] text-slate-400 mt-1">در این بخش می‌توانید تمامی دسته‌بندی‌هایی که به یک مرجع در دیجی‌کالا متصل شده‌اند را مشاهده کنید.</p>
                </div>
                <button onclick="loadLinkedData()" class="admin-btn admin-btn-secondary !p-2"><span class="material-icons">refresh</span></button>
            </div>
            <div id="linked-content" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="col-span-full flex justify-center py-20"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div></div>
            </div>
        </div>
    </div>

    <!-- SnappShop Tab -->
    <div id="tab-snappshop" class="tab-content hidden space-y-6">
        <div class="admin-card p-6">
            <div id="snappshop-tree" class="min-h-[400px]">
                <div class="flex justify-center p-20"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div></div>
            </div>
        </div>
    </div>

    <!-- Operations Tab -->
    <div id="tab-operations" class="tab-content hidden space-y-6">
        <div class="grid md:grid-cols-2 gap-8">
            <div class="admin-card p-8 space-y-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center">
                        <span class="material-icons !text-3xl">cloud_download</span>
                    </div>
                    <div>
                        <h3 class="font-black text-lg">واردسازی دسته‌بندی‌های اسنپ‌شاپ</h3>
                        <p class="text-[10px] text-slate-400 mt-1">ایمپورت درختی دسته‌بندی‌ها از خروجی JSON وب‌سرویس</p>
                    </div>
                </div>
                <button onclick="document.getElementById('snapp-import-modal').showModal()" class="admin-btn bg-blue-500 text-white w-full !h-12 shadow-lg shadow-blue-500/20">
                    <i class="material-icons">add_to_photos</i>
                    <span>باز کردن فرم ایمپورت</span>
                </button>
            </div>

            <div class="admin-card p-8 space-y-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                        <span class="material-icons !text-3xl">bolt</span>
                    </div>
                    <div>
                        <h3 class="font-black text-lg">نگاشت هوشمند سراسری</h3>
                        <p class="text-[10px] text-slate-400 mt-1">تطبیق تمامی دسته‌بندی‌های موجود بر اساس بردارهای هوش مصنوعی</p>
                    </div>
                </div>
                <button onclick="startAutoSync()" id="sync-all-btn" class="admin-btn bg-emerald-500 text-white w-full !h-12 shadow-lg shadow-emerald-500/20">
                    <i class="material-icons">sync_alt</i>
                    <span>شروع همگام‌سازی نگاشت‌ها</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="tab-settings" class="tab-content hidden space-y-8">
        <div class="grid lg:grid-cols-2 gap-8">
            <div class="space-y-6">
                <form action="{{ route('dash.admin.categories.settings.save', ['authkey' => $authkey]) }}" method="POST" class="admin-card p-8 space-y-8">
                    @csrf
                    <div>
                        <h3 class="text-xl font-black mb-2 flex items-center gap-3">
                            <span class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center"><i class="material-icons">auto_awesome</i></span>
                            تنظیمات موتور وکتورایز
                        </h3>
                    </div>

                    <div class="grid gap-6">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold">موتور پردازشگر</label>
                            <select name="vector_engine" id="vector_engine" class="admin-input w-full !h-12 !rounded-xl" onchange="toggleEngineSettings()">
                                <option value="local" {{ ($settings['vector_engine'] ?? '') === 'local' ? 'selected' : '' }}>موتور داخلی (N-gram)</option>
                                <option value="external" {{ ($settings['vector_engine'] ?? '') === 'external' ? 'selected' : '' }}>موتور خارجی (LLM API)</option>
                            </select>
                        </div>

                        <div id="external-engine-settings" class="{{ ($settings['vector_engine'] ?? '') === 'external' ? '' : 'hidden' }} space-y-6 p-6 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5">
                            <div class="space-y-2">
                                <label class="block text-xs font-bold">مدل هوش مصنوعی (Model Name)</label>
                                <input type="text" name="external_model" value="{{ $settings['external_model'] ?? 'gemma-4' }}" class="admin-input w-full !h-11" placeholder="مثلا: gemma-4">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-bold">آدرس وب‌سرویس (Endpoint)</label>
                                <input type="url" name="api_endpoint" value="{{ $settings['api_endpoint'] ?? '' }}" class="admin-input w-full !h-11 font-mono text-[11px]" placeholder="https://api.openai.com/v1/embeddings">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-bold">کلید دسترسی (API Key)</label>
                                <input type="password" name="api_key" value="{{ $settings['api_key'] ?? '' }}" class="admin-input w-full !h-11 font-mono" placeholder="sk-...">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary w-full !h-12 shadow-lg shadow-primary/20">
                        <i class="material-icons">save</i>
                        <span>ذخیره تنظیمات</span>
                    </button>
                </form>

                <div class="admin-card p-8">
                    <h3 class="font-bold mb-6 text-slate-700 dark:text-white flex items-center gap-2">
                        <span class="material-icons text-primary">science</span>
                        تست موتور وکتور
                    </h3>
                    <div class="flex gap-2">
                        <input type="text" id="ai-test-input" class="admin-input flex-1 !h-11" placeholder="متنی برای تست وارد کنید...">
                        <button onclick="testAi()" id="ai-test-btn" class="admin-btn admin-btn-primary !h-11 px-6">تست</button>
                    </div>
                    <div id="ai-test-result" class="mt-4 hidden p-4 bg-slate-50 dark:bg-white/5 rounded-xl text-xs font-mono border border-slate-100 dark:border-white/5"></div>
                </div>
            </div>

            <div class="admin-card p-8">
                <h3 class="text-lg font-black mb-6 flex items-center gap-2 text-slate-700 dark:text-white">
                    <span class="material-icons text-primary">history_toggle_off</span>
                    گزارش مصرف هوش مصنوعی
                </h3>
                <div class="space-y-4">
                    @foreach($aiUsage as $log)
                        <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5">
                            <div>
                                <div class="font-bold text-sm text-slate-700 dark:text-slate-200">{{ persianDate($log->date) }}</div>
                                <div class="text-[10px] text-slate-400">{{ $log->requests }} درخواست</div>
                            </div>
                            <div class="text-right">
                                <div class="text-primary font-black text-lg">{{ number_format($log->tokens) }}</div>
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Tokens</div>
                            </div>
                        </div>
                    @endforeach
                    @if($aiUsage->isEmpty())
                        <div class="flex flex-col items-center justify-center py-10 opacity-30">
                            <span class="material-icons !text-5xl mb-2">analytics</span>
                            <p class="text-xs font-bold italic">داده‌ای ثبت نشده است</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        @vite('resources/js/admin-categories.js')
    @endpush
</x-layouts.admin-dashboard>
