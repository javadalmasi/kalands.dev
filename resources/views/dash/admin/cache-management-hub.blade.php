<x-layouts.admin-dashboard title="مدیریت کش">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت کش</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('dash.admin.cache-management.htaccess.backup', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary h-10 px-4 text-xs">
                <span class="material-icons text-sm">download</span>
                دانلود آخرین بکاپ .htaccess
            </a>
        </div>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="cache-management-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-webservices">
                <span class="material-icons text-base">bolt</span>
                <span>وب‌سرویس‌ها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-optimization">
                <span class="material-icons text-base">settings_suggest</span>
                <span>بهینه‌سازی وب‌سرور</span>
            </button>
        </div>
    </div>

    <div id="tab-webservices" class="tab-content space-y-6">
        <form action="{{ route('dash.admin.cache-management.settings.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf
            {{-- Autocomplete Cache --}}
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">search</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات کش Autocomplete</h2>
                            <p class="text-xs text-slate/60 mt-1">مدیریت هدرهای کش برای وب‌سرویس جستجوی هوشمند</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-bold block mb-2">زمان TTL (ثانیه)</label>
                        <input type="number" name="autocomplete_ttl" value="{{ $settings['autocomplete_ttl'] ?? 31536000 }}" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="31536000">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="autocomplete_cache_type" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <option value="public" @selected(($settings['autocomplete_cache_type'] ?? 'public') === 'public')>Public</option>
                            <option value="private" @selected(($settings['autocomplete_cache_type'] ?? 'public') === 'private')>Private</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">LiteSpeed Cache</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="autocomplete_litespeed" value="1" class="admin-switch-input" @checked($settings['autocomplete_litespeed'] ?? true)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">هدرهای سفارشی</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="autocomplete_custom_enabled" value="1" class="admin-switch-input" id="sw-autocomplete-custom" @checked($settings['autocomplete_custom_enabled'] ?? false)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-4 pt-6 border-t border-slate/10 {{ ($settings['autocomplete_custom_enabled'] ?? false) ? '' : 'hidden' }}" id="autocomplete-custom-fields">
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom Cache-Control</label>
                        <input type="text" name="autocomplete_custom_cc" value="{{ $settings['autocomplete_custom_cc'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. public, max-age=3600">
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom X-LiteSpeed-Cache</label>
                        <input type="text" name="autocomplete_custom_lsc" value="{{ $settings['autocomplete_custom_lsc'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. public, max-age=3600">
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom CDN-Cache-Control</label>
                        <input type="text" name="autocomplete_custom_cdn" value="{{ $settings['autocomplete_custom_cdn'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. max-age=3600">
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom Cloudflare-CDN-Cache</label>
                        <input type="text" name="autocomplete_custom_cf" value="{{ $settings['autocomplete_custom_cf'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. max-age=3600">
                    </div>
                </div>
            </div>

            {{-- Visitor Info Cache --}}
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">info</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات کش Visitor Info (/api/info)</h2>
                            <p class="text-xs text-slate/60 mt-1">مدیریت هدرهای کش برای وب‌سرویس مشخصات بازدیدکننده</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-bold block mb-2">زمان TTL (ثانیه)</label>
                        <input type="number" name="visitor_info_ttl" value="{{ $settings['visitor_info_ttl'] ?? 3600 }}" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="3600">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="visitor_info_cache_type" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <option value="private" @selected(($settings['visitor_info_cache_type'] ?? 'private') === 'private')>Private</option>
                            <option value="public" @selected(($settings['visitor_info_cache_type'] ?? 'private') === 'public')>Public</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">LiteSpeed Cache</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="visitor_info_litespeed" value="1" class="admin-switch-input" @checked($settings['visitor_info_litespeed'] ?? true)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">هدرهای سفارشی</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="visitor_info_custom_enabled" value="1" class="admin-switch-input" id="sw-visitor-info-custom" @checked($settings['visitor_info_custom_enabled'] ?? false)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-4 pt-6 border-t border-slate/10 {{ ($settings['visitor_info_custom_enabled'] ?? false) ? '' : 'hidden' }}" id="visitor-info-custom-fields">
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom Cache-Control</label>
                        <input type="text" name="visitor_info_custom_cc" value="{{ $settings['visitor_info_custom_cc'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. private, max-age=3600">
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom X-LiteSpeed-Cache</label>
                        <input type="text" name="visitor_info_custom_lsc" value="{{ $settings['visitor_info_custom_lsc'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. private, max-age=3600">
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom CDN-Cache-Control</label>
                        <input type="text" name="visitor_info_custom_cdn" value="{{ $settings['visitor_info_custom_cdn'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. max-age=3600">
                    </div>
                    <div>
                        <label class="text-xs font-bold block mb-1.5 opacity-70">Custom Cloudflare-CDN-Cache</label>
                        <input type="text" name="visitor_info_custom_cf" value="{{ $settings['visitor_info_custom_cf'] ?? '' }}" class="admin-ltr w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="e.g. max-age=3600">
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6">
                <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                    <span class="material-icons">save</span>
                    ذخیره کلیه تنظیمات وب‌سرویس‌ها
                </button>
            </div>
        </form>
    </div>

    <div id="tab-optimization" class="tab-content hidden space-y-6">
        <form action="{{ route('dash.admin.cache-management.htaccess.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">dns</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات وب‌سرور LiteSpeed</h2>
                            <p class="text-xs text-slate/60 mt-1">مدیریت مستقیم پارامترهای بهینه‌سازی در فایل .htaccess</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-x-8 gap-y-6 md:grid-cols-2">
                    <div class="p-4 rounded-xl border border-slate/10 bg-slate/5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-slate">Cache Lookup (Global)</h4>
                                <p class="text-[10px] text-slate/50 mt-1">فعال‌سازی کلی جستجوی کش</p>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="cache_lookup" value="1" class="admin-switch-input" id="sw-cache-lookup" @checked($litespeedConfig['cache_lookup'] ?? false)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate/10 {{ ($litespeedConfig['cache_lookup'] ?? false) ? '' : 'opacity-50 pointer-events-none' }}" id="cache-lookup-options">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-slate/80">ESI Options</span>
                                <label class="admin-switch !w-9 !h-5">
                                    <input type="checkbox" name="cache_lookup_esi" value="1" class="admin-switch-input" @checked($litespeedConfig['cache_lookup_esi'] ?? false)>
                                    <div class="admin-switch-track"></div>
                                    <div class="admin-switch-ball"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-slate/80">Crawler Options</span>
                                <label class="admin-switch !w-9 !h-5">
                                    <input type="checkbox" name="cache_lookup_crawler" value="1" class="admin-switch-input" @checked($litespeedConfig['cache_lookup_crawler'] ?? false)>
                                    <div class="admin-switch-track"></div>
                                    <div class="admin-switch-ball"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl border border-slate/10 bg-slate/5">
                        <div>
                            <h4 class="text-sm font-bold text-slate">LSPHP Process Group</h4>
                            <p class="text-[10px] text-slate/50 mt-1">بهینه‌سازی مدیریت پروسه‌های PHP</p>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" name="process_group" value="1" class="admin-switch-input" @checked($litespeedConfig['process_group'] ?? false)>
                            <div class="admin-switch-track"></div>
                            <div class="admin-switch-ball"></div>
                        </label>
                    </div>

                    <div class="p-4 rounded-xl border border-slate/10 bg-slate/5">
                        <label class="text-sm font-bold block mb-2">LSPHP Workers</label>
                        <input type="number" name="workers" value="{{ $litespeedConfig['workers'] ?? 100 }}" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10" min="1" max="1000">
                        <p class="text-[10px] text-slate/50 mt-1.5">تعداد ورکر‌های PHP (پیش‌فرض: ۱۰۰)</p>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl border border-slate/10 bg-slate/5">
                        <div>
                            <h4 class="text-sm font-bold text-slate">QUIC Enable</h4>
                            <p class="text-[10px] text-slate/50 mt-1">فعال‌سازی پروتکل سریع UDP/QUIC</p>
                        </div>
                        <label class="admin-switch">
                            <input type="checkbox" name="quic" value="1" class="admin-switch-input" @checked($litespeedConfig['quic'] ?? false)>
                            <div class="admin-switch-track"></div>
                            <div class="admin-switch-ball"></div>
                        </label>
                    </div>

                    <div class="p-4 rounded-xl border border-slate/10 bg-slate/5">
                        <label class="text-sm font-bold block mb-2">SpdyEnabled (HTTP2/HTTP3)</label>
                        <select name="spdy" class="w-full rounded-lg border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10">
                            <option value="off" @selected($litespeedConfig['spdy'] === 'off')>غیرفعال (Off)</option>
                            <option value="http2" @selected($litespeedConfig['spdy'] === 'http2')>HTTP/2 Only</option>
                            <option value="http3" @selected($litespeedConfig['spdy'] === 'http3')>HTTP/3 Only</option>
                            <option value="http3_http2" @selected($litespeedConfig['spdy'] === 'http3_http2')>HTTP/3 & HTTP/2</option>
                        </select>
                        <p class="text-[10px] text-slate/50 mt-1.5">تنظیم اولویت پروتکل‌های Spdy</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-amber-600 font-bold text-[11px]">
                        <span class="material-icons text-sm">warning</span>
                        تغییرات مستقیماً در فایل .htaccess اعمال می‌شود.
                    </div>
                    <button class="admin-btn admin-btn-primary px-10 h-12 justify-center">
                        <span class="material-icons">bolt</span>
                        اعمال بهینه‌سازی‌ها
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('[data-tab-target]');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-tab-target');
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
                    document.getElementById(target).classList.remove('hidden');

                    tabs.forEach(t => {
                        t.classList.remove('text-primary', 'border-b-2', 'border-primary', 'font-bold');
                        t.classList.add('text-slate', 'font-medium');
                    });
                    tab.classList.add('text-primary', 'border-b-2', 'border-primary', 'font-bold');
                    tab.classList.remove('text-slate', 'font-medium');

                    // Update URL
                    const url = new URL(window.location);
                    url.searchParams.set('tab', target);
                    window.history.pushState({}, '', url);
                });
            });

            // Handle initial tab from URL
            const params = new URLSearchParams(window.location.search);
            const activeTab = params.get('tab');
            if (activeTab) {
                const tabBtn = document.querySelector(`[data-tab-target="${activeTab}"]`);
                if (tabBtn) tabBtn.click();
            }

            // UI Logic for Cache Lookup dependencies
            const swCacheLookup = document.getElementById('sw-cache-lookup');
            const cacheOptions = document.getElementById('cache-lookup-options');
            if (swCacheLookup && cacheOptions) {
                swCacheLookup.addEventListener('change', () => {
                    cacheOptions.classList.toggle('opacity-50', !swCacheLookup.checked);
                    cacheOptions.classList.toggle('pointer-events-none', !swCacheLookup.checked);
                });
            }

            // Custom Headers Toggles
            const swAutocompleteCustom = document.getElementById('sw-autocomplete-custom');
            const autocompleteFields = document.getElementById('autocomplete-custom-fields');
            if (swAutocompleteCustom && autocompleteFields) {
                swAutocompleteCustom.addEventListener('change', () => {
                    autocompleteFields.classList.toggle('hidden', !swAutocompleteCustom.checked);
                });
            }

            const swVisitorCustom = document.getElementById('sw-visitor-info-custom');
            const visitorFields = document.getElementById('visitor-info-custom-fields');
            if (swVisitorCustom && visitorFields) {
                swVisitorCustom.addEventListener('change', () => {
                    visitorFields.classList.toggle('hidden', !swVisitorCustom.checked);
                });
            }
        });
    </script>
</x-layouts.admin-dashboard>
