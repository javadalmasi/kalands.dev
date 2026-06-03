document.addEventListener('DOMContentLoaded', () => {
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

    const div = (a, b) => ~~(a / b);

    const jalCal = (jy) => {
        const breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
        let gy = jy + 621;
        let leapJ = -14;
        let jp = breaks[0];
        let jump = 0;

        for (let i = 1; i < breaks.length; i++) {
            const jm = breaks[i];
            jump = jm - jp;
            if (jy < jm) break;
            leapJ += div(jump, 33) * 8 + div(jump % 33, 4);
            jp = jm;
        }

        let n = jy - jp;
        leapJ += div(n, 33) * 8 + div((n % 33) + 3, 4);
        if (jump % 33 === 4 && jump - n === 4) leapJ += 1;

        const leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;
        const march = 20 + leapJ - leapG;

        if (jump - n < 6) n = n - jump + div(jump + 4, 33) * 33;
        let leap = (((n + 1) % 33) - 1) % 4;
        if (leap === -1) leap = 4;

        return { leap, gy, march };
    };

    const g2d = (gy, gm, gd) => (
        div((gy + div(gm - 8, 6) + 100100) * 1461, 4)
        + div(153 * ((gm + 9) % 12) + 2, 5)
        + gd - 34840408
        - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4)
        + 752
    );

    const d2g = (jdn) => {
        let j = 4 * jdn + 139361631;
        j += div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
        const i = div((j % 1461), 4) * 5 + 308;
        const gd = div((i % 153), 5) + 1;
        const gm = (div(i, 153) % 12) + 1;
        const gy = div(j, 1461) - 100100 + div(8 - gm, 6);
        return { gy, gm, gd };
    };

    const j2d = (jy, jm, jd) => {
        const r = jalCal(jy);
        return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1;
    };

    const d2j = (jdn) => {
        const gy = d2g(jdn).gy;
        let jy = gy - 621;
        const r = jalCal(jy);
        const jdn1f = g2d(gy, 3, r.march);
        let k = jdn - jdn1f;

        if (k >= 0) {
            if (k <= 185) {
                return { jy, jm: 1 + div(k, 31), jd: (k % 31) + 1 };
            }
            k -= 186;
        } else {
            jy -= 1;
            k += 179;
            if (r.leap === 1) k += 1;
        }

        return { jy, jm: 7 + div(k, 30), jd: (k % 30) + 1 };
    };

    const toGregorian = (jy, jm, jd) => d2g(j2d(jy, jm, jd));
    const toJalaali = (date) => d2j(g2d(date.getFullYear(), date.getMonth() + 1, date.getDate()));

    class PersianDate {
        constructor(value = new Date()) {
            if (Array.isArray(value)) {
                this.jy = Number(value[0]);
                this.jm = Number(value[1] || 1);
                this.jd = Number(value[2] || 1);
                this.gDate = this.#toGregorianDate();
                return;
            }

            if (typeof value === 'string' && value.match(/^\d{4}\/\d{1,2}\/\d{1,2}$/)) {
                const [jy, jm, jd] = value.split('/').map(Number);
                this.jy = jy;
                this.jm = jm;
                this.jd = jd;
                this.gDate = this.#toGregorianDate();
                return;
            }

            this.gDate = value instanceof Date ? new Date(value) : new Date(value);
            const j = toJalaali(this.gDate);
            this.jy = j.jy;
            this.jm = j.jm;
            this.jd = j.jd;
        }

        #toGregorianDate() {
            const g = toGregorian(this.jy, this.jm, this.jd);
            return new Date(g.gy, g.gm - 1, g.gd);
        }

        year() { return this.jy; }
        month() { return this.jm; }
        date() { return this.jd; }
        toDate() { return new Date(this.gDate); }

        day() {
            return ((this.gDate.getDay() + 1) % 7) + 1;
        }

        daysInMonth(year = this.jy, month = this.jm) {
            if (month <= 6) return 31;
            if (month <= 11) return 30;
            return jalCal(year).leap === 0 ? 30 : 29;
        }

        subtract(unit, amount) {
            if (unit === 'days' || unit === 'day') {
                const date = new Date(this.gDate);
                date.setDate(date.getDate() - amount);
                return new PersianDate(date);
            }

            if (unit === 'months' || unit === 'month') {
                let month = this.jm - amount;
                let year = this.jy;
                while (month < 1) {
                    month += 12;
                    year -= 1;
                }
                const day = Math.min(this.jd, this.daysInMonth(year, month));
                return new PersianDate([year, month, day]);
            }

            return new PersianDate(this.gDate);
        }

        format(pattern) {
            if (pattern !== 'YYYY/MM/DD') return `${this.jy}/${this.jm}/${this.jd}`;
            return `${this.jy}/${String(this.jm).padStart(2, '0')}/${String(this.jd).padStart(2, '0')}`;
        }
    }

    const initDatepicker = (wrapper) => {
        const input = wrapper.querySelector('input');
        if (!input) return;
        wrapper.style.position = 'relative';

        let pickerEl = null;
        let pickerVisible = false;

        const closePicker = () => {
            if (pickerEl) {
                pickerEl.remove();
                pickerEl = null;
            }
            pickerVisible = false;
        };

        const buildPicker = () => {
            closePicker();
            const now = input.value ? new PersianDate(input.value.replace(/\//g, '/')) : new PersianDate();
            let viewYear = now.year();
            let viewMonth = now.month();
            let pickerMode = 'days';
            let yearPageStart = viewYear - 6;

            pickerEl = document.createElement('div');
            pickerEl.className = 'shamsi-datepicker-popup';
            pickerEl.style.cssText = `
                position: absolute; z-index: 99999; background: #fff; border: 1px solid #d1d5db;
                border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); padding: 0;
                width: 620px; direction: rtl; font-family: inherit; top: 100%; left: 0; margin-top: 8px;
                display: flex; overflow: hidden;
            `;

            if (document.querySelector('.dark')) {
                pickerEl.style.background = '#0f172a';
                pickerEl.style.borderColor = 'rgba(255,255,255,0.1)';
                pickerEl.style.color = '#fff';
            }

            const render = () => {
                const monthNames = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
                const weekDays = ['ش','ی','د','س','چ','پ','ج'];
                const todayPd = new PersianDate();
                const isDark = Boolean(document.querySelector('.dark'));
                const subtleBg = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(15,23,42,0.05)';
                const hoverBg = isDark ? 'rgba(16,185,129,0.16)' : 'rgba(16,185,129,0.1)';
                const borderColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(15,23,42,0.08)';
                const activeBg = 'var(--color-success, #10b981)';
                const activeColor = '#fff';

                const getMonthHtml = (year, month) => {
                    let mYear = year;
                    let mMonth = month;
                    if (mMonth < 1) { mMonth = 12; mYear--; }
                    if (mMonth > 12) { mMonth = 1; mYear++; }

                    const daysInMonth = new PersianDate([mYear, mMonth, 1]).daysInMonth();
                    const firstDay = new PersianDate([mYear, mMonth, 1]).day();
                    const firstDayIdx = firstDay === 0 ? 6 : firstDay - 1;

                    let html = `
                        <div style="flex:1; padding: 16px;">
                            <div style="display:flex; align-items:center; justify-content:center; gap:6px; margin-bottom:15px;">
                                <button type="button" data-picker-month="${mMonth}" data-picker-year="${mYear}" style="background:${subtleBg}; color:var(--color-success, #10b981); border:1px solid ${borderColor}; border-radius:8px; cursor:pointer; padding:5px 9px; font-weight:bold; font-size:13px;">${monthNames[mMonth - 1]}</button>
                                <button type="button" data-picker-year-list="${mYear}" style="background:${subtleBg}; color:var(--color-success, #10b981); border:1px solid ${borderColor}; border-radius:8px; cursor:pointer; padding:5px 9px; font-weight:bold; font-size:13px;">${mYear}</button>
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(7,1fr); text-align:center; font-size:11px; font-weight:bold; margin-bottom:8px; opacity:0.6;">
                                ${weekDays.map(d => `<span style="padding:4px 0;">${d}</span>`).join('')}
                            </div>
                            <div style="display:grid; grid-template-columns:repeat(7,1fr); text-align:center; gap:2px;">
                    `;

                    for (let i = 0; i < firstDayIdx; i++) {
                        html += '<span></span>';
                    }

                    for (let d = 1; d <= daysInMonth; d++) {
                        const dateStr = `${mYear}/${String(mMonth).padStart(2,'0')}/${String(d).padStart(2,'0')}`;
                        const selected = input.value === dateStr;
                        const isToday = mYear === todayPd.year() && mMonth === todayPd.month() && d === todayPd.date();
                        const bg = selected ? activeBg : (isToday ? hoverBg : 'transparent');
                        const color = selected ? activeColor : (isToday ? 'var(--color-success, #10b981)' : 'inherit');
                        html += `<button type="button" data-date="${dateStr}" style="background:${bg}; color:${color}; cursor:pointer; border:none; border-radius:6px; padding:8px 0; font-size:12px; transition:all 0.2s; font-weight:${isToday || selected ? 'bold' : 'normal'};" onmouseover="this.style.background='${selected ? bg : hoverBg}'" onmouseout="this.style.background='${bg}'">${d}</button>`;
                    }

                    html += '</div></div>';
                    return html;
                };

                const getMonthPickerHtml = () => `
                    <div style="padding:16px; width:100%;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                            <button type="button" data-year-step="-1" style="background:${subtleBg}; border:1px solid ${borderColor}; color:inherit; cursor:pointer; width:34px; height:34px; border-radius:8px;">›</button>
                            <button type="button" data-picker-year-list="${viewYear}" style="background:${subtleBg}; color:var(--color-success, #10b981); border:1px solid ${borderColor}; border-radius:8px; cursor:pointer; padding:7px 14px; font-weight:bold;">${viewYear}</button>
                            <button type="button" data-year-step="1" style="background:${subtleBg}; border:1px solid ${borderColor}; color:inherit; cursor:pointer; width:34px; height:34px; border-radius:8px;">‹</button>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
                            ${monthNames.map((name, index) => {
                                const month = index + 1;
                                const selected = month === viewMonth;
                                return `<button type="button" data-select-month="${month}" style="background:${selected ? activeBg : subtleBg}; color:${selected ? activeColor : 'inherit'}; border:1px solid ${selected ? 'transparent' : borderColor}; cursor:pointer; border-radius:9px; padding:12px 8px; font-size:12px; font-weight:bold;">${name}</button>`;
                            }).join('')}
                        </div>
                    </div>
                `;

                const getYearPickerHtml = () => `
                    <div style="padding:16px; width:100%;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                            <button type="button" data-year-page="-12" style="background:${subtleBg}; border:1px solid ${borderColor}; color:inherit; cursor:pointer; width:34px; height:34px; border-radius:8px;">›</button>
                            <div style="font-weight:bold; color:var(--color-success, #10b981);">${yearPageStart} - ${yearPageStart + 11}</div>
                            <button type="button" data-year-page="12" style="background:${subtleBg}; border:1px solid ${borderColor}; color:inherit; cursor:pointer; width:34px; height:34px; border-radius:8px;">‹</button>
                        </div>
                        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:8px;">
                            ${Array.from({ length: 12 }, (_, index) => yearPageStart + index).map((year) => {
                                const selected = year === viewYear;
                                const isCurrent = year === todayPd.year();
                                return `<button type="button" data-select-year="${year}" style="background:${selected ? activeBg : (isCurrent ? hoverBg : subtleBg)}; color:${selected ? activeColor : (isCurrent ? 'var(--color-success, #10b981)' : 'inherit')}; border:1px solid ${selected ? 'transparent' : borderColor}; cursor:pointer; border-radius:9px; padding:12px 8px; font-size:12px; font-weight:bold;">${year}</button>`;
                            }).join('')}
                        </div>
                    </div>
                `;

                const presets = [
                    { label: 'دیروز', days: 1 },
                    { label: '۲ روز اخیر', days: 2 },
                    { label: 'هفته اخیر', days: 7 },
                    { label: 'ماه اخیر', months: 1 },
                    { label: '۳ ماه اخیر', months: 3 },
                    { label: '۶ ماه اخیر', months: 6 }
                ];

                let html = `
                    <div style="width:140px; background:rgba(0,0,0,0.02); border-left:1px solid rgba(0,0,0,0.05); display:flex; flex-direction:column; padding:8px; gap:4px;">
                        ${presets.map(p => `<button type="button" data-preset='${JSON.stringify(p)}' style="text-align:right; background:transparent; border:none; padding:8px 12px; font-size:12px; cursor:pointer; border-radius:6px; transition:all 0.2s; color:inherit;" onmouseover="this.style.background='rgba(16, 185, 129, 0.1)';this.style.color='var(--color-success, #10b981)'" onmouseout="this.style.background='transparent';this.style.color='inherit'">${p.label}</button>`).join('')}
                        </div>
                        <div style="flex:1; display:flex; flex-direction:column; position:relative;">
                            ${pickerMode === 'days' ? `
                                <div style="display:flex; align-items:center; justify-content:space-between; position:absolute; top:12px; left:12px; right:12px; z-index:10; pointer-events:none;">
                                    <button type="button" data-nav="next" style="background:${subtleBg}; border:1px solid ${borderColor}; cursor:pointer; font-size:18px; width:30px; height:30px; border-radius:8px; color:inherit; pointer-events:auto; display:flex; align-items:center; justify-content:center;">›</button>
                                    <button type="button" data-nav="prev" style="background:${subtleBg}; border:1px solid ${borderColor}; cursor:pointer; font-size:18px; width:30px; height:30px; border-radius:8px; color:inherit; pointer-events:auto; display:flex; align-items:center; justify-content:center;">‹</button>
                                </div>
                                <div style="display:flex;">
                                    ${getMonthHtml(viewYear, viewMonth - 1)}
                                    ${getMonthHtml(viewYear, viewMonth)}
                                </div>
                            ` : pickerMode === 'months' ? getMonthPickerHtml() : getYearPickerHtml()}
                        </div>
                `;

                pickerEl.innerHTML = html;

                pickerEl.querySelector('[data-nav="prev"]')?.addEventListener('click', (e) => { e.stopPropagation(); viewMonth--; if (viewMonth < 1) { viewMonth = 12; viewYear--; } render(); });
                pickerEl.querySelector('[data-nav="next"]')?.addEventListener('click', (e) => { e.stopPropagation(); viewMonth++; if (viewMonth > 12) { viewMonth = 1; viewYear++; } render(); });

                pickerEl.querySelectorAll('[data-picker-month]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        viewYear = Number(btn.dataset.pickerYear);
                        viewMonth = Number(btn.dataset.pickerMonth);
                        pickerMode = 'months';
                        render();
                    };
                });

                pickerEl.querySelectorAll('[data-picker-year-list]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        viewYear = Number(btn.dataset.pickerYearList);
                        yearPageStart = viewYear - 6;
                        pickerMode = 'years';
                        render();
                    };
                });

                pickerEl.querySelectorAll('[data-year-step]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        viewYear += Number(btn.dataset.yearStep);
                        render();
                    };
                });

                pickerEl.querySelectorAll('[data-select-month]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        viewMonth = Number(btn.dataset.selectMonth);
                        pickerMode = 'days';
                        render();
                    };
                });

                pickerEl.querySelectorAll('[data-year-page]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        yearPageStart += Number(btn.dataset.yearPage);
                        render();
                    };
                });

                pickerEl.querySelectorAll('[data-select-year]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        viewYear = Number(btn.dataset.selectYear);
                        pickerMode = 'months';
                        render();
                    };
                });

                pickerEl.querySelectorAll('[data-date]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        input.value = btn.dataset.date;
                        closePicker();
                        input.form.requestSubmit();
                    };
                });

                pickerEl.querySelectorAll('[data-preset]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        const p = JSON.parse(btn.dataset.preset);
                        const pd = new PersianDate();
                        let targetPd;
                        if (p.days) {
                            targetPd = pd.subtract('days', p.days);
                        } else if (p.months) {
                            targetPd = pd.subtract('months', p.months);
                        }
                        input.value = targetPd.format('YYYY/MM/DD');
                        closePicker();
                        input.form.requestSubmit();
                    };
                });
            };

            render();
            wrapper.appendChild(pickerEl);
            pickerVisible = true;
        };

        input.addEventListener('click', (e) => {
            e.stopPropagation();
            if (pickerVisible) { closePicker(); return; }
            buildPicker();
        });

        document.addEventListener('click', (e) => {
            if (pickerVisible && !wrapper.contains(e.target)) {
                closePicker();
            }
        }, true);

        if (!input.value) {
            const pd = new PersianDate();
            input.value = pd.format('YYYY/MM/DD');
        }
    };

    document.querySelectorAll('.shamsi-datepicker-popup').forEach(wrapper => {
        initDatepicker(wrapper);
    });
});
