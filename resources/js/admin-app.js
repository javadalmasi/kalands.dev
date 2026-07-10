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

    // ── Bulk Bar ──────────────────────────────────────────────────────────
    document.querySelectorAll('[data-bulk-bar]').forEach((bar) => {
        const barId      = bar.id;
        const selectAll  = bar.querySelector('[data-select-all]');
        const numEl      = bar.querySelector('[data-bulk-num]');
        const clearBtn   = bar.querySelector('[data-bulk-clear]');
        const confirmMsg = bar.dataset.confirm || null;

        // Use HTMLFormElement.elements — finds ALL controls associated with this form,
        // including those with form="barId" attribute outside the form tag.
        const getItems = () => {
            const form = document.getElementById(barId);
            if (!form) return [];
            return [...form.elements].filter(
                (el) => el.type === 'checkbox' && el.hasAttribute('data-bulk-item')
            );
        };
        const getSelected = () => getItems().filter((c) => c.checked);

        const update = () => {
            const items  = getItems();
            const count  = getSelected().length;
            const total  = items.length;

            bar.classList.toggle('is-visible', count > 0);
            document.body.style.paddingBottom = count > 0 ? '56px' : '';

            if (numEl) numEl.textContent = new Intl.NumberFormat('fa-IR').format(count);

            if (selectAll) {
                selectAll.checked       = total > 0 && count === total;
                selectAll.indeterminate = count > 0 && count < total;
            }
        };

        // Select-all toggle
        if (selectAll) {
            selectAll.addEventListener('change', () => {
                getItems().forEach((el) => { el.checked = selectAll.checked; });
                update();
            });
        }

        // Item checkboxes — listen on the document
        document.addEventListener('change', (e) => {
            if (e.target.type === 'checkbox' && e.target.hasAttribute('data-bulk-item')) {
                const f = e.target.getAttribute('form');
                if (f === barId || (!f && e.target.closest(`#${barId}`))) update();
            }
        });

        // Clear
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                getItems().forEach((el) => { el.checked = false; });
                if (selectAll) { selectAll.checked = false; selectAll.indeterminate = false; }
                update();
            });
        }

        // Submit guard
        bar.addEventListener('submit', (e) => {
            if (!getSelected().length) { e.preventDefault(); return; }
            if (confirmMsg && !window.confirm(confirmMsg)) e.preventDefault();
        });
    });
});
