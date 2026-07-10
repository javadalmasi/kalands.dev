<x-layouts.admin-dashboard title="بروزرسانی GeoIP" :helpModuleKey="'geoip'">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="بروزرسانی GeoIP" description="مدیریت دیتابیس‌های مکان‌دهی IP و گزارش بروزرسانی‌ها">
        <x-slot:actions>
            <a href="{{ route('dash.admin.modules', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary">
                <span class="material-icons">arrow_forward</span>
                بازگشت به ماژول‌ها
            </a>
            <form action="{{ route('dash.admin.geoip.update', ['authkey' => $authkey]) }}" method="POST">
                @csrf
                <button type="submit" class="admin-btn admin-btn-primary">
                    <span class="material-icons">refresh</span>
                    بروزرسانی دستی
                </button>
            </form>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="geoip-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-overview">
            <span class="material-icons text-base">info</span>
            <span>وضعیت فعلی</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-logs">
            <span class="material-icons text-base">list_alt</span>
            <span>لاگ‌ها</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-settings">
            <span class="material-icons text-base">settings</span>
            <span>تنظیمات</span>
        </button>
    </x-admin.tab-bar>

    <div id="tab-overview" class="tab-content space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($filesInfo as $name => $info)
                <div class="admin-card">
                    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate/10">
                        <span class="material-icons text-primary">storage</span>
                        <h3 class="font-bold text-slate">{{ $name }}</h3>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-slate/60">وضعیت:</span>
                            @if($info['exists'])
                                <span class="px-3 py-1 rounded-full bg-success/10 text-success text-[10px] font-bold">موجود</span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-danger/10 text-danger text-[10px] font-bold">ناموجود</span>
                            @endif
                        </div>
                        @if($info['exists'])
                            <div class="flex justify-between items-center">
                                <span class="text-slate/60">حجم فایل:</span>
                                <span class="font-medium text-slate">{{ $info['size'] }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate/60">آخرین تغییرات:</span>
                                <span dir="ltr" class="font-medium text-slate text-xs">{{ $info['updated_at'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="admin-card">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate/10">
                <span class="material-icons text-primary">schedule</span>
                <h3 class="font-bold text-slate">زمان‌بندی بروزرسانی</h3>
            </div>
            <p class="text-sm text-slate/70 mb-6 leading-relaxed">این ماژول به صورت خودکار هر ۵ ساعت یکبار از طریق سیستم صف‌ها (Queues) بروزرسانی می‌شود تا همواره دقیق‌ترین اطلاعات موقعیت مکانی در دسترس باشد.</p>
            <div class="bg-slate/5 rounded-lg p-4 flex justify-between items-center">
                <span class="text-sm font-medium text-slate">آخرین زمان اجرا (توسط سیستم):</span>
                <span dir="ltr" class="text-sm font-bold text-primary">{{ $lastRun ?? 'هرگز اجرا نشده' }}</span>
            </div>
        </div>
    </div>

    <div id="tab-logs" class="tab-content hidden space-y-4">
        <div class="admin-card p-0 overflow-hidden">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="p-4 text-right">زمان اجرا</th>
                        <th class="p-4 text-center">وضعیت نهایی</th>
                        <th class="p-4 text-right">جزئیات عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate/5">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate/5 transition-colors">
                            <td class="p-4">
                                <div dir="ltr" class="text-xs font-bold text-slate">{{ $log['executed_at'] }}</div>
                            </td>
                            <td class="p-4 text-center">
                                @if($log['status'] === 'success')
                                    <span class="admin-badge is-success">موفق</span>
                                @else
                                    <span class="admin-badge is-danger">ناموفق</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="space-y-2">
                                    @foreach($log['details'] as $fileName => $detail)
                                        <div class="text-[11px] flex items-center gap-2">
                                            @if($detail['status'] === 'success')
                                                <span class="material-icons text-success text-sm">check_circle</span>
                                            @else
                                                <span class="material-icons text-danger text-sm">error</span>
                                            @endif
                                            <span class="text-slate-700 dark:text-slate-300">{{ $detail['message'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-12 text-slate/50">
                                <span class="material-icons text-4xl block mb-2 opacity-20">history_toggle_off</span>
                                هیچ لاگی در سیستم ثبت نشده است.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-settings" class="tab-content hidden space-y-6">
        <div class="admin-card">
            <h3 class="font-bold text-slate mb-4">تنظیمات GeoIP</h3>
            <p class="text-sm text-slate/60">تنظیمات این ماژول در حال حاضر فقط برای نمایش وضعیت فایل‌ها است.</p>
        </div>
    </div>

    @vite(['resources/js/admin-geoip-hub.js'])
</x-layouts.admin-dashboard>
