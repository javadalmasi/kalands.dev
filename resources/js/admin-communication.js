document.addEventListener('DOMContentLoaded', () => {

    // ─────────────────────────────────────────────────────────────
    // Tab switching
    // ─────────────────────────────────────────────────────────────
    const TAB_ACTIVE   = ['border-primary', 'text-primary', 'font-bold'];
    const TAB_INACTIVE = ['border-transparent', 'text-slate/60', 'dark:text-white/50', 'font-medium'];

    document.querySelectorAll('#comm-tabs .comm-tab').forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            const target = tab.getAttribute('data-tab-target');

            document.querySelectorAll('#comm-tabs .comm-tab').forEach(t => {
                t.classList.remove(...TAB_ACTIVE);
                t.classList.add(...TAB_INACTIVE);
            });
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));

            tab.classList.remove(...TAB_INACTIVE);
            tab.classList.add(...TAB_ACTIVE);
            document.getElementById(target)?.classList.remove('hidden');
        });
    });

    // ─────────────────────────────────────────────────────────────
    // Driver picker
    // ─────────────────────────────────────────────────────────────
    const mailerInput = document.getElementById('mailer-input');

    document.querySelectorAll('.driver-card').forEach(card => {
        card.addEventListener('click', () => {
            const driver = card.dataset.driver;
            mailerInput.value = driver;

            // Visual: reset all cards
            document.querySelectorAll('.driver-card').forEach(c => {
                const isLog = c.dataset.driver === 'log';
                c.className = c.className
                    .replace(/border-primary|bg-primary\/8|text-primary|border-amber-500|bg-amber-500\/8|text-amber-600|dark:text-amber-400/g, '')
                    .trim();
                c.classList.add(
                    'border-slate/15', 'dark:border-white/8',
                    'text-slate/60', 'dark:text-white/50',
                    isLog ? 'hover:border-amber-400/40' : 'hover:border-primary/40',
                );
            });

            // Highlight selected
            card.classList.remove(
                'border-slate/15', 'dark:border-white/8',
                'text-slate/60', 'dark:text-white/50',
                'hover:border-primary/40', 'hover:border-amber-400/40',
            );
            if (driver === 'log') {
                card.classList.add('border-amber-500', 'bg-amber-500/8', 'text-amber-600', 'dark:text-amber-400');
            } else {
                card.classList.add('border-primary', 'bg-primary/8', 'text-primary');
            }

            // Show / hide field sections
            document.querySelectorAll('[data-driver-fields]').forEach(el => {
                el.classList.toggle('hidden', el.dataset.driverFields !== driver);
            });
        });
    });

    // ─────────────────────────────────────────────────────────────
    // Encryption ↔ Port auto-suggestion for SMTP
    // ─────────────────────────────────────────────────────────────
    const encryptionSel = document.getElementById('smtp-encryption');
    const portInput     = document.getElementById('smtp-port');

    if (encryptionSel && portInput) {
        encryptionSel.addEventListener('change', () => {
            const portMap = { tls: '587', ssl: '465', '': '25' };
            const suggested = portMap[encryptionSel.value];
            if (suggested) portInput.value = suggested;
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Password toggle (show / hide)
    // ─────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const field = document.getElementById(btn.dataset.togglePassword);
            if (!field) return;
            const isHidden = field.type === 'password';
            field.type = isHidden ? 'text' : 'password';
            btn.querySelector('.material-icons').textContent =
                isHidden ? 'visibility_off' : 'visibility';
        });
    });

    // ─────────────────────────────────────────────────────────────
    // Email test
    // ─────────────────────────────────────────────────────────────
    const emailBtn = document.getElementById('email-test-btn');
    if (emailBtn) {
        emailBtn.addEventListener('click', async () => {
            const to  = document.getElementById('test-email-target')?.value.trim();
            const url = emailBtn.dataset.url;
            if (!to)  { alert('لطفاً ایمیل مقصد را وارد کنید.'); return; }
            if (!url) { alert('URL تست تعریف نشده است.'); return; }

            await runTest(emailBtn, url, { to }, 'Email');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // SMS test
    // ─────────────────────────────────────────────────────────────
    const smsBtn = document.getElementById('sms-test-btn');
    if (smsBtn) {
        smsBtn.addEventListener('click', async () => {
            const to      = document.getElementById('test-sms-target')?.value.trim();
            const message = document.getElementById('test-sms-message')?.value.trim();
            const url     = smsBtn.dataset.url;
            if (!to)  { alert('لطفاً شماره مقصد را وارد کنید.'); return; }
            if (!url) { alert('URL تست تعریف نشده است.'); return; }

            await runTest(smsBtn, url, { to, message }, 'SMS');
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Copy log
    // ─────────────────────────────────────────────────────────────
    document.getElementById('copy-log-btn')?.addEventListener('click', () => {
        const text = document.getElementById('debug-full-log')?.textContent ?? '';
        navigator.clipboard.writeText(text).then(() => {
            const btn  = document.getElementById('copy-log-btn');
            const orig = btn.innerHTML;
            btn.innerHTML = '<span class="material-icons text-xs">check</span>کپی شد!';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        });
    });

    // ─────────────────────────────────────────────────────────────
    // Shared test runner
    // ─────────────────────────────────────────────────────────────
    async function runTest(btn, url, payload, channel) {
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons animate-spin text-base">refresh</span><span>در حال ارسال...</span>';
        document.getElementById('debug-log-card')?.classList.add('hidden');

        try {
            const res  = await fetch(url, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            renderLog(data, res.status, channel);
        } catch (err) {
            renderLog({ ok: false, error: 'خطای شبکه: ' + err.message, trace: err.stack ?? '' }, 0, channel);
        } finally {
            btn.disabled  = false;
            btn.innerHTML = originalHTML;
        }
    }

    function renderLog(data, httpStatus, channel) {
        const card   = document.getElementById('debug-log-card');
        const badge  = document.getElementById('debug-status-badge');
        const errMsg = document.getElementById('debug-error-msg');
        const logEl  = document.getElementById('debug-full-log');

        card.classList.remove('hidden');

        const http = httpStatus ? ` [HTTP ${httpStatus}]` : '';

        if (data.ok) {
            badge.className = 'mb-4 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-success/90 text-white';
            badge.innerHTML = `<span class="material-icons text-sm">check_circle</span> موفق — ${channel}${http}`;
            errMsg.classList.add('hidden');
            logEl.textContent = data.message ?? 'عملیات با موفقیت انجام شد.';
            logEl.className   = logEl.className.replace('text-red-400', 'text-green-400');
        } else {
            badge.className = 'mb-4 inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold bg-danger/90 text-white';
            badge.innerHTML = `<span class="material-icons text-sm">error</span> ناموفق — ${channel}${http}`;
            errMsg.textContent = data.error ?? 'جزئیات خطا موجود نیست.';
            errMsg.classList.remove('hidden');
            logEl.textContent = data.trace ?? 'لاگ stack trace دریافت نشد.';
            logEl.className   = logEl.className.replace('text-green-400', 'text-red-400');
        }

        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
