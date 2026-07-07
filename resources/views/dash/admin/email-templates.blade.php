<x-layouts.admin-dashboard title="تمپلیت ایمیل‌ها" :helpModuleKey="'email_templates'">
    @php($authkey = request()->route('authkey'))
    @php($currentKey = isset($templateCatalog[$activeKey ?? '']) ? ($activeKey ?? '') : array_key_first($templateCatalog))

    <h1 class="admin-page-title">مدیریت تمپلیت ایمیل‌ها</h1>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="email-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="templates-tab">
                <span class="material-icons text-base">mail</span>
                <span>تمپلیت‌ها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="common-features-tab">
                <span class="material-icons text-base">palette</span>
                <span>هدر و فوتر</span>
            </button>
        </div>
    </div>

    <div id="templates-tab" class="tab-content">
        <div class="grid gap-4 lg:grid-cols-[280px_1fr]" id="templates-grid">
            <aside class="admin-card space-y-2 h-fit">
                @foreach($templateCatalog as $key => $meta)
                    <a href="#tpl-{{ $key }}" class="admin-nav-link {{ $key === $currentKey ? 'active' : '' }}" data-template-tab="{{ $key }}">{{ $meta['label'] }}</a>
                @endforeach
            </aside>

            <section class="space-y-4" id="editor-section">
                @foreach($templateCatalog as $key => $meta)
                    @php($current = $templates[$key] ?? [])
                    <div id="tpl-{{ $key }}" class="admin-card space-y-3 {{ $key === $currentKey ? '' : 'hidden' }}" data-template-panel="{{ $key }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="font-semibold text-slate">{{ $meta['label'] }}</h2>
                                <p class="text-sm text-slate">{{ $meta['description'] }}</p>
                            </div>
                            <button id="preview-toggle-btn" class="text-slate hover:text-primary transition-colors p-2 hover:bg-slate/10 rounded" title="نمایش پیش‌نمایش">
                                <span class="material-icons text-base">preview</span>
                            </button>
                        </div>
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

        <aside class="admin-card space-y-2 h-fit sticky top-4 hidden" id="live-preview-card">
            <div class="flex items-center justify-between border-b pb-2 dark:border-white/10">
                <h4 class="font-semibold text-slate text-sm flex items-center gap-2">
                    <span class="material-icons text-base">preview</span>
                    پیش‌نمایش
                </h4>
                <button id="preview-close-btn" class="text-slate hover:text-primary transition-colors p-1 hover:bg-slate/10 rounded" title="بسته کردن">
                    <span class="material-icons text-base">close</span>
                </button>
            </div>
            <div class="overflow-hidden rounded-lg bg-slate-100 dark:bg-slate-800" id="preview-container">
                <iframe id="email-live-preview" class="w-full border-none bg-white" style="min-height: 600px"></iframe>
            </div>
        </aside>
    </div>

    <div id="common-features-tab" class="tab-content hidden">
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="admin-card space-y-4">
                <h2 class="font-semibold text-slate border-b pb-2 dark:border-white/10">لوگو و هدر/فوتر</h2>
                <form action="{{ route('dash.admin.email.templates.layout.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-4" id="global-email-settings-form">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-bold block">لوگو ایمیل</label>
                        <div class="flex">
                            <input id="email-template-logo-path" name="logo_path" value="{{ old('logo_path', $layout['logo_path'] ?? '') }}" placeholder="مسیر لوگو (مثال: /assets/logo.png)" class="flex-grow rounded-r-lg border border-l-0 border-slate bg-slate p-2.5 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <button type="button" class="admin-btn admin-btn-secondary !rounded-r-none px-4" data-file-picker data-file-picker-multi="0" data-explorer-url="{{ route('dash.admin.modules.explore', ['authkey' => $authkey]) }}" data-files-url="/uploads" data-cdn-base-url="{{ $fileManagerSettings['cdn_base_url'] ?? '' }}" data-file-pick-target="#email-template-logo-path">Browse</button>
                        </div>
                    </div>

                    <details class="group header-footer-group" open>
                        <summary class="cursor-pointer py-2 font-bold text-slate flex items-center gap-2 hover:text-primary transition-colors">
                            <span class="material-icons text-base group-open:rotate-180 transition-transform">expand_more</span>
                            HTML هدر
                        </summary>
                        <div class="space-y-2 pt-2">
                            <textarea name="header_html" rows="3" placeholder="محتوایی که در ابتدای تمامی ایمیل‌ها نمایش داده می‌شود" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" data-rich-editor>{{ old('header_html', $layout['header_html'] ?? '') }}</textarea>
                        </div>
                    </details>

                    <details class="group header-footer-group">
                        <summary class="cursor-pointer py-2 font-bold text-slate flex items-center gap-2 hover:text-primary transition-colors">
                            <span class="material-icons text-base group-open:rotate-180 transition-transform">expand_more</span>
                            HTML فوتر
                        </summary>
                        <div class="space-y-2 pt-2">
                            <textarea name="footer_html" rows="3" placeholder="محتوایی که در انتهای تمامی ایمیل‌ها نمایش داده می‌شود" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" data-rich-editor>{{ old('footer_html', $layout['footer_html'] ?? '') }}</textarea>
                        </div>
                    </details>

                    <div class="admin-actions border-t pt-4"><button class="admin-btn" type="submit"><span class="material-icons">save</span>ذخیره</button></div>
                </form>
            </div>

            <div class="admin-card space-y-4">
                <h2 class="font-semibold text-slate border-b pb-2 dark:border-white/10">لینک‌های فوتر</h2>
                <form method="POST" class="space-y-3">
                    @csrf
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-bold">لینک‌ها (حداکثر ۵ مورد)</label>
                        <button type="button" id="add-useful-link" class="admin-btn admin-btn-secondary text-xs py-1 px-2"><span class="material-icons text-xs">add</span></button>
                    </div>
                    <div id="useful-links-container" class="space-y-2">
                        @php($links = old('useful_links', $layout['useful_links'] ?? []))
                        @foreach($links as $index => $link)
                            <div class="grid gap-2 grid-cols-[1fr_30px] items-end useful-link-row">
                                <div class="grid gap-1">
                                    <input name="useful_links[{{ $index }}][label]" value="{{ $link['label'] ?? '' }}" placeholder="عنوان" class="rounded border border-slate bg-slate p-2 text-xs w-full dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <input name="useful_links[{{ $index }}][url]" value="{{ $link['url'] ?? '' }}" placeholder="https://..." class="rounded border border-slate bg-slate p-2 text-xs w-full dark:bg-slate-800 dark:text-white dark:border-white/10">
                                </div>
                                <button type="button" class="remove-link-row h-[42px] flex items-center justify-center text-danger hover:bg-danger/10 rounded transition-colors">
                                    <span class="material-icons text-sm">delete</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </form>
            </div>
        </div>
    </div>

    @vite('resources/js/admin-email-templates.js')
</x-layouts.admin-dashboard>
