import './bootstrap';
import '../css/admin-app.css';
import 'material-icons/iconfont/material-icons.css';
import 'material-symbols/outlined.css';
import { initFlowbite } from 'flowbite';
import { initAllHelpOffcanvas } from './admin/help-offcanvas';

document.addEventListener('DOMContentLoaded', () => {
    window.initFlowbite = initFlowbite;
    initFlowbite();
    initAllHelpOffcanvas();

    const getByIdInScope = (scope, id) => {
        if (!id) return null;
        const escaped = window.CSS && typeof window.CSS.escape === 'function' ? window.CSS.escape(id) : id.replace(/"/g, '\\"');
        return scope.querySelector(`#${escaped}`) || document.getElementById(id);
    };

    const activateTab = (tabsWrap, targetId) => {
        if (!tabsWrap || !targetId) return;
        const buttons = Array.from(tabsWrap.querySelectorAll('[data-tab-target]'));
        if (!buttons.length) return;

        const scope = tabsWrap.parentElement || document;
        const targetEl = getByIdInScope(scope, targetId);
        if (!targetEl) return;

        const contentEls = buttons
            .map((btn) => getByIdInScope(scope, btn.getAttribute('data-tab-target')))
            .filter(Boolean);

        buttons.forEach((btn) => {
            const active = btn.getAttribute('data-tab-target') === targetId;
            btn.classList.toggle('font-bold', active);
            btn.classList.toggle('border-b-2', active);
            btn.classList.toggle('border-primary', active);
            btn.classList.toggle('text-primary', active);
            btn.classList.toggle('font-medium', !active);
            btn.classList.toggle('text-slate-500', !active);
            btn.classList.toggle('text-slate', !active);
        });

        contentEls.forEach((el) => el.classList.add('hidden'));
        targetEl.classList.remove('hidden');

        window.dispatchEvent(new CustomEvent('admin-tab-switched', {
            detail: { tab: targetId, tabsWrap }
        }));
    };

    document.addEventListener('click', (e) => {
        const tabBtn = e.target.closest('[data-tab-target]');
        if (!tabBtn) return;
        const tabsWrap = tabBtn.parentElement;
        if (!tabsWrap || !tabsWrap.querySelector('[data-tab-target]')) return;

        e.preventDefault();
        const targetId = tabBtn.getAttribute('data-tab-target');
        activateTab(tabsWrap, targetId);

        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('tab', targetId);
        window.history.replaceState({}, '', newUrl.toString());
    });

    const activeTabParam = new URLSearchParams(window.location.search).get('tab');
    document.querySelectorAll('[id$="-tabs"]').forEach((tabsWrap) => {
        const buttons = Array.from(tabsWrap.querySelectorAll('[data-tab-target]'));
        if (!buttons.length) return;
        const fallbackBtn = buttons.find((btn) => btn.classList.contains('border-primary')) || buttons[0];
        const initialId = buttons.some((btn) => btn.getAttribute('data-tab-target') === activeTabParam)
            ? activeTabParam
            : fallbackBtn.getAttribute('data-tab-target');
        activateTab(tabsWrap, initialId);
    });

    // ── Bulk Bar (floating bottom action bar) ──────────────────────────────
    document.querySelectorAll('[data-bulk-bar]').forEach((bar) => {
        const barId = bar.dataset.bulkBar;
        const selectAllEl = bar.querySelector(`[data-select-all="${barId}"]`);
        const countEl = bar.querySelector(`[data-bulk-count="${barId}"]`);
        const clearBtn = bar.querySelector(`[data-bulk-clear="${barId}"]`);
        const confirmMsg = bar.dataset.confirm || null;

        const getItems = () => [...document.querySelectorAll(`[data-bulk-item][form="${barId}"]`)];
        const getSelected = () => getItems().filter((c) => c.checked);

        const update = () => {
            const items = getItems();
            const selected = getSelected();
            const count = selected.length;

            bar.classList.toggle('is-visible', count > 0);

            if (countEl) {
                const label = countEl.dataset.label || countEl.textContent.replace(/^\d+\s*/, '');
                countEl.textContent = `${new Intl.NumberFormat('fa-IR').format(count)} ${label}`;
            }

            if (selectAllEl) {
                selectAllEl.checked = count > 0 && count === items.length;
                selectAllEl.indeterminate = count > 0 && count < items.length;
            }
        };

        // Listen for checkbox changes anywhere in document
        document.addEventListener('change', (e) => {
            const cb = e.target;
            if (cb.dataset.bulkItem !== undefined && cb.getAttribute('form') === barId) {
                update();
                return;
            }
            if (selectAllEl && cb === selectAllEl) {
                getItems().forEach((item) => { item.checked = selectAllEl.checked; });
                update();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                getItems().forEach((item) => { item.checked = false; });
                if (selectAllEl) selectAllEl.checked = false;
                update();
            });
        }

        bar.addEventListener('submit', (e) => {
            if (getSelected().length === 0) {
                e.preventDefault();
                return;
            }
            if (confirmMsg && !window.confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });
});
