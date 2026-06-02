document.addEventListener('DOMContentLoaded', () => {
    const tabsContainer = document.querySelector('#object-cache-tabs');
    const tabs = tabsContainer ? tabsContainer.querySelectorAll('[data-tab-target]') : [];
    const urlParams = new URLSearchParams(window.location.search);
    let activeTab = urlParams.get('tab') || 'tab-config';

    if (!document.getElementById(activeTab)) activeTab = 'tab-config';

    const switchTab = (target) => {
        tabs.forEach(t => {
            t.className = 'px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2';
        });
        const activeBtn = document.querySelector(`[data-tab-target="${target}"]`);
        if(activeBtn) activeBtn.className = 'px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2';

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const targetEl = document.getElementById(target);
        if(targetEl) targetEl.classList.remove('hidden');
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const target = tab.getAttribute('data-tab-target');
            switchTab(target);
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', target);
            window.history.pushState({}, '', newUrl);
        });
    });

    switchTab(activeTab);

    const testBtn = document.getElementById('test-cache-btn');
    if (testBtn) {
        testBtn.addEventListener('click', () => {
            const resultContainer = document.getElementById('test-result');
            const resultContent = document.getElementById('test-result-content');
            const url = testBtn.getAttribute('data-url');
            const originalText = testBtn.innerHTML;

            testBtn.disabled = true;
            testBtn.innerHTML = '<span class="material-icons animate-spin">refresh</span> در حال تست...';

            resultContainer.classList.add('hidden');

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(res => res.json())
            .then(data => {
                resultContainer.classList.remove('hidden');
                if (data.ok) {
                    resultContent.className = 'p-4 rounded-lg text-sm font-bold flex items-center gap-3 bg-success/10 text-success';
                    resultContent.innerHTML = '<span class="material-icons">check_circle</span> اتصال با موفقیت برقرار شد. زمان پاسخ: ' + (data.latency_ms || '0') + 'ms';
                } else {
                    resultContent.className = 'p-4 rounded-lg text-sm font-bold flex items-center gap-3 bg-danger/10 text-danger';
                    resultContent.innerHTML = '<span class="material-icons">error</span> ' + (data.message || 'خطا در اتصال به سرور کش.');
                }
            })
            .catch(() => {
                resultContainer.classList.remove('hidden');
                resultContent.className = 'p-4 rounded-lg text-sm font-bold flex items-center gap-3 bg-danger/10 text-danger';
                resultContent.innerHTML = '<span class="material-icons">error</span> خطا در برقراری ارتباط با سرور.';
            })
            .finally(() => {
                testBtn.disabled = false;
                testBtn.innerHTML = originalText;
            });
        });
    }

    const driverSelect = document.getElementById('cache-driver-select');
    const redisConfig = document.getElementById('redis-connection-config');
    const memcachedConfig = document.getElementById('memcached-connection-config');

    if (driverSelect) {
        const toggleDriverConfig = () => {
            const val = driverSelect.value;
            if (redisConfig) redisConfig.classList.toggle('hidden', !val.startsWith('redis'));
            if (memcachedConfig) memcachedConfig.classList.toggle('hidden', val !== 'memcached');
        };
        driverSelect.addEventListener('change', toggleDriverConfig);
        toggleDriverConfig();
    }

    const schemeSelect = document.querySelector('select[name="redis_scheme"]');
    if (schemeSelect) {
        const toggleScheme = () => {
            const isUnix = schemeSelect.value === 'unix';
            const hostField = document.getElementById('redis-host-field');
            const portField = document.getElementById('redis-port-field');
            const socketField = document.getElementById('redis-socket-field');
            if (hostField) hostField.classList.toggle('hidden', isUnix);
            if (portField) portField.classList.toggle('hidden', isUnix);
            if (socketField) socketField.classList.toggle('hidden', !isUnix);
        };
        schemeSelect.addEventListener('change', toggleScheme);
        toggleScheme();
    }
});
