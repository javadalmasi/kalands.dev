<x-layouts.admin-dashboard title="مدیریت دسته‌بندی‌ها">
    @push('head')
        <meta name="authkey" content="{{ $authkey }}">
        <style>
            .tree-node-children { display: none; }
            .tree-node-expanded > .tree-node-children { display: block; }
            .tree-node-expanded > .tree-node-header .expand-icon { transform: rotate(-90deg); }
            .expand-icon { transition: transform 0.2s; }
        </style>
    @endpush

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0 text-2xl font-black">مدیریت دسته‌بندی‌ها</h1>
        <div class="flex items-center gap-3">
            <button onclick="loadData()" class="admin-btn admin-btn-secondary flex items-center gap-2">
                <i class="material-icons !text-lg">refresh</i>
                <span>بروزرسانی لیست‌ها</span>
            </button>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="category-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-digikala">
                <span class="material-icons text-base">category</span>
                <span>دیجی‌کالا</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-basalam">
                <span class="material-icons text-base">hub</span>
                <span>باسلام</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-snappshop">
                <span class="material-icons text-base">shopping_bag</span>
                <span>اسنپ‌شاپ</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-linked">
                <span class="material-icons text-base">link</span>
                <span>موارد لینک شده</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-operations">
                <span class="material-icons text-base">settings_suggest</span>
                <span>عملیات و ایمپورت</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-settings">
                <span class="material-icons text-base">psychology</span>
                <span>تنظیمات هوش مصنوعی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

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
                                <div class="font-bold text-sm text-slate-700 dark:text-slate-200">{{ verta($log->date)->format('Y/m/d') }}</div>
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

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل مدیریت دسته‌بندی‌ها</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>ماژول مدیریت دسته‌بندی، سامانه یکپارچه مدیریت درخت دسته‌بندی محصولات و نگاشت هوشمند بین فروشگاه‌های دیجی‌کالا، باسلام و اسنپ‌شاپ در پلتفرم kalands.ir است.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-stores">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">storefront</span>
                                فروشگاه‌ها
                            </h3>
                            <p>سه فروشگاه اصلی در این ماژول مدیریت می‌شوند:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>دیجی‌کالا:</b> دسته‌بندی مرجع که دیگر فروشگاه‌ها به آن نگاشت می‌شوند</li>
                                <li><b>باسلام:</b> دسته‌بندی باسلام که می‌تواند به دیجی‌کالا متصل شود</li>
                                <li><b>اسنپ‌شاپ:</b> دسته‌بندی اسنپ‌شاپ که می‌تواند به دیجی‌کالا متصل شود</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-mapping">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">link</span>
                                نگاشت هوشمند
                            </h3>
                            <p>نگاشت هوشمند بین دسته‌بندی‌ها با استفاده از بردارهای بیانیه (Vector Embeddings) انجام می‌شود. در تنظیمات می‌توانید موتور وکتور را انتخاب کنید:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>موتور داخلی (N-gram):</b> بدون نیاز به سرویس خارجی، سرعت بالا</li>
                                <li><b>موتور خارجی (LLM API):</b> دقت بالاتر با هزینه وابسته به API</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-operations">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">settings_suggest</span>
                                عملیات و ایمپورت
                            </h3>
                            <p>برای ایمپورت دسته‌بندی اسنپ‌شاپ، خروجی JSON وب‌سرویس را در فرم ایمپورت قرار دهید. سیستم به صورت خودکار ساختار درختی را استخراج و وکتورایز می‌کند.</p>
                            <p class="mt-3">همچنین می‌توانید نگاشت هوشمند سراسری را اجرا کنید تا تمام دسته‌بندی‌های باسلام و اسنپ‌شاپ با دیجی‌کالا مقایسه شوند.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-linked">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">link</span>
                                موارد لینک شده
                            </h3>
                            <p>در این بخش می‌توانید تمامی نگاشت‌های ایجاد شده را مشاهده کنید. نمایش درصد شباهت و وضعیت دستی/خودکار نگاشت‌ها امکان‌پذیر است.</p>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-stores" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">فروشگاه‌ها</a>
                        <a href="#doc-mapping" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نگاشت هوشمند</a>
                        <a href="#doc-operations" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">عملیات و ایمپورت</a>
                        <a href="#doc-linked" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">موارد لینک شده</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/admin-visitor-intelligence.js')
    @endpush
</x-layouts.admin-dashboard>