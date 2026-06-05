<x-layouts.admin-dashboard title="ماژول نظرات">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">ماژول نظرات</h1>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="comment-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-moderation">
                <span class="material-icons text-base">fact_check</span>
                <span>تایید نظرات</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-reports">
                <span class="material-icons text-base">assessment</span>
                <span>گزارش نظرات</span>
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

    <div id="tab-moderation" class="tab-content space-y-4">
        <form method="GET" class="admin-card grid gap-3 md:grid-cols-4">
            <input type="hidden" name="tab" value="tab-moderation">
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">مرتب‌سازی</label>
                <select name="sort" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
                    <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
                    <option value="status" @selected(($sort ?? '') === 'status')>بر اساس وضعیت</option>
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">وضعیت</label>
                <select name="status" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                    <option value="all" @selected(($status ?? 'all') === 'all')>همه وضعیت‌ها</option>
                    <option value="pending" @selected(($status ?? '') === 'pending')>در انتظار</option>
                    <option value="approved" @selected(($status ?? '') === 'approved')>تایید</option>
                    <option value="rejected" @selected(($status ?? '') === 'rejected')>رد</option>
                    <option value="spam" @selected(($status ?? '') === 'spam')>اسپم</option>
                </select>
            </div>
            <div class="md:col-span-2 flex items-end">
                <button class="admin-btn justify-center w-full h-[38px]"><span class="material-icons">filter_alt</span>اعمال فیلتر</button>
            </div>
        </form>

        <form id="bulk-comments-form" method="POST" action="{{ route('dash.admin.comments.bulk', ['authkey' => $authkey]) }}" class="admin-card mb-4">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-xs cursor-pointer bg-slate/5 px-3 py-2 rounded-lg border border-slate/10 hover:bg-slate/10 transition-colors">
                        <input type="checkbox" id="select-all-comments" class="rounded border-slate/30">
                        <span class="font-bold opacity-70">انتخاب همه</span>
                    </label>
                    <div class="h-8 w-px bg-slate/10"></div>
                    <div class="flex items-center gap-2">
                        <select name="action" class="rounded-lg border border-slate/20 bg-white p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                            <option value="approved">تایید</option>
                            <option value="rejected">رد</option>
                            <option value="spam">اسپم</option>
                            <option value="delete">حذف دائمی</option>
                        </select>
                        <button class="admin-btn admin-btn-primary !py-2" type="submit" data-admin-confirm="این اقدام روی نظرات انتخاب‌شده انجام می‌شود. ادامه می‌دهید؟">
                            <span class="material-icons !text-sm">done_all</span>
                            تایید و اجرا
                        </button>
                    </div>
                </div>
                <div class="text-[10px] font-bold opacity-40">
                    تعداد کل نظرات: {{ $comments->total() }}
                </div>
            </div>
        </form>

        <div class="space-y-3">
            @forelse($comments as $comment)
                <div class="admin-card !p-4 {{ $comment->status === 'pending' ? 'border-r-4 border-amber-500' : '' }}">
                    <div class="mb-2 flex items-start gap-3">
                        <input type="checkbox" form="bulk-comments-form" name="comment_ids[]" value="{{ $comment->id }}" class="comment-item-checkbox mt-1">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p class="text-xs font-bold text-slate">
                                    {{ $comment->user?->full_name ?? $comment->name ?? 'کاربر مهمان' }}
                                    @if($comment->email) <span class="font-normal opacity-60">({{ $comment->email }})</span> @endif
                                </p>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold
                                    @if($comment->status === 'approved') bg-success/10 text-success
                                    @elseif($comment->status === 'pending') bg-amber-500/10 text-amber-600
                                    @elseif($comment->status === 'rejected') bg-danger/10 text-danger
                                    @else bg-slate/10 text-slate @endif">
                                    {{ $comment->status }}
                                </span>
                            </div>
                            <p class="text-sm leading-relaxed">{{ $comment->content }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                <span class="flex items-center gap-1"><span class="material-icons !text-xs">inventory_2</span>محصول: {{ $comment->product?->title ?? $comment->product_id }}</span>
                                <span class="flex items-center gap-1"><span class="material-icons !text-xs">schedule</span>{{ persianTimeAgo($comment->created_at) }}</span>
                                <span class="flex items-center gap-1"><span class="material-icons !text-xs">public</span>IP: {{ $comment->ip_address }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 admin-actions text-xs border-t border-slate/5 pt-3">
                        <form action="{{ route('dash.admin.comments.status', ['authkey' => $authkey, 'comment' => $comment->id, 'status' => 'approved']) }}" method="POST">@csrf<button class="admin-btn admin-btn-secondary !py-1.5"><span class="material-icons !text-base text-success">check_circle</span>تایید</button></form>
                        <form action="{{ route('dash.admin.comments.status', ['authkey' => $authkey, 'comment' => $comment->id, 'status' => 'rejected']) }}" method="POST">@csrf<button class="admin-btn admin-btn-secondary !py-1.5"><span class="material-icons !text-base text-danger">cancel</span>رد</button></form>
                        <form action="{{ route('dash.admin.comments.status', ['authkey' => $authkey, 'comment' => $comment->id, 'status' => 'spam']) }}" method="POST">@csrf<button class="admin-btn admin-btn-secondary !py-1.5"><span class="material-icons !text-base text-slate">report</span>اسپم</button></form>
                        <div class="flex-1"></div>
                        <form action="{{ route('dash.admin.comments.delete', ['authkey' => $authkey, 'comment' => $comment->id]) }}" method="POST" data-admin-confirm="حذف این نظر غیرقابل بازگشت است. ادامه می‌دهید؟">@csrf @method('DELETE')<button class="admin-btn admin-btn-danger !py-1.5"><span class="material-icons !text-base">delete</span>حذف</button></form>
                    </div>
                </div>
            @empty
                <div class="admin-card text-center py-10 opacity-50">
                    <span class="material-icons text-4xl block mb-2">speaker_notes_off</span>
                    نظری یافت نشد.
                </div>
            @endforelse
        </div>
        <div class="mt-4">{{ $comments->links() }}</div>
    </div>

    <div id="tab-reports" class="tab-content hidden space-y-4">
        <div class="admin-card !p-0 overflow-hidden">
            <table class="w-full text-right text-sm">
                <thead class="bg-slate/5 border-b border-slate/10">
                    <tr>
                        <th class="p-4 font-bold">نام محصول</th>
                        <th class="p-4 font-bold text-center">فروشگاه</th>
                        <th class="p-4 font-bold text-center">تعداد نظرات</th>
                        <th class="p-4 font-bold text-left">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate/5">
                    @forelse($reports as $report)
                        <tr class="hover:bg-slate/5 transition-colors">
                            <td class="p-4">
                                <p class="font-medium line-clamp-1">{{ $report->product?->title ?? 'محصول نامشخص' }}</p>
                                <p class="text-[10px] text-slate/50 mt-1">شناسه: {{ $report->product_id }}</p>
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-bold {{ $report->store === 'digikala' ? 'bg-red-500/10 text-red-600' : 'bg-green-600/10 text-green-700' }}">
                                    {{ $report->store_label }}
                                </span>
                            </td>
                            <td class="p-4 text-center font-bold text-lg text-primary">
                                {{ number_format($report->comments_count) }}
                            </td>
                            <td class="p-4 text-left">
                                <a href="{{ route('index') }}/product/{{ $report->product_id }}/info" target="_blank" class="admin-btn admin-btn-secondary !p-2" title="مشاهده محصول">
                                    <span class="material-icons !text-base">open_in_new</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-10 text-center opacity-50">داده‌ای برای نمایش گزارش وجود ندارد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>

    <div id="tab-settings" class="tab-content hidden space-y-6">
        <form action="{{ route('dash.admin.comments.settings.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">comment</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">امکان ارسال نظر</h2>
                            <p class="text-xs text-slate/60 mt-1">فعال یا غیرفعال سازی فرم ارسال نظر در تمامی محصولات</p>
                        </div>
                    </div>
                    <label class="admin-switch"><input type="checkbox" name="enabled" value="1" class="admin-switch-input" @checked(old('enabled', $settings['enabled'] ?? true))><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold px-1">پیام هشدار هنگام غیرفعال بودن</label>
                    <textarea name="disabled_message" rows="4" class="w-full rounded-lg border border-slate p-3 dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="پیامی که در صورت غیرفعال بودن ارسال نظر به کاربر نمایش داده می‌شود...">{{ old('disabled_message', $settings['disabled_message'] ?? '') }}</textarea>
                    <p class="text-[11px] text-slate/50">این پیام در بالای فرم نظردهی (در صورت غیرفعال بودن) نمایش داده می‌شود.</p>
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

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول نظرات</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-intro">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">info</span>
                                معرفی ماژول
                            </h3>
                            <p>ماژول نظرات، سامانه مدیریت نظرات کاربران در پلتفرم kalands.ir است. این ماژول امکان تایید، رد، اسپم‌سازی و گزارش‌گیری از نظرات ارسال شده کاربران را فراهم می‌کند.</p>
                            <p class="mt-3">نظرات کاربران بر اساس IP، محصول و زمان ثبت پیگیری می‌شوند و امکانات مدیریت گروهی برای افزایش بهره‌وری پشتیبانان در نظر گرفته شده است.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-moderation">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">fact_check</span>
                                تایید نظرات
                            </h3>
                            <p>در این بخش نظرات ارسال شده را بررسی و وضعیت آن‌ها را تغییر دهید:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li><b>تایید:</b> نظر در صفحه محصول نمایش داده می‌شود</li>
                                <li><b>رد:</b> نظر تایید نمی‌شود</li>
                                <li><b>اسپم:</b> نظر به عنوان اسپم علامت‌گذاری می‌شود</li>
                                <li><b>حذف دائمی:</b> نظر از دیتابیس حذف می‌شود</li>
                            </ul>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-reports">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">assessment</span>
                                گزارش نظرات
                            </h3>
                            <p>گزارش نظرات بر اساس محصول و فروشگاه مرتب‌سازی می‌شود. برای هر محصول تعداد نظرات تایید شده و getsign فروشگاه (دیجی‌کالا یا باسلام) نمایش داده می‌شود.</p>
                            <p class="mt-3">امکان مشاهده سریع محصول از طریق لینک «مشاهده محصول» وجود دارد.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-settings">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">settings</span>
                                تنظیمات
                            </h3>
                            <p>در بخش تنظیمات می‌توانید:</p>
                            <ul class="list-disc list-inside mt-3 space-y-2 mr-4">
                                <li>امکان ارسال نظر را به صورت سراسری فعال/غیرفعال کنید</li>
                                <li>پیام هشدار برای زمان غیرفعال بودن فرم نظردهی تنظیم کنید</li>
                            </ul>
                            <p class="mt-3">با غیرفعال کردن ارسال نظر، فرم نظردهی در تمامی محصولات مسدود و پیام تعیین شده به کاربران نمایش داده می‌شود.</p>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-moderation" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تایید نظرات</a>
                        <a href="#doc-reports" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">گزارش نظرات</a>
                        <a href="#doc-settings" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تنظیمات</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/admin-comments-hub.js')
    @endpush
</x-layouts.admin-dashboard>
