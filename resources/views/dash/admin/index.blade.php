<x-layouts.admin-dashboard title="داشبورد ادمین">
    <x-admin.page-header
        title="داشبورد مدیریت"
        description="خلاصه‌ای از معیارهای کلیدی سایت و وضعیت سرویس‌ها"
    />

    <div class="grid gap-4 md:grid-cols-4 mb-8">
        <x-admin.stat-card title="کاربران" :value="$stats['users']" icon="group" color="primary" />
        <x-admin.stat-card title="ادمین‌ها" :value="$stats['admins']" icon="admin_panel_settings" color="success" />
        <x-admin.stat-card title="نظرات در انتظار" :value="$stats['pending_comments']" icon="forum" color="warning" />
        <x-admin.stat-card title="تیکت باز" :value="$stats['open_tickets']" icon="confirmation_number" color="danger" />
    </div>
</x-layouts.admin-dashboard>
