<x-layouts.admin-dashboard title="ماژول سوالات متداول" :helpModuleKey="'faq'">
    @php($authkey = request()->route('authkey'))
    @vite('resources/js/admin-faq-hub.js')

    <x-admin.page-header title="ماژول سوالات متداول" />

    <x-admin.tab-bar id="faq-tabs">
        <button type="button" class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="faq-tab-create">
            <span class="material-icons text-base">add_circle_outline</span>
            <span>افزودن آیتم</span>
        </button>
        <button type="button" class="admin-tab-btn" data-tab-target="faq-tab-manage">
            <span class="material-icons text-base">view_list</span>
            <span>مدیریت آیتم‌ها</span>
        </button>
    </x-admin.tab-bar>

    <div id="faq-tab-create" class="tab-content admin-card p-4">
            <form action="{{ route('dash.admin.faq.store', ['authkey' => $authkey]) }}" method="POST" class="space-y-3">
                @csrf
                <h2 class="font-bold">افزودن سوال جدید</h2>
                <input name="title" value="{{ old('title') }}" class="admin-input" placeholder="عنوان سوال">
                <textarea name="description" rows="6" class="admin-input" data-rich-editor>{{ old('description') }}</textarea>
                <div class="grid gap-3 md:grid-cols-2">
                    <input type="number" value="{{ $nextSortOrder }}" class="admin-input opacity-80" placeholder="ترتیب نمایش" readonly>
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
                <x-admin.filter-bar method="GET" :cols="3">
                    <x-admin.filter-field label="جستجو" :span="2">
                        <input name="q" value="{{ $q }}" class="admin-input" placeholder="عنوان یا متن سوال">
                    </x-admin.filter-field>
                    <x-admin.filter-field label="">
                        <button class="admin-btn w-full justify-center"><span class="material-icons">search</span>اعمال فیلتر</button>
                    </x-admin.filter-field>
                </x-admin.filter-bar>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 text-xs cursor-pointer bg-slate/5 px-3 py-2 rounded-lg border border-slate/10">
                            <input type="checkbox" id="select-all-faqs" class="rounded border-slate/30">
                            <span class="font-bold opacity-70">انتخاب همه</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <select id="faq-bulk-action" class="admin-input text-xs">
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
                <input type="text" id="faq-local-search" class="admin-input mt-3" placeholder="جستجوی سریع بین آیتم‌های همین صفحه...">
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
                                <input name="title" value="{{ $faq->title }}" class="admin-input">
                                <textarea name="description" rows="6" class="admin-input" data-rich-editor>{{ $faq->description }}</textarea>
                                <div class="grid gap-3 md:grid-cols-2">
                                    <input type="number" min="0" name="sort_order" value="{{ $faq->sort_order }}" class="admin-input">
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

            <x-admin.pagination :paginator="$faqs" />
        </div>
    </div>


</x-layouts.admin-dashboard>
