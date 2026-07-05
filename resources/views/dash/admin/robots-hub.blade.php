<x-layouts.admin-dashboard title="فایل Robots.txt" :helpModuleKey="'robots'">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">مدیریت Robots.txt</h1>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="robots-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-editor">
                <span class="material-icons text-base">edit</span>
                <span>ویرایشگر</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-tester">
                <span class="material-icons text-base">biotech</span>
                <span>تستر</span>
            </button>
        </div>
    </div>

    <div id="tab-editor" class="tab-content space-y-6">
        <form action="{{ route('dash.admin.robots.save', ['authkey' => $authkey]) }}" method="POST">
            @csrf
            <div class="admin-card">
                <div class="flex items-center gap-3 mb-4 border-b border-slate/10 pb-4">
                    <div class="rounded bg-primary/10 p-2 text-primary">
                        <span class="material-icons">description</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">محتوای فایل robots.txt</h2>
                        <p class="text-xs text-slate/60 mt-1">این فایل در ریشه سایت (public/robots.txt) قرار دارد.</p>
                    </div>
                </div>

                <div class="space-y-1">
                    <textarea name="content" class="admin-ltr w-full min-h-[400px] rounded border border-slate p-4 text-sm font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" placeholder="User-agent: *...">{{ $content }}</textarea>
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10">
                    <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                        <span class="material-icons">save</span>
                        ذخیره تغییرات
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div id="tab-tester" class="tab-content hidden space-y-6">
            <div class="grid gap-6 md:grid-cols-3">
                <div class="md:col-span-1 space-y-6">
                    <div class="admin-card">
                        <h3 class="font-bold text-slate mb-4 pb-2 border-b border-slate/10">تنظیمات تست</h3>
                        
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-sm font-bold text-slate px-1">User-Agent</label>
                                <div class="relative">
                                    <input type="text" id="test-agent" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" value="*">
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <button type="button" onclick="setAgent('*')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">همه (*)</button>
                                        <button type="button" onclick="setAgent('Googlebot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">Googlebot</button>
                                        <button type="button" onclick="setAgent('Bingbot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">Bingbot</button>
                                        <button type="button" onclick="setAgent('YandexBot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">YandexBot</button>
                                        <button type="button" onclick="setAgent('DuckDuckBot')" class="text-[10px] bg-slate/10 px-2 py-1 rounded hover:bg-primary/10 hover:text-primary transition-colors">DuckDuckBot</button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="text-sm font-bold text-slate px-1">آدرس مورد نظر (Path)</label>
                                <input type="text" id="test-path" class="admin-ltr w-full rounded border border-slate p-2 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 focus:ring-primary/20 focus:ring-2 outline-none" value="/" placeholder="/example-path">
                            </div>

                            <button onclick="runTest()" id="btn-run-test" class="admin-btn admin-btn-primary w-full justify-center h-11">
                                <span class="material-icons">play_arrow</span>
                                اجرای تست
                            </button>
                        </div>
                    </div>

                    <div id="test-summary" class="admin-card hidden">
                        <h3 class="font-bold text-slate mb-2">نتیجه تست:</h3>
                        <div id="test-status-box" class="p-4 rounded text-center font-black text-lg">
                            ---
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="admin-card !p-0 overflow-hidden min-h-[500px]">
                        <div class="bg-slate/5 p-4 border-b border-slate/10 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate">خروجی آنالیز فایل</span>
                            <span class="text-xs text-slate/50">خطوط منطبق با قرمز و سبز مشخص می‌شوند</span>
                        </div>
                        <div id="test-results-output" class="p-4 font-mono text-sm admin-ltr space-y-0.5 overflow-y-auto max-h-[600px]">
                            <div class="py-20 text-center text-slate/40 italic">برای شروع تست، دکمه «اجرای تست» را بزنید.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-robots-hub.js'])