<x-layouts.admin-dashboard title="ماژول سوالات متداول">
    @php($authkey = request()->route('authkey'))
    @vite('resources/js/admin-faq-hub.js')

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">ماژول سوالات متداول</h1>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="faq-tabs">
            <button type="button" class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="faq-tab-create">
                <span class="material-icons text-base">add_circle_outline</span>
                <span>افزودن آیتم</span>
            </button>
            <button type="button" class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="faq-tab-manage">
                <span class="material-icons text-base">view_list</span>
                <span>مدیریت آیتم‌ها</span>
            </button>
            <button type="button" class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="faq-tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <div id="faq-tab-create" class="tab-content admin-card p-4">
            <form action="{{ route('dash.admin.faq.store', ['authkey' => $authkey]) }}" method="POST" class="space-y-3">
                @csrf
                <h2 class="font-bold">افزودن سوال جدید</h2>
                <input name="title" value="{{ old('title') }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="عنوان سوال">
                <textarea name="description" rows="6" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" data-rich-editor>{{ old('description') }}</textarea>
                <div class="grid gap-3 md:grid-cols-2">
                    <input type="number" value="{{ $nextSortOrder }}" class="w-full rounded-lg border border-slate p-2 text-sm opacity-80 dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="ترتیب نمایش" readonly>
                    <label class="flex items-center justify-between text-sm">
                        <span>فعال باشد</span>
                        <span class="admin-switch !w-9 !h-5">
                            <input type="checkbox" name="is_active" value="1" class="admin-switch-input" checked>
                            <span class="admin-switch-track"></span><span class="admin-switch-ball"></span>
                        </span>
                    </label>
                </div>
                <button class="admin-btn admin-btn-primary"><span class="material-icons">add</span>افزودن آیتم</button>
            </form>
    </div>

    <div id="faq-tab-manage" class="tab-content hidden space-y-3">
            <div class="admin-card space-y-3">
                <form method="GET" class="grid gap-3 md:grid-cols-3">
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-[10px] font-bold opacity-60 px-1">جستجو</label>
                        <input name="q" value="{{ $q }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="عنوان یا متن سوال">
                    </div>
                    <div class="flex items-end">
                        <button class="admin-btn w-full justify-center"><span class="material-icons">search</span>اعمال فیلتر</button>
                    </div>
                </form>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-xs cursor-pointer bg-slate/5 px-3 py-2 rounded-lg border border-slate/10">
                            <input type="checkbox" id="select-all-faqs" class="rounded border-slate/30">
                            <span class="font-bold opacity-70">انتخاب همه</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <select id="faq-bulk-action" class="rounded-lg border border-slate/20 bg-white p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="activate">فعال‌سازی</option>
                                <option value="deactivate">غیرفعال‌سازی</option>
                                <option value="delete">حذف</option>
                            </select>
                            <button type="button" id="faq-bulk-run" class="admin-btn admin-btn-secondary !py-2"><span class="material-icons !text-sm">done_all</span>اقدام گروهی</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="faq-save-order" class="admin-btn admin-btn-primary !py-2"><span class="material-icons !text-sm">save</span>ثبت تغییرات</button>
                    </div>
                </div>
                <input type="text" id="faq-local-search" class="mt-3 w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="جستجوی سریع بین آیتم‌های همین صفحه...">
            </div>

            <form id="faq-bulk-form" action="{{ route('dash.admin.faq.bulk', ['authkey' => $authkey]) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="action" id="faq-bulk-action-input" value="activate">
            </form>

            <div id="faq-sortable-list" data-reorder-url="{{ route('dash.admin.faq.reorder', ['authkey' => $authkey]) }}" class="space-y-2 admin-card">
                @forelse($faqs as $faq)
                    <div class="border border-slate/15 rounded-lg p-3 faq-item" data-faq-id="{{ $faq->id }}" data-faq-search="{{ mb_strtolower(trim($faq->title.' '.strip_tags($faq->description))) }}">
                        <div class="w-full flex items-center justify-between gap-2 text-right">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="font-bold truncate">{{ $faq->title }}</span>
                                <span class="text-[11px] opacity-70">#{{ $faq->sort_order }}</span>
                                <span class="material-icons text-slate cursor-move faq-drag-handle">drag_indicator</span>
                                <button type="button" class="material-icons faq-expand-trigger faq-expand-icon">expand_more</button>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <input type="checkbox" class="faq-item-checkbox rounded border-slate/30" value="{{ $faq->id }}">
                                <button type="button" class="admin-btn admin-btn-secondary !py-1.5 faq-quick-toggle" data-toggle-url="{{ route('dash.admin.faq.toggle', ['authkey' => $authkey, 'faq' => $faq->id]) }}">
                                    <span class="material-icons !text-sm">{{ $faq->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                                    {{ $faq->is_active ? 'غیرفعال' : 'فعال' }}
                                </button>
                                <form action="{{ route('dash.admin.faq.delete', ['authkey' => $authkey, 'faq' => $faq->id]) }}" method="POST" data-admin-confirm="این آیتم حذف شود؟">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-btn admin-btn-danger !py-1.5" type="submit"><span class="material-icons !text-sm">delete</span>حذف</button>
                                </form>
                            </div>
                        </div>

                        <div class="hidden pt-3 faq-expand-body">
                            <form action="{{ route('dash.admin.faq.update', ['authkey' => $authkey, 'faq' => $faq->id]) }}" method="POST" class="space-y-3">
                                @csrf
                                <input name="title" value="{{ $faq->title }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <textarea name="description" rows="6" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" data-rich-editor>{{ $faq->description }}</textarea>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <input type="number" min="0" name="sort_order" value="{{ $faq->sort_order }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <label class="flex items-center justify-between text-sm">
                                        <span>فعال باشد</span>
                                        <span class="admin-switch !w-9 !h-5">
                                            <input type="checkbox" name="is_active" value="1" class="admin-switch-input" @checked($faq->is_active)>
                                            <span class="admin-switch-track"></span><span class="admin-switch-ball"></span>
                                        </span>
                                    </label>
                                </div>
                                <div class="admin-actions">
                                    <button class="admin-btn admin-btn-primary"><span class="material-icons">save</span>ذخیره تغییرات</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="admin-card text-sm opacity-70">آیتمی ثبت نشده است.</div>
                @endforelse
            </div>

            <div class="mt-4">{{ $faqs->links() }}</div>
        </div>
    </div>

    <div id="faq-tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول سوالات متداول</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>ماژول سوالات متداول، سامانه مدیریت سوال و جواب‌های پرتکرار در صفحه FAQ سایت است. این سامانه قابلیت افزودن، ویرایش، حذف و مرتب‌سازی با درگ اند دراپ را فراهم می‌کند.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-create">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">add_circle_outline</span>
                                افزودن سوال
                            </h3>
                            <p>برای افزودن سوال جدید، در تب افزودن آیتم موارد زیر را تکمیل کنید:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>عنوان سوال:</b> سؤال کوتاه و واضح</li>
                                <li><b>توضیحات:</b> پاسخ دقیق و کامل به سؤال</li>
                                <li><b>ترتیب نمایش:</b> عددی برای مرتب‌سازی در صفحه</li>
                                <li><b>وضعیت فعال:</b> در صورت فعال بودن در سایت نمایش داده می‌شود</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-manage">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">view_list</span>
                                مدیریت آیتم‌ها
                            </h3>
                            <p>در تب مدیریت آیتم‌ها می‌توانید:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li>سوالات را به صورت جستجوی سریع پیدا کنید</li>
                                <li>آیتم‌ها را با کشیدن و رها کردن مرتب کنید</li>
                                <li>عملیات دسته‌جمعی انجام دهید (فعال‌سازی، غیرفعال‌سازی، حذف)</li>
                                <li>هر آیتم را ویرایش یا حذف کنید</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-create" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">افزودن سوال</a>
                        <a href="#doc-manage" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">مدیریت آیتم‌ها</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

</x-layouts.admin-dashboard>
