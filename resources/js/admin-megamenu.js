import Sortable from 'sortablejs';

window.currentGroupIndex = null;
window.currentCategoryIndex = null;
window.currentSubsectionIndex = null;
window.activeBox = 1;
window.searchQueries = { 1: '', 2: '', 3: '' };
window.globalSearch = '';
window.globalFilter = 'all'; // all, inactive, desktop_only, mobile_only
window.brokenLinkResults = {};
window.autoSaveTimeout = null;

// --- Custom Modal & Helper Functions ---

window.adminAlert = function(message) {
    const modal = document.getElementById('megamenu-alert-modal');
    if (!modal) return;
    document.getElementById('megamenu-alert-message').innerText = message;
    modal.showModal();
};

window.adminConfirm = function(message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('megamenu-confirm-modal');
        if (!modal) return resolve(false);
        document.getElementById('megamenu-confirm-message').innerText = message;

        const submitBtn = document.getElementById('megamenu-confirm-submit');
        const cancelBtn = document.getElementById('megamenu-confirm-cancel');

        const cleanup = () => {
            submitBtn.removeEventListener('click', onConfirm);
            cancelBtn.removeEventListener('click', onCancel);
            modal.removeEventListener('close', onClose);
            window.removeEventListener('keydown', onKeyDown);
        };

        const onConfirm = () => { cleanup(); modal.close(); resolve(true); };
        const onCancel = () => { cleanup(); modal.close(); resolve(false); };
        const onClose = () => { cleanup(); resolve(false); };
        const onKeyDown = (e) => {
            if (e.key === 'Enter') { e.preventDefault(); onConfirm(); }
            if (e.key === 'Escape') { onCancel(); }
        };

        submitBtn.addEventListener('click', onConfirm, { once: true });
        cancelBtn.addEventListener('click', onCancel, { once: true });
        modal.addEventListener('close', onClose, { once: true });
        window.addEventListener('keydown', onKeyDown);

        modal.showModal();
    });
};

window.adminPrompt = function(options) {
    return new Promise((resolve) => {
        const isGroup = options.type === 'group';
        const modalId = isGroup ? 'group-form-modal' : 'item-form-modal';
        const modal = document.getElementById(modalId);
        if (!modal) return resolve(null);

        if (isGroup) {
            document.getElementById('group-modal-title').innerText = options.title || 'مدیریت گروه';
            document.getElementById('group-title-input').value = options.value || '';
        } else {
            document.getElementById('item-modal-title').innerText = options.title || 'مدیریت آیتم';
            document.getElementById('item-title-input').value = options.titleValue || '';
            const hrefContainer = document.getElementById('item-href-container');
            if (options.hasHref) {
                hrefContainer.classList.remove('hidden');
                document.getElementById('item-href-input').value = options.hrefValue || '';
            } else {
                hrefContainer.classList.add('hidden');
            }
        }

        const submitBtn = document.getElementById(isGroup ? 'group-modal-submit' : 'item-modal-submit');
        const cleanup = () => {
            submitBtn.removeEventListener('click', onSubmit);
            modal.removeEventListener('close', onClose);
        };
        const onSubmit = () => {
            let result = null;
            if (isGroup) {
                result = document.getElementById('group-title-input').value.trim();
            } else {
                result = {
                    title: document.getElementById('item-title-input').value.trim(),
                    href: options.hasHref ? document.getElementById('item-href-input').value.trim() : null
                };
            }
            if (isGroup ? result : result.title) {
                cleanup(); modal.close(); resolve(result);
            } else {
                window.adminAlert('لطفا تمامی موارد را تکمیل کنید');
            }
        };
        const onClose = () => { cleanup(); resolve(null); };
        submitBtn.addEventListener('click', onSubmit);
        modal.addEventListener('close', onClose, { once: true });
        modal.showModal();
    });
};

// --- Selection Helpers ---

window.clearAllSelections = function() {
    const recursiveClear = (node) => {
        delete node._selected;
        if (node.categories) node.categories.forEach(recursiveClear);
        if (node.sub_sections) node.sub_sections.forEach(recursiveClear);
        if (node.items) node.items.forEach(recursiveClear);
    };
    if (window.menuData) window.menuData.forEach(recursiveClear);
};

// --- Group Management ---

window.openGroupSelector = function() {
    window.renderModalGroups();
    document.getElementById('group-selector-modal').showModal();
};

window.filterModalGroups = function(val) {
    window.renderModalGroups(val);
};

window.renderModalGroups = function(filter = '') {
    const list = document.getElementById('modal-groups-list');
    if (!list) return;
    list.innerHTML = '';
    window.menuData.forEach((group, idx) => {
        if (filter && !group.title.toLowerCase().includes(filter.toLowerCase())) return;
        const btn = document.createElement('button');
        btn.className = `flex flex-col items-center gap-3 p-4 rounded-xl border transition-all ${window.currentGroupIndex === idx ? 'border-primary bg-primary/5 ring-2 ring-primary/20' : 'border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-white/5 hover:border-primary/50'}`;
        btn.onclick = () => window.selectGroup(idx);
        btn.innerHTML = `
            <div class="w-12 h-12 rounded-full bg-white dark:bg-slate-800 flex items-center justify-center shadow-sm text-slate-400">
                <span class="material-icons">folder</span>
            </div>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-200">${group.title}</span>
        `;
        list.appendChild(btn);
    });
};

