document.addEventListener('DOMContentLoaded', () => {

    // ─────────────────────────────────────────────────────────────
    // Driver picker
    // ─────────────────────────────────────────────────────────────
    const mailerInput = document.getElementById('mailer-input');

    document.querySelectorAll('.driver-card').forEach(card => {
        card.addEventListener('click', () => {
            const driver = card.dataset.driver;
            mailerInput.value = driver;

            document.querySelectorAll('.driver-card').forEach(c => {
                c.classList.remove(
                    'border-primary', 'bg-primary/8', 'text-primary',
                    'border-slate/15', 'dark:border-white/8',
                    'text-slate/60', 'dark:text-white/50',
                    'hover:border-primary/40',
                );
                c.classList.add(
                    'border-slate/15', 'dark:border-white/8',
                    'text-slate/60', 'dark:text-white/50',
                    'hover:border-primary/40',
                );
            });

            card.classList.remove(
                'border-slate/15', 'dark:border-white/8',
                'text-slate/60', 'dark:text-white/50',
                'hover:border-primary/40',
            );
            card.classList.add('border-primary', 'bg-primary/8', 'text-primary');

            document.querySelectorAll('[data-driver-fields]').forEach(el => {
                el.classList.toggle('hidden', el.dataset.driverFields !== driver);
            });
        });
    });

    // ─────────────────────────────────────────────────────────────
    // Encryption ↔ Port auto-suggestion
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
    // Password toggle
    // ─────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-toggle-password]').forEach(btn => {
        btn.addEventListener('click', () => {
            const field = document.getElementById(btn.dataset.togglePassword);
            if (!field) return;
            const isHidden = field.type === 'password';
            field.type = isHidden ? 'text' : 'password';
            btn.querySelector('.material-icons').textContent = isHidden ? 'visibility_off' : 'visibility';
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

            await runTest(emailBtn, url, { to });
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Shared test runner
    // ─────────────────────────────────────────────────────────────
    async function runTest(btn, url, payload) {
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons animate-spin text-base">refresh</span><span>در حال ارسال...</span>';

        const resultCard = document.getElementById('test-result-card');
        if (resultCard) resultCard.classList.add('hidden');

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
            renderResult(data, res.status);
        } catch (err) {
            renderResult({ ok: false, error: 'خطای شبکه: ' + err.message }, 0);
        } finally {
            btn.disabled  = false;
            btn.innerHTML = originalHTML;
        }
    }

    function renderResult(data, httpStatus) {
        const card   = document.getElementById('test-result-card');
        const badge  = document.getElementById('test-result-badge');
        const msgEl  = document.getElementById('test-result-msg');

        if (!card) return;
        card.classList.remove('hidden');

        const http = httpStatus ? ` [HTTP ${httpStatus}]` : '';

        if (data.ok) {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-success/90 text-white';
            badge.innerHTML = '<span class="material-icons text-sm">check_circle</span> ارسال موفق' + http;
            msgEl.textContent = data.message ?? 'ایمیل با موفقیت ارسال شد.';
            msgEl.className   = 'text-[11px] font-mono whitespace-pre-wrap leading-5 text-green-400';
        } else {
            badge.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-danger/90 text-white';
            badge.innerHTML = '<span class="material-icons text-sm">error</span> ارسال ناموفق' + http;
            msgEl.textContent = (data.error ? data.error + '\n\n' : '') + (data.trace ?? '');
            msgEl.className   = 'text-[11px] font-mono whitespace-pre-wrap leading-5 text-red-400';
        }

        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
