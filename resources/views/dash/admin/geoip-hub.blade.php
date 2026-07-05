<x-layouts.admin-dashboard title="بروزرسانی GeoIP" :helpModuleKey="'geoip'">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="admin-page-title !mb-0">بروزرسانی GeoIP</h1>
            <p class="text-sm text-slate">مدیریت دیتابیس‌های مکان‌دهی IP و گزارش بروزرسانی‌ها</p>
        </div>
        <div class="admin-actions">
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
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="geoip-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-overview">
                <span class="material-icons text-base">info</span>
                <span>وضعیت فعلی</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-logs">
                <span class="material-icons text-base">list_alt</span>
                <span>لاگ‌ها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-settings">
                <span class="material-icons text-base">settings</span>
                <span>تنظیمات</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

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

    <div id="tab-help" class="tab-content hidden space-y-6">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول بروزرسانی GeoIP</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>دیتابیس‌های GeoIP برای شناسایی موقعیت مکانی کاربران بر اساس آدرس IP آن‌ها استفاده می‌شوند. این سیستم به خصوص در ماژول‌های زیر نقش حیاتی دارد:</p>
                            <ul class="list-disc list-inside space-y-2 mt-2 mr-4">
                                <li>شناسایی و تفکیک ربات‌ها و خزنده‌های جستجوگر (در ماژول هوشمندی بازدیدکنندگان)</li>
                                <li>اعمال محدودیت‌های جغرافیایی برای دسترسی به بخش‌های خاص سایت</li>
                                <li>نمایش دکمه‌های افیلیت (مانند دیجی‌کالا و باسلام) صرفاً برای کاربران داخل ایران</li>
                            </ul>
                            <p class="mt-3">در صورتی که بروزرسانی خودکار با شکست مواجه شود، سیستم به طور هوشمند از آخرین نسخه معتبر موجود استفاده خواهد کرد تا اختلالی در تجربه کاربری ایجاد نشود.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-services">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">cloud</span>
                                سرویس‌های منابع
                            </h3>
                            <p>ماژول GeoIP از دو سرویس معتبر برای به‌روزرسانی دیتابیس‌ها استفاده می‌کند:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>MaxMind:</b> ارائه‌دهنده اصلی و دقیق‌ترین دیتابیس GeoIP2</li>
                                <li><b>IPinfo:</b> سرویس تکمیلی با پوشش بالاتر و جزئیات بیشتر</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-schedule">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">schedule</span>
                                زمان‌بندی بروزرسانی
                            </h3>
                            <p>بروزرسانی‌ها به صورت خودکار هر ۵ ساعت از طریق سیستم صف‌ها (Queues) انجام می‌شود. در صورت نیاز به بروزرسانی فوری، می‌توانید از دکمه «بروزرسانی دستی» در بالای صفحه استفاده کنید.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">وضعیت فایل‌ها</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>نمایش وضعیت وجود، حجم و آخرین زمان بروزرسانی هر دیتابیس</li>
                                    <li>لاگ‌های کامل اجراهای قبلی برای عیب‌یابی</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-important">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">warning_amber</span>
                                نکات بسیار مهم
                            </h3>
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-900 text-sm">
                                <p>بروزرسانی دیتابیس‌ها مستلزم دانلود فایل‌های حجیم (حدود ۵۰ تا ۱۰۰ مگابایت) از سرورهای MaxMind است. این فرآیند ممکن است بسته به سرعت سرور و کیفیت ارتباط بین‌المللی بین <b>۳۰ تا ۱۲۰ ثانیه</b> زمان ببرد. لطفاً در هنگام بروزرسانی دستی، تا پایان عملیات مرورگر خود را نبندید.</p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-services" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">سرویس‌های منابع</a>
                        <a href="#doc-schedule" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">زمان‌بندی بروزرسانی</a>
                        <a href="#doc-important" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نکات مهم</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-geoip-hub.js'])