window.selectGroup = function(idx) {
    window.currentGroupIndex = idx;
    window.currentCategoryIndex = null;
    window.currentSubsectionIndex = null;
    window.activeBox = 1;
    window.clearAllSelections();
    document.getElementById('current-group-label').innerText = window.menuData[idx].title;
    document.getElementById('group-actions').classList.remove('hidden');
    const groupSelectorModal = document.getElementById('group-selector-modal');
    if (groupSelectorModal?.open) {
        groupSelectorModal.close();
    }
    window.renderUI();
};

window.addGroup = async function() {
    const title = await window.adminPrompt({ type: 'group', title: 'افزودن گروه اصلی' });
    if (title) {
        const tabBtn = document.querySelector('[data-tab-target="tab-editor"]');
        if (tabBtn) tabBtn.click();
        window.menuData.push({ title: title, show_desktop: true, show_mobile: true, categories: [] });
        window.selectGroup(window.menuData.length - 1);
        window.saveDraft();
    }
};

window.editCurrentGroup = async function() {
    if (window.currentGroupIndex === null) return;
    const title = await window.adminPrompt({ type: 'group', title: 'ویرایش گروه اصلی', value: window.menuData[window.currentGroupIndex].title });
    if (title) {
        window.menuData[window.currentGroupIndex].title = title;
        document.getElementById('current-group-label').innerText = title;
        window.saveDraft();
    }
};

window.removeCurrentGroup = async function() {
    if (window.currentGroupIndex === null) return;
    if (await window.adminConfirm('آیا از حذف کامل این گروه و تمامی زیرمجموعه‌های آن مطمئن هستید؟')) {
        window.menuData.splice(window.currentGroupIndex, 1);
        window.currentGroupIndex = null;
        window.currentCategoryIndex = null;
        window.currentSubsectionIndex = null;
        window.clearAllSelections();
        document.getElementById('current-group-label').innerText = 'انتخاب گروه اصلی برای ویرایش...';
        document.getElementById('group-actions').classList.add('hidden');
        window.renderUI();
        window.saveDraft();
    }
};

// --- Selection & Global Operations ---

window.isGlobalMode = function() {
    return !!(window.globalSearch || window.globalFilter !== 'all');
};

window.handleGlobalSearch = function(val) {
    window.globalSearch = val.trim().toLowerCase();
    window.renderUI();
};

window.handleGlobalFilter = function(val) {
    window.globalFilter = val;
    window.renderUI();
};

window.toggleLevelSelect = function(item, type, event) {
    item._selected = !item._selected;
    if (event && event.shiftKey) {
        const state = item._selected;
        const setRecursive = (node) => {
            node._selected = state;
            if (node.categories) node.categories.forEach(setRecursive);
            if (node.sub_sections) node.sub_sections.forEach(setRecursive);
            if (node.items) node.items.forEach(setRecursive);
        };
        setRecursive(item);
    }
    window.renderUI();
};

window.toggleBoxSelectAll = function(boxNum) {
    let items = [];
    const groups = window.currentGroupIndex !== null ? [window.menuData[window.currentGroupIndex]] : (window.isGlobalMode() ? window.menuData : []);

    if (boxNum === 1) {
        groups.forEach(g => items.push(...g.categories));
    } else if (boxNum === 2) {
        if (window.isGlobalMode()) {
            groups.forEach(g => g.categories.forEach(c => items.push(...c.sub_sections)));
        } else if (window.currentCategoryIndex !== null) {
            items = window.menuData[window.currentGroupIndex].categories[window.currentCategoryIndex].sub_sections;
        }
    } else if (boxNum === 3) {
        if (window.isGlobalMode()) {
            groups.forEach(g => g.categories.forEach(c => c.sub_sections.forEach(s => items.push(...s.items))));
        } else if (window.currentSubsectionIndex !== null) {
            items = window.menuData[window.currentGroupIndex].categories[window.currentCategoryIndex].sub_sections[window.currentSubsectionIndex].items;
        }
    }
    const allSelected = items.length > 0 && items.every(i => i._selected);
    items.forEach(i => i._selected = !allSelected);
    window.renderUI();
};

