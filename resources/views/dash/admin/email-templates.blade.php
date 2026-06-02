<x-layouts.admin-dashboard title="تمپلیت ایمیل‌ها">
    @php($authkey = request()->route('authkey'))
    @php($currentKey = isset($templateCatalog[$activeKey ?? '']) ? ($activeKey ?? '') : array_key_first($templateCatalog))

    <h1 class="admin-page-title">مدیریت تمپلیت ایمیل‌ها</h1>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="email-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="templates-tab">
                <span class="material-icons text-base">library_books</span>
                <span>مدیریت تمامی تمپلیت‌ها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="common-features-tab">
                <span class="material-icons text-base">dashboard_customize</span>
                <span>ویژگی‌های مشترک (هدر و فوتر)</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="module-settings-tab">
                <span class="material-icons text-base">settings_applications</span>
                <span>تنظیمات ماژول</span>
            </button>
        </div>
    </div>

    <div id="templates-tab" class="tab-content">
        <div class="grid gap-4 lg:grid-cols-[280px_1fr]">
            <aside class="admin-card space-y-2 h-fit">
                @foreach($templateCatalog as $key => $meta)
                    <a href="#tpl-{{ $key }}" class="admin-nav-link {{ $key === $currentKey ? 'active' : '' }}" data-template-tab="{{ $key }}">{{ $meta['label'] }}</a>
                @endforeach
            </aside>

            <section class="space-y-4">
                @foreach($templateCatalog as $key => $meta)
                    @php($current = $templates[$key] ?? [])
                    <div id="tpl-{{ $key }}" class="admin-card space-y-3 {{ $key === $currentKey ? '' : 'hidden' }}" data-template-panel="{{ $key }}">
                        <h2 class="font-semibold text-slate">{{ $meta['label'] }}</h2>
                        <p class="text-sm text-slate">{{ $meta['description'] }}</p>
                        <form action="{{ route('dash.admin.email.templates.save', ['authkey' => $authkey, 'key' => $key]) }}" method="POST" class="space-y-3 template-form" data-template-form="{{ $key }}">
                            @csrf
                            <input name="subject" value="{{ old('subject', $current['subject'] ?? $meta['default_subject']) }}" placeholder="Subject" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <textarea name="body_html" rows="12" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" data-rich-editor>{{ old('body_html', $current['body_html'] ?? $meta['default_body_html']) }}</textarea>
                            <div class="rounded-squircle border border-slate/70 bg-white/5 p-3 text-xs text-slate">متغیرها: {{ implode(' , ', $meta['variables']) }}</div>
                            <div class="admin-actions">
                                <button class="admin-btn" type="submit"><span class="material-icons">save</span>ذخیره</button>
                                <a href="{{ route('dash.admin.email.templates.preview', ['authkey' => $authkey, 'key' => $key]) }}" target="_blank" class="admin-btn admin-btn-secondary"><span class="material-icons">preview</span>پیش‌نمایش سرور</a>
                            </div>
                        </form>
                    </div>
                @endforeach
            </section>
        </div>
    </div>

    <div id="common-features-tab" class="tab-content hidden">
        <div class="admin-card space-y-4">
            <h2 class="font-semibold text-slate border-b pb-2 dark:border-white/10">تنظیمات هدر و فوتر مشترک</h2>
            <form action="{{ route('dash.admin.email.templates.layout.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-4" id="global-email-settings-form">
                @csrf
                <div class="space-y-2">
                    <label class="text-sm font-bold block">لوگو ایمیل</label>
                    <div class="flex">
                        <input id="email-template-logo-path" name="logo_path" value="{{ old('logo_path', $layout['logo_path'] ?? '') }}" placeholder="مسیر لوگو (مثال: /assets/logo.png)" class="flex-grow rounded-r-lg border border-l-0 border-slate bg-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                        <button type="button" class="admin-btn admin-btn-secondary !rounded-r-none px-4" data-file-picker data-file-picker-multi="0" data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}" data-files-url="/uploads" data-cdn-base-url="{{ $fileManagerSettings['cdn_base_url'] ?? '' }}" data-file-pick-target="#email-template-logo-path">Browse</button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold block">HTML هدر</label>
                    <textarea name="header_html" rows="4" placeholder="محتوایی که در ابتدای تمامی ایمیل‌ها نمایش داده می‌شود" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" data-rich-editor>{{ old('header_html', $layout['header_html'] ?? '') }}</textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-bold block">HTML فوتر</label>
                    <textarea name="footer_html" rows="4" placeholder="محتوایی که در انتهای تمامی ایمیل‌ها نمایش داده می‌شود" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" data-rich-editor>{{ old('footer_html', $layout['footer_html'] ?? '') }}</textarea>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-bold">لینک‌های کاربردی در فوتر (حداکثر ۵ مورد)</label>
                        <button type="button" id="add-useful-link" class="admin-btn admin-btn-secondary text-xs py-1 px-2"><span class="material-icons text-xs">add</span>افزودن لینک</button>
                    </div>
                    <div id="useful-links-container" class="space-y-3">
                        @php($links = old('useful_links', $layout['useful_links'] ?? []))
                        @foreach($links as $index => $link)
                            <div class="grid gap-2 grid-cols-1 md:grid-cols-[1fr_1fr_40px] items-end useful-link-row">
                                <div>
                                    <label class="text-[10px] text-slate block mb-1">عنوان لینک</label>
                                    <input name="useful_links[{{ $index }}][label]" value="{{ $link['label'] ?? '' }}" placeholder="مثلا: درباره ما" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate block mb-1">آدرس (URL)</label>
                                    <input name="useful_links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}" placeholder="https://..." class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10">
                                </div>
                                <button type="button" class="remove-link-row h-[42px] flex items-center justify-center text-danger hover:bg-danger/10 rounded transition-colors"><span class="material-icons">delete</span></button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="admin-actions border-t pt-4"><button class="admin-btn" type="submit"><span class="material-icons">save</span>ذخیره ویژگی‌های مشترک</button></div>
            </form>
        </div>
    </div>

    <div id="module-settings-tab" class="tab-content hidden">
        <div class="admin-card space-y-4">
            <h2 class="font-semibold text-slate">تنظیمات ماژول ایمیل</h2>
            <div class="p-4 bg-primary/5 rounded-xl border border-primary/20 text-sm text-slate leading-7">
                <p>این ماژول برای تمامی فعالیت‌های کلیدی کاربر (مانند عضویت، تیکت، بازیابی رمز عبور و ...) امکان شخصی‌سازی ایمیل‌ها را فراهم می‌کند.</p>
                <p>تمامی ایمیل‌ها به صورت خودکار در <b>صف (Queue)</b> قرار گرفته و پردازش می‌شوند تا تاخیری در تجربه کاربر ایجاد نشود.</p>
            </div>
            <!-- More module settings could go here if needed -->
        </div>
    </div>

    <div class="admin-card mt-6 space-y-3" id="live-preview-card">
        <div class="flex items-center justify-between border-b pb-2 dark:border-white/10">
            <h3 class="font-semibold text-slate">پیش‌نمایش زنده (Live Preview)</h3>
            <span class="text-[10px] text-slate bg-slate-100 dark:bg-white/5 px-2 py-1 rounded">مشاهده آنی تغییرات</span>
        </div>
        <div class="relative bg-slate-200 dark:bg-slate-900 p-4 rounded-xl min-h-[560px] flex justify-center">
            <iframe id="email-live-preview" class="w-full max-w-[720px] rounded-lg border-none bg-white shadow-2xl" style="min-height: 520px"></iframe>
        </div>
    </div>

    @vite('resources/js/admin-email-templates.js')
</x-layouts.admin-dashboard>
