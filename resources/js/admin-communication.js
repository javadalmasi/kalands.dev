document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#comm-tabs [data-tab-target]');
    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const target = tab.getAttribute('data-tab-target');
            tabs.forEach(t => {
                t.className = 'px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2';
            });
            tab.className = 'px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2';

            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(target).classList.remove('hidden');
        });
    });

    window.runEmailTest = async function() {
        const btn = document.getElementById('email-test-btn');
        const type = document.getElementById('test-email-type').value;
        const target = document.getElementById('test-email-target').value;
        const logCard = document.getElementById('test-log-card');
        const url = type === 'general' ? btn.getAttribute('data-url-general') : btn.getAttribute('data-url-transactional');

        if (!target) { alert('لطفا ایمیل مقصد را وارد کنید.'); return; }

        btn.disabled = true; btn.innerHTML = '<span class="material-icons animate-spin text-sm">refresh</span> در حال ارسال...';
        logCard.classList.add('hidden');

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ to: target })
            });
            const data = await res.json();
            showTestResult(data);
        } catch (e) {
            showTestResult({ ok: false, error: 'خطای سیستمی در برقراری ارتباط با سرور.' });
        } finally {
            btn.disabled = false; btn.innerHTML = 'ارسال ایمیل تست';
        }
    };

    window.runSmsTest = async function() {
        const btn = document.getElementById('sms-test-btn');
        const target = document.getElementById('test-sms-target').value;
        const msg = document.getElementById('test-sms-message').value;
        const logCard = document.getElementById('test-log-card');
        const url = btn.getAttribute('data-url');

        if (!target) { alert('لطفا شماره مقصد را وارد کنید.'); return; }

        btn.disabled = true; btn.innerHTML = '<span class="material-icons animate-spin text-sm">refresh</span> در حال ارسال...';
        logCard.classList.add('hidden');

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ to: target, message: msg })
            });
            const data = await res.json();
            showTestResult(data);
        } catch (e) {
            showTestResult({ ok: false, error: 'خطای سیستمی در برقراری ارتباط با سرور.' });
        } finally {
            btn.disabled = false; btn.innerHTML = 'ارسال پیامک تست';
        }
    };

    function showTestResult(data) {
        const logCard = document.getElementById('test-log-card');
        const badge = document.getElementById('test-status-badge');
        const errMsg = document.getElementById('test-error-msg');
        const fullLog = document.getElementById('test-full-log');

        logCard.classList.remove('hidden');
        if (data.ok) {
            badge.className = 'mb-4 px-4 py-1.5 rounded-lg text-xs font-bold inline-block bg-success text-white';
            badge.innerHTML = '<span class="flex items-center gap-1"><span class="material-icons text-sm">check_circle</span> ارسال موفقیت‌آمیز</span>';
            errMsg.classList.add('hidden');
            fullLog.innerHTML = data.message || 'عملیات با موفقیت انجام شد.';
        } else {
            badge.className = 'mb-4 px-4 py-1.5 rounded-lg text-xs font-bold inline-block bg-danger text-white';
            badge.innerHTML = '<span class="flex items-center gap-1"><span class="material-icons text-sm">error</span> ارسال با خطا مواجه شد</span>';
            errMsg.innerHTML = data.error || 'جزئیات خطا موجود نیست.';
            errMsg.classList.remove('hidden');
            fullLog.innerHTML = data.trace || 'لاگی از سرور دریافت نشد.';
        }
        logCard.scrollIntoView({ behavior: 'smooth' });
    }
});
