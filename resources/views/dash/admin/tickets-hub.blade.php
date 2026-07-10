<x-layouts.admin-dashboard title="ماژول تیکت" :helpModuleKey="'tickets'">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="ماژول تیکت" />

    <x-admin.tab-bar id="ticket-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-moderation">
            <span class="material-icons text-base">fact_check</span>
            <span>پاسخ به تیکت‌ها</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-categories">
            <span class="material-icons text-base">category</span>
            <span>دسته‌بندی‌ها</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-blocked">
            <span class="material-icons text-base">block</span>
            <span>کاربران مسدود شده</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-settings">
            <span class="material-icons text-base">settings</span>
            <span>تنظیمات</span>
        </button>
    </x-admin.tab-bar>

    <div id="tab-moderation" class="tab-content space-y-4">
        <x-admin.filter-bar cols="4">
            <input type="hidden" name="tab" value="tab-moderation">
            <x-admin.filter-field label="مرتب‌سازی">
                <select name="sort" class="admin-input">
                    <option value="oldest" @selected(($sort ?? 'oldest') === 'oldest')>قدیمی‌ترین (ترتیب ثبت)</option>
                    <option value="latest" @selected(($sort ?? '') === 'latest')>جدیدترین</option>
                </select>
            </x-admin.filter-field>
            <x-admin.filter-field label="وضعیت">
                <select name="status" class="admin-input">
                    <option value="all" @selected(($status ?? 'all') === 'all')>همه وضعیت‌ها</option>
                    <option value="open" @selected(($status ?? '') === 'open')>باز</option>
                    <option value="answered" @selected(($status ?? '') === 'answered')>پاسخ داده شده</option>
                    <option value="closed" @selected(($status ?? '') === 'closed')>بسته شده</option>
                    <option value="spam" @selected(($status ?? '') === 'spam')>اسپم</option>
                </select>
            </x-admin.filter-field>
            <x-admin.filter-field label="" span="2">
                <div class="flex items-end h-full">
                    <button class="admin-btn justify-center w-full h-[38px]"><span class="material-icons">filter_alt</span>اعمال فیلتر</button>
                </div>
            </x-admin.filter-field>
        </x-admin.filter-bar>

        <x-admin.bulk-bar
            action="{{ route('dash.admin.tickets.bulk', ['authkey' => $authkey]) }}"
            id="bulk-tickets-form"
            label="تیکت‌ها"
            confirm="این اقدام روی تیکت‌های انتخاب‌شده انجام می‌شود. ادامه می‌دهید؟"
        >
            <x-slot:actions>
                <select name="action" class="admin-input">
                    <option value="answered">پاسخ داده شده</option>
                    <option value="closed">بسته شده</option>
                    <option value="spam">اسپم</option>
                    <option value="delete">حذف دائمی</option>
                </select>
                <button class="admin-btn admin-btn-primary" type="submit">
                    <span class="material-icons !text-sm">done_all</span>
                    تایید و اجرا
                </button>
                <div class="text-[10px] font-bold opacity-40">
                    تعداد تیکت‌ها: {{ $tickets->total() }}
                </div>
            </x-slot:actions>
        </x-admin.bulk-bar>

        <div class="space-y-3">
            @forelse($tickets as $ticket)
                <div class="admin-card !p-4 {{ $ticket->status === 'open' ? 'border-r-4 border-primary' : '' }}">
                    <div class="mb-2 flex items-start gap-3">
                        <input type="checkbox" form="bulk-tickets-form" name="ticket_ids[]" value="{{ $ticket->id }}" class="ticket-item-checkbox mt-1" data-bulk-item>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p class="text-xs font-bold text-slate">
                                    {{ ($ticket->user && $ticket->user->name !== "") ? $ticket->user->name : 'کاربر نامشخص' }}
                                    <span class="font-normal opacity-60">({{ $ticket->subject }})</span>
                                </p>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                                    @if($ticket->status === 'answered') bg-success/10 text-success
                                    @elseif($ticket->status === 'open') bg-primary/10 text-primary
                                    @elseif($ticket->status === 'closed') bg-slate/10 text-slate
                                    @elseif($ticket->status === 'spam') bg-danger/10 text-danger
                                    @else bg-slate/10 text-slate @endif">
                                    {{ $ticket->status }}
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                <span class="flex items-center gap-1"><span class="material-icons !text-xs">category</span>دسته: {{ $ticket->category?->name ?? 'نامشخص' }}</span>
                                <span class="flex items-center gap-1"><span class="material-icons !text-xs">schedule</span>ثبت شده: {{ persianTimeAgo($ticket->created_at) }}</span>
                                <span class="flex items-center gap-1"><span class="material-icons !text-xs">priority_high</span>اولویت: {{ $ticket->priority }}</span>
                                @if($ticket->latestMessage)
                                    <span class="flex items-center gap-1"><span class="material-icons !text-xs text-success">reply</span>آخرین پاسخ: {{ $ticket->latestMessage->sender_type === 'admin' ? 'پشتیبانی' : ($ticket->latestMessage->user->name ?? 'کاربر') }} ({{ persianTimeAgo($ticket->latestMessage->created_at) }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 admin-actions text-xs border-t border-slate/5 pt-3">
                        <a href="{{ route('dash.admin.tickets.show', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" class="admin-btn admin-btn-secondary !py-1.5"><span class="material-icons !text-base text-primary">reply</span>پاسخ و مشاهده</a>
                        <form action="{{ route('dash.admin.tickets.status', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" method="POST">@csrf<input type="hidden" name="status" value="closed"><button class="admin-btn admin-btn-secondary !py-1.5"><span class="material-icons !text-base text-slate">lock</span>بستن</button></form>
                        <form action="{{ route('dash.admin.tickets.bulk', ['authkey' => $authkey]) }}" method="POST">@csrf<input type="hidden" name="ticket_ids[]" value="{{ $ticket->id }}"><input type="hidden" name="action" value="spam"><button class="admin-btn admin-btn-secondary !py-1.5"><span class="material-icons !text-base text-danger">report</span>اسپم</button></form>
                        <div class="flex-1"></div>
                        <form action="{{ route('dash.admin.tickets.bulk', ['authkey' => $authkey]) }}" method="POST" data-admin-confirm="حذف این تیکت غیرقابل بازگشت است. ادامه می‌دهید؟">@csrf<input type="hidden" name="ticket_ids[]" value="{{ $ticket->id }}"><input type="hidden" name="action" value="delete"><button class="admin-btn admin-btn-danger !py-1.5"><span class="material-icons !text-base">delete</span>حذف</button></form>
                    </div>
                </div>
            @empty
                <div class="admin-card text-center py-10 opacity-50">
                    <span class="material-icons text-4xl block mb-2">confirmation_number</span>
                    تیکتی یافت نشد.
                </div>
            @endforelse
        </div>
        <x-admin.pagination :paginator="$tickets" />
    </div>

    <div id="tab-categories" class="tab-content hidden space-y-4">
        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-2">
                <div class="admin-card !p-0 overflow-hidden">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-slate/5 border-b border-slate/10">
                            <tr>
                                <th class="p-4 font-bold">نام دسته‌بندی</th>
                                <th class="p-4 font-bold text-center">تعداد تیکت‌ها</th>
                                <th class="p-4 font-bold text-center">وضعیت</th>
                                <th class="p-4 font-bold text-left">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate/5">
                            @forelse($categories as $category)
                                <tr class="hover:bg-slate/5 transition-colors">
                                    <td class="p-4">
                                        <p class="font-medium">{{ $category->name }}</p>
                                        <p class="text-[10px] text-slate/50 mt-1">Slug: {{ $category->slug }}</p>
                                    </td>
                                    <td class="p-4 text-center font-bold text-primary">
                                        {{ number_format($category->tickets_count) }}
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $category->is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                            {{ $category->is_active ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-left">
                                        <form action="{{ route('dash.admin.ticket.categories.toggle', ['authkey' => $authkey, 'category' => $category->id]) }}" method="POST">
                                            @csrf
                                            <button class="admin-btn admin-btn-secondary !p-2" title="تغییر وضعیت">
                                                <span class="material-icons !text-base">{{ $category->is_active ? 'visibility_off' : 'visibility' }}</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-10 text-center opacity-50">دسته‌بندی یافت نشد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <form action="{{ route('dash.admin.ticket.categories.store', ['authkey' => $authkey]) }}" method="POST" class="admin-card space-y-4">
                    @csrf
                    <h3 class="font-bold text-slate border-b border-slate/10 pb-2 mb-4">افزودن دسته‌بندی جدید</h3>
                    <div class="space-y-1">
                        <label class="text-xs font-bold opacity-60">نام دسته‌بندی</label>
                        <input type="text" name="name" required class="admin-input">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold opacity-60">نامک (Slug)</label>
                        <input type="text" name="slug" required class="admin-input">
                    </div>
                    <button class="admin-btn admin-btn-primary w-full justify-center">
                        <span class="material-icons">add_circle</span>
                        ثبت دسته‌بندی
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="tab-blocked" class="tab-content hidden space-y-4">
        <div class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-2">
                <div class="admin-card !p-0 overflow-hidden">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-slate/5 border-b border-slate/10">
                            <tr>
                                <th class="p-4 font-bold">کاربر</th>
                                <th class="p-4 font-bold">ایمیل/موبایل</th>
                                <th class="p-4 font-bold text-left">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate/5">
                            @forelse($blockedUsers as $user)
                                <tr class="hover:bg-slate/5 transition-colors">
                                    <td class="p-4">
                                        <p class="font-medium">{{ $user->full_name }}</p>
                                        <p class="text-[10px] text-slate/50 mt-1">شناسه کاربر: {{ $user->id }}</p>
                                    </td>
                                    <td class="p-4">
                                        <p>{{ $user->email ?? $user->phone }}</p>
                                    </td>
                                    <td class="p-4 text-left">
                                        <form action="{{ route('dash.admin.tickets.user.toggle-block', ['authkey' => $authkey]) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="action" value="unblock">
                                            <button class="admin-btn admin-btn-secondary !p-2 text-success" title="رفع مسدودیت">
                                                <span class="material-icons !text-base">lock_open</span>
                                                <span>رفع مسدودیت</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="p-10 text-center opacity-50">کاربر مسدود شده‌ای یافت نشد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="admin-card space-y-4">
                    <h3 class="font-bold text-slate border-b border-slate/10 pb-2 mb-4">مسدود کردن کاربر جدید</h3>
                    <p class="text-[10px] text-slate/60 mb-4">با مسدود کردن کاربر، وی امکان ثبت تیکت جدید یا پاسخ به تیکت‌های قبلی را نخواهد داشت.</p>

                    <div class="relative" id="user-search-container" data-authkey="{{ $authkey }}">
                        <label class="text-xs font-bold opacity-60">جستجوی کاربر (نام، ایمیل یا تلفن)</label>
                        <input type="text" id="user-search-input" class="admin-input" placeholder="شروع به تایپ کنید...">
                        <div id="user-search-results" class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-auto rounded-lg border border-slate bg-white shadow-xl dark:bg-slate-800 hidden"></div>
                    </div>

                    <form action="{{ route('dash.admin.tickets.user.toggle-block', ['authkey' => $authkey]) }}" method="POST" id="block-user-form" class="hidden">
                        @csrf
                        <div class="rounded-lg bg-slate/5 p-3 border border-slate/10 mb-3">
                            <p class="text-xs font-bold" id="selected-user-name"></p>
                            <p class="text-[10px] opacity-60" id="selected-user-meta"></p>
                            <input type="hidden" name="user_id" id="selected-user-id">
                            <input type="hidden" name="action" value="block">
                        </div>
                        <button class="admin-btn admin-btn-danger w-full justify-center">
                            <span class="material-icons">block</span>
                            تایید مسدودیت
                        </button>
                        <button type="button" id="cancel-selection" class="admin-btn admin-btn-secondary w-full justify-center mt-2">انصراف</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="tab-settings" class="tab-content hidden space-y-6">
        <form action="{{ route('dash.admin.tickets.settings.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">confirmation_number</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">امکان ارسال تیکت</h2>
                            <p class="text-xs text-slate/60 mt-1">فعال یا غیرفعال سازی فرم ارسال تیکت برای تمامی کاربران</p>
                        </div>
                    </div>
                    <label class="admin-switch"><input type="checkbox" name="enabled" value="1" class="admin-switch-input" @checked(old('enabled', $settings['enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                </div>

                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">visibility_off</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">مخفی سازی نام پاسخ دهنده</h2>
                            <p class="text-xs text-slate/60 mt-1">در پنل کاربری، به جای نام ادمین عبارت «پشتیبانی» نمایش داده شود.</p>
                        </div>
                    </div>
                    <label class="admin-switch"><input type="checkbox" name="hide_admin_name" value="1" class="admin-switch-input" @checked(old('hide_admin_name', $settings['hide_admin_name'] ?? false))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold px-1">پیام هشدار هنگام غیرفعال بودن</label>
                    <textarea name="disabled_message" rows="4" class="admin-input" placeholder="پیامی که در صورت غیرفعال بودن ارسال تیکت به کاربر نمایش داده می‌شود...">{{ old('disabled_message', $settings['disabled_message'] ?? '') }}</textarea>
                    <p class="text-[11px] text-slate/50">این پیام در بالای فرم تیکت (در صورت غیرفعال بودن) نمایش داده می‌شود.</p>
                </div>

                <div class="space-y-4 pt-6 border-t border-slate/10">
                    <h3 class="font-bold text-slate">تنظیمات اعلان</h3>
                    <div class="space-y-1">
                        <label class="text-sm font-bold px-1">ایمیل ادمین برای دریافت اعلان</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email', $settings['admin_email'] ?? '') }}" class="admin-input" placeholder="example@domain.com">
                        <p class="text-[11px] text-slate/50">با ثبت این ایمیل، هنگام ثبت تیکت جدید توسط کاربران، یک اعلان به این آدرس ارسال خواهد شد.</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10">
                    <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-tickets-hub.js'])