window.applyBoxBulkAction = async function(boxNum, action) {
    if (!action) return;
    let itemsToProcess = [];
    const collectSelected = (list) => list.forEach(i => { if (i._selected) itemsToProcess.push(i); });

    const groups = window.currentGroupIndex !== null ? [window.menuData[window.currentGroupIndex]] : (window.isGlobalMode() ? window.menuData : []);

    if (boxNum === 1) groups.forEach(g => collectSelected(g.categories));
    else if (boxNum === 2) {
        if (window.isGlobalMode()) groups.forEach(g => g.categories.forEach(c => collectSelected(c.sub_sections)));
        else if (window.currentCategoryIndex !== null) collectSelected(window.menuData[window.currentGroupIndex].categories[window.currentCategoryIndex].sub_sections);
    } else if (boxNum === 3) {
        if (window.isGlobalMode()) groups.forEach(g => g.categories.forEach(c => c.sub_sections.forEach(s => collectSelected(s.items))));
        else if (window.currentSubsectionIndex !== null) collectSelected(window.menuData[window.currentGroupIndex].categories[window.currentCategoryIndex].sub_sections[window.currentSubsectionIndex].items);
    }

    if (itemsToProcess.length === 0) return window.adminAlert('ابتدا مواردی را انتخاب کنید');
    if (action === 'delete') {
        if (await window.adminConfirm(`آیا از حذف ${itemsToProcess.length} مورد انتخاب شده مطمئن هستید؟`)) {
            const del = (parent, k) => parent[k] = parent[k].filter(i => !i._selected);
            if (boxNum === 1) {
                groups.forEach(g => del(g, 'categories'));
                window.currentCategoryIndex = null; window.currentSubsectionIndex = null;
            }
            else if (boxNum === 2) {
                groups.forEach(g => g.categories.forEach(c => del(c, 'sub_sections')));
                window.currentSubsectionIndex = null;
            }
            else if (boxNum === 3) {
                groups.forEach(g => g.categories.forEach(c => c.sub_sections.forEach(s => del(s, 'items'))));
            }
            window.renderUI(); window.saveDraft();
        }
    } else {
        itemsToProcess.forEach(item => {
            if (action === 'show_desktop') item.show_desktop = true;
            if (action === 'hide_desktop') item.show_desktop = false;
            if (action === 'show_mobile') item.show_mobile = true;
            if (action === 'hide_mobile') item.show_mobile = false;
            if (action === 'deactivate') { item.show_desktop = false; item.show_mobile = false; }
            if (action === 'activate') { item.show_desktop = true; item.show_mobile = true; }
            item._selected = false;
        });
        window.renderUI(); window.saveDraft();
    }
};

// --- Rendering Logic ---

window.isItemMatch = function(item) {
    if (!item) return false;
    if (window.globalFilter === 'inactive' && (item.show_desktop || item.show_mobile)) return false;
    if (window.globalFilter === 'desktop_only' && !item.show_desktop) return false;
    if (window.globalFilter === 'mobile_only' && !item.show_mobile) return false;
    if (window.globalSearch && window.globalSearch !== '*' && window.globalSearch !== 'any') {
        const title = (item.title || '').toLowerCase();
        const href = (item.href || '').toLowerCase();
        if (!title.includes(window.globalSearch) && !href.includes(window.globalSearch)) return false;
    }
    return true;
};

window.countInactiveChildren = function(node, type) {
    let count = 0;
    if (type === 'category') {
        node.sub_sections.forEach(sub => {
            if (!sub.show_desktop && !sub.show_mobile) count++;
            sub.items.forEach(item => { if (!item.show_desktop && !item.show_mobile) count++; });
        });
    } else if (type === 'subsection') {
        node.items.forEach(item => { if (!item.show_desktop && !item.show_mobile) count++; });
    }
    return count;
};

