<x-layouts.admin-dashboard title="ماژول‌ها">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">ماژول‌ها</h1>
    <p class="mb-4 text-sm text-slate">برای هر ماژول روی تنظیمات بزنید تا صفحه اختصاصی همان ماژول باز شود.</p>

    <div class="space-y-3">
        @foreach($modules as $module)
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
</x-layouts.admin-dashboard>
