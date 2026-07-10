<x-layouts.admin-dashboard title="تنظیمات جستجو" :helpModuleKey="'search'">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="تنظیمات جستجوی هوشمند">
        <x-slot:actions>
            <a href="{{ route('dash.admin.modules', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary" title="بازگشت">
                <span class="material-icons !text-base">arrow_forward</span>
                بازگشت به ماژول‌ها
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="search-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-settings">
            <span class="material-icons text-base">tune</span>
            <span>تنظیمات جستجو</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-guide">
            <span class="material-icons text-base">help</span>
            <span>راهنمای استفاده</span>
        </button>
    </x-admin.tab-bar>

    <div id="tab-settings" class="tab-content">
        <form action="{{ route('dash.admin.search.settings.save', ['authkey' => $authkey]) }}" method="POST">
            @csrf
            <input type="hidden" name="tab" value="tab-settings">

            <div class="admin-card is-surface">
                <h3 class="font-bold text-slate mb-2 flex items-center gap-2 text-right">
                    <span class="material-icons text-primary">search</span>
                    <span>موارد قابل جستجو</span>
                </h3>
                <p class="text-xs text-slate/60 mb-6">در این بخش مشخص کنید که در سیستم جستجوی هدر، چه مواردی جستجو شوند.</p>

                <div class="space-y-4 max-w-xl">
                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <span class="material-icons text-slate/50">settings_input_component</span>
                            <div>
                                <p class="text-sm font-bold text-slate">ماژول‌ها</p>
                                <p class="text-[10px] text-slate/60">جستجو در نام و توضیحات ماژول‌های سیستم</p>
                            </div>
                        </div>
                        <label class="admin-switch"><input type="checkbox" name="modules" value="1" class="admin-switch-input" {{ ($settings['modules'] ?? true) ? 'checked' : '' }}><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <span class="material-icons text-slate/50">group</span>
                            <div>
                                <p class="text-sm font-bold text-slate">کاربران</p>
                                <p class="text-[10px] text-slate/60">جستجو در نام، ایمیل و شماره تماس کاربران</p>
                            </div>
                        </div>
                        <label class="admin-switch"><input type="checkbox" name="users" value="1" class="admin-switch-input" {{ ($settings['users'] ?? true) ? 'checked' : '' }}><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <span class="material-icons text-slate/50">inventory_2</span>
                            <div>
                                <p class="text-sm font-bold text-slate">محصولات</p>
                                <p class="text-[10px] text-slate/60">جستجو در نام و کد محصولات</p>
                            </div>
                        </div>
                        <label class="admin-switch"><input type="checkbox" name="products" value="1" class="admin-switch-input" {{ ($settings['products'] ?? true) ? 'checked' : '' }}><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-white/5">
                        <div class="flex items-center gap-3">
                            <span class="material-icons text-slate/50">layers</span>
                            <div>
                                <p class="text-sm font-bold text-slate">بخش‌های پنل</p>
                                <p class="text-[10px] text-slate/60">جستجو در صفحات و بخش‌های مختلف مدیریت</p>
                            </div>
                        </div>
                        <label class="admin-switch"><input type="checkbox" name="sections" value="1" class="admin-switch-input" {{ ($settings['sections'] ?? true) ? 'checked' : '' }}><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-white/5">
                    <button class="admin-btn admin-btn-primary px-10 h-12">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="tab-guide" class="tab-content hidden admin-card is-surface !p-6">
        <h3 class="font-bold text-slate mb-4 flex items-center gap-2">
            <span class="material-icons text-primary">info</span>
            <span>راهنمای استفاده</span>
        </h3>
        <div class="text-sm text-slate/80 space-y-2 leading-loose">
            <p>۱. جستجو در هدر پنل ادمین برای دسترسی سریع به بخش‌های مختلف طراحی شده است.</p>
            <p>۲. می‌توانید با استفاده از فیلتر کنار فیلد جستجو، نتایج را محدود به یک دسته‌بندی خاص کنید.</p>
            <p>۳. برای باز شدن مودال جستجو می‌توانید روی فیلد کلیک کرده یا از کلید میانبر استفاده کنید.</p>
            <p>۴. نتایج به صورت ایجکس (Ajax) و با تایپ حداقل ۲ کاراکتر نمایش داده می‌شوند.</p>
        </div>
    </div>

    @vite(['resources/js/admin-search-hub.js'])
</x-layouts.admin-dashboard>
