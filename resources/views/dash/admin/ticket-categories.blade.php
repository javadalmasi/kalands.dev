<x-layouts.admin-dashboard title="دسته‌بندی تیکت">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">دسته‌بندی تیکت</h1>

    <form action="{{ route('dash.admin.ticket.categories.store', ['authkey' => $authkey]) }}" method="POST" class="admin-card mb-5 grid gap-2 md:grid-cols-3">
        @csrf
        <input name="name" placeholder="نام" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <input name="slug" placeholder="slug" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
        <button class="admin-btn"><span class="material-icons">add</span>افزودن</button>
    </form>

    <div class="space-y-2">
        @foreach($categories as $category)
            <div class="admin-card flex items-center justify-between">
                <div>
                    <p>{{ $category->name }}</p>
                    <p class="text-xs text-slate">{{ $category->slug }}</p>
                </div>
                <form action="{{ route('dash.admin.ticket.categories.toggle', ['authkey' => $authkey, 'category' => $category->id]) }}" method="POST">
                    @csrf
                    <button class="admin-btn">
                        <span class="material-icons">{{ $category->is_active ? 'toggle_on' : 'toggle_off' }}</span>
                        {{ $category->is_active ? 'فعال' : 'غیرفعال' }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</x-layouts.admin-dashboard>
