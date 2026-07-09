document.addEventListener('DOMContentLoaded', () => {
    // ─── Tab Switcher ──────────────────────────────────────────────
    const tabs = document.querySelectorAll('#home-tabs [data-tab-target]');
    const urlParams = new URLSearchParams(window.location.search);
    let activeTab = urlParams.get('tab');

    const switchTab = (targetId) => {
        if (!targetId || !document.getElementById(targetId)) return;

        tabs.forEach(t => {
            const isActive = t.getAttribute('data-tab-target') === targetId;
            if (isActive) {
                t.classList.add('border-b-2', 'border-primary', 'text-primary', 'font-bold');
                t.classList.remove('text-slate', 'font-medium');
            } else {
                t.classList.remove('border-b-2', 'border-primary', 'text-primary', 'font-bold');
                t.classList.add('text-slate', 'font-medium');
            }
        });

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.toggle('hidden', content.id !== targetId);
        });
    };

    if (tabs.length > 0) {
        if (!activeTab || !document.getElementById(activeTab)) {
            activeTab = tabs[0].getAttribute('data-tab-target');
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = tab.getAttribute('data-tab-target');
                switchTab(targetId);
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('tab', targetId);
                window.history.pushState({}, '', newUrl.toString());
            });
        });

        switchTab(activeTab);
    }

    // ─── Scroll-spy for doc nav links ─────────────────────────────
    const docNavLinks = document.querySelectorAll('.doc-nav-link');
    const docSections = document.querySelectorAll('section[id^="doc-"]');

    const updateActiveNav = () => {
        let currentId = null;
        docSections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top <= 120) {
                currentId = section.getAttribute('id');
            }
        });

        docNavLinks.forEach(link => {
            const href = link.getAttribute('href').replace('#', '');
            if (href === currentId) {
                link.classList.add('bg-primary/5', 'text-primary');
                link.classList.remove('text-slate');
            } else {
                link.classList.remove('bg-primary/5', 'text-primary');
                link.classList.add('text-slate');
            }
        });
    };

    if (docNavLinks.length > 0 && docSections.length > 0) {
        window.addEventListener('scroll', updateActiveNav, { passive: true });
        updateActiveNav();

        docNavLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').replace('#', '');
                const target = document.getElementById(targetId);
                if (target) {
                    const offset = 100;
                    const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                    window.scrollTo({ top, behavior: 'smooth' });
                }
            });
        });
    }

    // ─── Affiliate Date Filter: convert Shamsi → Gregorian ────────
    // The Shamsi datepicker stores gregorian equivalent in input.dataset.gregorian
    // but the form submits the Persian value. We intercept submit to swap the values.
    const affiliateFilterForms = document.querySelectorAll('form[method="GET"]');

    affiliateFilterForms.forEach(form => {
        const startInput = form.querySelector('input[name="start_date"]');
        const endInput = form.querySelector('input[name="end_date"]');

        if (!startInput && !endInput) return;

        form.addEventListener('submit', (e) => {
            // Use the gregorian value stored by admin-datepicker.js as dataset.gregorian
            // If not available, fall back to window.toGregorianFromShamsi conversion
            const convert = (input) => {
                if (!input || !input.value.trim()) return;
                const gregorian = input.dataset.gregorian
                    || (typeof window.toGregorianFromShamsi === 'function'
                        ? window.toGregorianFromShamsi(input.value.trim())
                        : null);
                if (gregorian) {
                    input.value = gregorian;
                }
            };

            convert(startInput);
            convert(endInput);
        });
    });
});
