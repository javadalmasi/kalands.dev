<x-layouts.admin-dashboard title="ماژول تماس با ما" :helpModuleKey="'contact'">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="ماژول تماس با ما" />

    <x-admin.tab-bar id="contact-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-messages">
            <span class="material-icons text-base">email</span>
            <span>پیام‌های دریافتی</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-settings">
            <span class="material-icons text-base">settings</span>
            <span>تنظیمات اطلاعات تماس</span>
        </button>
    </x-admin.tab-bar>

    <div id="tab-messages" class="tab-content space-y-4">
        <x-admin.filter-bar>
            <input type="hidden" name="tab" value="tab-messages">
            <x-admin.filter-field label="مرتب‌سازی">
                <select name="sort" class="admin-input">
                    <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
                    <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
                    <option value="subject" @selected(($sort ?? '') === 'subject')>موضوع</option>
                </select>
            </x-admin.filter-field>
            <x-admin.filter-field label="وضعیت مشاهده">
                <select name="read" class="admin-input">
                    <option value="all" @selected(($read ?? 'all') === 'all')>همه پیام‌ها</option>
                    <option value="unread" @selected(($read ?? '') === 'unread')>خوانده‌نشده</option>
                    <option value="read" @selected(($read ?? '') === 'read')>خوانده‌شده</option>
                </select>
            </x-admin.filter-field>
            <x-admin.filter-field label=" " span="2">
                <div class="flex items-end h-full">
                    <button class="admin-btn justify-center w-full h-[38px]"><span class="material-icons">filter_alt</span>اعمال فیلتر</button>
                </div>
            </x-admin.filter-field>
        </x-admin.filter-bar>

        <x-admin.bulk-bar
            action="{{ route('dash.admin.contact.bulk', ['authkey' => $authkey]) }}"
            id="bulk-contact-form"
            label="تعداد کل پیام‌ها: {{ $messages->total() }}"
            confirm="این اقدام روی پیام‌های انتخاب‌شده انجام می‌شود. ادامه می‌دهید؟"
        >
            <x-slot:actions>
                <select name="action" class="rounded-lg border border-slate/20 bg-white p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                    <option value="read">خوانده شد</option>
                    <option value="unread">خوانده‌نشده</option>
                    <option value="delete">حذف دائمی</option>
                </select>
                <button class="admin-btn admin-btn-primary !py-2" type="submit" data-admin-confirm="این اقدام روی پیام‌های انتخاب‌شده انجام می‌شود. ادامه می‌دهید؟">
                    <span class="material-icons !text-sm">done_all</span>
                    تایید و اجرا
                </button>
            </x-slot:actions>
        </x-admin.bulk-bar>

        <div class="space-y-3">
            @forelse($messages as $message)
                <div class="admin-card {{ $message->is_read ? 'opacity-80' : 'border-r-4 border-primary' }}">
                    <div class="mb-1 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" form="bulk-contact-form" name="message_ids[]" value="{{ $message->id }}" class="contact-item-checkbox" data-bulk-item>
                            <p class="font-bold">{{ $message->subject }}</p>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ $message->is_read ? 'bg-slate/10 text-slate' : 'bg-primary/10 text-primary' }} font-bold">
                            {{ $message->is_read ? 'خوانده‌شده' : 'جدید' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] text-slate/70 mb-2">
                        <span class="material-icons text-xs">person</span>
                        <span>{{ $message->name }}</span>
                        <span class="mx-1 opacity-30">|</span>
                        <span class="material-icons text-xs">email</span>
                        <span class="admin-ltr">{{ $message->email }}</span>
                        <span class="mx-1 opacity-30">|</span>
                        <span class="material-icons text-xs">schedule</span>
                        <span>{{ persianTimeAgo($message->created_at) }}</span>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-300">{{ $message->message }}</p>
                    <div class="mt-4 admin-actions text-xs border-t border-slate/5 pt-3">
                        <a href="{{ route('dash.admin.contact.edit', ['authkey' => $authkey, 'contactMessage' => $message->id]) }}" class="admin-btn admin-btn-secondary !p-1.5" title="ویرایش">
                            <span class="material-icons !text-base">edit</span>
                        </a>
                        @if(!$message->is_read)
                            <form action="{{ route('dash.admin.contact.read', ['authkey' => $authkey, 'contactMessage' => $message->id]) }}" method="POST">
                                @csrf
                                <button class="admin-btn admin-btn-secondary"><span class="material-icons !text-base">mark_email_read</span>خوانده شد</button>
                            </form>
                        @endif
                        <form action="{{ route('dash.admin.contact.delete', ['authkey' => $authkey, 'contactMessage' => $message->id]) }}" method="POST" data-admin-confirm="حذف این پیام غیرقابل بازگشت است. ادامه می‌دهید؟">
                            @csrf
                            @method('DELETE')
                            <button class="admin-btn admin-btn-danger !p-1.5" title="حذف"><span class="material-icons !text-base">delete</span></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="admin-card text-center py-10 opacity-50">
                    <span class="material-icons text-4xl block mb-2">mail_outline</span>
                    پیامی یافت نشد.
                </div>
            @endforelse
        </div>
        <x-admin.pagination :paginator="$messages" />
    </div>

    <div id="tab-settings" class="tab-content hidden space-y-6">
        <form action="{{ route('dash.admin.contact.page.info.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf

            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">visibility</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">نمایش باکس اطلاعات تماس</h2>
                            <p class="text-xs text-slate/60 mt-1">فعال یا غیرفعال سازی بخش نمایش اطلاعات تماس در سایت</p>
                        </div>
                    </div>
                    <label class="admin-switch"><input type="checkbox" name="enabled" value="1" class="admin-switch-input" @checked(old('enabled', $settings['enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-xs font-bold px-1">عنوان باکس تماس</label>
                        <input name="title" value="{{ old('title', $settings['title'] ?? '') }}" class="admin-input" placeholder="مثلا: با ما در ارتباط باشید">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold px-1">ایمیل</label>
                        <input name="email" type="email" value="{{ old('email', $settings['email'] ?? '') }}" class="admin-input admin-ltr" placeholder="info@example.com">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold px-1">تلفن</label>
                        <input name="phone" value="{{ old('phone', $settings['phone'] ?? '') }}" class="admin-input admin-ltr" placeholder="021-12345678">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold px-1">ساعت پاسخگویی</label>
                        <input name="work_hours" value="{{ old('work_hours', $settings['work_hours'] ?? '') }}" class="admin-input" placeholder="شنبه تا چهارشنبه ۹ تا ۱۷">
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold px-1">آدرس</label>
                        <input name="address" value="{{ old('address', $settings['address'] ?? '') }}" class="admin-input" placeholder="تهران، خیابان ...">
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold px-1">توضیحات کوتاه</label>
                        <textarea name="description" rows="3" class="admin-input" placeholder="توضیحات مختصری در مورد تماس با ما...">{{ old('description', $settings['description'] ?? '') }}</textarea>
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="text-xs font-bold px-1">Iframe نقشه (گوگل یا نشان)</label>
                        <textarea name="map_iframe" rows="4" class="admin-input admin-ltr" placeholder="<iframe ...></iframe>">{{ old('map_iframe', $settings['map_iframe'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="admin-card is-surface">
                <h3 class="font-bold text-slate mb-6 flex items-center gap-2">
                    <span class="material-icons text-primary">toggle_on</span>
                    تنظیمات نمایش آیتم‌ها
                </h3>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($toggles as $toggle)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate/5 border border-slate/10 transition-all hover:bg-slate/10">
                            <span class="text-sm font-medium text-slate">{{ $toggle['label'] }}</span>
                            <label class="admin-switch"><input type="checkbox" name="{{ $toggle['name'] }}" value="1" class="admin-switch-input" @checked(old($toggle['name'], $settings[$toggle['name']] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10">
                    <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                        <span class="material-icons">save</span>
                        ذخیره تمامی تنظیمات
                    </button>
                </div>
            </div>
        </form>
    </div>

</x-layouts.admin-dashboard>
@vite(['resources/js/admin-contact-hub.js'])
