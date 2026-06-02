<x-layouts.admin-dashboard title="داشبورد ادمین">
    <h1 class="admin-page-title">داشبورد مدیریت</h1>
    <div class="grid gap-4 md:grid-cols-4">
        <div class="admin-card">
            <p class="text-xs text-slate">کاربران</p>
            <p class="text-2xl font-bold">{{ $stats['users'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs text-slate">ادمین‌ها</p>
            <p class="text-2xl font-bold">{{ $stats['admins'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs text-slate">نظرات در انتظار</p>
            <p class="text-2xl font-bold">{{ $stats['pending_comments'] }}</p>
        </div>
        <div class="admin-card">
            <p class="text-xs text-slate">تیکت باز</p>
            <p class="text-2xl font-bold">{{ $stats['open_tickets'] }}</p>
        </div>
    </div>
</x-layouts.admin-dashboard>
