document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('checker-root');
    if (!root) return;

    // DOM Elements
    const startBtn = document.getElementById('start-btn');
    const stopBtn = document.getElementById('stop-btn');
    const clearLogsBtn = document.getElementById('clear-logs-btn');
    const batchSizeSelect = document.getElementById('batch-size');
    const logContainer = document.getElementById('log-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const etaText = document.getElementById('eta-text');
    const resultsSection = document.getElementById('results-section');
    const inactiveProductsTbody = document.getElementById('inactive-products-tbody');
    const inactiveCount = document.getElementById('inactive-count');

    // Stats elements
    const statTotal = document.getElementById('stat-total');
    const statProcessed = document.getElementById('stat-processed');
    const statInactive = document.getElementById('stat-inactive');
    const statSuccessRate = document.getElementById('stat-success-rate');

    // URLs
    const digikalaIdsUrl = root.dataset.idsUrl;
    const checkApiUrl = root.dataset.checkUrl;

    // State
    let state = {
        isWorking: false,
        shouldStop: false,
        allIds: [],
        totalCount: 0,
        processedCount: 0,
        inactiveFound: 0,
        successCount: 0,
        logs: [],
        inactiveProducts: [],
        startTime: null,
        batchSize: 10,
    };

    // Utility: Add log message
    function addLog(message, type = 'info') {
        const now = new Date();
        const time = String(now.getHours()).padStart(2, '0') + ':' +
                     String(now.getMinutes()).padStart(2, '0') + ':' +
                     String(now.getSeconds()).padStart(2, '0');

        const logLine = document.createElement('div');
        logLine.className = 'flex gap-2';

        const timeSpan = document.createElement('span');
        timeSpan.className = 'text-slate-600';
        timeSpan.textContent = time;

        const msgSpan = document.createElement('span');
        if (type === 'error') {
            msgSpan.className = 'text-red-400';
        } else if (type === 'success') {
            msgSpan.className = 'text-primary';
        } else {
            msgSpan.className = 'text-green-400';
        }
        msgSpan.textContent = message;

        logLine.appendChild(timeSpan);
        logLine.appendChild(msgSpan);
        logContainer.appendChild(logLine);
        logContainer.scrollTop = logContainer.scrollHeight;

        state.logs.push({ time, message, type });
    }

    // Utility: Calculate ETA
    function calculateETA() {
        if (!state.startTime || state.processedCount === 0) return '-';
        const elapsed = Date.now() - state.startTime;
        const remaining = state.totalCount - state.processedCount;
        const avgPerItem = elapsed / state.processedCount;
        const etaMs = remaining * avgPerItem;
        const etaSec = Math.floor(etaMs / 1000);
        if (etaSec < 60) return `${etaSec}s`;
        const etaMin = Math.floor(etaSec / 60);
        return `${etaMin}m`;
    }

    // Utility: Update stats
    function updateStats() {
        statTotal.textContent = state.totalCount;
        statProcessed.textContent = state.processedCount;
        statInactive.textContent = state.inactiveFound;
        const rate = state.totalCount > 0 ? Math.round((state.successCount / state.totalCount) * 100) : 0;
        statSuccessRate.textContent = rate + '%';

        const percent = state.totalCount > 0 ? Math.round((state.processedCount / state.totalCount) * 100) : 0;
        progressBar.style.width = percent + '%';
        progressText.textContent = percent;
        etaText.textContent = calculateETA();
    }

    // Utility: Add inactive product to results table
    function addInactiveProduct(productId) {
        const row = document.createElement('tr');
        row.className = 'border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50';

        const idCell = document.createElement('td');
        idCell.className = 'py-3 px-4 text-slate-700 dark:text-slate-300 font-mono';
        idCell.textContent = productId;

        const statusCell = document.createElement('td');
        statusCell.className = 'py-3 px-4';
        const statusBadge = document.createElement('span');
        statusBadge.className = 'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-500';
        statusBadge.innerHTML = '<span class="material-icons !text-sm">warning</span><span>غیرفعال</span>';
        statusCell.appendChild(statusBadge);

        const timeCell = document.createElement('td');
        timeCell.className = 'py-3 px-4 text-slate-500 dark:text-slate-400 text-xs';
        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + ':' +
                        String(now.getMinutes()).padStart(2, '0') + ':' +
                        String(now.getSeconds()).padStart(2, '0');
        timeCell.textContent = timeStr;

        row.appendChild(idCell);
        row.appendChild(statusCell);
        row.appendChild(timeCell);

        inactiveProductsTbody.appendChild(row);
        state.inactiveProducts.push(productId);
    }

    // Fetch initial product IDs
    async function fetchProductIds() {
        addLog('سیستم آماده شد. در حال دریافت لیست شناسه‌ها...', 'info');
        try {
            const response = await fetch(digikalaIdsUrl);
            const data = await response.json();
            if (data.ok) {
                state.allIds = data.ids;
                state.totalCount = state.allIds.length;
                updateStats();
                addLog(`تعداد ${state.totalCount} محصول دیجی‌کالا برای بررسی یافت شد.`, 'success');
                startBtn.disabled = state.totalCount === 0;
                if (state.totalCount === 0) {
                    addLog('هیچ محصول دیجی‌کالایی برای بررسی یافت نشد.', 'error');
                }
            } else {
                addLog('خطا در دریافت لیست محصولات.', 'error');
            }
        } catch (e) {
            addLog('خطا در ارتباط با سرور: ' + e.message, 'error');
        }
    }

    // Start checking
    async function startChecking() {
        if (state.isWorking || state.totalCount === 0) return;

        state.isWorking = true;
        state.shouldStop = false;
        state.processedCount = 0;
        state.inactiveFound = 0;
        state.successCount = 0;
        state.inactiveProducts = [];
        state.startTime = Date.now();

        inactiveProductsTbody.innerHTML = '';
        resultsSection.classList.add('hidden');

        startBtn.disabled = true;
        startBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
        batchSizeSelect.disabled = true;

        addLog('عملیات بررسی آغاز شد...', 'info');

        const batchSize = parseInt(batchSizeSelect.value) || 10;
        const batches = [];
        for (let i = 0; i < state.allIds.length; i += batchSize) {
            batches.push(state.allIds.slice(i, i + batchSize));
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        for (let i = 0; i < batches.length; i++) {
            if (state.shouldStop) break;

            const batch = batches[i];
            try {
                const response = await fetch(checkApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ product_ids: batch })
                });

                const result = await response.json();
                if (result.ok) {
                    Object.keys(result.results).forEach(id => {
                        if (result.results[id].ok) {
                            state.successCount++;
                            if (result.results[id].is_inactive) {
                                state.inactiveFound++;
                                addInactiveProduct(id);
                                addLog(`✓ محصول ${id} غیرفعال شناسایی و در دیتابیس آپدیت شد.`, 'success');
                            }
                        } else {
                            addLog(`✗ محصول ${id} خطا داشت.`, 'error');
                        }
                    });

                    state.processedCount += batch.length;
                    updateStats();
                } else {
                    addLog(`خطا در پردازش دسته ${i + 1}`, 'error');
                }
            } catch (e) {
                addLog(`خطای شبکه در دسته ${i + 1}: ${e.message}`, 'error');
            }

            // Small delay between batches to avoid overwhelming server
            await new Promise(r => setTimeout(r, 100));
        }

        state.isWorking = false;
        startBtn.disabled = false;
        startBtn.classList.remove('hidden');
        stopBtn.classList.add('hidden');
        batchSizeSelect.disabled = false;

        if (state.shouldStop) {
            addLog('عملیات توقف داده شد.', 'error');
        } else {
            addLog(`عملیات به پایان رسید. بررسی ${state.processedCount} محصول انجام شد.`, 'success');
        }

        if (state.inactiveProducts.length > 0) {
            resultsSection.classList.remove('hidden');
            inactiveCount.textContent = state.inactiveProducts.length;
        }
    }

    // Stop checking
    function stopChecking() {
        state.shouldStop = true;
        addLog('درخواست توقف ارسال شد. بعد از اتمام دسته فعلی، عملیات متوقف می‌شود.', 'error');
    }

    // Clear logs
    function clearLogs() {
        logContainer.innerHTML = '<div class="py-10 text-center text-slate-700">منتظر شروع عملیات...</div>';
        state.logs = [];
    }

    // Event listeners
    startBtn.addEventListener('click', startChecking);
    stopBtn.addEventListener('click', stopChecking);
    clearLogsBtn.addEventListener('click', clearLogs);

    batchSizeSelect.addEventListener('change', (e) => {
        state.batchSize = parseInt(e.target.value) || 10;
    });

    // Initialize
    fetchProductIds();
});
