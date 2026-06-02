<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-xl font-black text-slate-800 dark:text-white">بوکمارک‌ها</h1>
                <button
                    type="button"
                    data-modal-target="bookmark-category-modal"
                    data-modal-toggle="bookmark-category-modal"
                    class="btn-subtle px-4 py-2 text-xs"
                >
                    مدیریت دسته‌بندی‌ها
                </button>
            </div>

            <div class="mb-8 flex flex-wrap gap-2 border-b border-slate-100 pb-6 dark:border-white/5">
                <a
                    href="{{ route('dash.user.bookmarks', ['authkey' => $authkey]) }}"
                    class="rounded-full px-4 py-1.5 text-[11px] font-bold transition {{ $activeCategory === null ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 dark:bg-white/5 dark:text-slate-400 dark:hover:bg-white/10' }}"
                >
                    همه
                </a>
                @forelse($categories as $category)
                    <a
                        href="{{ route('dash.user.bookmarks', ['authkey' => $authkey, 'category' => $category]) }}"
                        class="rounded-full px-4 py-1.5 text-[11px] font-bold transition {{ $activeCategory === $category ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'bg-slate-50 text-slate-500 hover:bg-slate-100 dark:bg-white/5 dark:text-slate-400 dark:hover:bg-white/10' }}"
                    >
                        {{ $category }}
                    </a>
                @empty
                    <span class="text-sm text-slate-400">هنوز دسته‌بندی شخصی ایجاد نشده است.</span>
                @endforelse
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @forelse($bookmarks as $bookmark)
                    <div data-bookmark-item class="group flex flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm transition-all hover:border-primary/20 hover:shadow-md dark:border-white/5 dark:bg-slate-800/50">
                        <div class="flex-1 p-5">
                            <a href="{{ route('index') }}/product/{{ $bookmark->product_id }}/info" class="block">
                                <p class="line-clamp-2 h-10 text-sm font-black text-slate-800 transition-colors group-hover:text-primary dark:text-white">{{ $bookmark->product->title ?? 'نام محصول' }}</p>
                            </a>

                            <div class="mt-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-1.5 w-1.5 rounded-full bg-primary/40"></div>
                                    <p data-bookmark-current-category class="text-[10px] font-bold text-slate-400">دسته: {{ $bookmark->category_name }}</p>
                                </div>
                                <span data-bookmark-category-status class="hidden text-[10px] font-black"></span>
                            </div>

                            <form action="{{ route('dash.user.bookmarks.category.update', ['authkey' => $authkey, 'bookmark' => $bookmark->id]) }}" method="POST" class="mt-3" data-bookmark-category-form>
                                @csrf
                                @method('PATCH')
                                <select name="category_name" data-bookmark-category-select class="w-full rounded-xl border border-slate-100 bg-slate-50/50 px-3 py-2 text-[10px] font-black text-slate-600 outline-none transition-all focus:border-primary/30 dark:bg-slate-900 dark:text-slate-400 dark:border-white/5">
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected($bookmark->category_name === $category)>تغییر به: {{ $category }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-5 py-3.5 dark:border-white/5 dark:bg-white/5">
                            <a href="{{ route('index') }}/product/{{ $bookmark->product_id }}/info" class="flex items-center gap-1.5 text-[10px] font-black text-primary hover:underline">
                                <span class="material-icons !text-sm">visibility</span>
                                مشاهده محصول
                            </a>
                            <a
                                class="flex h-8 items-center gap-1.5 rounded-lg bg-warning/10 px-3 text-[10px] font-black text-warning transition-all hover:bg-warning hover:text-white"
                                href="{{ route('dash.user.bookmarks.delete', ['authkey' => $authkey, 'bookmark' => $bookmark->id]) }}"
                            >
                                <span class="material-icons !text-sm">delete</span>
                                حذف
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-squircle border border-dashed border-slate-200 p-10 text-center dark:border-white/10">
                        <p class="text-sm text-slate-400">بوکمارکی ثبت نشده است.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div
        id="bookmark-category-modal"
        tabindex="-1"
        aria-hidden="true"
        class="fixed inset-0 z-50 hidden h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden p-4"
        data-modal-root
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-md" data-modal-overlay="bookmark-category-modal"></div>
        <div class="relative z-10 max-h-full w-full max-w-md" data-modal-content>
            <div class="relative rounded-squircle border border-slate bg-white p-5 shadow-2xl dark:border-white/15 dark:bg-slate dark:shadow-[0_0_0_1px_rgba(255,255,255,0.08),0_20px_50px_rgba(0,0,0,0.7)]">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-slate dark:text-white">دسته‌بندی جدید</h3>
                    <button type="button" data-modal-hide="bookmark-category-modal" class="text-slate hover:text-slate dark:hover:text-white">✕</button>
                </div>
                <form action="{{ route('dash.user.bookmarks.categories.store', ['authkey' => $authkey]) }}" method="POST" class="space-y-3">
                    @csrf
                    <label class="block text-sm text-slate dark:text-slate-200">
                        نام دسته‌بندی
                        <input name="name" maxlength="50" required class="mt-1 w-full rounded-squircle border border-slate bg-transparent px-3 py-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" />
                    </label>
                    <button class="w-full rounded-squircle bg-primary px-3 py-2 text-sm text-white">ذخیره دسته‌بندی</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const forms = document.querySelectorAll('[data-bookmark-category-form]');
            if (!forms.length || !window.axios) {
                return;
            }

            forms.forEach((form) => {
                const select = form.querySelector('[data-bookmark-category-select]');
                const card = form.closest('[data-bookmark-item]');
                const label = card?.querySelector('[data-bookmark-current-category]');
                const status = card?.querySelector('[data-bookmark-category-status]');

                if (!select) {
                    return;
                }

                select.dataset.previousValue = select.value;

                select.addEventListener('change', async () => {
                    const previousValue = select.dataset.previousValue || select.value;

                    select.disabled = true;

                    if (status) {
                        status.textContent = 'در حال ذخیره...';
                        status.classList.remove('hidden', 'text-warning', 'dark:text-warning', 'text-primary', 'dark:text-primary');
                        status.classList.add('text-slate', 'dark:text-slate-200');
                    }

                    try {
                        await window.axios.patch(form.action, {
                            category_name: select.value,
                        }, {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        select.dataset.previousValue = select.value;

                        if (label) {
                            label.textContent = 'دسته: ' + select.value;
                        }

                        if (status) {
                            status.textContent = 'ذخیره شد';
                            status.classList.remove('text-slate', 'dark:text-slate-200');
                            status.classList.add('text-primary', 'dark:text-primary');
                        }
                    } catch (error) {
                        select.value = previousValue;

                        if (status) {
                            status.textContent = 'خطا در بروزرسانی';
                            status.classList.remove('text-slate', 'dark:text-slate-200');
                            status.classList.add('text-warning', 'dark:text-warning');
                        }
                    } finally {
                        select.disabled = false;
                    }
                });
            });
        })();
    </script>
</x-layouts.profile>