window.renderUI = function() {
    const rootEl = document.getElementById('megamenu-builder-root');
    if (!rootEl) return;
    if (window.currentGroupIndex === null && !window.isGlobalMode()) return;

    const group = window.currentGroupIndex !== null ? window.menuData[window.currentGroupIndex] : null;

    const boxHeader = (title, icon, onAdd = null) => `
        <div class="p-4 border-b border-slate-200 dark:border-white/10 flex items-center justify-between bg-white dark:bg-slate-900 rounded-t-2xl">
            <div class="flex items-center gap-2">
                <span class="material-icons text-primary !text-lg">${icon}</span>
                <span class="font-black text-sm">${title}</span>
            </div>
            ${onAdd ? `<button onclick="${onAdd}" class="p-1.5 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors shadow-sm"><span class="material-icons !text-sm">add</span></button>` : ''}
        </div>
    `;

    const boxFilters = (boxNum) => `
        <div class="p-3 border-b border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-white/2">
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 p-1 rounded-xl border border-slate-100 dark:border-white/10 shadow-sm h-10">
                <div class="relative flex-1">
                    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 !text-base">search</span>
                    <input type="text" value="${window.searchQueries[boxNum]}" oninput="window.handleBoxSearch(${boxNum}, this.value)" class="admin-input !border-0 !bg-transparent w-full pr-9 !text-[11px] !h-8" placeholder="جستجو...">
                </div>
                <div class="w-px h-5 bg-slate-100 dark:bg-white/10 mx-0.5"></div>
                <button onclick="window.toggleBoxSelectAll(${boxNum})" class="p-1.5 text-slate-400 hover:text-primary transition-colors flex items-center" title="انتخاب همه">
                    <span class="material-icons !text-lg">done_all</span>
                </button>
                <div class="w-px h-5 bg-slate-100 dark:bg-white/10 mx-0.5"></div>
                <select onchange="window.applyBoxBulkAction(${boxNum}, this.value); this.value='';" class="admin-input !border-0 !bg-transparent !w-[110px] !py-0 !text-[10px] !h-8 cursor-pointer font-bold text-slate-500">
                    <option value="">عملیات کلی</option>
                    <option value="show_desktop">نمایش دسکتاپ</option>
                    <option value="hide_desktop">عدم نمایش دسکتاپ</option>
                    <option value="show_mobile">نمایش موبایل</option>
                    <option value="hide_mobile">عدم نمایش موبایل</option>
                    <option value="deactivate">غیرفعال‌سازی</option>
                    <option value="activate">فعال‌سازی</option>
                    <option value="delete">حذف نهایی</option>
                </select>
            </div>
        </div>
    `;

    rootEl.innerHTML = `
        <div class="flex gap-4 w-full h-[700px] overflow-hidden">
            <div class="editor-box transition-all duration-300 flex flex-col bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/10 ${window.activeBox === 1 ? 'w-1/2' : 'w-1/4'}" onclick="window.setActiveBox(1)">
                ${boxHeader('دسته‌بندی‌ها (L1)', 'category', !window.isGlobalMode() && window.currentGroupIndex !== null ? `window.addCategory(${window.currentGroupIndex})` : null)}
                ${boxFilters(1)}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2 categories-list" data-gidx="${window.currentGroupIndex}">
                    ${window.isGlobalMode() && window.currentGroupIndex === null ? window.renderCategoriesGlobal() : window.renderCategories(group?.categories || [])}
                </div>
            </div>
            <div class="editor-box transition-all duration-300 flex flex-col bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/10 ${window.activeBox === 2 ? 'w-1/2' : 'w-1/4'}" onclick="window.setActiveBox(2)">
                ${boxHeader('زیربخش‌ها (L2)', 'account_tree', !window.isGlobalMode() && window.currentCategoryIndex !== null ? `window.addSubsection(${window.currentGroupIndex}, ${window.currentCategoryIndex})` : null)}
                ${boxFilters(2)}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-2 subsections-list" data-gidx="${window.currentGroupIndex}" data-cidx="${window.currentCategoryIndex}">
                    ${window.isGlobalMode() ? window.renderSubsectionsGlobal() : (window.currentCategoryIndex !== null ? window.renderSubsections(group.categories[window.currentCategoryIndex].sub_sections || []) : '<div class="h-full flex items-center justify-center opacity-20 flex-col text-center p-4"><span class="material-icons !text-4xl mb-2">touch_app</span><p class="text-xs font-bold">یک دسته انتخاب کنید</p></div>')}
                </div>
            </div>
            <div class="editor-box transition-all duration-300 flex flex-col bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-200 dark:border-white/10 ${window.activeBox === 3 ? 'w-1/2' : 'w-1/4'}" onclick="window.setActiveBox(3)">
                ${boxHeader('آیتم‌های نهایی (L3)', 'link', !window.isGlobalMode() && window.currentSubsectionIndex !== null ? `window.addItemJS(${window.currentGroupIndex}, ${window.currentCategoryIndex}, ${window.currentSubsectionIndex})` : null)}
                ${boxFilters(3)}
                <div class="flex-1 overflow-y-auto custom-scrollbar p-2 space-y-1 items-list" data-gidx="${window.currentGroupIndex}" data-cidx="${window.currentCategoryIndex}" data-sidx="${window.currentSubsectionIndex}">
                    ${window.isGlobalMode() ? window.renderItemsGlobal() : (window.currentSubsectionIndex !== null ? window.renderItems(group.categories[window.currentCategoryIndex].sub_sections[window.currentSubsectionIndex].items || []) : '<div class="h-full flex items-center justify-center opacity-20 flex-col text-center p-4"><span class="material-icons !text-4xl mb-2">touch_app</span><p class="text-xs font-bold">یک زیربخش انتخاب کنید</p></div>')}
                </div>
            </div>
        </div>
    `;
    window.initSortable();
};

window.setActiveBox = function(boxNum) {
    if (window.activeBox === boxNum) return;
    window.activeBox = boxNum;
    window.renderUI();
};

window.handleBoxSearch = function(boxNum, val) {
    window.searchQueries[boxNum] = val.toLowerCase();
    window.renderUI();
};

window.toggleLevelActive = function(item, status) {
    if (status === 'active') {
        item.show_desktop = true;
        item.show_mobile = true;
    } else {
        item.show_desktop = false;
        item.show_mobile = false;
    }
    window.renderUI();
    window.saveDraft();
};

