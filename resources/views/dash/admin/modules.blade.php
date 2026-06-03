<x-layouts.admin-dashboard title="ماژول‌ها">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">ماژول‌ها</h1>
    <p class="mb-4 text-sm text-slate">برای هر ماژول روی تنظیمات بزنید تا صفحه اختصاصی همان ماژول باز شود.</p>

    <div class="flex flex-wrap items-center gap-2 mb-6" id="module-filters">
        <button class="admin-btn admin-btn-primary !text-xs px-4 py-2" data-filter="all">
            همه
        </button>
        @foreach($groupLabels as $key => $label)
            <button class="admin-btn !text-xs px-4 py-2 bg-slate-100 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate hover:bg-primary hover:text-white hover:border-primary transition-all" data-filter="{{ $key }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="space-y-6" id="module-groups">
        @foreach($groupLabels as $groupKey => $groupLabel)
            @if(isset($grouped[$groupKey]) && count($grouped[$groupKey]))
                <div class="module-group" data-category="{{ $groupKey }}">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-lg text-slate/50">{{ $groupIcons[$groupKey] }}</span>
                        <h2 class="text-sm font-bold text-slate/50 uppercase tracking-wider">{{ $groupLabel }}</h2>
                        <span class="text-xs text-slate/30">({{ count($grouped[$groupKey]) }} ماژول)</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($grouped[$groupKey] as $module)
                            <div class="admin-card is-surface flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between text-right" dir="rtl">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="h-12 w-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate shrink-0 border border-slate-200 dark:border-white/5">
                                        <span class="material-symbols-outlined !text-2xl">{{ $module['icon'] }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h2 class="font-bold text-slate text-base">{{ $module['label'] }}</h2>
                                        <p class="mt-0.5 text-xs text-slate/60">{{ $module['description'] }}</p>
                                    </div>
                                </div>
                                <div class="admin-actions lg:justify-end shrink-0">
                                    <a href="{{ route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => $module['key']]) }}" class="admin-btn admin-btn-secondary !text-xs px-4">
                                        <span class="material-icons !text-base">settings</span>
                                        <span>تنظیمات</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    @push('scripts')
        <script>
            (function() {
                const filterEl = document.getElementById('module-filters');
                const groups = document.querySelectorAll('#module-groups .module-group');
                const buttons = filterEl.querySelectorAll('[data-filter]');

                function updateFilter(selected) {
                    buttons.forEach(btn => {
                        const filter = btn.getAttribute('data-filter');
                        const isActive = filter === selected;
                        btn.classList.toggle('admin-btn-primary', isActive);
                        btn.classList.toggle('bg-slate-100', !isActive);
                        btn.classList.toggle('dark:bg-white/5', !isActive);
                        btn.classList.toggle('border-slate-200', !isActive);
                        btn.classList.toggle('dark:border-white/10', !isActive);
                        btn.classList.toggle('text-slate', !isActive);
                        btn.classList.toggle('hover:bg-primary', !isActive);
                        btn.classList.toggle('hover:text-white', !isActive);
                        btn.classList.toggle('hover:border-primary', !isActive);
                    });

                    groups.forEach(group => {
                        const category = group.getAttribute('data-category');
                        if (selected === 'all' || selected === category) {
                            group.style.display = '';
                        } else {
                            group.style.display = 'none';
                        }
                    });
                }

                filterEl.addEventListener('click', (e) => {
                    const btn = e.target.closest('[data-filter]');
                    if (!btn) return;
                    e.preventDefault();
                    const filter = btn.getAttribute('data-filter');
                    updateFilter(filter);
                });

                updateFilter('all');
            })();
        </script>
    @endpush
</x-layouts.admin-dashboard>
