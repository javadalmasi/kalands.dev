import Highcharts from 'highcharts/highmaps.js';
import 'highcharts/modules/accessibility.js';
import worldMap from '@highcharts/map-collection/custom/world.topo.json';
import iranMap from '@highcharts/map-collection/countries/ir/ir-all.topo.json';

(function() {
    const dashboardRoot = document.getElementById('analytics-dashboard-root');
    const hubRoot = document.getElementById('analytics-hub-root');
    const root = dashboardRoot || hubRoot;
    if (!root) return;

    const endpoint = dashboardRoot ? root.dataset.dashboardUrl : root.dataset.reportUrl;
    const loadedSections = new Set();
    const filterForm = hubRoot ? root.querySelector('[data-analytics-filters]') : null;

    const number = (value) => new Intl.NumberFormat('fa-IR').format(value || 0);

    const fetchJson = (url) => fetch(url, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin'
    }).then((response) => response.json()).then((payload) => payload.data || {});

    const sectionUrl = (section) => {
        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('section', section);
        if (filterForm) {
            new FormData(filterForm).forEach((value, key) => {
                if (String(value || '').trim() !== '') {
                    if (key === 'from' || key === 'to') {
                        const g = toGregorianFromShamsi(String(value).trim());
                        if (g) url.searchParams.set(key, g);
                    } else {
                        url.searchParams.set(key, String(value).trim());
                    }
                }
            });
        }
        return url.toString();
    };

    const empty = '<p class="text-center py-10 opacity-50">داده‌ای یافت نشد.</p>';

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));

    const renderBars = (container, rows, isNegative = false) => {
        if (!container) return;
        const items = Array.isArray(rows) ? rows : [];
        if (!items.length) { container.innerHTML = empty; return; }
        const max = Math.max(...items.map((row) => row.count || 0), 1);
        const colorClass = isNegative ? 'text-danger' : 'text-success';
        const barClass = isNegative ? 'bg-danger' : 'bg-success';

        container.innerHTML = items.map((row) => `
            <div class="space-y-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="truncate ml-4" title="${escapeHtml(row.label)}">${escapeHtml(row.label)}</span>
                    <span class="font-bold ${colorClass} shrink-0">${number(row.count)}</span>
                </div>
                <div class="w-full bg-slate/5 h-1.5 rounded-full overflow-hidden">
                    <div class="${barClass} h-full opacity-80" style="width: ${(row.count / max) * 100}%"></div>
                </div>
            </div>
        `).join('');
    };

    const renderSimpleList = (container, rows, suffix = 'بازدید', isNegative = false) => {
        if (!container) return;
        const items = Array.isArray(rows) ? rows : [];
        if (!items.length) { container.innerHTML = empty; return; }
        const colorClass = isNegative ? 'text-danger' : 'text-success';
        container.innerHTML = items.map((row) => {
            const isUrl = row.key && (row.key.startsWith('/') || row.key.startsWith('http'));
            const openLinkHtml = isUrl ? `<a href="${row.key}" target="_blank" class="material-icons text-[14px] opacity-40 hover:opacity-100 hover:text-success mr-1">open_in_new</a>` : '';

            return `
                <div class="flex items-center justify-between text-[11px] border-b border-slate/5 pb-2 gap-4">
                    <span class="truncate flex items-center" title="${escapeHtml(row.label)}">
                        ${openLinkHtml}
                        <span class="truncate">${escapeHtml(row.label)}</span>
                    </span>
                    <span class="font-bold ${colorClass} shrink-0">${number(row.count)} ${suffix}</span>
                </div>
            `;
        }).join('');
    };

    const svgNs = 'http://www.w3.org/2000/svg';
    const mapViewBox = {
        world: { height: 340 },
        iran: { height: 340 }
    };

    const getMapMode = (rows) => {
        const codes = [...new Set((rows || [])
            .map((row) => String(row.code || row.key || '').toUpperCase())
            .filter(Boolean))];

        if (codes.length > 0 && codes.every((code) => code === 'IR')) {
            return 'iran';
        }

        return 'world';
    };

    const getMapData = (mode) => {
        if (mode === 'iran') {
            return {
                chart: iranMap,
                mapKey: 'ir'
            };
        }

        return {
            chart: worldMap,
            mapKey: 'custom/world'
        };
    };

    let liveMapMode = '2d';

    const renderHighchartsMap = (container, rows, title, projection = 'miller') => {
        if (!container) return;

        const mode = getMapMode(rows);
        const mapData = getMapData(mode);
        const seriesData = (rows || [])
            .map((row) => [String(row.code || row.key || '').toLowerCase(), Number(row.count || 0)])
            .filter(([code]) => Boolean(code));

        container.innerHTML = '';
        const chartHost = document.createElement('div');
        chartHost.className = 'h-full w-full';
        container.appendChild(chartHost);

        const isGlobe = projection === 'Orthographic';
        const isDark = Boolean(document.querySelector('.dark'));
        const globePalette = isDark
            ? {
                borderColor: 'rgba(226, 232, 240, 0.55)',
                nullColor: 'rgba(51, 65, 85, 0.85)',
                hoverColor: '#67e8f9',
                labelColor: '#a7f3d0',
                colorStops: [
                    [0, '#164e63'],
                    [0.35, '#0891b2'],
                    [0.7, '#22d3ee'],
                    [1, '#a7f3d0']
                ]
            }
            : {
                borderColor: 'rgba(71, 85, 105, 0.72)',
                nullColor: '#dbeafe',
                hoverColor: '#0e7490',
                labelColor: '#064e3b',
                colorStops: [
                    [0, '#e0f2fe'],
                    [0.35, '#7dd3fc'],
                    [0.7, '#0ea5e9'],
                    [1, '#075985']
                ]
            };

        Highcharts.mapChart(chartHost, {
            chart: {
                map: mapData.chart,
                backgroundColor: 'transparent',
                style: { fontFamily: 'inherit' },
                animation: true
            },
            title: { text: '' },
            credits: { enabled: false },
            exporting: { enabled: false },
            legend: { enabled: false },
            mapNavigation: {
                enabled: true,
                buttonOptions: {
                    verticalAlign: 'bottom'
                }
            },
            mapView: {
                projection: {
                    name: projection
                },
                insetOptions: {
                    borderColor: 'rgba(16, 185, 129, 0.2)'
                }
            },
            colorAxis: {
                min: 0,
                stops: isGlobe ? globePalette.colorStops : [
                    [0, '#ecfdf5'], // emerald 50
                    [0.35, '#6ee7b7'], // emerald 300
                    [0.7, '#10b981'], // emerald 500
                    [1, '#047857'] // emerald 700
                ]
            },
            tooltip: {
                useHTML: true,
                formatter() {
                    const value = this.point?.value || 0;
                    return `<span>${escapeHtml(this.point?.name || this.point?.['hc-key'] || title)}</span><br><strong>${number(value)}</strong>`;
                }
            },
            series: [{
                name: title,
                data: seriesData,
                joinBy: 'hc-key',
                borderColor: isGlobe ? globePalette.borderColor : '#ffffff',
                borderWidth: isGlobe ? 0.9 : 0.6,
                nullColor: isGlobe ? globePalette.nullColor : '#e5e7eb',
                states: {
                    hover: { color: isGlobe ? globePalette.hoverColor : '#34d399' }
                },
                dataLabels: {
                    enabled: isGlobe,
                    formatter() {
                        return this.point.value > 0 ? number(this.point.value) : '';
                    },
                    style: {
                        fontSize: '9px',
                        fontWeight: 'bold',
                        textOutline: 'none',
                        color: isGlobe ? globePalette.labelColor : '#047857'
                    }
                }
            }]
        });
    };

    const renderChart = (container, series) => {
        if (!container) return;
        const entries = Object.entries(series || {});
        if (!entries.length) { container.innerHTML = empty; return; }
        container.innerHTML = '';
        container.style.position = 'relative';
        const margin = { top: 10, right: 10, bottom: 30, left: 40 };
        const rect = container.getBoundingClientRect();
        const width = rect.width || 600;
        const height = 220;
        const iw = width - margin.left - margin.right;
        const ih = height - margin.top - margin.bottom;

        const data = entries.map(([date, value]) => ({ date, value }));
        const maxVal = Math.max(...data.map(d => d.value), 1);
        const step = Math.max(1, Math.ceil(data.length / 10));

        const band = iw / data.length;
        const barPad = band * 0.15;
        const barW = band - barPad * 2;

        const svg = document.createElementNS(svgNs, 'svg');
        svg.setAttribute('width', width);
        svg.setAttribute('height', height);
        container.appendChild(svg);

        const g = document.createElementNS(svgNs, 'g');
        g.setAttribute('transform', `translate(${margin.left},${margin.top})`);
        svg.appendChild(g);

        const tooltip = document.createElement('div');
        tooltip.style.cssText = 'position:absolute;background:rgba(0,0,0,0.8);color:#fff;padding:4px 8px;border-radius:4px;font-size:11px;opacity:0;pointer-events:none;z-index:10;';
        container.appendChild(tooltip);

        data.forEach((d, i) => {
            const x = barPad + i * band;
            const barH = (d.value / maxVal) * ih;
            const bar = document.createElementNS(svgNs, 'rect');
            bar.setAttribute('x', x);
            bar.setAttribute('y', ih - barH);
            bar.setAttribute('width', barW);
            bar.setAttribute('height', barH);
            bar.setAttribute('fill', '#10b981'); // Emerald 500
            bar.setAttribute('opacity', '0.7');
            bar.style.cursor = 'pointer';
            bar.addEventListener('mouseenter', (e) => {
                bar.setAttribute('opacity', '1');
                const c = container.getBoundingClientRect();
                tooltip.style.opacity = '1';
                tooltip.textContent = `${d.date}: ${number(d.value)}`;
                tooltip.style.left = (e.clientX - c.left + 8) + 'px';
                tooltip.style.top = (e.clientY - c.top - 28) + 'px';
            });
            bar.addEventListener('mouseleave', () => {
                bar.setAttribute('opacity', '0.7');
                tooltip.style.opacity = '0';
            });
            g.appendChild(bar);
        });

        const xAxis = document.createElementNS(svgNs, 'g');
        xAxis.setAttribute('transform', `translate(0,${ih})`);
        svg.appendChild(xAxis);
        data.filter((d, i) => i % step === 0 || i === data.length - 1).forEach((d) => {
            const i2 = data.indexOf(d);
            const x = barPad + i2 * band + barW / 2;
            const txt = document.createElementNS(svgNs, 'text');
            txt.setAttribute('x', x);
            txt.setAttribute('y', 16);
            txt.setAttribute('transform', `rotate(-30 ${x} 16)`);
            txt.setAttribute('text-anchor', 'end');
            txt.setAttribute('font-size', '9px');
            txt.setAttribute('fill', '#94a3b8');
            txt.textContent = d.date.length > 7 ? d.date.slice(5) : d.date;
            xAxis.appendChild(txt);
        });

        const yTicks = 5;
        for (let i = 0; i <= yTicks; i++) {
            const v = Math.round((maxVal / yTicks) * i);
            const y = ih - (v / maxVal) * ih;
            const txt = document.createElementNS(svgNs, 'text');
            txt.setAttribute('x', -8);
            txt.setAttribute('y', y + 3.5);
            txt.setAttribute('text-anchor', 'end');
            txt.setAttribute('font-size', '10px');
            txt.setAttribute('fill', '#94a3b8');
            txt.textContent = number(v);
            g.appendChild(txt);
        }
    };

    const renderWorldMap = (container, rows, modeLabel, isLive = false) => {
        if (!container) return;
        const items = (rows || []).filter((row) => row.code || row.key);
        container.innerHTML = '';

        if (isLive) {
            // Full size map for live view
            const mapDiv = document.createElement('div');
            mapDiv.className = 'w-full h-full overflow-hidden';
            container.appendChild(mapDiv);

            const projection = (liveMapMode === '3d') ? 'Orthographic' : 'Miller';
            renderHighchartsMap(mapDiv, items, modeLabel, projection);
            return;
        }

        const wrap = document.createElement('div');
        wrap.className = 'grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]';
        container.appendChild(wrap);

        const mapDiv = document.createElement('div');
        mapDiv.className = 'rounded border border-slate/10 bg-slate/5 overflow-hidden';
        mapDiv.style.minHeight = '340px';
        wrap.appendChild(mapDiv);

        renderHighchartsMap(mapDiv, items, modeLabel, 'Miller');

        const topList = items.slice(0, 12).map((row) => `
            <div class="flex items-center justify-between gap-3 rounded border border-slate/10 px-3 py-2 text-xs">
                <span class="truncate">${escapeHtml(row.label || row.code || row.key)}</span>
                <span class="font-bold text-success shrink-0">${number(row.count)}</span>
            </div>
        `).join('');

        const listDiv = document.createElement('div');
        listDiv.className = 'space-y-2';
        listDiv.innerHTML = topList || '<p class="text-center py-6 text-xs opacity-50">داده‌ای نیست</p>';
        wrap.appendChild(listDiv);
    };

    const loadDashboard = () => {
        fetchJson(endpoint).then((data) => {
            const today = document.querySelector('[data-dashboard-today]');
            const live = document.querySelector('[data-dashboard-live]');
            if (today) today.textContent = number(data.today);
            if (live) live.textContent = number(data.live);
            renderChart(document.querySelector('[data-dashboard-chart]'), data.chart);
            renderSimpleList(document.querySelector('[data-dashboard-goals]'), data.goals, 'تحقق');
        });
    };

    const loadOverview = () => {
        fetchJson(sectionUrl('overview')).then((data) => {
            if (!data) return;
            const todayEl = root.querySelector('[data-stat="today"]');
            const weekEl = root.querySelector('[data-stat="week"]');
            const monthEl = root.querySelector('[data-stat="month"]');
            const liveEl = root.querySelector('[data-stat="live"]');

            if (todayEl) todayEl.textContent = number(data.today);
            if (weekEl) weekEl.textContent = number(data.week);
            if (monthEl) monthEl.textContent = number(data.month);
            if (liveEl) liveEl.textContent = number(data.live);

            renderChart(root.querySelector('[data-chart="overview"]'), data.chart);

            const activityData = Array.isArray(data.activity) ? data.activity : (Array.isArray(data.activities) ? data.activities : []);
            renderBars(root.querySelector('[data-list="overview-activity"]'), activityData.map(a => {
                return a.key === 'error' ? { ...a, isNegative: true } : a;
            }));
        });
    };

    const loadReports = () => {
        fetchJson(sectionUrl('reports')).then((data) => {
            if (!data) return;
            renderBars(root.querySelector('[data-list="countries"]'), data.countries);
            renderBars(root.querySelector('[data-list="referrers"]'), data.referrers);
            renderBars(root.querySelector('[data-list="device-types"]'), data.device_types);
            renderBars(root.querySelector('[data-list="device-brands"]'), data.device_brands);
            renderBars(root.querySelector('[data-list="browsers"]'), data.browsers);
            renderBars(root.querySelector('[data-list="platforms"]'), data.platforms);
            renderBars(root.querySelector('[data-list="utm-sources"]'), data.utm_sources);
            renderBars(root.querySelector('[data-list="utm-mediums"]'), data.utm_mediums);
            renderBars(root.querySelector('[data-list="utm-campaigns"]'), data.utm_campaigns);
            renderBars(root.querySelector('[data-list="search-engines"]'), data.search_engines);

            const activitiesData = Array.isArray(data.activities) ? data.activities : (Array.isArray(data.activity) ? data.activity : []);
            renderBars(root.querySelector('[data-list="activities"]'), activitiesData.map(a => {
                return a.key === 'error' ? { ...a, isNegative: true } : a;
            }));
            renderWorldMap(root.querySelector('[data-map="reports"]'), data.map, 'reports');
        });
    };

    const loadContent = () => {
        fetchJson(sectionUrl('content')).then((data) => {
            if (!data) return;
            renderSimpleList(root.querySelector('[data-list="pages"]'), data.pages);
            renderSimpleList(root.querySelector('[data-list="products"]'), data.products);
            renderSimpleList(root.querySelector('[data-list="categories"]'), data.categories);
            renderSimpleList(root.querySelector('[data-list="sellers"]'), data.sellers);
        });
    };

    const loadSearch = () => {
        fetchJson(sectionUrl('search')).then((data) => {
            if (!data) return;
            renderSimpleList(root.querySelector('[data-list="keywords"]'), data.keywords, 'بار جستجو');
        });
    };

    const findGoal = (rows, key) => (rows || []).find((row) => row.key === key)?.count || 0;

    const loadGoals = () => {
        fetchJson(sectionUrl('goals')).then((data) => {
            if (!data) return;
            ['tr_dk', 'tr_bs'].forEach((key) => {
                const today = findGoal(data.today, key);
                const yesterday = findGoal(data.yesterday, key);
                const diff = today - yesterday;
                const pct = yesterday > 0 ? Math.round((diff / yesterday) * 100) : (diff > 0 ? 100 : 0);
                const valueEl = root.querySelector(`[data-goal-today="${key}"]`);
                const deltaEl = root.querySelector(`[data-goal-delta="${key}"]`);
                if (valueEl) valueEl.textContent = number(today);
                if (deltaEl) {
                    deltaEl.innerHTML = `<span class="${diff >= 0 ? 'text-success' : 'text-danger'}">${diff >= 0 ? '+' : ''}${number(diff)} (${number(pct)}٪)</span>`;
                }
            });

            const table = root.querySelector('[data-goals-table]');
            if (!table) return;
            if (!data.month || !data.month.length) {
                table.innerHTML = '<tr><td colspan="2" class="p-10 text-center opacity-50">هدفی یافت نشد.</td></tr>';
                return;
            }
            table.innerHTML = data.month.map((goal) => `
                <tr class="hover:bg-slate/5 transition-colors">
                    <td class="p-4 font-medium text-xs">${escapeHtml(goal.label)}</td>
                    <td class="p-4 text-center font-bold text-success text-xs">${number(goal.count)}</td>
                </tr>
            `).join('');
        });
    };

    const loadLive = () => {
        fetchJson(sectionUrl('live')).then((data) => {
            if (!data) return;
            const container = root.querySelector('[data-live-list]');
            renderWorldMap(root.querySelector('[data-map="live"]'), data.map, 'live', true);
            if (!container) return;
            if (!data.users || !data.users.length) { container.innerHTML = empty; return; }
            container.innerHTML = data.users.map((user) => `
                <div class="rounded-xl border border-success/10 bg-success/5 p-4 text-xs space-y-3 relative overflow-hidden shadow-sm">
                    <div class="absolute inset-y-0 right-0 w-1 bg-success opacity-30"></div>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate truncate flex items-center gap-2 mb-1">
                                <a href="${user.path}" target="_blank" class="material-icons text-base opacity-40 hover:opacity-100 hover:text-success" title="باز کردن لینک">open_in_new</a>
                                <span class="text-sm">${escapeHtml(user.title || user.path || '-')}</span>
                            </p>
                            <p class="admin-ltr text-left text-slate/50 truncate text-[10px]">${escapeHtml(user.path || '-')}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="px-2 py-1 rounded bg-success/10 text-success font-bold text-[10px]">${user.last_seen}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3 border-t border-slate/5">
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-success/40 text-sm">person</span>
                            <span class="text-slate/70 truncate">${escapeHtml(user.user)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-success/40 text-sm">lan</span>
                            <span class="admin-ltr text-slate/70 text-[10px]">${escapeHtml(user.ip)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-success/40 text-sm">public</span>
                            <span class="text-slate/70">${escapeHtml(user.country)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-icons text-success/40 text-sm">devices</span>
                            <span class="text-slate/70 truncate">${escapeHtml(user.device_brand)} / ${escapeHtml(user.device)}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] text-slate/40 admin-ltr bg-white/50 dark:bg-slate-900/50 p-1.5 rounded">
                        <span class="material-icons text-xs">info</span>
                        <span class="truncate">${escapeHtml(user.user_agent)}</span>
                    </div>
                </div>
            `).join('');
        });
    };

    const loadUsers = () => {
        fetchJson(sectionUrl('users')).then((data) => {
            if (!data) return;
            const container = root.querySelector('[data-list="users"]');
            if (!container) return;
            if (!data.users || !data.users.length) { container.innerHTML = empty; return; }

            container.innerHTML = data.users.map((row) => `
                <div class="flex items-center justify-between text-[11px] border-b border-slate/5 pb-2 gap-4">
                    <span class="truncate" title="${escapeHtml(row.label)}">${escapeHtml(row.label)}</span>
                    <div class="flex items-center gap-3 shrink-0">
                        <span class="font-bold text-success">${number(row.count)} بازدید</span>
                        <button type="button" data-user-id="${row.key}" data-user-name="${escapeHtml(row.label)}" class="admin-btn !h-7 !px-2 !bg-slate/10 hover:!bg-success/20 !text-slate hover:!text-success !text-[10px] !gap-1 !rounded-md">
                            <span class="material-icons text-xs">visibility</span>
                            بیشتر
                        </button>
                    </div>
                </div>
            `).join('');

            container.querySelectorAll('[data-user-id]').forEach(btn => {
                btn.onclick = () => showUserActivity(btn.dataset.userId, btn.dataset.userName);
            });
        });
    };

    const showUserActivity = (userId, userName) => {
        const dialog = document.getElementById('user-activity-modal');
        const content = document.getElementById('activity-modal-content');
        const title = document.getElementById('activity-modal-title');

        if (!dialog || !content) return;

        title.textContent = `فعالیت کاربر: ${userName}`;
        content.innerHTML = '<p class="text-center py-10 opacity-50">در حال دریافت اطلاعات...</p>';
        dialog.showModal();

        const url = root.dataset.userActivityUrl.replace(':id', userId);
        fetchJson(url).then(data => {
            if (!data.events || !data.events.length) {
                content.innerHTML = '<p class="text-center py-10 opacity-50">هیچ فعالیتی ثبت نشده است.</p>';
                return;
            }

            content.innerHTML = `
                <div class="space-y-4">
                    ${data.events.map(e => `
                        <div class="p-3 rounded-xl border border-slate/10 bg-slate/5 space-y-3 relative overflow-hidden">
                            ${e.type === 'goal' ? '<div class="absolute inset-y-0 right-0 w-1 bg-success"></div>' : ''}
                            ${e.type === 'error' ? '<div class="absolute inset-y-0 right-0 w-1 bg-danger"></div>' : ''}

                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold ${e.type === 'goal' ? 'bg-success/10 text-success' : (e.type === 'error' ? 'bg-danger/10 text-danger' : 'bg-slate/10 text-slate/70')}">
                                            ${e.type === 'goal' ? 'تحقق هدف' : (e.type === 'error' ? 'خطا' : 'بازدید')}
                                        </span>
                                        <p class="font-bold text-slate truncate text-sm">${escapeHtml(e.title || e.path || '-')}</p>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-slate/50 admin-ltr">
                                        <a href="${e.path}" target="_blank" class="material-icons text-xs hover:text-success">open_in_new</a>
                                        <span>${escapeHtml(e.path || '-')}</span>
                                    </div>
                                </div>
                                <div class="text-left shrink-0">
                                    <p class="text-[10px] font-bold text-slate/70">${e.time}</p>
                                    <p class="text-[9px] text-slate/40">${e.full_time}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t border-slate/5">
                                <div>
                                    <p class="text-[9px] text-slate/40 mb-0.5">آدرس IP</p>
                                    <p class="text-[10px] font-medium admin-ltr text-right">${escapeHtml(e.ip)}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate/40 mb-0.5">مکان</p>
                                    <p class="text-[10px] font-medium">${escapeHtml(e.country)}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate/40 mb-0.5">دستگاه</p>
                                    <p class="text-[10px] font-medium">${escapeHtml(e.device)}</p>
                                </div>
                                <div>
                                    <p class="text-[9px] text-slate/40 mb-0.5">مرورگر</p>
                                    <p class="text-[10px] font-medium">${escapeHtml(e.browser)}</p>
                                </div>
                            </div>

                            ${e.search_engine || Object.keys(e.utm || {}).length || e.goal ? `
                                <div class="flex flex-wrap gap-2 pt-2">
                                    ${e.search_engine ? `
                                        <div class="px-2 py-1 rounded bg-blue-500/5 border border-blue-500/10 text-blue-500 text-[9px] flex items-center gap-1">
                                            <span class="material-icons text-[10px]">search</span>
                                            ${e.search_engine}
                                        </div>
                                    ` : ''}
                                    ${Object.entries(e.utm || {}).map(([k, v]) => `
                                        <div class="px-2 py-1 rounded bg-amber-500/5 border border-amber-500/10 text-amber-500 text-[9px] flex items-center gap-1">
                                            <span class="font-bold">${k}:</span> ${escapeHtml(v)}
                                        </div>
                                    `).join('')}
                                    ${e.goal ? `
                                        <div class="px-2 py-1 rounded bg-success/10 border border-success/20 text-success text-[9px] font-bold flex items-center gap-1">
                                            <span class="material-icons text-[10px]">emoji_events</span>
                                            ${escapeHtml(e.goal)}
                                        </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
            `;
        });
    };

    const loadErrors = () => {
        fetchJson(sectionUrl('errors')).then((data) => {
            if (!data) return;
            const total = root.querySelector('[data-errors-total]');
            const container = root.querySelector('[data-errors-list]');
            if (total) total.textContent = `${number(data.total)} خطا در ۳۰ روز`;
            if (!container) return;
            if (!data.recent || !data.recent.length) { container.innerHTML = empty; return; }
            container.innerHTML = data.recent.map((error) => `
                <div class="rounded-lg border border-danger/20 bg-danger/10 p-3 text-xs space-y-2 shadow-sm shadow-danger/5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-bold text-danger truncate flex items-center gap-2">
                                <a href="${error.path}" target="_blank" class="material-icons text-sm opacity-40 hover:opacity-100 hover:text-danger">open_in_new</a>
                                <span>${escapeHtml(error.message)}</span>
                            </p>
                            <p class="admin-ltr text-left text-slate/60 truncate mt-1">${escapeHtml(error.path || '-')}</p>
                        </div>
                        <div class="text-slate/70 flex flex-wrap gap-2">
                            <span>${escapeHtml(error.user)}</span>
                            <span>${escapeHtml(error.ip)}</span>
                            <span>${escapeHtml(error.country)}</span>
                            <span>${escapeHtml(error.device)}</span>
                            <span>${escapeHtml(error.time)}</span>
                        </div>
                    </div>
                    <p class="admin-ltr text-left text-[10px] text-slate/50 truncate">${escapeHtml(error.source || '-')} ${error.line ? ':' + number(error.line) : ''}</p>
                    <p class="admin-ltr text-left text-[10px] text-slate/50 truncate">${escapeHtml(error.user_agent)}</p>
                </div>
            `).join('');
        });
    };

    const loaders = {
        'tab-overview': loadOverview,
        'tab-live': loadLive,
        'tab-reports': () => {
            fetchJson(sectionUrl('reports')).then((data) => {
                if (!data) return;
                renderBars(root.querySelector('[data-list="countries"]'), data.countries);
                renderBars(root.querySelector('[data-list="referrers"]'), data.referrers);
                renderBars(root.querySelector('[data-list="device-types"]'), data.device_types);
                renderBars(root.querySelector('[data-list="device-brands"]'), data.device_brands);
                renderBars(root.querySelector('[data-list="browsers"]'), data.browsers);
                renderBars(root.querySelector('[data-list="platforms"]'), data.platforms);
                renderBars(root.querySelector('[data-list="utm-sources"]'), data.utm_sources);
                renderBars(root.querySelector('[data-list="utm-mediums"]'), data.utm_mediums);
                renderBars(root.querySelector('[data-list="utm-campaigns"]'), data.utm_campaigns);
                renderBars(root.querySelector('[data-list="search-engines"]'), data.search_engines);

                const activitiesData = Array.isArray(data.activities) ? data.activities : (Array.isArray(data.activity) ? data.activity : []);
                renderBars(root.querySelector('[data-list="activities"]'), activitiesData.map(a => {
                    return a.key === 'error' ? { ...a, isNegative: true } : a;
                }));

                // Unified behavior: Always use live-style map for reports tab if container found
                const reportsMapContainer = root.querySelector('[data-map="reports"]');
                if (reportsMapContainer) {
                    renderWorldMap(reportsMapContainer, data.map, 'reports', true);
                }
            });
        },
        'tab-content': loadContent,
        'tab-search': loadSearch,
        'tab-goals': loadGoals,
        'tab-users': loadUsers,
        'tab-errors': loadErrors
    };

    if (dashboardRoot) {
        loadDashboard();
        return;
    }

    if (filterForm) {
        const today = new Date();
        const start = new Date();
        start.setDate(today.getDate() - 29);

        const todayPd = new PersianDate(today);
        const startPd = new PersianDate(start);

        const fromInput = filterForm.elements.from;
        const toInput = filterForm.elements.to;

        if (fromInput && !fromInput.value) {
            fromInput.value = startPd.format('YYYY/MM/DD');
            fromInput.dataset.gregorian = toGregorianFromShamsi(fromInput.value);
        }
        if (toInput && !toInput.value) {
            toInput.value = todayPd.format('YYYY/MM/DD');
            toInput.dataset.gregorian = toGregorianFromShamsi(toInput.value);
        }

        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            loadedSections.clear();
            loadOverview();
            const activeTab = document.querySelector('.analytics-tab-content:not(.hidden)')?.id;
            if (activeTab && activeTab !== 'tab-overview') {
                loadedSections.add(activeTab);
                loaders[activeTab]?.();
            }
        });

        filterForm.querySelectorAll('select').forEach((select) => {
            select.addEventListener('change', () => {
                filterForm.requestSubmit();
            });
        });
    }

    loadOverview();
    window.addEventListener('analytics-tab-switched', (event) => {
        const tab = event.detail.tab;
        if (loadedSections.has(tab) && tab !== 'tab-live') return;
        loadedSections.add(tab);
        loaders[tab]?.();
    });

    root.querySelectorAll('[data-live-map-mode]').forEach(btn => {
        btn.onclick = () => {
            liveMapMode = btn.dataset.liveMapMode;
            root.querySelectorAll('[data-live-map-mode]').forEach(b => {
                const isActive = b.dataset.liveMapMode === liveMapMode;
                if (isActive) {
                    b.className = 'flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-xs font-black text-white shadow-sm ring-1 ring-emerald-500/20 transition-all dark:bg-emerald-400 dark:text-slate-950 dark:ring-emerald-300/30';
                } else {
                    b.className = 'flex items-center gap-2 rounded-lg px-6 py-2.5 text-xs font-black text-slate-600 transition-all hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white';
                }
            });

            // Reload whichever map is currently visible
            const activeTab = document.querySelector('.analytics-tab-content:not(.hidden)')?.id;
            if (activeTab === 'tab-live' || activeTab === 'tab-reports') {
                loaders[activeTab]?.();
            }
        };
    });

    setInterval(() => {
        if (!document.getElementById('tab-live')?.classList.contains('hidden')) {
            loadLive();
        }
    }, 30000);
})();
