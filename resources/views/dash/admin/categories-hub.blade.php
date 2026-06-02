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

    <!-- Snapp Import Modal -->
    <dialog id="snapp-import-modal" class="admin-dialog w-[min(100vw-32px,700px)]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons text-primary">cloud_upload</span>
                    <span>ایمپورت داده‌های اسنپ‌شاپ</span>
                </h3>
                <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle"><span class="material-icons">close</span></button>
            </div>

            <div class="space-y-4 py-6">
                <p class="text-xs text-slate-500 leading-6">محتوای JSON دریافتی از وب‌سرویس اسنپ‌شاپ را در کادر زیر قرار دهید. سیستم به صورت خودکار سلسله مراتب دسته‌بندی را استخراج و وکتورایز می‌کند.</p>
                <textarea id="snapp-json-input" class="admin-input w-full h-64 font-mono text-[10px] !p-4" placeholder='{"status":true, "data": { "menus": [...] } }' spellcheck="false"></textarea>
            </div>

            <div class="admin-dialog-actions">
                <button onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <button onclick="submitSnappImport()" id="snapp-submit-btn" class="admin-btn admin-btn-primary px-8">شروع عملیات ایمپورت</button>
            </div>
        </div>
    </dialog>

    <!-- Details Modal -->
    <dialog id="details-modal" class="admin-dialog w-[min(100vw-32px,600px)]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons text-primary">analytics</span>
                    <span>جزئیات وکتور و شباهت</span>
                </h3>
                <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle"><span class="material-icons">close</span></button>
            </div>
            <div id="details-content" class="space-y-6 py-6"></div>
        </div>
    </dialog>

    <!-- Edit Title Modal -->
    <dialog id="edit-category-modal" class="admin-dialog w-[min(100vw-32px,500px)]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons text-primary">edit</span>
                    <span>ویرایش نام دسته‌بندی</span>
                </h3>
                <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle"><span class="material-icons">close</span></button>
            </div>

            <div class="space-y-4 py-6">
                <div>
                    <label class="block text-xs font-bold mb-2">نام جدید</label>
                    <input type="text" id="edit-category-title-input" class="admin-input w-full !h-12 !rounded-xl" placeholder="نام جدید را وارد کنید...">
                </div>
            </div>

            <div class="admin-dialog-actions pt-2">
                <button onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <button onclick="submitCategoryUpdate()" id="update-category-btn" class="admin-btn admin-btn-primary px-8">بروزرسانی نام</button>
            </div>
        </div>
    </dialog>

    <!-- Mapping Modal -->
    <dialog id="mapping-modal" class="admin-dialog w-[min(100vw-32px,550px)]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title flex items-center gap-2">
                    <span class="material-icons text-primary">link</span>
                    <span>نگاشت دستی دسته‌بندی</span>
                </h3>
                <button type="button" onclick="this.closest('dialog').close()" class="admin-toggle"><span class="material-icons">close</span></button>
            </div>
            <div class="space-y-6 py-6">
                <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5">
                    <label class="block text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-wider">دسته‌بندی مبدا</label>
                    <div id="modal-basalam-title" class="font-black text-slate-700 dark:text-white text-lg"></div>
                </div>
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-300">انتخاب دسته‌بندی معادل در دیجی‌کالا (مقصد)</label>
                    <select id="modal-digikala-select" class="admin-input w-full !h-12 !rounded-xl"></select>
                </div>
            </div>
            <div class="admin-dialog-actions pt-2">
                <button onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <button onclick="saveManualMapping()" class="admin-btn admin-btn-primary px-8">ذخیره نگاشت</button>
            </div>
        </div>
    </dialog>

    @push('scripts')
    <script>
        (function() {
            let categoriesData = { digikala: [], basalam: [], snappshop: [], mappings: [], digikala_flat: [] };

            const myAdminAlert = (msg) => {
                if (window.adminAlert) window.adminAlert(msg);
                else alert(msg);
            };

            window.toggleEngineSettings = function() {
                const engine = document.getElementById('vector_engine').value;
                document.getElementById('external-engine-settings').classList.toggle('hidden', engine !== 'external');
            };

            window.loadData = async function() {
                try {
                    const response = await fetch('{{ route('dash.admin.categories.tree', ['authkey' => $authkey]) }}');
                    categoriesData = await response.json();
                    renderTrees();
                    populateSelect();
                    loadLinkedData();
                } catch (e) { myAdminAlert('خطا در بارگذاری داده‌ها'); }
            };

            window.loadLinkedData = async function() {
                const container = document.getElementById('linked-content');
                try {
                    const response = await fetch('{{ route('dash.admin.categories.linked', ['authkey' => $authkey]) }}');
                    const data = await response.json();

                    container.innerHTML = data.linked.map(item => `
                        <div class="admin-card !bg-slate-50/50 dark:!bg-white/5 p-5 border border-slate-100 dark:border-white/5 relative overflow-hidden group hover:border-primary/30 transition-all">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-600 flex items-center justify-center">
                                    <span class="material-icons">category</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-[10px] font-black text-orange-500 uppercase tracking-tighter mb-0.5">Digikala Reference</div>
                                    <div class="font-black text-slate-800 dark:text-white truncate">${item.digikala.title}</div>
                                </div>
                            </div>
                            <div class="space-y-2 border-t border-slate-200/50 dark:border-white/5 pt-4">
                                ${item.links.map(link => `
                                    <div class="flex items-center justify-between p-2.5 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-white/5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-[8px] bg-slate-100 dark:bg-white/10 px-1.5 py-0.5 rounded text-slate-500 uppercase font-black">${link.category.store}</span>
                                            <span class="font-bold text-xs truncate">${link.category.title}</span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span class="text-[9px] font-black ${link.confidence > 0.8 ? 'text-emerald-500' : 'text-amber-500'}">${Math.round(link.confidence * 100)}%</span>
                                            ${link.is_manual ? '<span class="material-icons text-[10px] text-blue-500" title="Manual Mapping">verified</span>' : ''}
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('') || '<div class="col-span-full py-20 text-center opacity-20"><span class="material-icons !text-6xl">link_off</span><p class="font-bold">هیچ نگاشتی ثبت نشده است</p></div>';
                } catch (e) { container.innerHTML = '<p class="text-red-500 text-center col-span-full">خطا در بارگذاری نگاشت‌ها</p>'; }
            };

            window.startAutoSync = async function() {
                const btn = document.getElementById('sync-all-btn');
                if (!await adminConfirm('آیا مایلید تمامی دسته‌بندی‌های باسلام و اسنپ‌شاپ را مجدداً به صورت هوشمند با دیجی‌کالا مقایسه و لینک کنید؟')) return;

                btn.disabled = true;
                btn.innerHTML = '<span class="animate-spin material-icons text-sm">bolt</span> در حال پردازش...';

                try {
                    const response = await fetch('{{ route('dash.admin.categories.sync_all', ['authkey' => $authkey]) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    const res = await response.json();
                    if (res.ok) {
                        myAdminAlert(`عملیات با موفقیت پایان یافت. تعداد ${res.count} نگاشت جدید ایجاد یا بروزرسانی شد.`);
                        loadData();
                    }
                } catch (e) { myAdminAlert('خطا در اجرای همگام‌سازی'); }

                btn.disabled = false;
                btn.innerHTML = '<i class="material-icons !text-lg">bolt</i><span>نگاشت هوشمند سراسری</span>';
            };

            function renderTrees() {
                renderTree('digikala-tree', categoriesData.digikala, 'digikala');
                renderTree('basalam-tree', categoriesData.basalam, 'basalam');
                renderTree('snappshop-tree', categoriesData.snappshop, 'snappshop');
            }

            function renderTree(containerId, items, store) {
                const container = document.getElementById(containerId);
                container.innerHTML = items.length ? items.map(item => renderNode(item, store)).join('') : `
                    <div class="flex flex-col items-center justify-center py-20 opacity-20"><i class="material-icons !text-6xl mb-4">search_off</i><p class="font-bold">داده‌ای یافت نشد</p></div>
                `;
            }

            window.toggleNode = function(event, el) {
                event.stopPropagation();
                el.closest('.tree-node').classList.toggle('tree-node-expanded');
            };

            function renderNode(node, store, depth = 0) {
                const hasChildren = node.children && node.children.length > 0;
            const mapping = (store === 'basalam' || store === 'snappshop') ? categoriesData.mappings.find(m => m.source_category_id === node.id) : null;
                const mappedTo = mapping ? categoriesData.digikala_flat.find(d => d.id === mapping.digikala_category_id) : null;

                return `
                    <div class="tree-node">
                        <div class="tree-node-header flex items-center gap-2 p-2.5 hover:bg-slate-50 dark:hover:bg-white/5 rounded-xl transition-all group cursor-pointer"
                             onclick="showDetails(${node.id})">
                            <div onclick="toggleNode(event, this)" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-primary transition-colors">
                                ${hasChildren ? '<span class="material-icons expand-icon text-xs">chevron_left</span>' : ''}
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:text-primary transition-colors">
                                <span class="material-icons text-sm">${hasChildren ? 'folder' : 'label'}</span>
                            </div>
                            <div class="flex-grow min-w-0" style="margin-right: ${depth * 20}px">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-700 dark:text-slate-200 truncate">${node.title}</span>
                                    <span class="text-[9px] font-black bg-slate-200/50 dark:bg-white/10 px-2 py-0.5 rounded-full text-slate-500">${node.product_count}</span>
                                    ${node.vector_source === 'external' ? `<span class="material-icons text-[10px] text-blue-500">auto_awesome</span>` : ''}
                                </div>
                                ${mappedTo ? `
                                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5 mt-1 font-bold">
                                        <span class="material-icons text-[12px]">link</span>
                                        <span>${mappedTo.title}</span>
                                        <span class="px-1.5 py-0.5 bg-emerald-500/10 rounded text-[8px]">${Math.round(mapping.confidence * 100)}%</span>
                                    </div>
                                ` : ''}
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="event.stopPropagation(); showDetails(${node.id})" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 text-slate-400 hover:text-blue-500" title="جزئیات وکتور"><span class="material-icons text-xs">visibility</span></button>
                            <button onclick="event.stopPropagation(); openEditModal(${node.id}, '${node.title}')" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 text-slate-400 hover:text-amber-500" title="ویرایش نام"><span class="material-icons text-xs">edit</span></button>
                            ${(store === 'basalam' || store === 'snappshop') ? `<button onclick="event.stopPropagation(); openMappingModal(${node.id}, '${node.title}')" class="w-7 h-7 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 text-slate-400 hover:text-primary" title="تنظیم نگاشت"><span class="material-icons text-xs">link</span></button>` : ''}
                            </div>
                        </div>
                        <div class="tree-node-children">${hasChildren ? node.children.map(child => renderNode(child, store, depth + 1)).join('') : ''}</div>
                    </div>
                `;
            }

            window.showDetails = async function(id) {
                const content = document.getElementById('details-content');
                content.innerHTML = '<div class="flex justify-center p-10"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div></div>';
                document.getElementById('details-modal').showModal();
                try {
                    const url = '{{ route('dash.admin.categories.details', ['authkey' => $authkey, 'category' => ':id']) }}'.replace(':id', id);
                    const response = await fetch(url);
                    const data = await response.json();
                    content.innerHTML = `
                        <div class="space-y-6">
                            <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl flex items-center justify-between">
                                <div><h4 class="text-[10px] font-bold text-slate-400 mb-1 uppercase tracking-widest">مدل وکتور</h4><div class="font-bold text-sm text-blue-600">${data.category.vector_source} (${data.category.vector_model || 'N/A'})</div></div>
                            </div>
                            <div><h4 class="text-xs font-bold text-slate-400 mb-3 uppercase flex items-center gap-2"><span class="material-icons text-sm text-primary">auto_awesome</span>بردار تولید شده</h4><pre class="p-4 bg-slate-900 text-emerald-400 rounded-2xl text-[10px] font-mono h-40 overflow-y-auto custom-scrollbar">${JSON.stringify(data.category.vector, null, 2)}</pre></div>
                            <div><h4 class="text-xs font-bold text-slate-400 mb-3 uppercase flex items-center gap-2"><span class="material-icons text-sm text-primary">compare_arrows</span>مشابهت در سایر فروشگاه‌ها</h4><div class="space-y-2">${data.similar.map(item => `<div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-white/5 rounded-xl border border-slate-100 dark:border-white/5"><div class="flex items-center gap-2"><span class="text-[8px] bg-slate-200 dark:bg-white/10 px-1.5 py-0.5 rounded text-slate-500 uppercase font-black">${item.store}</span><span class="font-bold text-sm">${item.title}</span></div><div class="flex items-center gap-2"><div class="w-24 h-1.5 bg-slate-200 dark:bg-white/10 rounded-full overflow-hidden"><div class="h-full bg-primary" style="width: ${item.score}%"></div></div><span class="text-[10px] font-black text-primary">${item.score}%</span></div></div>`).join('') || '<p class="text-center py-4 text-slate-400 text-xs italic">موردی یافت نشد</p>'}</div></div>
                        </div>
                    `;
                } catch (e) { content.innerHTML = '<p class="text-red-500 p-10 text-center">خطا در بارگذاری جزئیات</p>'; }
            };

            window.testAi = async function() {
                const text = document.getElementById('ai-test-input').value;
                const btn = document.getElementById('ai-test-btn');
                const result = document.getElementById('ai-test-result');
                if (!text) return;
                btn.disabled = true;
                result.classList.remove('hidden');
                result.innerHTML = 'در حال پردازش...';
                try {
                    const response = await fetch('{{ route('dash.admin.categories.ai_test', ['authkey' => $authkey]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ text })
                    });
                    result.innerHTML = JSON.stringify(await response.json(), null, 2);
                } catch (e) { result.innerHTML = 'خطا در برقراری ارتباط'; }
                btn.disabled = false;
            };

            window.submitSnappImport = async function() {
                const jsonInput = document.getElementById('snapp-json-input');
                const btn = document.getElementById('snapp-submit-btn');
                const json = jsonInput.value.trim();

                if (!json) { myAdminAlert('لطفا محتوای JSON را وارد کنید.'); return; }

                btn.disabled = true;
                btn.innerHTML = '<span class="animate-spin material-icons text-sm">sync</span> در حال پردازش...';

                try {
                    const response = await fetch('{{ route('dash.admin.categories.import.snapp', ['authkey' => $authkey]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: json
                    });
                    const res = await response.json();
                    if (res.ok) {
                        myAdminAlert('ایمپورت با موفقیت انجام شد.');
                        document.getElementById('snapp-import-modal').close();
                        jsonInput.value = '';
                        loadData();
                    } else {
                        myAdminAlert('خطا در پردازش داده‌ها: ' + (res.message || 'نامشخص'));
                    }
                } catch (e) { myAdminAlert('خطا در برقراری ارتباط با سرور'); }

                btn.disabled = false;
                btn.innerHTML = 'شروع عملیات ایمپورت';
            };

            let currentEditId = null;
            window.openEditModal = function(id, title) {
                currentEditId = id;
                const input = document.getElementById('edit-category-title-input');
                input.value = title;
                document.getElementById('edit-category-modal').showModal();
                input.focus();
            };

            window.submitCategoryUpdate = async function() {
                const title = document.getElementById('edit-category-title-input').value.trim();
                const btn = document.getElementById('update-category-btn');
                if (!title) return;

                btn.disabled = true;
                try {
                    const url = '{{ route('dash.admin.categories.update', ['authkey' => $authkey, 'category' => ':id']) }}'.replace(':id', currentEditId);
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ title })
                    });
                    if ((await response.json()).ok) {
                        document.getElementById('edit-category-modal').close();
                        loadData();
                    }
                } catch (e) { myAdminAlert('خطا در بروزرسانی نام'); }
                btn.disabled = false;
            };

            function populateSelect() {
                const select = document.getElementById('modal-digikala-select');
                select.innerHTML = '<option value="">انتخاب دسته‌بندی مقصد در دیجی‌کالا...</option>' + categoriesData.digikala_flat.map(c => `<option value="${c.id}">${c.title}</option>`).join('');
            }

            let currentBasalamId = null;
            window.openMappingModal = function(id, title) {
                currentBasalamId = id;
                document.getElementById('modal-basalam-title').textContent = title;
                const mapping = categoriesData.mappings.find(m => m.source_category_id === id);
                document.getElementById('modal-digikala-select').value = mapping ? mapping.digikala_category_id : "";
                document.getElementById('mapping-modal').showModal();
            };

            window.saveManualMapping = async function() {
                const dkId = document.getElementById('modal-digikala-select').value;
                if (!dkId) { myAdminAlert('لطفا مقصد را انتخاب کنید.'); return; }
                try {
                    const response = await fetch('{{ route('dash.admin.categories.map', ['authkey' => $authkey]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ basalam_category_id: currentBasalamId, digikala_category_id: dkId })
                    });
                    if ((await response.json()).ok) { document.getElementById('mapping-modal').close(); loadData(); }
                } catch (e) { myAdminAlert('خطا در ذخیره'); }
            };

            document.addEventListener('DOMContentLoaded', loadData);
        })();
    </script>
    @endpush
</x-layouts.admin-dashboard>
