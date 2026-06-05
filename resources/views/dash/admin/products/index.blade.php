<x-layouts.admin-dashboard title="مدیریت محصولات">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت محصولات</h1>
    </div>

    <form action="{{ route('dash.admin.products', ['authkey' => $authkey]) }}" method="GET" class="admin-card mb-6 grid grid-cols-1 gap-4 md:grid-cols-4 lg:grid-cols-5">
        <div class="space-y-1">
            <label class="text-[10px] font-bold opacity-60 px-1">جستجو (نام یا شناسه)</label>
            <input type="text" name="q" value="{{ $q }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="مثلاً: گوشی موبایل...">
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold opacity-60 px-1">فروشگاه</label>
            <select name="store" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="all" {{ $store === 'all' ? 'selected' : '' }}>همه</option>
                <option value="digikala" {{ $store === 'digikala' ? 'selected' : '' }}>دیجی‌کالا</option>
                <option value="basalam" {{ $store === 'basalam' ? 'selected' : '' }}>باسلام</option>
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold opacity-60 px-1">وضعیت</label>
            <select name="status" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>همه</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>فعال</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
            </select>
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold opacity-60 px-1">مرتب‌سازی</label>
            <select name="sort" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>جدیدترین</option>
                <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>قدیمی‌ترین</option>
                <option value="id_asc" {{ $sort === 'id_asc' ? 'selected' : '' }}>شناسه (صعودی)</option>
                <option value="id_desc" {{ $sort === 'id_desc' ? 'selected' : '' }}>شناسه (نزولی)</option>
                <option value="api_inactive" {{ $sort === 'api_inactive' ? 'selected' : '' }}>غیرفعال در وب‌سرویس</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="admin-btn w-full justify-center h-[38px]">
                <span class="material-icons !text-base">filter_alt</span>
                فیلتر
            </button>
        </div>
    </form>

    <form action="{{ route('dash.admin.products.bulk', ['authkey' => $authkey]) }}" method="POST" id="bulk-action-form">
        @csrf
        <div class="admin-card mb-4 flex flex-wrap items-center justify-between gap-4 bg-primary/5 border-primary/20">
            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2 text-xs cursor-pointer bg-slate/5 px-3 py-2 rounded-lg border border-slate/10 hover:bg-slate/10 transition-colors">
                    <input type="checkbox" id="select-all" class="rounded border-slate/30">
                    <span class="font-bold opacity-70">انتخاب همه</span>
                </label>
                <div class="h-8 w-px bg-slate/10"></div>
                <div class="flex items-center gap-2">
                    <select name="action" class="rounded-lg border border-slate/20 bg-white p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                        <option value="">عملیات گروهی...</option>
                        <option value="activate">فعالسازی</option>
                        <option value="deactivate">غیرفعالسازی</option>
                        <option value="delete">حذف قطعی</option>
                    </select>
                    <button type="submit" class="admin-btn !py-2" data-admin-confirm="آیا از انجام این عملیات روی محصولات انتخاب شده مطمئن هستید؟">
                        <span class="material-icons !text-sm">done_all</span>
                        اجرا
                    </button>
                </div>
            </div>
            <div class="text-[10px] font-bold opacity-40">
                تعداد کل محصولات: {{ $products->total() }}
            </div>
        </div>

        <div class="space-y-3">
            @forelse($products as $product)
                <div class="admin-card is-surface !p-4">
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox rounded border-slate-300">
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate text-sm truncate" title="{{ $product->title }}">{{ $product->title }}</h3>
                                <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                    <span class="admin-ltr font-mono">#{{ $product->id }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-bold {{ $product->store === 'digikala' ? 'bg-red-500/10 text-red-600' : 'bg-green-600/10 text-green-700' }}">
                                        {{ $product->store === 'digikala' ? 'دیجی‌کالا' : 'باسلام' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span class="material-icons !text-xs">schedule</span>
                                        آخرین بررسی: {{ persianDateTime($product->last_checked_at, 'Y/m/d H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 border-t border-slate/5 pt-3 md:border-0 md:pt-0">
                            <div class="flex flex-col gap-1 items-center">
                                <span class="text-[9px] font-bold opacity-40">وضعیت سیستم</span>
                                <label class="admin-switch"><input type="checkbox" class="admin-switch-input product-status-toggle" data-id="{{ $product->id }}" data-url="{{ route('dash.admin.products.toggle', ['authkey' => $authkey, 'product' => $product->id]) }}" @checked($product->is_active)><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                            </div>

                            <div class="flex flex-col gap-1 items-center">
                                <span class="text-[9px] font-bold opacity-40">وضعیت API</span>
                                @php($apiInactive = data_get($product->api_status, 'data.product.is_inactive'))
                                @if($product->last_checked_at)
                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[10px] font-bold {{ $apiInactive ? 'bg-amber-500/10 text-amber-600' : 'bg-primary/10 text-primary' }}">
                                        {{ $apiInactive ? 'غیرفعال' : 'فعال' }}
                                    </span>
                                @else
                                    <span class="text-[10px] opacity-30">---</span>
                                @endif
                            </div>

                            <div class="flex-1 md:flex-none flex justify-end gap-2">
                                <a href="{{ url('/product/' . ($product->store === 'basalam' ? 'XBS-' : '') . $product->id) }}" target="_blank" class="admin-btn admin-btn-secondary !p-2" title="مشاهده محصول">
                                    <span class="material-icons !text-base">open_in_new</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="admin-card text-center py-10 opacity-50">
                    <span class="material-icons text-4xl block mb-2">inventory_2</span>
                    محصولی یافت نشد.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $products->links('vendor.pagination.admin') }}
        </div>
    </form>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-products-hub.js'])