window.getItemHTML = function(item, type, options = {}) {
    const isInactive = !item.show_desktop && !item.show_mobile;
    const isActive = options.isActive;
    const inactiveCount = (type === 'L1' || type === 'L2') ? window.countInactiveChildren(item, type === 'L1' ? 'category' : 'subsection') : 0;
    const linkStatus = type === 'L3' ? window.brokenLinkResults[item.href] : null;
    const safeRef = options.itemRef.replace(/"/g, '&quot;');

    return `
        <div class="group flex items-center gap-2 p-2.5 rounded-xl border transition-all cursor-pointer ${isActive ? 'bg-primary/5 border-primary shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-100 dark:border-white/5 hover:border-primary/30'} ${isInactive ? 'opacity-60 grayscale' : ''}" onclick="${options.onclick}">
            <input type="checkbox" class="admin-checkbox !w-4 !h-4 shrink-0" ${item._selected ? 'checked' : ''} onclick="event.stopPropagation(); window.toggleLevelSelect(${safeRef}, '${type}', event)">
            ${!window.isGlobalMode() ? `<span class="material-icons text-slate-300 !text-sm cursor-move shrink-0">drag_handle</span>` : ''}
            <div class="flex-1 min-w-0">
                ${options.breadcrumb ? `<p class="text-[8px] text-slate-500 font-bold mb-0.5 truncate uppercase tracking-tighter">${options.breadcrumb}</p>` : ''}
                <p class="text-xs font-bold truncate ${isActive ? 'text-primary' : 'text-slate-800 dark:text-slate-100'} ${isInactive ? 'text-red-600' : ''}">${item.title}</p>
                ${inactiveCount > 0 ? `<p class="text-[9px] text-red-500 font-bold mt-1 flex items-center gap-1"><span class="w-1 h-1 bg-red-500 rounded-full animate-pulse"></span>${inactiveCount} غیرفعال</p>` : ''}
                ${linkStatus ? `<p class="text-[9px] font-mono text-slate-500 truncate flex items-center gap-1 mt-1"><span class="w-1.5 h-1.5 rounded-full ${linkStatus.ok ? 'bg-emerald-500 shadow-sm' : 'bg-red-500 animate-pulse'}"></span>${item.href}</p>` : ''}
            </div>
            <div class="flex items-center gap-1.5 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                <div class="flex items-center gap-0.5 bg-slate-100 dark:bg-white/10 p-0.5 rounded-lg border border-slate-200 dark:border-white/10">
                    <button onclick="event.stopPropagation(); window.toggleLevelVisibilityDirect(${safeRef}, 'show_desktop')" class="w-6 h-6 flex items-center justify-center rounded-md transition-all ${item.show_desktop ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:bg-slate-200 dark:hover:bg-white/20'}" title="نمایش در دسکتاپ">
                        <span class="material-icons !text-xs">desktop_windows</span>
                    </button>
                    <button onclick="event.stopPropagation(); window.toggleLevelVisibilityDirect(${safeRef}, 'show_mobile')" class="w-6 h-6 flex items-center justify-center rounded-md transition-all ${item.show_mobile ? 'bg-primary text-white shadow-sm' : 'text-slate-500 hover:bg-slate-200 dark:hover:bg-white/20'}" title="نمایش در موبایل">
                        <span class="material-icons !text-xs">smartphone</span>
                    </button>
                </div>
                <label class="relative inline-flex h-5 w-9 items-center cursor-pointer shrink-0" onclick="event.stopPropagation()">
                    <input type="checkbox" class="peer sr-only" ${!isInactive ? 'checked' : ''} onchange="window.toggleLevelActive(${safeRef}, this.checked ? 'active' : 'inactive')">
                    <span class="absolute inset-0 rounded-full bg-slate-400 dark:bg-slate-600 transition-colors peer-checked:bg-emerald-500"></span>
                    <span class="absolute right-0.5 h-4 w-4 rounded-full bg-white border border-slate-200 transition-all peer-checked:right-4.5"></span>
                </label>
                <button onclick="event.stopPropagation(); window.editLevelItemDirect(${safeRef}, '${type}')" class="w-7 h-7 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:text-primary hover:bg-primary/10 rounded-lg transition-all" title="ویرایش">
                    <span class="material-icons !text-xs">edit</span>
                </button>
                <button onclick="event.stopPropagation(); window.removeLevelItemDirect(${safeRef}, '${type}')" class="w-7 h-7 flex items-center justify-center text-red-500 hover:text-white hover:bg-red-500 rounded-lg transition-all shadow-sm" title="حذف">
                    <span class="material-icons !text-xs">delete</span>
                </button>
            </div>
        </div>
    `;
};

window.renderCategories = function(categories) {
    return categories.map((cat, idx) => {
        if ((window.searchQueries[1] && !cat.title.toLowerCase().includes(window.searchQueries[1])) || !window.isItemMatch(cat)) return '';
        return window.getItemHTML(cat, 'L1', {
            isActive: window.currentCategoryIndex === idx, onclick: `window.selectCategory(${idx})`,
            itemRef: `window.menuData[${window.currentGroupIndex}].categories[${idx}]`
        });
    }).join('');
};

window.renderCategoriesGlobal = function() {
    let html = '';
    window.menuData.forEach((group, gIdx) => {
        group.categories.forEach((cat, cIdx) => {
            if ((window.searchQueries[1] && !cat.title.toLowerCase().includes(window.searchQueries[1])) || !window.isItemMatch(cat)) return;
            html += window.getItemHTML(cat, 'L1', {
                breadcrumb: group.title, onclick: `window.selectGroup(${gIdx}); window.selectCategory(${cIdx})`,
                itemRef: `window.menuData[${gIdx}].categories[${cIdx}]`
            });
        });
    });
    return html || '<p class="text-center py-10 opacity-30 text-xs">موردی یافت نشد</p>';
};

window.renderSubsections = function(subsections) {
    return subsections.map((sub, idx) => {
        if ((window.searchQueries[2] && !sub.title.toLowerCase().includes(window.searchQueries[2])) || !window.isItemMatch(sub)) return '';
        return window.getItemHTML(sub, 'L2', {
            isActive: window.currentSubsectionIndex === idx, onclick: `window.selectSubsection(${idx})`,
            itemRef: `window.menuData[${window.currentGroupIndex}].categories[${window.currentCategoryIndex}].sub_sections[${idx}]`
        });
    }).join('');
};

window.renderSubsectionsGlobal = function() {
    let html = '';
    const groups = window.currentGroupIndex !== null ? [window.menuData[window.currentGroupIndex]] : window.menuData;
    groups.forEach((group, gIdxOffset) => {
        const gIdx = window.currentGroupIndex !== null ? window.currentGroupIndex : gIdxOffset;
        group.categories.forEach((cat, cIdx) => {
            cat.sub_sections.forEach((sub, sIdx) => {
                if ((window.searchQueries[2] && !sub.title.toLowerCase().includes(window.searchQueries[2])) || !window.isItemMatch(sub)) return;
                html += window.getItemHTML(sub, 'L2', {
                    breadcrumb: (window.currentGroupIndex === null ? group.title + ' > ' : '') + cat.title,
                    onclick: `window.selectGroup(${gIdx}); window.selectCategory(${cIdx}); window.selectSubsection(${sIdx})`,
                    itemRef: `window.menuData[${gIdx}].categories[${cIdx}].sub_sections[${sIdx}]`
                });
            });
        });
    });
    return html || '<p class="text-center py-10 opacity-30 text-xs">موردی یافت نشد</p>';
};

window.renderItems = function(items) {
    return items.map((item, idx) => {
        if ((window.searchQueries[3] && !item.title.toLowerCase().includes(window.searchQueries[3]) && !item.href.toLowerCase().includes(window.searchQueries[3])) || !window.isItemMatch(item)) return '';
        return window.getItemHTML(item, 'L3', {
            onclick: '', itemRef: `window.menuData[${window.currentGroupIndex}].categories[${window.currentCategoryIndex}].sub_sections[window.currentSubsectionIndex].items[${idx}]`
        });
    }).join('');
};

window.renderItemsGlobal = function() {
    let html = '';
    const groups = window.currentGroupIndex !== null ? [window.menuData[window.currentGroupIndex]] : window.menuData;
    groups.forEach((group, gIdxOffset) => {
        const gIdx = window.currentGroupIndex !== null ? window.currentGroupIndex : gIdxOffset;
        group.categories.forEach((cat, cIdx) => {
            cat.sub_sections.forEach((sub, sIdx) => {
                sub.items.forEach((item, iIdx) => {
                    if ((window.searchQueries[3] && !item.title.toLowerCase().includes(window.searchQueries[3]) && !item.href.toLowerCase().includes(window.searchQueries[3])) || !window.isItemMatch(item)) return;
                    html += window.getItemHTML(item, 'L3', {
                        breadcrumb: (window.currentGroupIndex === null ? group.title + ' > ' : '') + `${cat.title} > ${sub.title}`,
                        onclick: `window.selectGroup(${gIdx}); window.selectCategory(${cIdx}); window.selectSubsection(${sIdx})`,
                        itemRef: `window.menuData[${gIdx}].categories[${cIdx}].sub_sections[${sIdx}].items[${iIdx}]`
                    });
                });
            });
        });
    });
    return html || '<p class="text-center py-10 opacity-30 text-xs">موردی یافت نشد</p>';
};

window.toggleLevelVisibilityDirect = (item, key) => { item[key] = !item[key]; window.renderUI(); window.saveDraft(); };
window.editLevelItemDirect = async (item, type) => {
    const res = await window.adminPrompt({ type: 'item', title: 'ویرایش مورد', titleValue: item.title, hasHref: type === 'L3', hrefValue: item.href || '' });
    if (res) { item.title = res.title; if (type === 'L3') item.href = res.href; window.renderUI(); window.saveDraft(); }
};
window.removeLevelItemDirect = async (item, type) => {
    if (await window.adminConfirm('آیا از حذف این مورد مطمئن هستید؟')) {
        item._selected = true;
        window.applyBoxBulkAction(type === 'L1' ? 1 : (type === 'L2' ? 2 : 3), 'delete');
    }
};

window.selectCategory = (idx) => { window.currentCategoryIndex = idx; window.currentSubsectionIndex = null; window.activeBox = 2; window.renderUI(); };
window.selectSubsection = (idx) => { window.currentSubsectionIndex = idx; window.activeBox = 3; window.renderUI(); };

window.addCategory = (gIdx) => { window.menuData[gIdx].categories.push({ id: 'id-'+Date.now(), title: "دسته جدید", show_desktop: true, show_mobile: true, sub_sections: [] }); window.renderUI(); window.saveDraft(); };
window.addSubsection = (gIdx, cIdx) => { window.menuData[gIdx].categories[cIdx].sub_sections.push({ title: "بخش جدید", show_desktop: true, show_mobile: true, items: [] }); window.renderUI(); window.saveDraft(); };
window.addItemJS = (gIdx, cIdx, sIdx) => { window.menuData[gIdx].categories[cIdx].sub_sections[sIdx].items.push({ title: "آیتم جدید", href: "#", show_desktop: true, show_mobile: true }); window.renderUI(); window.saveDraft(); };

window.initSortable = () => {
    if (window.isGlobalMode()) return;
    const cfg = { animation: 150, handle: '.cursor-move', ghostClass: 'opacity-50', forceFallback: true };
    document.querySelectorAll('.categories-list').forEach(el => new Sortable(el, { ...cfg, onEnd: (evt) => {
        const item = window.menuData[el.dataset.gidx].categories.splice(evt.oldIndex, 1)[0];
        window.menuData[el.dataset.gidx].categories.splice(evt.newIndex, 0, item);
        window.currentCategoryIndex = evt.newIndex; window.renderUI(); window.saveDraft();
    }}));
    document.querySelectorAll('.subsections-list').forEach(el => {
        if (el.dataset.cidx === "null") return;
        new Sortable(el, { ...cfg, onEnd: (evt) => {
            const item = window.menuData[el.dataset.gidx].categories[el.dataset.cidx].sub_sections.splice(evt.oldIndex, 1)[0];
            window.menuData[el.dataset.gidx].categories[el.dataset.cidx].sub_sections.splice(evt.newIndex, 0, item);
            window.currentSubsectionIndex = evt.newIndex; window.renderUI(); window.saveDraft();
        }});
    });
    document.querySelectorAll('.items-list').forEach(el => {
        if (el.dataset.sidx === "null") return;
        new Sortable(el, { ...cfg, onEnd: (evt) => {
            const item = window.menuData[el.dataset.gidx].categories[el.dataset.cidx].sub_sections[el.dataset.sidx].items.splice(evt.oldIndex, 1)[0];
            window.menuData[el.dataset.gidx].categories[el.dataset.cidx].sub_sections[el.dataset.sidx].items.splice(evt.newIndex, 0, item);
            window.renderUI(); window.saveDraft();
        }});
    });
};

window.saveDraft = () => {
    localStorage.setItem('megamenu_draft', JSON.stringify(window.menuData));
    window.updateJsonViewer();
    if (document.getElementById('auto-save-toggle')?.checked) {
        clearTimeout(window.autoSaveTimeout);
        window.autoSaveTimeout = setTimeout(() => { window.saveMenuConfig(true); }, 2000);
    }
};

window.exportMenuConfig = () => {
    const cleanData = JSON.parse(JSON.stringify(window.menuData));
    const recursiveClean = (n) => { delete n._selected; if (n.categories) n.categories.forEach(recursiveClean); if (n.sub_sections) n.sub_sections.forEach(recursiveClean); if (n.items) n.items.forEach(recursiveClean); };
    cleanData.forEach(recursiveClean);
    const blob = new Blob([JSON.stringify(cleanData)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `megamenu-config-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

window.importMenuConfig = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = async (e) => {
        try {
            const data = JSON.parse(e.target.result);
            if (!Array.isArray(data)) throw new Error('Invalid format');
            if (await window.adminConfirm('آیا از جایگزینی کل تنظیمات با فایل انتخابی مطمئن هستید؟')) {
                window.menuData = data;
                window.currentGroupIndex = null;
                window.renderUI();
                window.saveDraft();
                window.adminAlert('تنظیمات با موفقیت وارد شد.');
            }
        } catch (err) {
            window.adminAlert('فایل JSON نامعتبر است.');
        }
        event.target.value = '';
    };
    reader.readAsText(file);
};

window.updateJsonViewer = () => {
    const viewer = document.getElementById('megamenu-json-viewer');
    if (!viewer) return;
    const cleanData = JSON.parse(JSON.stringify(window.menuData));
    const recursiveClean = (n) => { delete n._selected; if (n.categories) n.categories.forEach(recursiveClean); if (n.sub_sections) n.sub_sections.forEach(recursiveClean); if (n.items) n.items.forEach(recursiveClean); };
    cleanData.forEach(recursiveClean);
    viewer.value = JSON.stringify(cleanData, null, 4);
};

window.handleJsonViewerInput = (val) => {
    try {
        const data = JSON.parse(val);
        if (Array.isArray(data)) {
            window.menuData = data;
            // Debounce UI update to avoid flickering while typing
            clearTimeout(window._jsonUpdateTimeout);
            window._jsonUpdateTimeout = setTimeout(() => {
                window.renderUI();
                localStorage.setItem('megamenu_draft', JSON.stringify(window.menuData));
            }, 500);
        }
    } catch (e) {
        // Silently fail while typing invalid JSON
    }
};

window.saveMenuConfig = (isAuto = false) => {
    const authkey = document.querySelector('meta[name="authkey"]')?.content;
    const cleanData = JSON.parse(JSON.stringify(window.menuData));
    const recursiveClean = (n) => { delete n._selected; if (n.categories) n.categories.forEach(recursiveClean); if (n.sub_sections) n.sub_sections.forEach(recursiveClean); if (n.items) n.items.forEach(recursiveClean); };
    cleanData.forEach(recursiveClean);
    axios.post(`/dash/admin/${authkey}/megamenu/save`, { config: cleanData }).then(res => {
        if (!isAuto) window.adminAlert(res.data.message);
        localStorage.removeItem('megamenu_draft');
    }).catch(() => { if (!isAuto) window.adminAlert('خطا در ذخیره سازی'); });
};

window.startLinkTest = async () => {
    const container = document.getElementById('broken-links-container');
    const progress = document.getElementById('test-progress');
    const bar = document.getElementById('progress-bar');
    const percent = document.getElementById('progress-percent');
    const btn = document.getElementById('start-test-btn');
    btn.disabled = true; progress.classList.remove('hidden'); container.innerHTML = '';
    document.getElementById('broken-links-bulk-actions').classList.add('hidden');
    let urls = [];
    window.menuData.forEach(g => g.categories.forEach(c => c.sub_sections.forEach(s => s.items.forEach(i => { if(i.href && i.href !== '#') urls.push(i.href); }))));
    urls = [...new Set(urls)];
    if (!urls.length) { btn.disabled = false; container.innerHTML = '<p class="text-sm text-center py-10">لینکی یافت نشد.</p>'; return; }
    const authkey = document.querySelector('meta[name="authkey"]')?.content;
    for (let i = 0; i < urls.length; i += 10) {
        const chunk = urls.slice(i, i + 10);
        try {
            const res = await axios.post(`/dash/admin/${authkey}/megamenu/test-links`, { urls: chunk });
            res.data.results.forEach(r => {
                window.brokenLinkResults[r.url] = { ok: r.ok, status: r.status };
                if (!r.ok) {
                    const div = document.createElement('div');
                    div.className = 'flex items-center justify-between p-3 bg-white dark:bg-slate-900 border border-red-200 dark:border-red-900/30 rounded-xl shadow-sm';
                    div.innerHTML = `<div class="flex items-center gap-3 min-w-0"><input type="checkbox" class="broken-link-checkbox admin-checkbox !w-4 !h-4" data-url="${r.url}"><div class="flex flex-col min-w-0"><span class="text-[10px] font-mono text-red-600 dark:text-red-400 break-all">${r.url}</span><span class="text-[9px] font-bold text-slate-400 mt-1">خطای ${r.status}</span></div></div>`;
                    container.appendChild(div);
                }
            });
            bar.style.width = percent.innerText = Math.round(((i + chunk.length) / urls.length) * 100) + '%';
        } catch (e) {}
    }
    btn.disabled = false;
    if (!container.children.length) container.innerHTML = '<div class="col-span-full py-20 text-center text-emerald-500 font-bold">تمامی لینک‌ها سالم هستند!</div>';
    else document.getElementById('broken-links-bulk-actions').classList.remove('hidden');
};

window.bulkActionBrokenLinks = async (action) => {
    const selectedUrls = Array.from(document.querySelectorAll('.broken-link-checkbox:checked')).map(el => el.dataset.url);
    const targetUrls = action.includes('selected') ? selectedUrls : Object.keys(window.brokenLinkResults).filter(u => !window.brokenLinkResults[u].ok);
    if (!targetUrls.length) return window.adminAlert('موردی یافت نشد');
    if (await window.adminConfirm(action.includes('delete') ? `حذف ${targetUrls.length} مورد؟` : `غیرفعال‌سازی ${targetUrls.length} مورد؟`)) {
        window.menuData.forEach(g => g.categories.forEach(c => c.sub_sections.forEach(s => {
            if (action.includes('delete')) s.items = s.items.filter(i => !targetUrls.includes(i.href));
            else s.items.forEach(i => { if(targetUrls.includes(i.href)) { i.show_desktop = false; i.show_mobile = false; } });
        })));
        window.adminAlert('با موفقیت انجام شد'); window.renderUI(); window.saveDraft();
        document.getElementById('broken-links-container').innerHTML = '<p class="text-center py-10 opacity-50 text-xs">عملیات انجام شد.</p>';
        document.getElementById('broken-links-bulk-actions').classList.add('hidden');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const draft = localStorage.getItem('megamenu_draft');
    if (draft) {
        window.adminConfirm('تغییرات ذخیره نشده دارید. بازیابی شود؟').then(confirmed => {
            if (confirmed) { window.menuData = JSON.parse(draft); window.renderUI(); }
            else { localStorage.removeItem('megamenu_draft'); window.renderUI(); }
            window.updateJsonViewer();
        });
    } else {
        window.renderUI();
        window.updateJsonViewer();
    }
});
