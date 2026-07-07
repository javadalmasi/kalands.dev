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
</x-layouts.admin-dashboard>
