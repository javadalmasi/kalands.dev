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
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
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
                                <label class="text-sm font-bold block mb-1">پیشرفته</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="autocomplete_custom_enabled" value="1" class="admin-switch-input" id="sw-autocomplete-custom" @checked($settings['autocomplete_custom_enabled'] ?? false)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2 pt-6 border-t border-slate/10 {{ ($settings['autocomplete_custom_enabled'] ?? false) ? '' : 'hidden' }}" id="autocomplete-custom-fields">
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="autocomplete_custom_cc_ttl" value="{{ $settings['autocomplete_custom_cc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_cc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['autocomplete_custom_cc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['autocomplete_custom_cc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom X-LiteSpeed-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="autocomplete_custom_lsc_ttl" value="{{ $settings['autocomplete_custom_lsc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_lsc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['autocomplete_custom_lsc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['autocomplete_custom_lsc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom CDN-Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="autocomplete_custom_cdn_ttl" value="{{ $settings['autocomplete_custom_cdn_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_cdn_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['autocomplete_custom_cdn_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['autocomplete_custom_cdn_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cloudflare-CDN-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="autocomplete_custom_cf_ttl" value="{{ $settings['autocomplete_custom_cf_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_cf_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['autocomplete_custom_cf_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['autocomplete_custom_cf_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Affiliate Redirect Cache --}}
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">open_in_new</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات کش Affiliate (/go/*)</h2>
                            <p class="text-xs text-slate/60 mt-1">مدیریت هدرهای کش برای مسیرهای ریدایرکت لینک‌های وابسته</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-bold block mb-2">زمان TTL (ثانیه)</label>
                        <input type="number" name="affiliate_ttl" value="{{ $settings['affiliate_ttl'] ?? 31536000 }}" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="31536000">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="affiliate_cache_type" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <option value="public" @selected(($settings['affiliate_cache_type'] ?? 'public') === 'public')>Public</option>
                            <option value="private" @selected(($settings['affiliate_cache_type'] ?? 'public') === 'private')>Private</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">LiteSpeed Cache</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="affiliate_litespeed" value="1" class="admin-switch-input" @checked($settings['affiliate_litespeed'] ?? true)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">پیشرفته</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="affiliate_custom_enabled" value="1" class="admin-switch-input" id="sw-affiliate-custom" @checked($settings['affiliate_custom_enabled'] ?? false)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2 pt-6 border-t border-slate/10 {{ ($settings['affiliate_custom_enabled'] ?? false) ? '' : 'hidden' }}" id="affiliate-custom-fields">
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="affiliate_custom_cc_ttl" value="{{ $settings['affiliate_custom_cc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_cc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['affiliate_custom_cc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['affiliate_custom_cc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom X-LiteSpeed-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="affiliate_custom_lsc_ttl" value="{{ $settings['affiliate_custom_lsc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_lsc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['affiliate_custom_lsc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['affiliate_custom_lsc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom CDN-Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="affiliate_custom_cdn_ttl" value="{{ $settings['affiliate_custom_cdn_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_cdn_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['affiliate_custom_cdn_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['affiliate_custom_cdn_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cloudflare-CDN-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="affiliate_custom_cf_ttl" value="{{ $settings['affiliate_custom_cf_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_cf_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['affiliate_custom_cf_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['affiliate_custom_cf_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Product Page Cache --}}
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">inventory_2</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات کش Product Page (/product/*)</h2>
                            <p class="text-xs text-slate/60 mt-1">مدیریت هدرهای کش برای صفحات محصول</p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <label class="text-sm font-bold block mb-2">زمان TTL (ثانیه)</label>
                        <input type="number" name="product_ttl" value="{{ $settings['product_ttl'] ?? 86400 }}" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="86400">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="product_cache_type" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                            <option value="public" @selected(($settings['product_cache_type'] ?? 'public') === 'public')>Public</option>
                            <option value="private" @selected(($settings['product_cache_type'] ?? 'public') === 'private')>Private</option>
                        </select>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">LiteSpeed Cache</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="product_litespeed" value="1" class="admin-switch-input" @checked($settings['product_litespeed'] ?? true)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate/10 bg-slate/5">
                            <div class="flex-1">
                                <label class="text-sm font-bold block mb-1">پیشرفته</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="product_custom_enabled" value="1" class="admin-switch-input" id="sw-product-custom" @checked($settings['product_custom_enabled'] ?? false)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2 pt-6 border-t border-slate/10 {{ ($settings['product_custom_enabled'] ?? false) ? '' : 'hidden' }}" id="product-custom-fields">
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="product_custom_cc_ttl" value="{{ $settings['product_custom_cc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_cc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['product_custom_cc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['product_custom_cc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom X-LiteSpeed-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="product_custom_lsc_ttl" value="{{ $settings['product_custom_lsc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_lsc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['product_custom_lsc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['product_custom_lsc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom CDN-Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="product_custom_cdn_ttl" value="{{ $settings['product_custom_cdn_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_cdn_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['product_custom_cdn_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['product_custom_cdn_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cloudflare-CDN-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="product_custom_cf_ttl" value="{{ $settings['product_custom_cf_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_cf_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['product_custom_cf_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['product_custom_cf_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
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
                                <label class="text-sm font-bold block mb-1">پیشرفته</label>
                            </div>
                            <label class="admin-switch">
                                <input type="checkbox" name="visitor_info_custom_enabled" value="1" class="admin-switch-input" id="sw-visitor-info-custom" @checked($settings['visitor_info_custom_enabled'] ?? false)>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2 pt-6 border-t border-slate/10 {{ ($settings['visitor_info_custom_enabled'] ?? false) ? '' : 'hidden' }}" id="visitor-info-custom-fields">
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="visitor_info_custom_cc_ttl" value="{{ $settings['visitor_info_custom_cc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_cc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['visitor_info_custom_cc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['visitor_info_custom_cc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom X-LiteSpeed-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="visitor_info_custom_lsc_ttl" value="{{ $settings['visitor_info_custom_lsc_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_lsc_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['visitor_info_custom_lsc_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['visitor_info_custom_lsc_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom CDN-Cache-Control</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="visitor_info_custom_cdn_ttl" value="{{ $settings['visitor_info_custom_cdn_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_cdn_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['visitor_info_custom_cdn_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['visitor_info_custom_cdn_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="border border-slate/10 rounded-xl p-4 bg-slate/5">
                        <label class="text-xs font-bold block mb-3 text-slate">Custom Cloudflare-CDN-Cache</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">زمان TTL (ثانیه)</label>
                                <input type="number" name="visitor_info_custom_cf_ttl" value="{{ $settings['visitor_info_custom_cf_ttl'] ?? '' }}" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_cf_type" class="w-full rounded border border-slate p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10">
                                    <option value="">پیش‌فرض</option>
                                    <option value="public" @selected(($settings['visitor_info_custom_cf_type'] ?? '') === 'public')>Public</option>
                                    <option value="private" @selected(($settings['visitor_info_custom_cf_type'] ?? '') === 'private')>Private</option>
                                </select>
                            </div>
                        </div>
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

    <div id="tab-help" class="tab-content hidden">
        <div class="flex gap-6 items-start">
            <div class="flex-1 min-w-0">
                <div class="admin-card space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate/10 pb-4">
                        <span class="material-icons text-2xl text-primary">help_outline</span>
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول مدیریت کش</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-webservices">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">bolt</span>
                                تنظیمات وب‌سرویس‌ها
                            </h3>
                            <p>در این بخش می‌توانید تنظیمات کش برای وب‌سرویس‌های Autocomplete و Visitor Info را مدیریت کنید.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">وب‌سرویس‌های قابل تنظیم</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li><b>Autocomplete:</b> وب‌سرویس جستجوی هوشمند سایت</li>
                                    <li><b>Affiliate:</b> وب‌سرویس ریدایرکت لینک‌های وابسته (/go/*)</li>
                                    <li><b>Product Page:</b> صفحات محصول (/product/*)</li>
                                    <li><b>Visitor Info:</b> وب‌سرویس مشخصات بازدیدکننده (IP، موقعیت، مرورگر)</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-ttl">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">schedule</span>
                                تنظیمات TTL و Cache-Control
                            </h3>
                            <p>TTL (Time To Live) زمان نگهداری داده‌ها در کش را مشخص می‌کند. مقدار پیش‌فرض برای Autocomplete و Affiliate ۳۱۵۳۶۰۰۰ ثانیه (یک سال) و برای Product Page ۸۶۴۰۰ ثانیه (۲۴ ساعت) است.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">انواع کش</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li><b>Public:</b> قابل ذخیره در کش عمومی (CDN، پراکسی)</li>
                                    <li><b>Private:</b> فقط برای مرورگر کاربر قابل ذخیره است</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-optimization">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">settings_suggest</span>
                                بهینه‌سازی وب‌سرور LiteSpeed
                            </h3>
                            <p>این بخش تنظیمات مربوط به بهینه‌سازی سرور LiteSpeed را در فایل .htaccess مدیریت می‌کند.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">تنظیمات قابل تغییر</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li>Cache Lookup و وابستگی‌ها (ESI، Crawler)</li>
                                    <li>QUIC Enable برای سرعت بالاتر ارتباطات UDP</li>
                                    <li>SpdyEnabled برای فعال‌سازی HTTP/2 و HTTP/3</li>
                                    <li>LSPHP Workers و Process Group برای بهینه‌سازی PHP</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-custom-headers">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">tune</span>
                                پیشرفته
                            </h3>
                            <p>با فعال‌سازی حالت پیشرفته، می‌توانید به صورت جداگانه برای هر هدر (Cache-Control، X-LiteSpeed-Cache، CDN-Cache-Control و Cloudflare-CDN-Cache) مقدار TTL و نوع کش را تعیین کنید.</p>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-webservices" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">وب‌سرویس‌ها</a>
                        <a href="#doc-ttl" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">TTL و Cache-Control</a>
                        <a href="#doc-optimization" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">بهینه‌سازی LiteSpeed</a>
                        <a href="#doc-custom-headers" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">پیشرفته</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

    @vite(['resources/js/admin-cache-hub.js'])
</x-layouts.admin-dashboard>
