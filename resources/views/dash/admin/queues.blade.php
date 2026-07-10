<x-layouts.admin-dashboard title="مدیریت صف‌ها" :helpModuleKey="'queues'">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="مدیریت صف‌ها">
        <x-slot:actions>
            <button type="submit" form="queue-settings-form" class="admin-btn admin-btn-primary px-6 shadow-lg shadow-primary/20">
                <span class="material-icons">save</span>
                ذخیره تمامی تنظیمات
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="queue-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-logs">
            <span class="material-icons text-base">history</span>
            <span>گزارش اجراها</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-config">
            <span class="material-icons text-base">settings</span>
            <span>تنظیمات و دستورات</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-driver">
            <span class="material-icons text-base">storage</span>
            <span>انتخاب درایور</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-maintenance">
            <span class="material-icons text-base">cleaning_services</span>
            <span>پاکسازی خودکار</span>
        </button>
    </x-admin.tab-bar>

    <form action="{{ route('dash.admin.queues.save', ['authkey' => $authkey]) }}" method="POST" id="queue-settings-form">
        @csrf

        <div id="tab-logs" class="tab-content space-y-6">
            <div class="admin-card">
                <h2 class="mb-4 font-bold flex items-center justify-between">
                    <div class="flex items-center gap-2"><span class="material-icons text-primary">history</span>گزارش ۱۰۰ اجرای آخر</div>
                    <span class="text-[10px] font-normal text-slate">برای مشاهده جزئیات عملیات کلیک کنید</span>
                </h2>
                <div class="space-y-3 max-h-[700px] overflow-y-auto pr-2 custom-scrollbar">
                    @forelse($lastRuns as $run)
                        <div class="rounded-xl border border-slate bg-slate/10 p-4 hover:bg-slate/20 transition-all cursor-pointer group dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10" onclick="toggleLogMeta(this)">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="material-icons text-sm text-slate">schedule</span>
                                    <span class="text-xs font-bold admin-ltr">{{ $run->persian_executed_at }}</span>
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $run->status === 'success' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                    {{ $run->status === 'success' ? 'موفق' : 'ناموفق' }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 text-[11px] text-slate mb-1">
                                <span class="material-icons text-xs">info</span>
                                <span>خلاصه وضعیت: {{ $run->status === 'success' ? 'عملیات با موفقیت پایان یافت.' : 'برخی پردازش‌ها با خطا مواجه شدند.' }}</span>
                            </div>

                            @if($run->error_message)
                                <div class="bg-danger/5 border border-danger/20 p-2 rounded mt-2">
                                    <p class="text-[11px] text-danger font-medium leading-5">پیام خطا: {{ $run->error_message }}</p>
                                </div>
                            @endif

                            <div class="log-meta hidden mt-4 pt-4 border-t border-slate/50 dark:border-white/10">
                                <p class="text-[11px] font-bold text-primary mb-2 flex items-center gap-1">
                                    <span class="material-icons text-xs">task_alt</span>
                                    کارهای انجام شده در این اجرا:
                                </p>
                                @if($run->meta && isset($run->meta['tasks']) && is_array($run->meta['tasks']))
                                    <ul class="space-y-1">
                                        @foreach($run->meta['tasks'] as $task)
                                            <li class="flex items-center gap-2 text-[10px] text-slate-700 dark:text-slate-300">
                                                <span class="w-1 h-1 rounded-full bg-primary/40"></span>
                                                {{ $task }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif($run->meta && is_array($run->meta))
                                    <div class="space-y-2">
                                        @foreach($run->meta as $key => $val)
                                            @if($key !== 'tasks')
                                            <div class="bg-slate/40 dark:bg-black/30 p-2 rounded border border-slate dark:border-white/5">
                                                <div class="text-[10px] font-bold text-slate-500 mb-1 border-b border-slate/20 pb-1">{{ strtoupper($key) }}</div>
                                                <pre class="text-[10px] admin-ltr whitespace-pre-wrap">{{ is_array($val) ? json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $val }}</pre>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-[10px] italic text-slate">جزئیات بیشتری برای این اجرا ثبت نشده است.</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 opacity-40">
                            <span class="material-icons text-4xl block mb-2">inventory_2</span>
                            <p class="text-xs">هیچ لاگی در سیستم ثبت نشده است.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div id="tab-config" class="tab-content hidden space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="admin-card">
                    <h2 class="mb-4 font-bold flex items-center gap-2"><span class="material-icons text-primary">settings</span>تنظیمات کلی</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium block mb-2">حالت پردازش صف:</label>
                            <select name="mode" class="admin-input">
                                <option value="cron" @selected(($settings['mode'] ?? 'cron') === 'cron')>Cron Job (پیشنهادی برای هاست اشتراکی)</option>
                                <option value="artisan" @selected(($settings['mode'] ?? '') === 'artisan')>Artisan Worker (پیشنهادی برای سرور اختصاصی/مجازی)</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-xl bg-slate/5 border border-slate/10">
                            <div>
                                <label class="text-sm font-bold block">دسترسی از وبسرویس</label>
                                <p class="text-[11px] text-slate/50 mt-0.5">امکان پردازش صف از طریق آدرس Cron را فعال/غیرفعال کنید</p>
                            </div>
                            <label class="admin-switch">
                                <input type="hidden" name="webservice_enabled" value="0">
                                <input type="checkbox" name="webservice_enabled" value="1" class="admin-switch-input" @checked($settings['webservice_enabled'] ?? true)>
                                <span class="admin-switch-track"></span>
                                <span class="admin-switch-ball"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="mb-4 font-bold flex items-center gap-2"><span class="material-icons text-primary">terminal</span>راه اندازی سریع</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="mb-2 text-xs font-bold text-slate">توکن فعلی Cron:</p>
                            <div class="flex gap-2">
                                <code class="admin-ltr flex-grow rounded-lg bg-slate-100 p-3 text-xs border border-slate dark:bg-slate-900/50 dark:border-white/5">{{ $settings['cron_token'] ?? '-' }}</code>
                                <button type="button" class="admin-btn admin-btn-secondary p-2" data-copy-text="{{ $settings['cron_token'] ?? '-' }}"><span class="material-icons">content_copy</span></button>
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-xs font-bold text-slate">آدرس فراخوانی Cron (Wget):</p>
                            <div class="flex gap-2">
                                <code class="admin-ltr flex-grow rounded-lg bg-slate-100 p-3 text-xs border border-slate dark:bg-slate-900/50 dark:border-white/5">wget -q --header="X-Queue-Token: {{ $settings['cron_token'] ?? '-' }}" {{ route('api.queue.process') }}</code>
                                <button type="button" class="admin-btn admin-btn-secondary p-2" data-copy-text='wget -q --header="X-Queue-Token: {{ $settings['cron_token'] ?? '-' }}" {{ route('api.queue.process') }}'><span class="material-icons">content_copy</span></button>
                            </div>
                            <p class="text-[10px] text-slate mt-2 italic">این دستور را در Cron Job پنل هاست خود (مثلا هر ۵ دقیقه) تنظیم کنید.</p>
                        </div>

                        <div class="pt-2">
                            <button type="button" onclick="document.getElementById('artisan-command-section').classList.toggle('hidden')" class="text-xs font-bold text-primary flex items-center gap-1 hover:underline">
                                <span class="material-icons text-sm">code</span>
                                نمایش دستور Artisan Worker
                            </button>
                            <div id="artisan-command-section" class="mt-3 hidden bg-slate-900 text-slate-300 p-4 rounded-lg admin-ltr text-xs border border-white/10">
                                <p class="text-slate-500 mb-2"># برای اجرا در پس‌زمینه سرور:</p>
                                <code>php artisan queue:work --stop-when-empty</code>
                                <div class="mt-2 text-slate-500 italic">توجه: برای پایداری بیشتر از Supervisor استفاده کنید.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-card border-danger/20 bg-danger/5">
                <h2 class="mb-4 font-bold text-danger flex items-center gap-2"><span class="material-icons">security</span>منطقه خطر</h2>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold">بازتولید توکن امنیتی</h3>
                        <p class="text-xs text-slate/60">باعث نامعتبر شدن دستور Cron فعلی می‌شود.</p>
                    </div>
                    <button type="button"
                            onclick="document.getElementById('regenerate-token-modal').showModal()"
                            class="admin-btn admin-btn-secondary text-danger border-danger/20 hover:bg-danger/10">
                        <span class="material-icons">sync</span>
                        تولید مجدد توکن
                    </button>
                </div>
            </div>

        </div>

        <div id="tab-driver" class="tab-content hidden space-y-6">
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">storage</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات درایور صف</h2>
                            <p class="text-xs text-slate/60 mt-1">تغییر زیرساخت اجرای صف‌های برنامه</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-bold block mb-2">درایور فعال (QUEUE_CONNECTION)</label>
                        <select name="driver" class="admin-input">
                            @foreach($drivers as $key => $label)
                                <option value="{{ $key }}" @selected($currentDriver === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate/50 mt-1.5 font-bold">تغییر این گزینه بلافاصله در فایل <code class="bg-slate/10 px-1 rounded">.env</code> اعمال می‌شود.</p>
                    </div>

                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-slate">وضعیت پیش‌نیازها:</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate/5 border border-slate/10">
                                <span class="text-xs">دیتابیس (Jobs Table):</span>
                                <span class="material-icons text-sm {{ $driverStatus['database'] ? 'text-emerald-500' : 'text-rose-500' }}">
                                    {{ $driverStatus['database'] ? 'check_circle' : 'cancel' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate/5 border border-slate/10">
                                <span class="text-xs">اکستنشن Redis (PHP):</span>
                                <span class="material-icons text-sm {{ $driverStatus['redis'] ? 'text-emerald-500' : 'text-rose-500' }}">
                                    {{ $driverStatus['redis'] ? 'check_circle' : 'cancel' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-maintenance" class="tab-content hidden space-y-6">
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="admin-card">
                    <h2 class="mb-4 font-bold flex items-center gap-2"><span class="material-icons text-primary">auto_delete</span>پاکسازی خودکار لاگ‌ها</h2>
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold px-1">نگهداری لاگ‌های اجرای صف (روز):</label>
                            <input type="number" name="queue_log_retention_days" value="{{ $settings['queue_log_retention_days'] ?? 7 }}" min="1" max="365" class="admin-input">
                            <p class="text-[10px] text-slate/50 px-1">لاگ‌های قدیمی‌تر از این مقدار به صورت خودکار حذف می‌شوند.</p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold px-1">نگهداری لاگ‌های لاراول (روز):</label>
                            <input type="number" name="laravel_log_retention_days" value="{{ $settings['laravel_log_retention_days'] ?? 14 }}" min="1" max="365" class="admin-input">
                            <p class="text-[10px] text-slate/50 px-1">فایل‌های لاگ لاراول (.log) قدیمی‌تر از این مقدار حذف می‌شوند.</p>
                        </div>
                    </div>
                </div>

                <div class="admin-card">
                    <h2 class="mb-4 font-bold flex items-center gap-2"><span class="material-icons text-primary">info</span>راهنما</h2>
                    <div class="bg-primary/5 p-4 rounded-lg border border-primary/10">
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-6">
                            پاکسازی خودکار در هر بار اجرای صف (توسط Cron یا دستور Artisan) انجام می‌شود.
                            این کار به بهینه‌سازی دیتابیس و فضای دیسک کمک می‌کند.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="regenerate-token-form" action="{{ route('dash.admin.queues.token.regenerate', ['authkey' => $authkey]) }}" method="POST" class="hidden">@csrf</form>

    <dialog id="regenerate-token-modal" class="admin-dialog w-[min(100vw-32px,450px)]">
        <div class="admin-dialog-body">
            <div class="flex items-start gap-4 p-2">
                <div class="w-12 h-12 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                    <span class="material-icons !text-2xl">help_outline</span>
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-slate-800 dark:text-white mb-2">تایید عملیات</h3>
                    <p class="text-xs leading-6 text-slate-500 dark:text-slate-400">بازتولید توکن صف باعث نامعتبر شدن دستور Cron قبلی می‌شود. ادامه می‌دهید؟</p>
                </div>
            </div>
            <div class="admin-dialog-actions">
                <button type="button" onclick="this.closest('dialog').close()" class="admin-btn admin-btn-secondary">انصراف</button>
                <button type="button" onclick="document.getElementById('regenerate-token-form').submit()" class="admin-btn admin-btn-primary px-8">تایید</button>
            </div>
        </div>
    </dialog>

    @vite(['resources/js/admin-queues-hub.js'])

</x-layouts.admin-dashboard>
