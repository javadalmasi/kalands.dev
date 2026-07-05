<x-layouts.admin-dashboard title="داشبورد ادمین">
    <div>
        <h1 class="admin-page-title">داشبورد مدیریت</h1>
        <p class="text-sm text-slate/60 mb-6">خلاصه‌ای از معیارهای کلیدی سایت و وضعیت سرویس‌ها</p>
    </div>

    <div class="grid gap-4 md:grid-cols-4 mb-8">
        <div class="admin-card border-l-4 border-l-primary/30">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate/60 mb-1">کاربران</p>
                    <p class="text-3xl font-bold text-slate">{{ $stats['users'] }}</p>
                </div>
                <span class="material-icons text-primary/50">group</span>
            </div>
        </div>

        <div class="admin-card border-l-4 border-l-success/30">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate/60 mb-1">ادمین‌ها</p>
                    <p class="text-3xl font-bold text-slate">{{ $stats['admins'] }}</p>
                </div>
                <span class="material-icons text-success/50">admin_panel_settings</span>
            </div>
        </div>

        <div class="admin-card border-l-4 border-l-warning/30">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate/60 mb-1">نظرات در انتظار</p>
                    <p class="text-3xl font-bold text-slate">{{ $stats['pending_comments'] }}</p>
                </div>
                <span class="material-icons text-warning/50">forum</span>
            </div>
        </div>

        <div class="admin-card border-l-4 border-l-error/30">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs text-slate/60 mb-1">تیکت باز</p>
                    <p class="text-3xl font-bold text-slate">{{ $stats['open_tickets'] }}</p>
                </div>
                <span class="material-icons text-error/50">confirmation_number</span>
            </div>
        </div>
    </div>

    <div>
        <h2 class="text-sm font-bold text-slate/50 uppercase tracking-wider mb-4">وضعیت سرویس‌ها</h2>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <div class="admin-card">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-xs text-slate/60 mb-1">{{ $service['name'] }}</p>
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2.5 w-2.5 rounded-full {{ $service['status'] === 'ok' ? 'bg-success' : ($service['status'] === 'warning' ? 'bg-warning' : 'bg-error') }}"></span>
                                <span class="text-xs font-medium text-slate/50">
                                    {{ $service['status'] === 'ok' ? 'فعال' : ($service['status'] === 'warning' ? 'هشدار' : 'خرابی') }}
                                </span>
                            </div>
                        </div>
                        <span class="material-icons text-lg text-slate/30">{{ $service['icon'] }}</span>
                    </div>

                    <div class="bg-slate/5 rounded-lg p-3 mb-3">
                        <p class="text-xs text-slate/50">{{ $service['metricLabel'] }}</p>
                        <p class="text-lg font-bold text-slate truncate">{{ $service['metric'] }}</p>
                    </div>

                    <p class="text-xs text-slate/50 leading-relaxed">{{ $service['details'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin-dashboard>
