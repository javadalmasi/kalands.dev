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
    const liveRefreshMs = 30000;
    let overviewLivePrevious = null;
    let overviewLiveCountdownTimer = null;
    let overviewLiveCountdownStartedAt = 0;
    let liveSummaryPrevious = null;
    let liveSummaryCountdownTimer = null;
    let liveSummaryCountdownStartedAt = 0;
    let currentTab = 'tab-overview';

    const tabFilterConfig = {
        'tab-overview': {
            title: 'فیلترهای داشبورد',
            description: 'فیلترهای بازه، رفتار و کیفیت ترافیک برای KPIها و نمودارهای خلاصه.',
            fields: ['from', 'to', 'period', 'search', 'country', 'device_type', 'activity', 'source', 'session_status'],
        },
        'tab-live': {
            title: 'فیلترهای نمای زنده',
            description: 'تمرکز روی کاربران فعال، منبع ورود، دستگاه، مرورگر و وضعیت جلسات زنده.',
            fields: ['country', 'device_type', 'source', 'browser', 'platform', 'session_status', 'search'],
        },
        'tab-analytics': {
            title: 'فیلترهای آنالیز',
            description: 'تحلیل کشور، دستگاه، مرورگر، سیستم‌عامل، موتور جستجو، UTM و محتوا.',
            fields: ['from', 'to', 'period', 'path', 'country', 'device_type', 'activity', 'source', 'campaign', 'browser', 'platform', 'search'],
        },
        'tab-goals': {
            title: 'فیلترهای اهداف',
            description: 'تحلیل دقیق goalها بر اساس کلید هدف، منبع، کمپین و نوع کاربر.',
            fields: ['from', 'to', 'period', 'goal_key', 'source', 'campaign', 'country', 'device_type', 'session_status'],
        },
    };

    const number = (value) => new Intl.NumberFormat('fa-IR').format(value || 0);
    const syncLiveCounters = (count) => {
        const liveValue = number(count || 0);
        root.querySelectorAll('[data-stat="live"], #live-summary-users, #live-users-count').forEach((el) => {
            if (!el) return;
            el.textContent = liveValue;
        });
    };

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

    const updateLiveProgress = (count, selectors, state) => {
        const progressEl = root.querySelector(selectors.progress);
        const statusEl = root.querySelector(selectors.status);
        const deltaEl = root.querySelector(selectors.delta);
        if (!progressEl || !statusEl || !deltaEl) return;

        const current = Number(count) || 0;
        const previous = state.previous;
        const delta = previous === null ? 0 : current - previous;

        let tone = {
            bar: 'bg-sky-500/12',
            status: 'bg-sky-500/[0.04]',
            text: 'بدون تغییر نسبت به نوبت قبل',
        };

        if (previous === null) {
            tone.text = 'اولین اسنپ‌شات زنده ثبت شد';
        } else if (delta > 0) {
            tone = {
                bar: 'bg-emerald-500/14',
                status: 'bg-emerald-500/[0.05]',
                text: `+${number(delta)} کاربر نسبت به نوبت قبل`,
            };
        } else if (delta < 0) {
            tone = {
                bar: 'bg-rose-500/14',
                status: 'bg-rose-500/[0.05]',
                text: `${number(delta)} کاربر نسبت به نوبت قبل`,
            };
        }

        progressEl.className = `absolute inset-y-0 right-0 w-full transition-all duration-700 ease-linear ${tone.bar}`;
        statusEl.className = `absolute inset-0 transition-colors duration-500 ${tone.status}`;
        deltaEl.textContent = tone.text;
        state.previous = current;
        state.startedAt = Date.now();

        if (state.timer) {
            clearInterval(state.timer);
        }

        const tick = () => {
            const elapsed = Date.now() - state.startedAt;
            const remainingRatio = Math.max(0, 1 - (elapsed / liveRefreshMs));
            progressEl.style.width = `${Math.max(6, remainingRatio * 100)}%`;
            if (remainingRatio <= 0 && state.timer) {
                clearInterval(state.timer);
                state.timer = null;
            }
        };

        progressEl.style.width = '100%';
        tick();
        state.timer = setInterval(tick, 250);
    };

    const updateOverviewLiveProgress = (count) => {
        updateLiveProgress(count, {
            progress: '#overview-live-progress',
            status: '#overview-live-status',
            delta: '#overview-live-delta',
        }, {
            get previous() { return overviewLivePrevious; },
            set previous(value) { overviewLivePrevious = value; },
            get timer() { return overviewLiveCountdownTimer; },
            set timer(value) { overviewLiveCountdownTimer = value; },
            get startedAt() { return overviewLiveCountdownStartedAt; },
            set startedAt(value) { overviewLiveCountdownStartedAt = value; },
        });
    };

    const updateLiveSummaryProgress = (count) => {
        updateLiveProgress(count, {
            progress: '#live-summary-progress',
            status: '#live-summary-status',
            delta: '#live-summary-delta',
        }, {
            get previous() { return liveSummaryPrevious; },
            set previous(value) { liveSummaryPrevious = value; },
            get timer() { return liveSummaryCountdownTimer; },
            set timer(value) { liveSummaryCountdownTimer = value; },
            get startedAt() { return liveSummaryCountdownStartedAt; },
            set startedAt(value) { liveSummaryCountdownStartedAt = value; },
        });
    };

    const syncFilterVisibility = (tab) => {
        if (!filterForm) return;
        const filterCard = root.querySelector('#analytics-filter-card');
        const title = root.querySelector('#analytics-filter-title');
        const description = root.querySelector('#analytics-filter-description');
        const config = tabFilterConfig[tab];

        if (!filterCard) return;

        if (!config) {
            filterCard.classList.add('hidden');
            return;
        }

        filterCard.classList.remove('hidden');
        if (title) title.textContent = config.title;
        if (description) description.textContent = config.description;

        filterForm.querySelectorAll('[data-filter-field]').forEach((field) => {
            const shouldShow = config.fields.includes(field.dataset.filterField);
            field.classList.toggle('hidden', !shouldShow);
            field.querySelectorAll('input, select').forEach((input) => {
                input.disabled = !shouldShow;
            });
        });
    };

    const empty = '<p class="text-center py-10 opacity-50">داده‌ای یافت نشد.</p>';

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[char]));

    const decodeUrl = (value) => {
        try {
            return decodeURIComponent(String(value ?? ''));
        } catch {
            return String(value ?? '');
        }
    };

    const displayPath = (value) => escapeHtml(decodeUrl(value));

    const renderLabel = (row) => {
        if (row && row.htmlLabel) return row.htmlLabel;
        return displayPath(row?.label || '-');
    };

    const countryFlag = (code) => {
        const cc = String(code || '').trim().toUpperCase();
        if (!/^[A-Z]{2}$/.test(cc)) return '🌐';
        return String.fromCodePoint(...[...cc].map((char) => 127397 + char.charCodeAt(0)));
    };

    const iconWrap = (inner, classes = 'bg-transparent') => `
        <span class="inline-flex h-7 w-7 items-center justify-center rounded-md shrink-0 overflow-hidden ${classes}">${inner}</span>
    `;

    const simpleIcon = (slug, fallback = 'public') => iconWrap(
        `<i class="si si-${slug} si--color text-[19px] leading-none"></i>`,
        'bg-transparent'
    );

    const fallbackMaterial = (name = 'public') => iconWrap(
        `<span class="material-icons text-[20px] text-slate-500">${name}</span>`,
        'bg-transparent'
    );

    const firstMatchingIcon = (value, pairs, fallback = 'public') => {
        for (const [matcher, slug] of pairs) {
            if (matcher.test(value)) return simpleIcon(slug, fallback);
        }
        return fallbackMaterial(fallback);
    };

    const browserIcon = (label) => {
        const value = String(label || '').toLowerCase();
        return firstMatchingIcon(value, [
            [/google chrome|^chrome$|chromium/, 'googlechrome'],
            [/firefox/, 'firefoxbrowser'],
            [/tor/, 'torbrowser'],
            [/microsoft edge|^edge$/, 'microsoftedge'],
            [/safari/, 'safari'],
            [/opera/, 'opera'],
            [/brave/, 'brave'],
            [/vivaldi/, 'vivaldi'],
            [/arc/, 'arc'],
            [/duckduckgo/, 'duckduckgo'],
            [/samsung internet/, 'samsung'],
            [/internet explorer|\bie\b|^ie$/, 'internetexplorer'],
            [/claude|anthropic/, 'claude'],
            [/qwen/, 'qwen'],
            [/deepseek/, 'deepseek'],
            [/github/, 'github'],
            [/openai|chatgpt|gpt/, 'openai'],
            [/perplexity/, 'perplexity'],
        ], 'language');
    };

    const platformIcon = (label) => {
        const value = String(label || '').toLowerCase();
        return firstMatchingIcon(value, [
            [/windows/, 'windows'],
            [/android/, 'android'],
            [/ios|iphone|ipad/, 'ios'],
            [/mac|os x|macos/, 'apple'],
            [/ubuntu/, 'ubuntu'],
            [/debian/, 'debian'],
            [/fedora/, 'fedora'],
            [/arch/, 'archlinux'],
            [/linux/, 'linux'],
            [/chrome ?os/, 'googlechrome'],
        ], 'devices');
    };

    const searchEngineIcon = (label) => {
        const value = String(label || '').toLowerCase();
        return firstMatchingIcon(value, [
            [/google/, 'google'],
            [/bing/, 'bing'],
            [/yahoo/, 'yahoo'],
            [/duckduckgo/, 'duckduckgo'],
            [/yandex/, 'yandex'],
            [/baidu/, 'baidu'],
            [/qwant/, 'qwant'],
            [/ecosia/, 'ecosia'],
            [/naver/, 'naver'],
            [/searx/, 'searxng'],
            [/startpage/, 'startpage'],
            [/claude|anthropic/, 'claude'],
            [/qwen/, 'qwen'],
            [/deepseek/, 'deepseek'],
            [/github/, 'github'],
            [/openai|chatgpt|gpt/, 'openai'],
            [/perplexity/, 'perplexity'],
        ], 'search');
    };

    const aiBrandIcon = (label) => {
        const value = String(label || '').toLowerCase();
        return firstMatchingIcon(value, [
            [/claude|anthropic/, 'claude'],
            [/qwen/, 'qwen'],
            [/deepseek/, 'deepseek'],
            [/github/, 'github'],
            [/openai|chatgpt|gpt/, 'openai'],
            [/perplexity/, 'perplexity'],
            [/gemini/, 'googlegemini'],
            [/copilot/, 'githubcopilot'],
            [/mistral/, 'mistralai'],
            [/meta|llama/, 'meta'],
        ], 'smart_toy');
    };

    const deviceBrandIcon = (label) => {
        const value = String(label || '').toLowerCase();
        return firstMatchingIcon(value, [
            [/apple|iphone|ipad|mac/, 'apple'],
            [/samsung/, 'samsung'],
            [/xiaomi|\bmi\b|redmi|poco/, 'xiaomi'],
            [/huawei/, 'huawei'],
            [/honor/, 'honor'],
            [/oppo/, 'oppo'],
            [/vivo/, 'vivo'],
            [/realme/, 'realme'],
            [/oneplus/, 'oneplus'],
            [/google|pixel/, 'google'],
            [/sony/, 'sony'],
            [/nokia/, 'nokia'],
            [/asus/, 'asus'],
            [/lenovo/, 'lenovo'],
            [/dell/, 'dell'],
            [/hp|hewlett/, 'hp'],
            [/acer/, 'acer'],
            [/msi/, 'msi'],
            [/intel/, 'intel'],
            [/amd/, 'amd'],
            [/motorola/, 'motorola'],
            [/lg/, 'lg'],
            [/meizu/, 'meizu'],
            [/zte/, 'zte'],
            [/claude|anthropic/, 'claude'],
            [/qwen/, 'qwen'],
            [/deepseek/, 'deepseek'],
            [/github/, 'github'],
            [/openai|chatgpt|gpt/, 'openai'],
            [/perplexity/, 'perplexity'],
        ], 'smartphone');
    };

    const withIconLabel = (iconHtml, label) => `
        <span class="inline-flex items-center gap-2 min-w-0">
            <span class="shrink-0">${iconHtml}</span>
            <span class="truncate">${displayPath(label)}</span>
        </span>
    `;

    const referrerIcon = (label) => {
        const value = String(label || '').toLowerCase();
        if (/google|bing|duckduckgo|yahoo|yandex|search|organic/.test(value)) return searchEngineIcon(label);
        if (/instagram|facebook|linkedin|youtube|telegram|twitter|x|social/.test(value)) return deviceBrandIcon(label);
        if (/direct/.test(value)) return fallbackMaterial('south_west');
        if (/email/.test(value)) return fallbackMaterial('mail');
        if (/referral|partner|affiliate/.test(value)) return fallbackMaterial('link');
        return fallbackMaterial('hub');
    };

    const iconizeRows = (rows, kind) => {
        const items = Array.isArray(rows) ? rows : [];
        return items.map((row) => {
            switch (kind) {
                case 'countries':
                    return { ...row, htmlLabel: withIconLabel(`<span class="text-base">${countryFlag(row.key)}</span>`, row.label) };
                case 'device-brands':
                    return { ...row, htmlLabel: withIconLabel(deviceBrandIcon(row.label), row.label) };
                case 'browsers':
                    return { ...row, htmlLabel: withIconLabel(browserIcon(row.label), row.label) };
                case 'platforms':
                    return { ...row, htmlLabel: withIconLabel(platformIcon(row.label), row.label) };
                case 'search-engines':
                    return { ...row, htmlLabel: withIconLabel(searchEngineIcon(row.label), row.label) };
                case 'referrers':
                case 'utm':
                    return { ...row, htmlLabel: withIconLabel(referrerIcon(row.label), row.label) };
                case 'pages':
                    return { ...row, htmlLabel: withIconLabel(fallbackMaterial('description'), row.label) };
                case 'products':
                    return { ...row, htmlLabel: withIconLabel(fallbackMaterial('inventory_2'), row.label) };
                case 'categories':
                    return { ...row, htmlLabel: withIconLabel(fallbackMaterial('category'), row.label) };
                case 'sellers':
                    return { ...row, htmlLabel: withIconLabel(fallbackMaterial('storefront'), row.label) };
                case 'keywords':
                    return { ...row, htmlLabel: withIconLabel(fallbackMaterial('search'), row.label) };
                case 'goals':
                    return { ...row, htmlLabel: withIconLabel(fallbackMaterial('emoji_events'), row.label) };
                case 'users':
                    return { ...row, htmlLabel: withIconLabel(fallbackMaterial('person'), row.label) };
                default:
                    return row;
            }
        });
    };

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
                    <span class="truncate ml-4" title="${escapeHtml(row.label || '-')}">${renderLabel(row)}</span>
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
                    <span class="truncate flex items-center" title="${escapeHtml(row.label || '-')}">
                        ${openLinkHtml}
                        <span class="truncate">${renderLabel(row)}</span>
                    </span>
                    <span class="font-bold ${colorClass} shrink-0">${number(row.count)} ${suffix}</span>
                </div>
            `;
        }).join('');
    };

    const svgNs = 'http://www.w3.org/2000/svg';

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
            return { chart: iranMap, mapKey: 'ir' };
        }
        return { chart: worldMap, mapKey: 'custom/world' };
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
                    [0, '#164e63'], [0.35, '#0891b2'], [0.7, '#22d3ee'], [1, '#a7f3d0']
                ]
            }
            : {
                borderColor: 'rgba(71, 85, 105, 0.72)',
                nullColor: '#dbeafe',
                hoverColor: '#0e7490',
                labelColor: '#064e3b',
                colorStops: [
                    [0, '#e0f2fe'], [0.35, '#7dd3fc'], [0.7, '#0ea5e9'], [1, '#075985']
                ]
            };

        Highcharts.mapChart(chartHost, {
            chart: { map: mapData.chart, backgroundColor: 'transparent', style: { fontFamily: 'inherit' }, animation: true },
            title: { text: '' },
            credits: { enabled: false },
            exporting: { enabled: false },
            legend: { enabled: false },
            mapNavigation: { enabled: true, buttonOptions: { verticalAlign: 'bottom' } },
            mapView: { projection: { name: projection }, insetOptions: { borderColor: 'rgba(16, 185, 129, 0.2)' } },
            colorAxis: { min: 0, stops: isGlobe ? globePalette.colorStops : [
                [0, '#ecfdf5'], [0.35, '#6ee7b7'], [0.7, '#10b981'], [1, '#047857']
            ]},
            tooltip: { useHTML: true, formatter() {
                const value = this.point?.value || 0;
                return `<span>${escapeHtml(this.point?.name || this.point?.['hc-key'] || title)}</span><br><strong>${number(value)}</strong>`;
            }},
            series: [{
                name: title,
                data: seriesData,
                joinBy: 'hc-key',
                borderColor: isGlobe ? globePalette.borderColor : '#ffffff',
                borderWidth: isGlobe ? 0.9 : 0.6,
                nullColor: isGlobe ? globePalette.nullColor : '#e5e7eb',
                states: { hover: { color: isGlobe ? globePalette.hoverColor : '#34d399' } },
                dataLabels: { enabled: isGlobe, formatter() {
                    return this.point.value > 0 ? number(this.point.value) : '';
                }, style: { fontSize: '9px', fontWeight: 'bold', textOutline: 'none', color: isGlobe ? globePalette.labelColor : '#047857' } }
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
            bar.setAttribute('fill', '#10b981');
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

    const renderCompareChart = (container, currentSeries, previousSeries) => {
        if (!container) return;
        const current = Object.entries(currentSeries || {});
        const previous = Object.entries(previousSeries || {});
        if (!current.length && !previous.length) { container.innerHTML = empty; return; }
        container.innerHTML = '';
        const svg = document.createElementNS(svgNs, 'svg');
        svg.setAttribute('width', '100%');
        svg.setAttribute('height', '250');
        svg.setAttribute('viewBox', '0 0 800 250');
        svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        container.appendChild(svg);

        const pad = { top: 10, right: 10, bottom: 30, left: 50 };
        const w = 800 - pad.left - pad.right;
        const h = 250 - pad.top - pad.bottom;

        const allDates = [...new Set([...current, ...previous].map(([d]) => d))].sort();
        const curMap = Object.fromEntries(current);
        const prevMap = Object.fromEntries(previous);
        const maxVal = Math.max(...allDates.map(d => Math.max(curMap[d] || 0, prevMap[d] || 0)), 1);
        const step = Math.max(1, Math.ceil(allDates.length / 12));

        const g = document.createElementNS(svgNs, 'g');
        g.setAttribute('transform', `translate(${pad.left},${pad.top})`);
        svg.appendChild(g);

        const yTicks = 5;
        for (let i = 0; i <= yTicks; i++) {
            const v = Math.round((maxVal / yTicks) * i);
            const y = h - (v / maxVal) * h;
            const line = document.createElementNS(svgNs, 'line');
            line.setAttribute('x1', '0'); line.setAttribute('y1', y);
            line.setAttribute('x2', w); line.setAttribute('y2', y);
            line.setAttribute('stroke', '#e5e7eb'); line.setAttribute('stroke-width', '0.5');
            g.appendChild(line);
            const txt = document.createElementNS(svgNs, 'text');
            txt.setAttribute('x', '-8'); txt.setAttribute('y', y + 3.5);
            txt.setAttribute('text-anchor', 'end'); txt.setAttribute('font-size', '10');
            txt.setAttribute('fill', '#94a3b8'); txt.textContent = number(v);
            g.appendChild(txt);
        }

        const band = w / allDates.length;
        allDates.forEach((d, i) => {
            const cx = band * i + band / 2;
            const cv = curMap[d] || 0;
            const pv = prevMap[d] || 0;
            const cy = h - (cv / maxVal) * h;
            const py = h - (pv / maxVal) * h;

            if (cv > 0) {
                const cDot = document.createElementNS(svgNs, 'circle');
                cDot.setAttribute('cx', cx); cDot.setAttribute('cy', cy);
                cDot.setAttribute('r', '4'); cDot.setAttribute('fill', '#10b981');
                cDot.setAttribute('opacity', '0.8');
                cDot.title = `${d}: ${number(cv)}`;
                g.appendChild(cDot);
            }
            if (pv > 0) {
                const pDot = document.createElementNS(svgNs, 'circle');
                pDot.setAttribute('cx', cx); pDot.setAttribute('cy', py);
                pDot.setAttribute('r', '3'); pDot.setAttribute('fill', '#94a3b8');
                pDot.setAttribute('opacity', '0.5');
                pDot.title = `${d}: ${number(pv)}`;
                g.appendChild(pDot);
            }

            if (i % step === 0 || i === allDates.length - 1) {
                const txt = document.createElementNS(svgNs, 'text');
                txt.setAttribute('x', cx); txt.setAttribute('y', h + 18);
                txt.setAttribute('text-anchor', 'middle'); txt.setAttribute('font-size', '9');
                txt.setAttribute('fill', '#94a3b8');
                txt.textContent = d.length > 7 ? d.slice(5) : d;
                g.appendChild(txt);
            }
        });

        const legend = document.createElement('div');
        legend.className = 'flex items-center gap-6 mt-2 text-xs text-slate/70';
        legend.innerHTML = '<span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-success opacity-80"></span> فعلی</span><span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-slate opacity-50"></span> قبلی</span>';
        container.appendChild(legend);
    };

    const renderWorldMap = (container, rows, modeLabel, isLive = false) => {
        if (!container) return;
        const items = (rows || []).filter((row) => row.code || row.key);
        container.innerHTML = '';

        if (isLive) {
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
            const uniquesEl = root.querySelector('[data-stat="today-uniques"]');
            const sessionsEl = root.querySelector('[data-stat="today-sessions"]');
            const bounceEl = root.querySelector('[data-stat="bounce-rate"]');
            const durationEl = root.querySelector('[data-stat="avg-duration"]');

            if (todayEl) todayEl.textContent = number(data.today);
            if (weekEl) weekEl.textContent = number(data.week);
            if (monthEl) monthEl.textContent = number(data.month);
            if (liveEl) liveEl.textContent = number(data.live);
            syncLiveCounters(data.live);
            updateOverviewLiveProgress(data.live);
            if (uniquesEl) uniquesEl.textContent = number(data.today_uniques);
            if (sessionsEl) sessionsEl.textContent = number(data.sessions);
            if (bounceEl) bounceEl.textContent = (data.bounce_rate || 0) + '%';
            if (durationEl) {
                const sec = data.avg_duration || 0;
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                durationEl.textContent = m + ':' + String(s).padStart(2, '0');
            }

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
            const countryCountEl = root.querySelector('[data-reports-country-count]');
            const browserCountEl = root.querySelector('[data-reports-browser-count]');
            const sourceCountEl = root.querySelector('[data-reports-source-count]');
            if (countryCountEl) countryCountEl.textContent = number((data.countries || []).length);
            if (browserCountEl) browserCountEl.textContent = number((data.browsers || []).length);
            if (sourceCountEl) sourceCountEl.textContent = number((data.referrers || []).length);
            renderBars(root.querySelector('[data-list="countries"]'), iconizeRows(data.countries, 'countries'));
            renderBars(root.querySelector('[data-list="referrers"]'), iconizeRows(data.referrers, 'referrers'));
            renderBars(root.querySelector('[data-list="device-types"]'), data.device_types);
            renderBars(root.querySelector('[data-list="device-brands"]'), iconizeRows(data.device_brands, 'device-brands'));
            renderBars(root.querySelector('[data-list="browsers"]'), iconizeRows(data.browsers, 'browsers'));
            renderBars(root.querySelector('[data-list="platforms"]'), iconizeRows(data.platforms, 'platforms'));
            renderBars(root.querySelector('[data-list="utm-sources"]'), iconizeRows(data.utm_sources, 'utm'));
            renderBars(root.querySelector('[data-list="utm-mediums"]'), iconizeRows(data.utm_mediums, 'utm'));
            renderBars(root.querySelector('[data-list="utm-campaigns"]'), iconizeRows(data.utm_campaigns, 'utm'));
            renderBars(root.querySelector('[data-list="search-engines"]'), iconizeRows(data.search_engines, 'search-engines'));

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
            renderSimpleList(root.querySelector('[data-list="pages"]'), iconizeRows(data.pages, 'pages'));
            renderSimpleList(root.querySelector('[data-list="products"]'), iconizeRows(data.products, 'products'));
            renderSimpleList(root.querySelector('[data-list="categories"]'), iconizeRows(data.categories, 'categories'));
            renderSimpleList(root.querySelector('[data-list="sellers"]'), iconizeRows(data.sellers, 'sellers'));
        });
    };

    const loadSearch = () => {
        fetchJson(sectionUrl('search')).then((data) => {
            if (!data) return;
            renderSimpleList(root.querySelector('[data-list="keywords"]'), iconizeRows(data.keywords, 'keywords'), 'بار جستجو');
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
            table.innerHTML = iconizeRows(data.month, 'goals').map((goal) => `
                <tr class="hover:bg-slate/5 transition-colors">
                    <td class="p-4 font-medium text-xs">${renderLabel(goal)}</td>
                    <td class="p-4 text-center font-bold text-success text-xs">${number(goal.count)}</td>
                </tr>
            `).join('');
        });
    };

    const loadLive = () => {
        fetchJson(sectionUrl('live')).then((data) => {
            if (!data) return;
            const container = root.querySelector('#live-users-list');
            const countEl = root.querySelector('#live-users-count');
            const indicator = root.querySelector('#live-update-indicator');
            const summaryUsers = root.querySelector('#live-summary-users');
            const summaryBrowserIcon = root.querySelector('#live-summary-browser-icon');
            const summaryBrowserName = root.querySelector('#live-summary-browser-name');
            const summaryGoals = root.querySelector('#live-summary-goals');
            const summaryTopSource = root.querySelector('#live-summary-top-source');
            const countryBreakdown = root.querySelector('#live-country-breakdown');
            const sourceBreakdown = root.querySelector('#live-source-breakdown');
            const deviceBreakdown = root.querySelector('#live-device-breakdown');
            const browserBreakdown = root.querySelector('#live-browser-breakdown');
            renderWorldMap(root.querySelector('[data-map="live"]'), data.map, 'live', true);
            if (!container) return;
            
            syncLiveCounters(data.count || 0);
            updateLiveSummaryProgress(data.count || 0);
            if (indicator) {
                indicator.classList.remove('animate-pulse');
                void indicator.offsetWidth;
                indicator.classList.add('animate-pulse');
            }

            if (!data.users || !data.users.length) { 
                container.innerHTML = empty; 
                if (summaryBrowserIcon) summaryBrowserIcon.innerHTML = '';
                if (summaryBrowserName) summaryBrowserName.textContent = '-';
                if (summaryGoals) summaryGoals.textContent = '0';
                if (summaryTopSource) summaryTopSource.textContent = '-';
                renderLiveMiniBars(countryBreakdown, []);
                renderLiveMiniBars(sourceBreakdown, []);
                renderLiveMiniBars(deviceBreakdown, []);
                renderLiveMiniBars(browserBreakdown, []);
                return; 
            }

            const visibleUsers = data.users.slice(0, 10);
            const sourceRows = Array.isArray(data.sources) && data.sources.length
                ? data.sources
                : aggregateLiveRows(data.users, (row) => row.source_label, (row) => row.source_label || 'مستقیم', 6);
            const deviceRows = aggregateLiveRows(visibleUsers, (row) => `${row.device_brand || '-'}|${row.device || '-'}`, (row) => [row.device_brand, row.device].filter(Boolean).join(' / ') || '-', 6)
                .map((row) => ({ ...row, htmlLabel: withIconLabel(deviceBrandIcon(row.label), row.label) }));
            const browserRows = aggregateLiveRows(visibleUsers, (row) => `${row.browser || '-'}|${row.platform || '-'}`, (row) => [row.browser, row.platform].filter(Boolean).join(' / ') || '-', 6)
                .map((row) => ({ ...row, htmlLabel: withIconLabel(browserIcon(row.label), row.label) }));

            if (summaryBrowserIcon || summaryBrowserName) {
                const topBrowser = data.stats?.top_browser || null;
                const browserLabel = [topBrowser?.browser, topBrowser?.platform].filter(Boolean).join(' / ') || '-';
                const browserIconHtml = browserIcon(browserLabel);
                if (summaryBrowserIcon) summaryBrowserIcon.innerHTML = browserIconHtml;
                if (summaryBrowserName) summaryBrowserName.textContent = browserLabel;
            }
            if (summaryGoals) summaryGoals.textContent = number(data.stats?.goals_last_10m || 0);
            if (summaryTopSource) {
                summaryTopSource.textContent = data.stats?.top_source || sourceRows[0]?.label || '-';
            }

            renderLiveMiniBars(countryBreakdown, (data.map || []).slice(0, 6).map((row) => ({
                label: row.label || row.code || '-',
                count: row.count || 0,
            })), 'success');
            renderLiveMiniBars(sourceBreakdown, sourceRows, 'amber');
            renderLiveMiniBars(deviceBreakdown, deviceRows, 'info');
            renderLiveMiniBars(browserBreakdown, browserRows, 'violet');
            
            container.innerHTML = visibleUsers.map((user, index) => `
                <button type="button"
                    class="live-user-item group block w-full min-h-[172px] text-right rounded-2xl border border-slate/15 bg-white/80 dark:bg-slate-900/80 p-4 text-xs relative overflow-visible transition-all hover:border-success/30 hover:shadow-lg hover:shadow-success/5"
                    data-session-id="${escapeHtml(user.session_id)}"
                    title="${buildLiveUserTooltip(user)}"
                    style="min-width: 0;">
                    <div class="flex items-start gap-3">
                        <div class="pt-0.5 flex items-center gap-2 shrink-0">
                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-success animate-pulse shadow-[0_0_0_4px_rgba(16,185,129,0.12)]" aria-hidden="true"></span>
                            <span class="text-[10px] font-black text-slate/35">${String(index + 1).padStart(2, '0')}</span>
                        </div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <p class="font-bold text-slate truncate flex-1">${escapeHtml(truncateText(user.title || decodeUrl(user.path) || '-', 52))}</p>
                                        <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-bold ${sourceTone(user)}">${escapeHtml(truncateText(user.source_label || 'مستقیم', 24))}</span>
                                    </div>
                                    <p class="admin-ltr text-left text-slate/45 truncate text-[10px] mt-1">${displayPath(user.path || '-')}</p>
                                </div>
                                <div class="shrink-0 flex items-center gap-1.5">
                                    <span class="px-2 py-1 rounded-lg bg-info/10 text-info font-bold text-[10px]">${number(user.pageviews || 1)} PV</span>
                                    <span class="px-2 py-1 rounded-lg bg-success/10 text-success font-bold text-[10px] live-timer" data-first-seen="${escapeHtml(user.first_seen_at || '')}" data-last-seen="${escapeHtml(user.last_seen_at || '')}">${user.last_seen || 'همین الان'}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5 text-[10px] text-slate/60">
                                <span class="inline-flex items-center gap-1 rounded-lg bg-slate/5 px-2 py-1">
                                    <span class="material-icons text-[12px] text-success/60">person</span>
                                    <span class="truncate max-w-[140px]">${escapeHtml(truncateText(user.user, 20))}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-lg bg-slate/5 px-2 py-1">
                                    <span class="material-icons text-[12px] text-success/60">public</span>
                                    <span>${escapeHtml(truncateText([user.country, user.city && user.city !== '-' ? user.city : null].filter(Boolean).join(' / '), 22))}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-lg bg-slate/5 px-2 py-1">
                                    <span class="material-icons text-[12px] text-success/60">devices</span>
                                    <span>${escapeHtml(truncateText([user.device_brand, user.device].filter(Boolean).join(' / '), 20))}</span>
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-lg bg-slate/5 px-2 py-1">
                                    <span class="material-icons text-[12px] text-success/60">language</span>
                                    <span>${escapeHtml(truncateText([user.browser, user.platform].filter(Boolean).join(' / '), 18))}</span>
                                </span>
                            </div>

                            <div class="pointer-events-none absolute inset-x-2 top-full z-30 mt-3 opacity-0 translate-y-1 transition-all duration-150 group-hover:opacity-100 group-hover:translate-y-0">
                                <div class="rounded-2xl border border-cyan-400/20 bg-[linear-gradient(135deg,rgba(8,145,178,0.10),rgba(59,130,246,0.08),rgba(245,158,11,0.08))] dark:bg-[linear-gradient(135deg,rgba(8,145,178,0.16),rgba(30,41,59,0.96),rgba(124,58,237,0.16))] backdrop-blur-md shadow-2xl p-3 text-[10px] leading-5 text-slate/80">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div><span class="font-bold text-cyan-700 dark:text-cyan-300">User-Agent:</span> ${escapeHtml(user.user_agent || '-')}</div>
                                        <div><span class="font-bold text-sky-700 dark:text-sky-300">IP:</span> <span class="admin-ltr">${escapeHtml(user.ip || '-')}</span></div>
                                        <div><span class="font-bold text-amber-700 dark:text-amber-300">سورس:</span> ${escapeHtml(sessionSourceSummary(user))}</div>
                                        <div><span class="font-bold text-violet-700 dark:text-violet-300">آخرین فعالیت:</span> ${escapeHtml(user.last_seen || 'همین الان')}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </button>
            `).join('');

            startLiveTimers();
            attachLiveUserClickHandlers();
        });
    };


    const showUserActivity = (userId, userName) => {
        const dialog = document.getElementById('user-activity-modal');
        const content = document.getElementById('activity-modal-content');
        const title = document.getElementById('activity-modal-title');

        if (!dialog || !content) return;
        if (!userId || Number(userId) <= 0) {
            title.textContent = `فعالیت کاربر: ${userName}`;
            content.innerHTML = '<p class="text-center py-10 opacity-50">برای این آیتم شناسه کاربر معتبری ثبت نشده است.</p>';
            dialog.showModal();
            return;
        }

        title.textContent = `فعالیت کاربر: ${userName}`;
        content.innerHTML = '<p class="text-center py-10 opacity-50">در حال دریافت اطلاعات...</p>';
        dialog.showModal();

        const url = root.dataset.userActivityUrl.replace(':id', userId);
        const activityUrl = new URL(url, window.location.origin);
        activityUrl.searchParams.set('per_page', '200');

        fetchJson(activityUrl.toString()).then(data => {
            if (!data.events || !data.events.length) {
                content.innerHTML = '<p class="text-center py-10 opacity-50">هیچ فعالیتی ثبت نشده است.</p>';
                return;
            }

            content.innerHTML = `
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs text-slate/60">${number(data.total)} رویداد | صفحه ${data.page} از ${data.last_page}</p>
                    <a href="${url}" target="_blank" class="text-xs text-success">مشاهده همه</a>
                </div>
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
                                            <p class="font-bold text-slate truncate text-sm">${escapeHtml(e.title || decodeUrl(e.path) || '-')}</p>
                                        </div>
                                        <div class="flex items-center gap-2 text-[10px] text-slate/50 admin-ltr">
                                            <a href="${e.path}" target="_blank" class="material-icons text-xs hover:text-success">open_in_new</a>
                                            <span>${displayPath(e.path || '-')}</span>
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
                                    <p class="text-[10px] font-medium">${escapeHtml(e.country)}${e.city ? ' / ' + escapeHtml(e.city) : ''}</p>
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

                            ${e.duration ? `<div class="text-[10px] text-slate/40">مدت: ${Math.floor(e.duration / 60)}:${String(e.duration % 60).padStart(2, '0')} | اسکرول: ${e.scroll || 0}%</div>` : ''}

                            ${e.search_engine || Object.keys(e.utm || {}).length || e.goal || e.funnel ? `
                                <div class="flex flex-wrap gap-2 pt-2">
                                    ${e.funnel ? `
                                        <div class="px-2 py-1 rounded bg-purple-500/5 border border-purple-500/10 text-purple-500 text-[9px] flex items-center gap-1">
                                            <span class="font-bold">قیف:</span> ${escapeHtml(e.funnel.key)} > ${escapeHtml(e.funnel.step_name)}
                                        </div>
                                    ` : ''}
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


    const formatDuration = (seconds) => {
        if (seconds < 60) return `${seconds}s`;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        if (mins < 60) return `${mins}m ${secs}s`;
        const hours = Math.floor(mins / 60);
        const remainingMins = mins % 60;
        return `${hours}h ${remainingMins}m`;
    };

    const compactDuration = (seconds) => {
        const total = Math.max(0, Number(seconds) || 0);
        const hours = Math.floor(total / 3600);
        const mins = Math.floor((total % 3600) / 60);
        const secs = total % 60;
        if (hours > 0) return `${hours}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    };

    const truncateText = (value, max = 42) => {
        const str = String(value || '').trim();
        if (!str) return '-';
        return str.length > max ? `${str.slice(0, max - 1)}…` : str;
    };

    const sourceTone = (user) => {
        if (user.search_engine) return 'bg-blue-500/10 text-blue-600 dark:text-blue-300 border-blue-500/20';
        if (user.utm_source) return 'bg-amber-500/10 text-amber-600 dark:text-amber-300 border-amber-500/20';
        if (user.referrer_type && user.referrer_type !== 'direct') return 'bg-violet-500/10 text-violet-600 dark:text-violet-300 border-violet-500/20';
        return 'bg-slate/10 text-slate/70 border-slate/10';
    };

    const sessionSourceSummary = (user) => {
        if (user.search_engine) return user.search_engine;
        if (user.utm_source) return [user.utm_source, user.utm_medium, user.utm_campaign].filter(Boolean).join(' / ');
        if (user.referrer_host) return user.referrer_host;
        if (user.referrer_type && user.referrer_type !== 'direct') return user.referrer_type;
        return 'مستقیم';
    };

    const buildLiveUserTooltip = (user) => {
        return [
            `کاربر: ${escapeHtml(user.user || 'مهمان')}`,
            `مسیر: ${escapeHtml(user.path || '-')}`,
            `مرورگر: ${escapeHtml(user.browser || '-')}`,
            `سیستم‌عامل: ${escapeHtml(user.platform || '-')}`,
            `دستگاه: ${escapeHtml([user.device_brand, user.device].filter(Boolean).join(' / ') || '-')}`,
            `مکان: ${escapeHtml([user.country, user.city && user.city !== '-' ? user.city : null].filter(Boolean).join(' / ') || '-')}`,
            `سورس: ${escapeHtml(sessionSourceSummary(user))}`,
            `User-Agent: ${escapeHtml(user.user_agent || '-')}`,
        ].join('&#10;');
    };

    const aggregateLiveRows = (rows, getKey, getLabel, limit = 5) => {
        const map = new Map();
        (rows || []).forEach((row) => {
            const key = String(getKey(row) || '').trim();
            if (!key) return;
            const current = map.get(key) || { key, label: getLabel(row), count: 0 };
            current.count += 1;
            map.set(key, current);
        });

        return Array.from(map.values())
            .sort((a, b) => b.count - a.count)
            .slice(0, limit);
    };

    const renderLiveMiniBars = (container, rows, tone = 'success') => {
        if (!container) return;
        if (!rows || !rows.length) {
            container.innerHTML = '<p class="text-center py-8 opacity-50 text-xs">داده‌ای موجود نیست.</p>';
            return;
        }

        const max = Math.max(...rows.map((row) => Number(row.count) || 0), 1);
        const toneClass = tone === 'amber'
            ? 'bg-amber-500'
            : tone === 'info'
                ? 'bg-sky-500'
                : tone === 'violet'
                    ? 'bg-violet-500'
                    : 'bg-emerald-500';

        container.innerHTML = rows.map((row) => `
            <div class="space-y-1.5">
                <div class="flex items-center justify-between gap-3 text-[11px]">
                    <span class="truncate text-slate/75" title="${escapeHtml(row.label || '-')}">${renderLabel(row)}</span>
                    <span class="font-bold text-slate shrink-0">${number(row.count)}</span>
                </div>
                <div class="h-1.5 rounded-full bg-slate/5 overflow-hidden">
                    <div class="${toneClass} h-full rounded-full" style="width:${Math.max(8, Math.round((row.count / max) * 100))}%"></div>
                </div>
            </div>
        `).join('');
    };

    const startLiveTimers = () => {
        document.querySelectorAll('.live-timer').forEach(el => {
            const firstSeen = el.dataset.firstSeen;
            const lastSeen = el.dataset.lastSeen;
            if (!firstSeen) return;
            if (el._timerInterval) {
                clearInterval(el._timerInterval);
                el._timerInterval = null;
            }
            
            const start = new Date(firstSeen).getTime();
            const end = lastSeen ? new Date(lastSeen).getTime() : Date.now();
            let duration = Math.max(0, Math.floor((end - start) / 1000));
            el.textContent = compactDuration(duration);
            
            const update = () => {
                duration++;
                el.textContent = compactDuration(duration);
            };
            
            el._timerInterval = setInterval(update, 1000);
        });
    };

    const stopLiveTimers = () => {
        document.querySelectorAll('.live-timer').forEach(el => {
            if (el._timerInterval) {
                clearInterval(el._timerInterval);
                el._timerInterval = null;
            }
        });
    };

    const showLiveUserDetail = async (sessionId) => {
        const dialog = document.getElementById('live-user-detail-modal');
        const content = document.getElementById('live-user-detail-content');
        const title = document.getElementById('live-user-detail-title');
        
        if (!dialog || !content) return;
        
        title.textContent = 'جزئیات جلسه کاربر';
        content.innerHTML = '<p class="text-center py-10 opacity-50">در حال دریافت اطلاعات...</p>';
        dialog.showModal();
        
        const url = new URL(root.dataset.userJourneyUrl, window.location.origin);
        url.searchParams.set('session_id', sessionId);
        
        try {
            const response = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const result = await response.json();
            const data = result.data || {};
            
            if (!data.events || !data.events.length) {
                content.innerHTML = '<p class="text-center py-10 opacity-50">هیچ فعالیتی ثبت نشده است.</p>';
                return;
            }
            
            const session = data.session;
            const events = data.events;
            const pathSequence = data.path_sequence || [];
            const summary = data.summary || {};
            const relatedSessions = data.related_sessions || [];
            const sessionSource = session?.landing_source
                || events.find((e) => e.search_engine)?.search_engine
                || events.find((e) => e.utm?.source)?.utm?.source
                || events.find((e) => e.referrer_host)?.referrer_host
                || (session?.referrer_type && session.referrer_type !== 'direct' ? session.referrer_type : 'مستقیم');

            content.innerHTML = `
                <div class="p-5 space-y-5">
                    <div class="rounded-[20px] border border-slate/10 bg-slate/5 p-4">
                        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-start 2xl:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success/10 px-3 py-1 text-[10px] font-bold text-success">
                                        <span class="material-icons text-[12px]">sensors</span>
                                        سشن زنده
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate/10 px-3 py-1 text-[10px] font-bold text-slate/70">
                                        ${escapeHtml(sessionSource || 'مستقیم')}
                                    </span>
                                </div>
                                <h3 class="mt-3 text-lg font-black text-slate">${escapeHtml(events[0]?.title || decodeUrl(session?.entry_path || events[0]?.path || 'جلسه فعال'))}</h3>
                                <p class="admin-ltr text-left text-xs text-slate/50 mt-1 break-all">${displayPath(session?.entry_path || events[0]?.path || '-')}</p>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-6 gap-3 min-w-0 w-full 2xl:w-auto 2xl:min-w-[760px]">
                                <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-3">
                                    <p class="text-[10px] text-slate/50">آی‌دی جلسه</p>
                                    <p class="font-mono text-xs admin-ltr break-all mt-1" title="${escapeHtml(session?.session_id || sessionId)}">${escapeHtml(session?.session_id || sessionId)}</p>
                                </div>
                                <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-3">
                                    <p class="text-[10px] text-slate/50">شروع</p>
                                    <p class="font-medium text-xs mt-1">${session?.started_at ? new Date(session.started_at).toLocaleString('fa-IR') : '-'}</p>
                                </div>
                                <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-3">
                                    <p class="text-[10px] text-slate/50">مدت</p>
                                    <p class="font-medium text-xs text-success mt-1">${session?.duration_seconds ? compactDuration(session.duration_seconds) : compactDuration(summary.pageviews ? 0 : 0)}</p>
                                </div>
                                <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-3">
                                    <p class="text-[10px] text-slate/50">Pageviews</p>
                                    <p class="font-medium text-xs mt-1">${number(summary.pageviews || session?.pageviews_count || pathSequence.length || 0)}</p>
                                </div>
                                <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-3">
                                    <p class="text-[10px] text-slate/50">Goals / Errors</p>
                                    <p class="font-medium text-xs mt-1">${number(summary.goals || 0)} / ${number(summary.errors || 0)}</p>
                                </div>
                                <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-3">
                                    <p class="text-[10px] text-slate/50">آخرین رویداد</p>
                                    <p class="font-medium text-xs mt-1">${escapeHtml(summary.last_event_ago || '-')}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 xl:grid-cols-2 2xl:grid-cols-[minmax(280px,0.9fr)_minmax(0,1.55fr)_minmax(280px,0.9fr)] items-start">
                        <div class="space-y-4 min-w-0">
                            <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-4 space-y-3">
                                <h4 class="font-bold text-slate flex items-center gap-2">
                                    <span class="material-icons text-info text-sm">hub</span>
                                    پروفایل جلسه
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div><span class="text-slate/40 block mb-1">منبع ورود</span><span class="font-medium">${escapeHtml(sessionSource || 'مستقیم')}</span></div>
                                    <div><span class="text-slate/40 block mb-1">نرخ پرش</span><span class="font-medium">${session?.is_bounce ? 'بله' : 'خیر'}</span></div>
                                    <div><span class="text-slate/40 block mb-1">نوع دستگاه</span><span class="font-medium">${escapeHtml(session?.device_type || events[0]?.device || '-')}</span></div>
                                    <div><span class="text-slate/40 block mb-1">مرورگر</span><span class="font-medium">${escapeHtml(session?.browser || events[0]?.browser || '-')}</span></div>
                                    <div><span class="text-slate/40 block mb-1">پلتفرم</span><span class="font-medium">${escapeHtml(session?.platform || events[0]?.platform || '-')}</span></div>
                                    <div><span class="text-slate/40 block mb-1">کشور</span><span class="font-medium">${escapeHtml(events[0]?.country || session?.country_code || '-')}</span></div>
                                </div>
                                <div class="rounded-xl bg-slate/5 border border-slate/10 p-3 text-[11px] text-slate/65 space-y-2">
                                    <div><span class="text-slate/40">ورود:</span> <span class="admin-ltr">${escapeHtml(session?.entry_path || '-')}</span></div>
                                    <div><span class="text-slate/40">خروج:</span> <span class="admin-ltr">${escapeHtml(session?.exit_path || '-')}</span></div>
                                    <div><span class="text-slate/40">User-Agent:</span> <span class="admin-ltr break-all">${escapeHtml(events[0]?.user_agent || '-')}</span></div>
                                </div>
                            </div>

                            ${pathSequence.length > 0 ? `
                                <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-4">
                                    <h4 class="font-bold text-slate mb-3 flex items-center gap-2">
                                        <span class="material-icons text-info text-sm">call_split</span>
                                        مسیر صفحات
                                    </h4>
                                    <div class="space-y-2 max-h-[280px] overflow-y-auto custom-scrollbar pr-1">
                                        ${pathSequence.map((p, idx) => `
                                            <div class="flex items-center gap-2 rounded-xl bg-slate/5 px-3 py-2 text-[11px]">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-success/10 text-success font-bold">${idx + 1}</span>
                                                <span class="truncate admin-ltr">${displayPath(p)}</span>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>

                        <div class="space-y-4 min-w-0 xl:col-span-1 2xl:col-span-1">
                            <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950">
                                <div class="flex items-center justify-between p-4 border-b border-slate/10">
                                    <h4 class="font-bold text-slate flex items-center gap-2">
                                        <span class="material-icons text-success text-sm">timeline</span>
                                        Timeline رویدادها
                                    </h4>
                                    <span class="text-xs text-slate/50">${events.length} رویداد</span>
                                </div>
                                <div class="space-y-2 max-h-[58vh] overflow-y-auto custom-scrollbar p-4">
                                    ${events.map((e) => `
                                        <div class="p-3 rounded-xl border border-slate/10 bg-slate/5 space-y-3 relative overflow-hidden ${e.type === 'goal' ? 'border-r-2 border-success' : (e.type === 'error' ? 'border-r-2 border-danger' : '')}">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold ${e.type === 'goal' ? 'bg-success/10 text-success' : (e.type === 'error' ? 'bg-danger/10 text-danger' : 'bg-slate/10 text-slate/70')}">
                                                            ${e.type === 'goal' ? 'تحقق هدف' : (e.type === 'error' ? 'خطا' : 'بازدید')}
                                                        </span>
                                                        <p class="font-bold text-slate truncate text-sm">${escapeHtml(e.title || decodeUrl(e.path) || '-')}</p>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-[10px] text-slate/50 admin-ltr">
                                                        <a href="${e.url || e.path}" target="_blank" class="material-icons text-xs hover:text-success">open_in_new</a>
                                                        <span>${displayPath(e.path || '-')}</span>
                                                    </div>
                                                </div>
                                                <div class="text-left shrink-0">
                                                    <p class="text-[10px] font-bold text-slate/70">${e.time_ago}</p>
                                                    <p class="text-[9px] text-slate/40">${e.time}</p>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-[10px]">
                                                <div><span class="text-slate/40">مرورگر</span><div class="mt-1 font-medium">${escapeHtml(e.browser || '-')}</div></div>
                                                <div><span class="text-slate/40">پلتفرم</span><div class="mt-1 font-medium">${escapeHtml(e.platform || '-')}</div></div>
                                                <div><span class="text-slate/40">موقعیت</span><div class="mt-1 font-medium">${escapeHtml([e.country, e.city].filter(Boolean).join(' / ') || '-')}</div></div>
                                                <div><span class="text-slate/40">اسکرول / مدت</span><div class="mt-1 font-medium">${e.scroll ? `${e.scroll}%` : '-'}${e.duration ? ` / ${compactDuration(e.duration)}` : ''}</div></div>
                                            </div>

                                            ${(e.goal || e.funnel || e.search_engine || Object.keys(e.utm || {}).length || e.referrer_host) ? `
                                                <div class="flex flex-wrap gap-2">
                                                    ${e.goal ? `<span class="px-2 py-1 rounded-lg bg-success/10 border border-success/20 text-success text-[9px] font-bold flex items-center gap-1"><span class="material-icons text-[10px]">emoji_events</span>${escapeHtml(e.goal)}</span>` : ''}
                                                    ${e.funnel ? `<span class="px-2 py-1 rounded-lg bg-purple-500/10 border border-purple-500/20 text-purple-600 dark:text-purple-300 text-[9px] flex items-center gap-1"><span class="font-bold">قیف</span>${escapeHtml(e.funnel.key)} / ${escapeHtml(e.funnel.step_name)}</span>` : ''}
                                                    ${e.search_engine ? `<span class="px-2 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-300 text-[9px]">${escapeHtml(e.search_engine)}</span>` : ''}
                                                    ${e.referrer_host ? `<span class="px-2 py-1 rounded-lg bg-violet-500/10 border border-violet-500/20 text-violet-600 dark:text-violet-300 text-[9px]">${escapeHtml(e.referrer_host)}</span>` : ''}
                                                    ${Object.entries(e.utm || {}).map(([k, v]) => `<span class="px-2 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 text-[9px]">${escapeHtml(k)}: ${escapeHtml(v)}</span>`).join('')}
                                                </div>
                                            ` : ''}

                                            ${e.user_agent ? `<div class="rounded-lg bg-slate/5 border border-slate/10 p-2 text-[10px] text-slate/55 admin-ltr text-left break-all">${escapeHtml(e.user_agent)}</div>` : ''}
                                            ${e.error_message ? `<div class="rounded-lg bg-danger/5 border border-danger/20 p-2 text-[10px] text-danger/80 admin-ltr text-left break-all">${escapeHtml(e.error_message)}${e.error_source ? ` | ${escapeHtml(e.error_source)}` : ''}${e.error_line ? `:${escapeHtml(String(e.error_line))}` : ''}</div>` : ''}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 min-w-0 xl:col-span-2 2xl:col-span-1">
                            <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-4">
                                <h4 class="font-bold text-slate mb-3 flex items-center gap-2">
                                    <span class="material-icons text-warning text-sm">insights</span>
                                    مشخصات فنی و Attribution
                                </h4>
                                <div class="space-y-3 text-xs">
                                    <div class="rounded-xl bg-slate/5 border border-slate/10 p-3">
                                        <p class="text-slate/40 mb-2">UTM / Search / Referrer</p>
                                        <div class="flex flex-wrap gap-2">
                                            ${events.flatMap((e) => {
                                                const badges = [];
                                                if (e.search_engine) badges.push(`<span class="px-2 py-1 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-300 text-[10px]">${escapeHtml(e.search_engine)}</span>`);
                                                if (e.referrer_host) badges.push(`<span class="px-2 py-1 rounded-lg bg-violet-500/10 border border-violet-500/20 text-violet-600 dark:text-violet-300 text-[10px]">${escapeHtml(e.referrer_host)}</span>`);
                                                Object.entries(e.utm || {}).forEach(([k, v]) => {
                                                    badges.push(`<span class="px-2 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px]">${escapeHtml(k)}: ${escapeHtml(v)}</span>`);
                                                });
                                                return badges;
                                            }).slice(0, 12).join('') || '<span class="text-slate/45">موردی ثبت نشده است.</span>'}
                                        </div>
                                    </div>
                                    <div class="rounded-xl bg-slate/5 border border-slate/10 p-3">
                                        <p class="text-slate/40 mb-2">شناسه‌ها و شبکه</p>
                                        <div class="space-y-2 text-[11px]">
                                            <div><span class="text-slate/40">Session:</span> <span class="admin-ltr break-all">${escapeHtml(session?.session_id || sessionId)}</span></div>
                                            <div><span class="text-slate/40">IP:</span> <span class="admin-ltr break-all">${escapeHtml(events[0]?.ip || '-')}</span></div>
                                            <div><span class="text-slate/40">Visitor/User:</span> <span class="break-all">${escapeHtml(events[0]?.user_id || session?.user_id || 'مهمان')}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-slate/10 bg-white dark:bg-slate-950 p-4">
                                <h4 class="font-bold text-slate mb-3 flex items-center gap-2">
                                    <span class="material-icons text-success text-sm">history</span>
                                    سایر نشست‌های این کاربر
                                </h4>
                                ${relatedSessions.length ? `
                                    <div class="space-y-2 max-h-[320px] overflow-y-auto custom-scrollbar">
                                        ${relatedSessions.map((item) => `
                                            <div class="rounded-xl border border-slate/10 bg-slate/5 p-3 text-xs space-y-2">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="font-bold admin-ltr truncate">${escapeHtml(item.session_id)}</p>
                                                        <p class="text-[10px] text-slate/50 mt-1">${item.started_at ? new Date(item.started_at).toLocaleString('fa-IR') : '-'}</p>
                                                    </div>
                                                    <div class="text-left">
                                                        <p class="text-success font-bold">${compactDuration(item.duration_seconds || 0)}</p>
                                                        <p class="text-[10px] text-slate/50">${number(item.pageviews_count || 0)} صفحه</p>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2 text-[10px] text-slate/65">
                                                    <div>ورود: ${escapeHtml(item.entry_path || '-')}</div>
                                                    <div>خروج: ${escapeHtml(item.exit_path || '-')}</div>
                                                    <div>سورس: ${escapeHtml(item.landing_source || item.referrer_type || '-')}</div>
                                                    <div>${escapeHtml([item.browser, item.platform].filter(Boolean).join(' / ') || '-')}</div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : '<p class="text-xs text-slate/50">نشست دیگری برای این کاربر پیدا نشد.</p>'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } catch (err) {
            content.innerHTML = '<p class="text-center py-10 text-danger">خطا در دریافت اطلاعات</p>';
        }
    };

    const attachLiveUserClickHandlers = () => {
        document.querySelectorAll('.live-user-item').forEach(item => {
            item.onclick = () => {
                const sessionId = item.dataset.sessionId;
                if (sessionId) showLiveUserDetail(sessionId);
            };
        });
    };

    const loadAnalyticsTab = () => {
        fetchJson(sectionUrl('reports')).then((data) => {
            if (!data) return;
            const countries = iconizeRows(data.countries, 'countries');
            const deviceBrands = iconizeRows(data.device_brands, 'device-brands');
            const browsers = iconizeRows(data.browsers, 'browsers');
            const platforms = iconizeRows(data.platforms, 'platforms');
            const searchEngines = iconizeRows(data.search_engines, 'search-engines');

            renderBars(root.querySelector('[data-list="countries"]'), countries);
            renderBars(root.querySelector('[data-list="referrers"]'), iconizeRows(data.referrers, 'referrers'));
            renderBars(root.querySelector('[data-list="device-types"]'), data.device_types);
            renderBars(root.querySelector('[data-list="device-brands"]'), deviceBrands);
            renderBars(root.querySelector('[data-list="browsers"]'), browsers);
            renderBars(root.querySelector('[data-list="platforms"]'), platforms);
            renderBars(root.querySelector('[data-list="utm-sources"]'), iconizeRows(data.utm_sources, 'utm'));
            renderBars(root.querySelector('[data-list="utm-mediums"]'), iconizeRows(data.utm_mediums, 'utm'));
            renderBars(root.querySelector('[data-list="utm-campaigns"]'), iconizeRows(data.utm_campaigns, 'utm'));
            renderBars(root.querySelector('[data-list="search-engines"]'), searchEngines);

            const activitiesData = Array.isArray(data.activities) ? data.activities : (Array.isArray(data.activity) ? data.activity : []);
            renderBars(root.querySelector('[data-list="activities"]'), activitiesData.map(a => {
                return a.key === 'error' ? { ...a, isNegative: true } : a;
            }));

            const reportsMapContainer = root.querySelector('[data-map="reports"]');
            if (reportsMapContainer) {
                renderWorldMap(reportsMapContainer, data.map, 'reports', true);
            }

            const countryCount = root.querySelector('[data-reports-country-count]');
            if (countryCount) countryCount.textContent = countries.length || 0;
            const browserCount = root.querySelector('[data-reports-browser-count]');
            if (browserCount) browserCount.textContent = browsers.length || 0;
            const sourceCount = root.querySelector('[data-reports-source-count]');
            if (sourceCount) sourceCount.textContent = iconizeRows(data.referrers, 'referrers').length || 0;
        });
        loadContent();
    };

    const loaders = {
        'tab-overview': loadOverview,
        'tab-live': loadLive,
        'tab-analytics': loadAnalyticsTab,
        'tab-search': loadSearch,
        'tab-goals': loadGoals,
        'tab-management': () => {},
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

        root.querySelector('#analytics-filter-reset')?.addEventListener('click', () => {
            window.setTimeout(() => {
                if (fromInput) {
                    fromInput.value = startPd.format('YYYY/MM/DD');
                    fromInput.dataset.gregorian = toGregorianFromShamsi(fromInput.value);
                }
                if (toInput) {
                    toInput.value = todayPd.format('YYYY/MM/DD');
                    toInput.dataset.gregorian = toGregorianFromShamsi(toInput.value);
                }
                filterForm.requestSubmit();
            }, 0);
        });
    }

    syncFilterVisibility(currentTab);
    loadOverview();
    window.addEventListener('analytics-tab-switched', (event) => {
        const tab = event.detail.tab;
        currentTab = tab;
        syncFilterVisibility(tab);
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
                    b.className = 'flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-black text-white shadow-sm ring-1 ring-emerald-500/20 transition-all dark:bg-emerald-400 dark:text-slate-950 dark:ring-emerald-300/30';
                } else {
                    b.className = 'flex items-center gap-2 rounded-lg px-4 py-2 text-xs font-black text-slate-600 transition-all hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white';
                }
            });

            const activeTab = document.querySelector('.analytics-tab-content:not(.hidden)')?.id;
            if (activeTab === 'tab-live') {
                loaders[activeTab]?.();
            }
        };
    });

    let livePollingInterval = null;
    const startLivePolling = () => {
        if (livePollingInterval) return;
        livePollingInterval = setInterval(() => {
            const liveTab = document.getElementById('tab-live');
            const isVisible = liveTab && !liveTab.classList.contains('hidden') && document.visibilityState === 'visible';
            if (isVisible) {
                loadLive();
            }
        }, liveRefreshMs);
    };

    const stopLivePolling = () => {
        if (livePollingInterval) {
            clearInterval(livePollingInterval);
            livePollingInterval = null;
        }
        stopLiveTimers();
    };

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            startLivePolling();
        } else {
            stopLivePolling();
        }
    });

    window.addEventListener('analytics-tab-switched', (event) => {
        if (event.detail.tab === 'tab-live') {
            startLivePolling();
            loadLive();
        } else {
            stopLivePolling();
        }
    });

    startLivePolling();
})();
