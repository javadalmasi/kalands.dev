<x-layouts.admin-dashboard title="مپینگ شناسه‌های محصول">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <h1 class="admin-page-title !mb-0">مپینگ شناسه‌های محصول (تغییر شناسه)</h1>
    </div>

    <div class="admin-card mb-6">
        <h2 class="mb-4 text-lg font-bold text-slate-800 dark:text-white">ایجاد/ویرایش مپینگ</h2>
        <form action="{{ route('dash.admin.product_mappings.store', ['authkey' => $authkey]) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            @csrf
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">شناسه قدیمی (منتشر شده)</label>
                <input type="text" name="old_product_id" required class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="مثال: 21896165">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">شناسه جدید (مقصد)</label>
                <input type="text" name="new_product_id" required class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="مثال: 21942327">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">فروشگاه</label>
                <select name="store" required class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <option value="digikala">دیجی‌کالا</option>
                    <option value="basalam">باسلام</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">دلیل (اختیاری)</label>
                <input type="text" name="reason" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="مثال: تغییر شناسه در وب‌سرویس 301">
            </div>
            <div class="flex items-end">
                <button type="submit" class="admin-btn w-full justify-center h-[38px]">
                    <span class="material-icons !text-base">add_link</span>
                    ثبت مپینگ
                </button>
            </div>
        </form>
    </div>

    <form action="{{ route('dash.admin.product_mappings', ['authkey' => $authkey]) }}" method="GET" class="admin-card mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="space-y-1">
            <label class="text-[10px] font-bold opacity-60 px-1">جستجو (شناسه یا نام)</label>
            <input type="text" name="q" value="{{ $q }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="جستجو...">
        </div>
        <div class="space-y-1">
            <label class="text-[10px] font-bold opacity-60 px-1">فروشگاه</label>
            <select name="store" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="all" {{ $store === 'all' ? 'selected' : '' }}>همه</option>
                <option value="digikala" {{ $store === 'digikala' ? 'selected' : '' }}>دیجی‌کالا</option>
                <option value="basalam" {{ $store === 'basalam' ? 'selected' : '' }}>باسلام</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="admin-btn w-full justify-center h-[38px]">
                <span class="material-icons !text-base">filter_alt</span>
                فیلتر
            </button>
        </div>
    </form>

    <form action="{{ route('dash.admin.product_mappings', ['authkey' => $authkey]) }}" method="GET" class="space-y-3">
        @forelse($mappings as $mapping)
            <div class="admin-card is-surface !p-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="min-w-0">
                            <div class="font-bold text-slate text-sm truncate">
                                {{ $mapping->oldProduct->title ?? '—' }}
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                <span class="admin-ltr font-mono">#{{ $mapping->old_product_id }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-bold {{ $mapping->store === 'digikala' ? 'bg-red-500/10 text-red-600' : 'bg-green-600/10 text-green-700' }}">
                                    {{ $mapping->store === 'digikala' ? 'دیجی‌کالا' : 'باسلام' }}
                                </span>
                                @if($mapping->reason)
                                    <span class="flex items-center gap-1 opacity-70">
                                        <span class="material-icons !text-xs">info</span>
                                        {{ $mapping->reason }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-center md:flex-1">
                        <span class="material-icons text-3xl text-primary !leading-none">arrow_forward</span>
                    </div>

                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="min-w-0">
                            <div class="font-bold text-slate text-sm truncate">
                                {{ $mapping->newProduct->title ?? '—' }}
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                <span class="admin-ltr font-mono">#{{ $mapping->new_product_id }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-bold bg-primary/10 text-primary">
                                    مقصد
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 border-t border-slate/5 pt-3 md:border-0 md:pt-0">
                        <form action="{{ route('dash.admin.product_mappings.delete', ['authkey' => $authkey, 'mapping' => $mapping->id]) }}" method="POST" onsubmit="return confirm('آیا از حذف این مپینگ مطمئن هستید؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-secondary !p-2" title="حذف مپینگ">
                                <span class="material-icons !text-base">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="admin-card text-center py-10 opacity-50">
                <span class="material-icons text-4xl block mb-2">link_off</span>
                مپینگی یافت نشد.
            </div>
        @endforelse

        <div class="mt-6">
            {{ $mappings->links('vendor.pagination.admin') }}
        </div>
    </form>
</x-layouts.admin-dashboard>