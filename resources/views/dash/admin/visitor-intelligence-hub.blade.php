<x-layouts.admin-dashboard title="هوشمندی بازدیدکنندگان">
    @php($authkey = request()->route('authkey'))

    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">هوشمندی بازدیدکنندگان</h1>
    </div>

    <div class="admin-card mb-6 !p-0 overflow-hidden">
        <div class="flex border-b border-slate dark:border-white/10 overflow-x-auto whitespace-nowrap bg-slate/5" id="visitor-intelligence-tabs">
            <button class="px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2" data-tab-target="tab-detection">
                <span class="material-icons text-base">psychology</span>
                <span>الگوهای تشخیص</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-asn-checker">
                <span class="material-icons text-base">lan</span>
                <span>بررسی ASNها</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-test">
                <span class="material-icons text-base">science</span>
                <span>تست User-Agent</span>
            </button>
            <button class="px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2" data-tab-target="tab-help">
                <span class="material-icons text-base">help_outline</span>
                <span>راهنما</span>
            </button>
        </div>
    </div>

    <div id="tab-asn-checker" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-6 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">lan</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">بررسی هویت ASNها</h2>
                        <p class="text-xs text-slate/60 mt-1">شناسایی مالکیت، نوع ترافیک (میزبانی، مسکونی و ...) و امتیاز سوءاستفاده ASNها</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <input type="number" id="input-new-asn" placeholder="ASN جدید (مثلا: 15169)" class="admin-ltr w-44 rounded-lg border border-slate pr-10 pl-3 h-10 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10 outline-none focus:border-primary">
                        <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate/40 text-sm">add</span>
                    </div>
                    <button type="button" id="btn-add-asn" class="admin-btn admin-btn-primary h-10 px-4 text-xs">بررسی و افزودن</button>
                    <div class="h-6 w-px bg-slate/10 mx-2"></div>
                    <button type="button" id="btn-asn-bulk-update" class="admin-btn admin-btn-secondary h-10 px-4 text-xs">
                        <span class="material-icons text-sm">sync</span>
                        بروزرسانی همگانی اطلاعات
                    </button>
                </div>
            </div>

            <div class="grid gap-6" id="asn-list-container">
                @php($asnData = app(\App\Repositories\SettingsRepository::class)->get('visitor_intelligence.asn_data', []))
                @foreach($config['trusted_asns'] as $asn)
                    @php($data = $asnData[$asn] ?? null)
                    <div class="p-5 rounded-2xl border border-slate/10 bg-slate/5 transition-all hover:border-primary/20" id="asn-row-{{ $asn }}">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-xl bg-white dark:bg-slate-800 flex flex-col items-center justify-center border border-slate/10 shadow-sm">
                                    <span class="text-[10px] opacity-40 font-bold">AS</span>
                                    <span class="text-sm font-black text-primary">{{ $asn }}</span>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate text-sm">{{ $data['org'] ?? 'اطلاعات یافت نشد' }}</h4>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <span class="flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-slate/10 text-slate/70">
                                            <span class="material-icons text-[12px]">public</span>
                                            {{ strtoupper($data['country'] ?? '??') }}
                                        </span>
                                        <span class="flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-primary/10 text-primary font-bold">
                                            <span class="material-icons text-[12px]">dns</span>
                                            {{ ucfirst($data['type'] ?? 'unknown') }}
                                        </span>
                                        @if($data['domain'] ?? null)
                                            <a href="https://{{ $data['domain'] }}" target="_blank" class="text-[10px] text-blue-500 hover:underline admin-ltr">{{ $data['domain'] }}</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-left">
                                    <p class="text-[10px] opacity-40 mb-0.5">Abuse Score</p>
                                    <p class="text-xs font-bold {{ str_contains(strtolower($data['abuser_score'] ?? ''), 'high') ? 'text-danger' : 'text-success' }}">{{ $data['abuser_score'] ?? '-' }}</p>
                                </div>
                                <button type="button" data-fetch-asn="{{ $asn }}" class="admin-toggle !w-10 !h-10 !rounded-xl">
                                    <span class="material-icons text-base">refresh</span>
                                </button>
                            </div>
                        </div>
                        @if($data['descr'] ?? null)
                            <div class="mt-4 pt-4 border-t border-slate/5 text-[11px] text-slate/50 italic admin-ltr text-left">
                                {{ $data['descr'] }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div id="tab-test" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">bug_report</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">تست موتور تشخیص</h2>
                        <p class="text-xs text-slate/60 mt-1">یک User-Agent را وارد کنید تا وضعیت تشخیص آن توسط سیستم را بررسی کنید.</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-bold block mb-2">User-Agent مورد نظر</label>
                    <textarea id="ua-test-input" class="w-full rounded-lg border border-slate p-2.5 text-sm font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" rows="3" dir="ltr" placeholder="Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)"></textarea>
                </div>
                <button type="button" id="btn-run-ua-test" class="admin-btn admin-btn-primary px-8 h-11">
                    <span class="material-icons">play_arrow</span>
                    شروع تست
                </button>

                <div id="ua-test-result" class="hidden mt-6 p-6 rounded-2xl border transition-all">
                    <div class="flex items-center gap-4">
                        <div id="ua-test-icon-wrapper" class="w-12 h-12 rounded-full flex items-center justify-center">
                            <span id="ua-test-icon" class="material-icons text-2xl"></span>
                        </div>
                        <div>
                            <h3 id="ua-test-status" class="font-black text-lg"></h3>
                            <p id="ua-test-details" class="text-sm mt-1 opacity-70"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                });
            });

            // Finder Logic
            const finderInput = document.getElementById('visitor-finder-input');
            const finderCount = document.getElementById('finder-count');
            const findables = [
                document.getElementById('robots-pattern-textarea'),
                document.getElementById('trusted-asns-textarea')
            ];

            const runFinder = () => {
                const query = finderInput.value.trim().toLowerCase();
                let totalMatches = 0;

                findables.forEach(el => {
                    if (!el) return;
                    const content = el.value.toLowerCase();
                    if (query && content.includes(query)) {
                        const matches = content.split(query).length - 1;
                        totalMatches += matches;
                        el.classList.add('ring-2', 'ring-primary/50', 'border-primary');

                        const index = content.indexOf(query);
                        el.focus();
                        el.setSelectionRange(index, index + query.length);
                    } else {
                        el.classList.remove('ring-2', 'ring-primary/50', 'border-primary');
                    }
                });

                finderCount.textContent = totalMatches;
            };

            if (finderInput) {
                finderInput.addEventListener('input', runFinder);
                window.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                        const activeTab = document.querySelector('[data-tab-target="tab-detection"]');
                        if (activeTab && activeTab.classList.contains('border-primary')) {
                            e.preventDefault();
                            finderInput.focus();
                        }
                    }
                });
            }

            // ASN Checker Logic
            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-fetch-asn]');
                if (btn) {
                    const asn = btn.dataset.fetchAsn;
                    const icon = btn.querySelector('.material-icons');
                    if (icon) icon.classList.add('animate-spin');
                    try {
                        const response = await fetch('{{ route("dash.admin.visitor-intelligence.asn_fetch", ["authkey" => $authkey]) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ asn })
                        });
                        const res = await response.json();
                        if (res.ok) {
                            // Update row content instead of full reload for better UX
                            location.reload();
                        } else {
                            alert('خطا در دریافت اطلاعات: ' + (res.error || ''));
                        }
                    } catch (e) { alert('خطا در برقراری ارتباط'); }
                    finally { if (icon) icon.classList.remove('animate-spin'); }
                }
            });

            const btnAddAsn = document.getElementById('btn-add-asn');
            if (btnAddAsn) {
                btnAddAsn.onclick = async () => {
                    const asn = document.getElementById('input-new-asn').value.trim();
                    if (!asn) return;

                    btnAddAsn.disabled = true;
                    btnAddAsn.innerHTML = '<span class="material-icons animate-spin text-sm">sync</span>';

                    try {
                        const response = await fetch('{{ route("dash.admin.visitor-intelligence.asn_fetch", ["authkey" => $authkey]) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ asn })
                        });
                        const res = await response.json();
                        if (res.ok) {
                            const textarea = document.getElementById('trusted-asns-textarea');
                            const currentAsns = textarea.value.split(/[\n,]+/).map(a => a.trim()).filter(Boolean);
                            if (!currentAsns.includes(asn)) {
                                textarea.value = [...currentAsns, asn].join('\n');
                                const form = textarea.closest('form');
                                if (form) {
                                    const formData = new FormData(form);
                                    await fetch(form.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                    });
                                }
                            }
                            location.reload();
                        } else {
                            alert('خطا در بررسی ASN: ' + (res.error || 'ناشناخته'));
                        }
                    } catch (e) { alert('خطا در ارتباط با سرور'); }
                    finally {
                        btnAddAsn.disabled = false;
                        btnAddAsn.textContent = 'بررسی و افزودن';
                    }
                };
            }

            const btnBulkAsn = document.getElementById('btn-asn-bulk-update');
            if (btnBulkAsn) {
                btnBulkAsn.onclick = async () => {
                    btnBulkAsn.disabled = true;
                    const originalHtml = btnBulkAsn.innerHTML;
                    btnBulkAsn.innerHTML = '<span class="material-icons animate-spin text-sm">sync</span> در حال بروزرسانی...';
                    try {
                        const response = await fetch('{{ route("dash.admin.visitor-intelligence.asn_bulk_update", ["authkey" => $authkey]) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });
                        const res = await response.json();
                        alert(`تعداد ${res.count} ASN بروزرسانی شد.`);
                        location.reload();
                    } catch (e) { alert('خطا در بروزرسانی همگانی'); }
                    finally {
                        btnBulkAsn.disabled = false;
                        btnBulkAsn.innerHTML = originalHtml;
                    }
                };
            }

            const btnTest = document.getElementById('btn-run-ua-test');
            const resultBox = document.getElementById('ua-test-result');
            if (btnTest) {
                btnTest.addEventListener('click', async () => {
                    const ua = document.getElementById('ua-test-input').value.trim();
                    if (!ua) return;

                    btnTest.disabled = true;
                    btnTest.innerHTML = '<span class="material-icons animate-spin">sync</span> در حال تست...';

                    try {
                        const response = await fetch('{{ route("dash.admin.visitor-intelligence.test", ["authkey" => $authkey]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ user_agent: ua })
                        });
                        const data = await response.json();

                        resultBox.classList.remove('hidden', 'bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'bg-rose-50', 'border-rose-200', 'text-rose-800', 'dark:bg-emerald-950/20', 'dark:border-emerald-800/30', 'dark:bg-rose-950/20', 'dark:border-rose-800/30');

                        const iconWrapper = document.getElementById('ua-test-icon-wrapper');
                        const icon = document.getElementById('ua-test-icon');
                        const status = document.getElementById('ua-test-status');
                        const details = document.getElementById('ua-test-details');

                        if (data.is_robot) {
                            resultBox.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800', 'dark:bg-rose-950/20', 'dark:border-rose-800/30');
                            iconWrapper.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-rose-200 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300';
                            icon.textContent = 'smart_toy';
                            status.textContent = 'تشخیص داده شد: ربات / خزنده';
                            details.textContent = 'User-Agent با الگوی Regex تعریف شده مطابقت دارد.';
                        } else {
                            resultBox.classList.add('bg-emerald-50', 'border-emerald-200', 'text-emerald-800', 'dark:bg-emerald-950/20', 'dark:border-emerald-800/30');
                            iconWrapper.className = 'w-12 h-12 rounded-full flex items-center justify-center bg-emerald-200 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300';
                            icon.textContent = 'person';
                            status.textContent = 'تشخیص داده شد: کاربر انسانی';
                            details.textContent = 'User-Agent با هیچ‌یک از الگوهای ربات مطابقت ندارد.';
                        }
                    } catch (e) {
                        alert('خطا در اجرای تست');
                    } finally {
                        btnTest.disabled = false;
                        btnTest.innerHTML = '<span class="material-icons">play_arrow</span> شروع تست';
                    }
                });
            }
        });
    </script>

    <div id="tab-detection" class="tab-content space-y-6">
        <div class="admin-card mb-6" id="visitor-finder">
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate/40">search</span>
                    <input type="text" id="visitor-finder-input" placeholder="جستجوی سریع در الگوها و ASNها (Ctrl + F)" class="w-full rounded-xl border border-slate pr-10 pl-4 h-11 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 outline-none focus:ring-4 focus:ring-primary/10 transition-all">
                </div>
                <div class="flex items-center gap-2 px-4 h-11 rounded-xl bg-slate/5 border border-slate/10 text-[11px] font-bold text-slate/40">
                    <span class="admin-ltr">FOUND:</span>
                    <span id="finder-count" class="text-primary">0</span>
                </div>
            </div>
        </div>

        <form action="{{ route('dash.admin.visitor-intelligence.save', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
            @csrf
            <div class="admin-card is-surface">
                <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                    <div class="flex items-center gap-3">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">smart_toy</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-slate">تنظیمات تشخیص ربات و خزنده‌ها</h2>
                            <p class="text-xs text-slate/60 mt-1">مدیریت الگوهای Regex و سیستم‌های خودمختار (ASN) معتبر</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div data-findable>
                        <label class="text-sm font-bold block mb-2">الگوی User-Agent ربات‌ها (Regex)</label>
                        <textarea name="robots_pattern" id="robots-pattern-textarea" class="w-full rounded-lg border border-slate p-2.5 text-sm font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" rows="8" dir="ltr">{{ $config['robots_pattern'] }}</textarea>
                        <p class="text-[11px] text-slate/50 mt-1.5">عبارت منظم برای شناسایی User-Agent ربات‌ها. (بدون اسلش شروع و پایان)</p>
                    </div>

                    <div data-findable>
                        <label class="text-sm font-bold block mb-2">لیست ASN‌های معتبر (هر سطر یک ASN)</label>
                        <textarea name="trusted_asns" id="trusted-asns-textarea" class="w-full rounded-lg border border-slate p-2.5 text-sm font-mono dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" rows="5" dir="ltr" placeholder="15169&#10;8075">{{ implode("\n", $config['trusted_asns']) }}</textarea>
                        <p class="text-[11px] text-slate/50 mt-1.5">شماره‌های ASN که به عنوان ربات/ترافیک ابری در نظر گرفته می‌شوند (مانند گوگل، مایکروسافت و ...)</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-slate/10">
                    <button class="admin-btn admin-btn-primary w-full sm:w-auto px-10 h-12 justify-center">
                        <span class="material-icons">save</span>
                        ذخیره تنظیمات هوشمندی
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
                        <h2 class="font-bold text-slate text-lg">راهنمای کامل ماژول هوشمندی بازدیدکنندگان</h2>
                    </div>

                    <div class="space-y-8 text-sm text-slate leading-7">
                        <section id="doc-robot-detection">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">smart_toy</span>
                                تشخیص ربات و خزنده‌ها
                            </h3>
                            <p>سیستم تشخیص ربات بازدیدکنندگان بر اساس دو روش کار می‌کند: الگوهای Regex و ASNهای معتبر.</p>
                            <div class="mt-4 bg-primary/5 border border-primary/20 rounded-xl p-4">
                                <h4 class="font-bold text-slate mb-2">روش‌های تشخیص</h4>
                                <ul class="list-disc list-inside space-y-1 mr-4">
                                    <li><b>الگوهای Regex:</b> User-Agent را علیه الگوهای تعریف شده بررسی می‌کند</li>
                                    <li><b>ASNها:</b> IPهای معتبر سامانه‌های بزرگ (گوگل، مایکروسافت، ...) را بر اساس شماره ASN تشخیص می‌دهد</li>
                                </ul>
                            </div>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-asn">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">lan</span>
                                بررسی ASNها
                            </h3>
                            <p>ASNهای معتبر می‌توانند به صورت دسته‌جمعی از سرویس‌های IPinfo یا Whois دریافت شوند.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-test">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">science</span>
                                تست User-Agent
                            </h3>
                            <p>می‌توانید User-Agent دلخواه را وارد کنید تا سیستم تشخیص آن را بررسی کند.</p>
                        </section>

                        <hr class="border-slate/10">

                        <section id="doc-security">
                            <h3 class="text-base font-bold text-slate mb-3 flex items-center gap-2">
                                <span class="material-icons text-primary text-lg">security</span>
                                نکات امنیتی
                            </h3>
                            <ul class="list-disc list-inside space-y-1 mr-4">
                                <li>تغییر الگوهای Regex می‌تواند رفتار سایت را به هوشمندترین ربات‌ها تغییر دهد</li>
                                <li>ASNهای معتبر باید از سرویس‌های معتبر (IPinfo، BGPView) گرفته شوند</li>
                                <li>همیشه قبل از تغییر ASNها، از لیست‌های معتبر اطمینان حاصل کنید</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="hidden lg:block w-64 shrink-0">
                <div class="admin-card !p-4 sticky top-4">
                    <h4 class="text-xs font-bold text-slate uppercase tracking-wider mb-3 px-2">فهرست مطالب</h4>
                    <nav class="space-y-1">
                        <a href="#doc-intro" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">معرفی ماژول</a>
                        <a href="#doc-robot-detection" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تشخیص ربات</a>
                        <a href="#doc-asn" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">بررسی ASNها</a>
                        <a href="#doc-test" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">تست User-Agent</a>
                        <a href="#doc-security" class="doc-nav-link block px-3 py-2 rounded-lg text-xs font-medium text-slate hover:bg-primary/5 hover:text-primary transition-colors">نکات امنیتی</a>
                    </nav>
                </div>
            </aside>
        </div>
    </div>

</x-layouts.admin-dashboard>
    @push('scripts')
        @vite('resources/js/admin-visitor-intelligence.js')
    @endpush
