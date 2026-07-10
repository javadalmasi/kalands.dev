<x-layouts.admin-dashboard title="مدیریت کش" :helpModuleKey="'cache_management'">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="مدیریت کش">
        <x-slot:actions>
            <a href="{{ route('dash.admin.cache-management.htaccess.backup', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary h-10 px-4 text-xs">
                <span class="material-icons text-sm">download</span>
                دانلود آخرین بکاپ .htaccess
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="cache-management-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-webservices">
            <span class="material-icons text-base">bolt</span>
            <span>وب‌سرویس‌ها</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-optimization">
            <span class="material-icons text-base">settings_suggest</span>
            <span>بهینه‌سازی وب‌سرور</span>
        </button>
    </x-admin.tab-bar>

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
                        <input type="number" name="autocomplete_ttl" value="{{ $settings['autocomplete_ttl'] ?? 31536000 }}" class="admin-input" placeholder="31536000">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="autocomplete_cache_type" class="admin-input">
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
                                <input type="number" name="autocomplete_custom_cc_ttl" value="{{ $settings['autocomplete_custom_cc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_cc_type" class="admin-input">
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
                                <input type="number" name="autocomplete_custom_lsc_ttl" value="{{ $settings['autocomplete_custom_lsc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_lsc_type" class="admin-input">
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
                                <input type="number" name="autocomplete_custom_cdn_ttl" value="{{ $settings['autocomplete_custom_cdn_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_cdn_type" class="admin-input">
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
                                <input type="number" name="autocomplete_custom_cf_ttl" value="{{ $settings['autocomplete_custom_cf_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="autocomplete_custom_cf_type" class="admin-input">
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
                        <input type="number" name="affiliate_ttl" value="{{ $settings['affiliate_ttl'] ?? 31536000 }}" class="admin-input" placeholder="31536000">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="affiliate_cache_type" class="admin-input">
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
                                <input type="number" name="affiliate_custom_cc_ttl" value="{{ $settings['affiliate_custom_cc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_cc_type" class="admin-input">
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
                                <input type="number" name="affiliate_custom_lsc_ttl" value="{{ $settings['affiliate_custom_lsc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_lsc_type" class="admin-input">
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
                                <input type="number" name="affiliate_custom_cdn_ttl" value="{{ $settings['affiliate_custom_cdn_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_cdn_type" class="admin-input">
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
                                <input type="number" name="affiliate_custom_cf_ttl" value="{{ $settings['affiliate_custom_cf_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="affiliate_custom_cf_type" class="admin-input">
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
                        <input type="number" name="product_ttl" value="{{ $settings['product_ttl'] ?? 86400 }}" class="admin-input" placeholder="86400">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="product_cache_type" class="admin-input">
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
                                <input type="number" name="product_custom_cc_ttl" value="{{ $settings['product_custom_cc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_cc_type" class="admin-input">
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
                                <input type="number" name="product_custom_lsc_ttl" value="{{ $settings['product_custom_lsc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_lsc_type" class="admin-input">
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
                                <input type="number" name="product_custom_cdn_ttl" value="{{ $settings['product_custom_cdn_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_cdn_type" class="admin-input">
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
                                <input type="number" name="product_custom_cf_ttl" value="{{ $settings['product_custom_cf_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="product_custom_cf_type" class="admin-input">
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
                        <input type="number" name="visitor_info_ttl" value="{{ $settings['visitor_info_ttl'] ?? 3600 }}" class="admin-input" placeholder="3600">
                    </div>

                    <div>
                        <label class="text-sm font-bold block mb-2">نوع کش (Cache-Control)</label>
                        <select name="visitor_info_cache_type" class="admin-input">
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
                                <input type="number" name="visitor_info_custom_cc_ttl" value="{{ $settings['visitor_info_custom_cc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_cc_type" class="admin-input">
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
                                <input type="number" name="visitor_info_custom_lsc_ttl" value="{{ $settings['visitor_info_custom_lsc_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_lsc_type" class="admin-input">
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
                                <input type="number" name="visitor_info_custom_cdn_ttl" value="{{ $settings['visitor_info_custom_cdn_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_cdn_type" class="admin-input">
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
                                <input type="number" name="visitor_info_custom_cf_ttl" value="{{ $settings['visitor_info_custom_cf_ttl'] ?? '' }}" class="admin-input">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold block mb-1 opacity-70">نوع کش</label>
                                <select name="visitor_info_custom_cf_type" class="admin-input">
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
                        <input type="number" name="workers" value="{{ $litespeedConfig['workers'] ?? 100 }}" class="admin-input" min="1" max="1000">
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
                        <select name="spdy" class="admin-input">
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


    @vite(['resources/js/admin-cache-hub.js'])
</x-layouts.admin-dashboard>
