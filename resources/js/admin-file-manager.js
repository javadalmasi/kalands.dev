const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const FILE_MANAGER_VIEW_MODE_KEY = 'admin:file-manager:view-mode';

function getShell() {
    return document.getElementById('admin-shell');
}

function isLightTheme() {
    return !!getShell()?.classList.contains('admin-light');
}

function htmlEscape(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

const ALLOWED_UPLOAD_EXTENSIONS = new Set(['png', 'apng', 'jpg', 'jpeg', 'webp', 'avif', 'gif', 'bmp', 'svg', 'tif', 'tiff', 'heic', 'heif']);

function filterAllowedUploads(files) {
    const accepted = [];
    const rejected = [];

    for (const file of files || []) {
        const name = String(file?.name || '');
        const ext = name.includes('.') ? name.split('.').pop().toLowerCase() : '';
        const mime = String(file?.type || '').toLowerCase();
        const extensionAllowed = ALLOWED_UPLOAD_EXTENSIONS.has(ext);
        const mimeAllowed = mime ? mime.startsWith('image/') : false;
        if (mimeAllowed || (!mime && extensionAllowed)) accepted.push(file);
        else rejected.push(file);
    }

    return { accepted, rejected };
}

function joinPath(...parts) {
    return parts
        .filter((p) => p !== null && p !== undefined)
        .map((p) => String(p).trim())
        .filter(Boolean)
        .join('/')
        .replace(/\\/g, '/')
        .replace(/\/+/g, '/')
        .replace(/^\/+/, '')
        .replace(/\/+$/, '');
}

function normalizePath(path) {
    return joinPath(path || '');
}

function dirname(path) {
    const normalized = normalizePath(path);
    if (!normalized) return '';
    const tokens = normalized.split('/');
    tokens.pop();
    return tokens.join('/');
}

function joinUrl(base, path) {
    return encodeURI(String(base || '').replace(/\/?$/, '/') + String(path || '').replace(/^\/+/, ''));
}

function toRelativeUrl(rawUrl) {
    const input = String(rawUrl || '').trim();
    if (!input) return '';
    try {
        const parsed = new URL(input, window.location.origin);
        return `${parsed.pathname}${parsed.search}${parsed.hash}`;
    } catch (_error) {
        if (input.startsWith('/')) return input;
        return `/${input.replace(/^\/+/, '')}`;
    }
}

function formatBytes(size) {
    const n = Number(size || 0);
    if (n < 1024) return `${n} B`;
    if (n < 1024 * 1024) return `${(n / 1024).toFixed(1)} KB`;
    if (n < 1024 * 1024 * 1024) return `${(n / (1024 * 1024)).toFixed(1)} MB`;
    return `${(n / (1024 * 1024 * 1024)).toFixed(2)} GB`;
}

function fileTypeByName(name) {
    const ext = String(name || '').split('.').pop()?.toLowerCase() || '';
    if (['jpg', 'jpeg', 'png', 'webp', 'avif', 'apng', 'gif', 'bmp', 'svg'].includes(ext)) return 'image';
    if (['mp4', 'webm', 'mov', 'mkv', 'avi'].includes(ext)) return 'video';
    if (['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'].includes(ext)) return 'audio';
    if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) return 'archive';
    if (['pdf'].includes(ext)) return 'pdf';
    if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(ext)) return 'office';
    if (['txt', 'md', 'json', 'xml', 'csv', 'log'].includes(ext)) return 'text';
    return 'other';
}

function kindIcon(entry) {
    if (entry.kind === 'dir') return 'folder';
    const t = fileTypeByName(entry.name);
    if (t === 'image') return 'image';
    if (t === 'video') return 'movie';
    if (t === 'audio') return 'audiotrack';
    if (t === 'archive') return 'folder_zip';
    if (t === 'pdf') return 'picture_as_pdf';
    if (t === 'office') return 'description';
    if (t === 'text') return 'article';
    return 'draft';
}

function endpoints(explorerUrl) {
    return {
        browse: explorerUrl,
        upload: `${explorerUrl}/upload`,
        createFolder: `${explorerUrl}/create-folder`,
        del: `${explorerUrl}/delete`,
        rename: `${explorerUrl}/rename`,
        move: `${explorerUrl}/move`,
        copy: `${explorerUrl}/copy`,
    };
}

async function apiPost(url, payload, isFormData = false) {
    const options = {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
        },
        body: null,
    };

    if (isFormData) {
        options.body = payload;
    } else {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(payload || {});
    }

    const response = await fetch(url, options);
    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        const message = data?.message || 'خطا در انجام درخواست';
        throw new Error(message);
    }

    return data;
}

function showToast(message, tone = 'normal') {
    const wrap = document.getElementById('admin-toast-wrap');
    if (!wrap) return;

    const node = document.createElement('div');
    node.className = 'admin-toast';
    if (tone === 'error') node.classList.add('admin-toast-error');
    node.textContent = message;
    wrap.appendChild(node);
    setTimeout(() => node.remove(), 3200);
}

function getSavedViewMode() {
    const saved = localStorage.getItem(FILE_MANAGER_VIEW_MODE_KEY);
    return saved === 'icon' || saved === 'list' ? saved : null;
}

function setSavedViewMode(mode) {
    if (mode !== 'icon' && mode !== 'list') return;
    localStorage.setItem(FILE_MANAGER_VIEW_MODE_KEY, mode);
}

function withDialog(id, callback) {
    const dialog = document.getElementById(id);
    if (!dialog) return;
    callback(dialog);
}

function setButtonContent(button, icon, label) {
    button.innerHTML = `<span class="material-icons">${icon}</span><span>${htmlEscape(label)}</span>`;
}

function openTextPrompt({ title = 'ورودی', placeholder = '', value = '', submitText = 'ثبت', allowEmpty = false, onSubmit }) {
    withDialog('admin-explorer-input-dialog', (dialog) => {
        const titleNode = dialog.querySelector('#admin-explorer-input-title');
        const input = dialog.querySelector('#admin-explorer-input-value');
        const submitBtn = dialog.querySelector('#admin-explorer-input-submit');
        if (!titleNode || !input || !submitBtn) return;

        titleNode.textContent = title;
        input.value = value;
        input.placeholder = placeholder;
        setButtonContent(submitBtn, 'check_circle', submitText);

        dialog.querySelectorAll('[data-explorer-input-close]').forEach((btn) => {
            btn.onclick = () => dialog.close();
        });

    submitBtn.onclick = () => {
        const val = input.value.trim();
        if (!allowEmpty && !val) return;
        onSubmit(val);
        dialog.close();
    };

        dialog.showModal();
        setTimeout(() => input.focus(), 10);
    });
}

function openConfirm({ title = 'تایید', message = 'آیا مطمئن هستید؟', submitText = 'تایید', tone = 'danger', onConfirm }) {
    withDialog('admin-explorer-confirm-dialog', (dialog) => {
        const titleNode = dialog.querySelector('#admin-explorer-confirm-title');
        const messageNode = dialog.querySelector('#admin-explorer-confirm-message');
        const submitBtn = dialog.querySelector('#admin-explorer-confirm-submit');
        if (!titleNode || !messageNode || !submitBtn) return;

        titleNode.textContent = title;
        messageNode.textContent = message;
        setButtonContent(submitBtn, tone === 'danger' ? 'delete' : 'check_circle', submitText);
        submitBtn.classList.toggle('admin-btn-danger', tone === 'danger');
        submitBtn.classList.toggle('admin-btn-secondary', tone !== 'danger');

        dialog.querySelectorAll('[data-explorer-confirm-close]').forEach((btn) => {
            btn.onclick = () => dialog.close();
        });

        submitBtn.onclick = () => {
            onConfirm();
            dialog.close();
        };

        dialog.showModal();
    });
}

function openPreview({ name, url, type }) {
    withDialog('admin-explorer-preview-dialog', (dialog) => {
        const titleNode = dialog.querySelector('#admin-explorer-preview-title');
        const bodyNode = dialog.querySelector('#admin-explorer-preview-body');
        if (!titleNode || !bodyNode) return;

        titleNode.textContent = name || 'Preview';
        const escaped = htmlEscape(name || '');

        if (type === 'image') {
            bodyNode.innerHTML = `<img src="${url}" alt="${escaped}" class="admin-preview-media admin-preview-media-image">`;
        } else if (type === 'video') {
            bodyNode.innerHTML = `<video controls class="admin-preview-media"><source src="${url}"></video>`;
        } else if (type === 'audio') {
            bodyNode.innerHTML = `<audio controls class="w-full"><source src="${url}"></audio>`;
        } else if (type === 'pdf') {
            bodyNode.innerHTML = `<iframe src="${url}" class="admin-preview-media h-[78vh]"></iframe>`;
        } else {
            bodyNode.innerHTML = `<div class="admin-preview-empty">پیش‌نمایش برای این نوع فایل در دسترس نیست.</div>`;
        }

        dialog.querySelectorAll('[data-explorer-preview-close]').forEach((btn) => {
            btn.onclick = () => dialog.close();
        });

        dialog.showModal();
    });
}

function openMoveCopyDialog({ sourceCount, targetPath, onMove, onCopy }) {
    withDialog('admin-explorer-move-copy-dialog', (dialog) => {
        const srcNode = dialog.querySelector('#admin-explorer-move-copy-source');
        const dstNode = dialog.querySelector('#admin-explorer-move-copy-target');
        const moveBtn = dialog.querySelector('#admin-explorer-move-submit');
        const copyBtn = dialog.querySelector('#admin-explorer-copy-submit');
        if (!srcNode || !dstNode || !moveBtn || !copyBtn) return;

        srcNode.textContent = `${sourceCount} آیتم`;
        dstNode.textContent = targetPath || '/';
        setButtonContent(moveBtn, 'drive_file_move', 'انتقال');
        setButtonContent(copyBtn, 'content_copy', 'کپی');

        dialog.querySelectorAll('[data-explorer-move-copy-close]').forEach((btn) => {
            btn.onclick = () => dialog.close();
        });

        moveBtn.onclick = async () => {
            await onMove();
            dialog.close();
        };

        copyBtn.onclick = async () => {
            await onCopy();
            dialog.close();
        };

        dialog.showModal();
    });
}

function getExplorerState(container) {
    if (!container.__fmState) {
        container.__fmState = {
            path: normalizePath(container.dataset.explorerPath || ''),
            query: '',
            sort: 'name_asc',
            typeFilter: 'all',
            view: getSavedViewMode() || (container.dataset.viewMode === 'grid' ? 'icon' : (container.dataset.viewMode || 'list')),
            selected: new Set(),
            loading: false,
            entries: [],
            raw: null,
            pickMode: container.dataset.pickMode === '1',
            allowMultiSelect: container.dataset.allowMultiSelect !== '0',
            activePath: '',
        };
    }
    return container.__fmState;
}

function rowMatchesQuery(entry, query) {
    if (!query) return true;
    const q = query.toLowerCase();
    return String(entry.name || '').toLowerCase().includes(q);
}

function rowMatchesType(entry, type) {
    if (type === 'all') return true;
    if (entry.kind === 'dir') return type === 'folder';
    return fileTypeByName(entry.name) === type;
}

function sortEntries(entries, key) {
    const out = [...entries];
    const cmp = {
        name_asc: (a, b) => a.name.localeCompare(b.name, 'fa'),
        name_desc: (a, b) => b.name.localeCompare(a.name, 'fa'),
        size_desc: (a, b) => (Number(b.size || 0) - Number(a.size || 0)),
        size_asc: (a, b) => (Number(a.size || 0) - Number(b.size || 0)),
        date_desc: (a, b) => (Number(b.modified_at || 0) - Number(a.modified_at || 0)),
        date_asc: (a, b) => (Number(a.modified_at || 0) - Number(b.modified_at || 0)),
        type_asc: (a, b) => fileTypeByName(a.name).localeCompare(fileTypeByName(b.name), 'fa'),
    }[key] || ((a, b) => a.name.localeCompare(b.name, 'fa'));

    out.sort((a, b) => {
        if (a.kind !== b.kind) return a.kind === 'dir' ? -1 : 1;
        return cmp(a, b);
    });

    return out;
}

async function fetchExplorer(container) {
    const state = getExplorerState(container);
    const endpoint = container.dataset.explorerUrl;
    if (!endpoint) return;

    const url = new URL(endpoint, window.location.origin);
    if (state.path) url.searchParams.set('path', state.path);

    state.loading = true;
    container.setAttribute('aria-busy', 'true');

    const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
    const payload = await response.json();

    const dirs = Array.isArray(payload.directories) ? payload.directories : [];
    const files = Array.isArray(payload.files) ? payload.files : [];

    const dirRows = dirs.map((d) => ({
        kind: 'dir',
        name: d.name || '',
        path: normalizePath(d.path || ''),
        size: 0,
        modified_at: 0,
        is_image: false,
    }));

    const fileRows = files.map((f) => ({
        kind: 'file',
        name: f.name || '',
        path: normalizePath(f.path || ''),
        size: Number(f.size || 0),
        modified_at: Number(f.modified_at || 0),
        is_image: !!f.is_image,
    }));

    state.raw = payload;
    state.entries = [...dirRows, ...fileRows];
    state.path = normalizePath(payload.path || state.path);
    state.activePath = state.path;

    for (const selectedPath of [...state.selected]) {
        const exists = state.entries.some((e) => e.path === selectedPath);
        if (!exists) state.selected.delete(selectedPath);
    }

    state.loading = false;
    container.removeAttribute('aria-busy');
}

function getEntryUrl(container, entry) {
    const fileBaseUrl = container.dataset.fileUrlBase || '';
    const cdnBaseUrl = (container.dataset.cdnBaseUrl || '').trim().replace(/\/+$/, '');

    if (entry.kind !== 'file') return '';
    const fileUrl = fileBaseUrl ? joinUrl(fileBaseUrl, entry.path) : '';
    if (!cdnBaseUrl) return fileUrl;
    return joinUrl(`${cdnBaseUrl}/`, entry.path);
}

function getLivePreviewHtml(container, state) {
    if (state.view !== 'list') return '';

    const selectedPath = state.activePath || [...state.selected][0] || '';
    const entry = selectedPath ? getEntryByPath(container, selectedPath) : null;
    if (!entry || entry.kind !== 'file') {
        return `
            <aside class="fm-live-preview">
                <div class="fm-live-preview-head">Preview</div>
                <div class="fm-live-preview-empty">برای پیش‌نمایش، یک فایل را انتخاب کنید.</div>
            </aside>
        `;
    }

    const fileUrl = getEntryUrl(container, entry);
    const fileType = fileTypeByName(entry.name);
    let media = '<div class="fm-live-preview-empty">پیش‌نمایش این نوع فایل موجود نیست.</div>';
    if (fileType === 'image') {
        media = `<img src="${fileUrl}" alt="${htmlEscape(entry.name)}" class="fm-live-preview-media fm-live-preview-image">`;
    } else if (fileType === 'video') {
        media = `<video controls class="fm-live-preview-media"><source src="${fileUrl}"></video>`;
    } else if (fileType === 'audio') {
        media = `<audio controls class="w-full"><source src="${fileUrl}"></audio>`;
    } else if (fileType === 'pdf') {
        media = `<iframe src="${fileUrl}" class="fm-live-preview-media fm-live-preview-pdf"></iframe>`;
    }

    return `
        <aside class="fm-live-preview">
            <div class="fm-live-preview-head">Preview</div>
            <div class="fm-live-preview-name">${htmlEscape(entry.name)}</div>
            <div class="fm-live-preview-body">${media}</div>
        </aside>
    `;
}

function getBreadcrumbHtml(state, crumbs) {
    const chain = [{ name: 'root', path: '' }, ...(crumbs || [])];
    return chain.map((item) => {
        return `<button type="button" class="fm-crumb" data-fm-go="${htmlEscape(item.path || '')}">${htmlEscape(item.name || 'root')}</button>`;
    }).join('<span class="fm-crumb-sep">/</span>');
}

function entryRowHtml(container, state, entry) {
    const selected = state.selected.has(entry.path);
    const kind = entry.kind;
    const icon = kindIcon(entry);
    const type = kind === 'dir' ? 'folder' : fileTypeByName(entry.name);
    const url = getEntryUrl(container, entry);
    const modified = entry.modified_at ? new Date(entry.modified_at * 1000).toLocaleString('fa-IR') : '-';
    const size = kind === 'dir' ? '-' : formatBytes(entry.size || 0);

    const thumb = kind === 'file' && type === 'image'
        ? `<img src="${url}" alt="${htmlEscape(entry.name)}" class="fm-thumb">`
        : `<span class="material-icons fm-kind-icon">${icon}</span>`;

    const selectBtn = state.allowMultiSelect ? `<button type="button" class="fm-select-btn ${selected ? 'is-selected' : ''}" data-fm-toggle-select="${htmlEscape(entry.path)}" title="انتخاب">
        <span class="material-icons">${selected ? 'check_box' : 'check_box_outline_blank'}</span>
    </button>` : '';

    const pickBtn = state.pickMode && kind === 'file'
        ? `<button type="button" class="fm-action-btn fm-action-btn-insert" data-fm-pick-url="${htmlEscape(url)}" title="درج فایل"><span class="material-icons">check_circle</span></button>`
        : '';

    const previewBtn = kind === 'file'
        ? `<button type="button" class="fm-action-btn" data-fm-preview="${htmlEscape(entry.path)}" title="پیش‌نمایش"><span class="material-icons">visibility</span></button>`
        : '';
    const renameBtn = kind === 'dir'
        ? `<button type="button" class="fm-action-btn" data-fm-rename="${htmlEscape(entry.path)}" title="تغییرنام"><span class="material-icons">drive_file_rename_outline</span></button>`
        : '';

    const rowViewClass = state.view === 'icon' ? 'is-icon' : '';

    if (state.view === 'icon') {
        return `
        <div class="fm-grid-item">
            <div class="fm-row ${rowViewClass} ${selected ? 'is-selected' : ''}" data-fm-row="${htmlEscape(entry.path)}" data-fm-kind="${kind}">
                <div class="fm-row-main" data-fm-open="${htmlEscape(entry.path)}">
                    <div class="fm-row-thumb">${thumb}</div>
                </div>
            </div>
            <div class="fm-grid-name">${htmlEscape(entry.name)}</div>
        </div>
        `;
    }

    return `
        <div class="fm-row ${rowViewClass} ${selected ? 'is-selected' : ''}" data-fm-row="${htmlEscape(entry.path)}" data-fm-kind="${kind}">
            <div class="fm-row-main" data-fm-open="${htmlEscape(entry.path)}">
                ${selectBtn}
                <div class="fm-row-thumb">${thumb}</div>
                <div class="fm-row-name-wrap">
                    <div class="fm-row-name">${htmlEscape(entry.name)}</div>
                    <div class="fm-row-meta">${kind === 'dir' ? 'Folder' : type.toUpperCase()} • ${size} • ${modified}</div>
                </div>
            </div>
            <div class="fm-row-actions">
                ${pickBtn}
                ${previewBtn}
                <button type="button" class="fm-action-btn" data-fm-move-copy="${htmlEscape(entry.path)}" title="Move/Copy"><span class="material-icons">drive_file_move</span></button>
                <button type="button" class="fm-action-btn" data-fm-copy-url="${htmlEscape(url)}" title="کپی URL" ${kind === 'dir' ? 'disabled' : ''}><span class="material-icons">link</span></button>
                ${renameBtn}
                <button type="button" class="fm-action-btn is-danger" data-fm-delete="${htmlEscape(entry.path)}" title="حذف"><span class="material-icons">delete</span></button>
            </div>
        </div>
    `;
}

function renderExplorerUI(container) {
    const state = getExplorerState(container);
    const payload = state.raw || { breadcrumbs: [] };

    const filtered = sortEntries(
        state.entries.filter((entry) => rowMatchesQuery(entry, state.query)).filter((entry) => rowMatchesType(entry, state.typeFilter)),
        state.sort,
    );

    const parentPath = dirname(state.path);
    const canGoParent = state.path !== '';
    const breadcrumbHtml = getBreadcrumbHtml(state, payload.breadcrumbs || []);
    const selectedCount = state.selected.size;
    const fileCount = filtered.filter((x) => x.kind === 'file').length;
    const dirCount = filtered.filter((x) => x.kind === 'dir').length;

    const treeButtons = (payload.directories || []).map((dir) => {
        return `<button type="button" class="fm-tree-item" data-fm-go="${htmlEscape(dir.path || '')}"><span class="material-icons">folder</span><span>${htmlEscape(dir.name || '')}</span></button>`;
    }).join('') || '<div class="fm-muted">پوشه‌ای وجود ندارد.</div>';

    const rowsHtml = filtered.map((entry) => entryRowHtml(container, state, entry)).join('') || '<div class="fm-empty">موردی یافت نشد.</div>';
    const livePreviewHtml = getLivePreviewHtml(container, state);

    container.classList.add('adm-fm-root');
    container.innerHTML = `
        <div class="adm-fm-shell ${isLightTheme() ? 'is-light' : 'is-dark'}">
            <div class="adm-fm-toolbar">
                <div class="fm-breadcrumb-wrap">${breadcrumbHtml}</div>
                <div class="fm-toolbar-actions">
                    <button type="button" class="fm-toolbar-btn" data-fm-parent ${canGoParent ? '' : 'disabled'}><span class="material-icons">arrow_upward</span>Parent</button>
                    <button type="button" class="fm-toolbar-btn" data-fm-refresh><span class="material-icons">refresh</span>Refresh</button>
                    <button type="button" class="fm-toolbar-btn" data-fm-create-folder><span class="material-icons">create_new_folder</span>Folder</button>
                    <label class="fm-toolbar-btn fm-upload-btn"><span class="material-icons">upload_file</span>Upload<input type="file" data-fm-upload accept="image/*" multiple></label>
                    ${state.allowMultiSelect ? `<button type="button" class="fm-toolbar-btn" data-fm-select-all><span class="material-icons">select_all</span>Select</button>` : ''}
                    ${state.allowMultiSelect ? `<button type="button" class="fm-toolbar-btn" data-fm-clear-selection><span class="material-icons">deselect</span>Clear</button>` : ''}
                    ${state.pickMode && state.allowMultiSelect ? `<button type="button" class="fm-toolbar-btn" data-fm-insert-selected ${selectedCount ? '' : 'disabled'}><span class="material-icons">input</span>درج انتخاب‌شده</button>` : ''}
                    ${state.allowMultiSelect ? `<button type="button" class="fm-toolbar-btn" data-fm-bulk-move ${selectedCount ? '' : 'disabled'}><span class="material-icons">drive_file_move</span>Move/Copy</button>` : ''}
                    ${state.allowMultiSelect ? `<button type="button" class="fm-toolbar-btn is-danger" data-fm-bulk-delete ${selectedCount ? '' : 'disabled'}><span class="material-icons">delete_sweep</span>Delete</button>` : ''}
                </div>
            </div>

            <div class="adm-fm-filters">
                <input type="text" class="fm-input" data-fm-search value="${htmlEscape(state.query)}" placeholder="Search files and folders...">
                <select class="fm-select" data-fm-type>
                    <option value="all" ${state.typeFilter === 'all' ? 'selected' : ''}>All types</option>
                    <option value="folder" ${state.typeFilter === 'folder' ? 'selected' : ''}>Folders</option>
                    <option value="image" ${state.typeFilter === 'image' ? 'selected' : ''}>Images</option>
                    <option value="video" ${state.typeFilter === 'video' ? 'selected' : ''}>Videos</option>
                    <option value="audio" ${state.typeFilter === 'audio' ? 'selected' : ''}>Audio</option>
                    <option value="pdf" ${state.typeFilter === 'pdf' ? 'selected' : ''}>PDF</option>
                    <option value="archive" ${state.typeFilter === 'archive' ? 'selected' : ''}>Archive</option>
                    <option value="text" ${state.typeFilter === 'text' ? 'selected' : ''}>Text</option>
                </select>
                <select class="fm-select" data-fm-sort>
                    <option value="name_asc" ${state.sort === 'name_asc' ? 'selected' : ''}>Name A-Z</option>
                    <option value="name_desc" ${state.sort === 'name_desc' ? 'selected' : ''}>Name Z-A</option>
                    <option value="date_desc" ${state.sort === 'date_desc' ? 'selected' : ''}>Newest</option>
                    <option value="date_asc" ${state.sort === 'date_asc' ? 'selected' : ''}>Oldest</option>
                    <option value="size_desc" ${state.sort === 'size_desc' ? 'selected' : ''}>Size Desc</option>
                    <option value="size_asc" ${state.sort === 'size_asc' ? 'selected' : ''}>Size Asc</option>
                    <option value="type_asc" ${state.sort === 'type_asc' ? 'selected' : ''}>Type</option>
                </select>
                <div class="fm-view-switch">
                    <button type="button" class="fm-toolbar-btn ${state.view === 'icon' ? 'is-active' : ''}" data-fm-view="icon"><span class="material-icons">apps</span></button>
                    <button type="button" class="fm-toolbar-btn ${state.view === 'list' ? 'is-active' : ''}" data-fm-view="list"><span class="material-icons">view_list</span></button>
                </div>
            </div>

            <div class="adm-fm-layout">
                <aside class="adm-fm-tree">
                    <div class="fm-tree-head">Folders</div>
                    <button type="button" class="fm-tree-item ${state.path === '' ? 'is-current' : ''}" data-fm-go=""><span class="material-icons">home</span><span>Root</span></button>
                    ${treeButtons}
                </aside>

                <section class="adm-fm-list" data-fm-drop-zone>
                    <div class="fm-statusbar">
                        <span>${dirCount} پوشه • ${fileCount} فایل</span>
                        <span>${selectedCount} انتخاب شده</span>
                    </div>
                    <div class="fm-list-stage ${state.view === 'list' ? 'is-list-with-preview' : ''}">
                        ${livePreviewHtml}
                        <div class="fm-rows ${state.view === 'icon' ? 'is-icon' : ''}">
                            ${rowsHtml}
                        </div>
                    </div>
                </section>
            </div>
        </div>
    `;
}

function getEntryByPath(container, path) {
    const state = getExplorerState(container);
    return state.entries.find((entry) => entry.path === normalizePath(path)) || null;
}

async function uploadFiles(container, files) {
    const state = getExplorerState(container);
    if (!files?.length) return;
    const { accepted, rejected } = filterAllowedUploads(files);
    if (rejected.length) {
        showToast(`${rejected.length} فایل رد شد. فقط فایل تصویری مجاز است.`, 'error');
    }
    if (!accepted.length) return;
    const ep = endpoints(container.dataset.explorerUrl);

    const fd = new FormData();
    fd.append('path', state.path || '');
    for (const file of accepted) fd.append('files[]', file);

    await apiPost(ep.upload, fd, true);
    showToast(`${accepted.length} فایل آپلود شد.`);
}

async function renameEntry(container, path) {
    const entry = getEntryByPath(container, path);
    if (!entry) return;

    openTextPrompt({
        title: `تغییر نام ${entry.kind === 'dir' ? 'پوشه' : 'فایل'}`,
        value: entry.name,
        submitText: 'ذخیره',
        onSubmit: async (newName) => {
            try {
                await apiPost(endpoints(container.dataset.explorerUrl).rename, {
                    path: entry.path,
                    new_name: newName,
                });
                showToast('نام آیتم تغییر کرد.');
                await reloadExplorer(container);
            } catch (error) {
                showToast(error.message || 'تغییر نام انجام نشد.', 'error');
            }
        },
    });
}

async function deleteEntries(container, paths) {
    if (!paths.length) return;

    openConfirm({
        title: 'حذف آیتم',
        message: `آیا از حذف ${paths.length} آیتم مطمئن هستید؟`,
        submitText: 'حذف',
        tone: 'danger',
        onConfirm: async () => {
            try {
                await apiPost(endpoints(container.dataset.explorerUrl).del, { paths });
                const state = getExplorerState(container);
                for (const path of paths) state.selected.delete(path);
                showToast('حذف انجام شد.');
                await reloadExplorer(container);
            } catch (error) {
                showToast(error.message || 'حذف انجام نشد.', 'error');
            }
        },
    });
}

function openMoveCopy(container, explicitPaths = null) {
    const state = getExplorerState(container);
    const selected = explicitPaths && explicitPaths.length ? explicitPaths : [...state.selected];
    if (!selected.length) return;

    openTextPrompt({
        title: 'مسیر مقصد برای Move/Copy',
        placeholder: 'مثال: banners/home یا خالی برای root',
        submitText: 'ادامه',
        allowEmpty: true,
        onSubmit: (targetDirectory) => {
            openMoveCopyDialog({
                sourceCount: selected.length,
                targetPath: targetDirectory || '/',
                onMove: async () => {
                    const ep = endpoints(container.dataset.explorerUrl);
                    try {
                        for (const path of selected) {
                            await apiPost(ep.move, { path, target_directory: targetDirectory || '' });
                        }
                        state.selected.clear();
                        showToast('انتقال انجام شد.');
                        await reloadExplorer(container);
                    } catch (error) {
                        showToast(error.message || 'انتقال انجام نشد.', 'error');
                    }
                },
                onCopy: async () => {
                    const ep = endpoints(container.dataset.explorerUrl);
                    try {
                        for (const path of selected) {
                            await apiPost(ep.copy, { path, target_directory: targetDirectory || '' });
                        }
                        state.selected.clear();
                        showToast('کپی انجام شد.');
                        await reloadExplorer(container);
                    } catch (error) {
                        showToast(error.message || 'کپی انجام نشد.', 'error');
                    }
                },
            });
        },
    });
}

function attachExplorerEvents(container) {
    const state = getExplorerState(container);

    container.querySelectorAll('[data-fm-go]').forEach((button) => {
        button.addEventListener('click', async () => {
            state.path = normalizePath(button.dataset.fmGo || '');
            await reloadExplorer(container);
        });
    });

    container.querySelectorAll('[data-fm-open]').forEach((button) => {
        button.addEventListener('dblclick', async () => {
            const path = button.dataset.fmOpen || '';
            const entry = getEntryByPath(container, path);
            if (!entry) return;

            if (entry.kind === 'dir') {
                state.path = normalizePath(entry.path);
                await reloadExplorer(container);
            } else {
                if (state.pickMode) {
                    const url = getEntryUrl(container, entry);
                    if (url) {
                        window.dispatchEvent(new CustomEvent('admin-file-picked', { detail: { url: toRelativeUrl(url) } }));
                    }
                } else {
                    const type = fileTypeByName(entry.name);
                    openPreview({ name: entry.name, url: getEntryUrl(container, entry), type });
                }
            }
        });

        button.addEventListener('click', () => {
            const row = button.closest('[data-fm-row]');
            if (!row) return;
            const path = row.getAttribute('data-fm-row') || '';
            state.activePath = path;
            if (state.view === 'list') {
                renderExplorerUI(container);
                attachExplorerEvents(container);
            }
        });
    });

    container.querySelectorAll('[data-fm-row]').forEach((row) => {
        row.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            if (state.view === 'list') return;
            const path = row.getAttribute('data-fm-row') || '';
            if (!path) return;
            const entry = getEntryByPath(container, path);
            if (!entry) return;
            openContextMenu(container, event.clientX, event.clientY, entry);
        });
    });

    container.querySelectorAll('[data-fm-toggle-select]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!state.allowMultiSelect) return;
            const path = button.dataset.fmToggleSelect || '';
            if (!path) return;
            if (state.selected.has(path)) state.selected.delete(path);
            else state.selected.add(path);
            renderExplorerUI(container);
            attachExplorerEvents(container);
        });
    });

    const searchInput = container.querySelector('[data-fm-search]');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            state.query = searchInput.value || '';
            renderExplorerUI(container);
            attachExplorerEvents(container);
        });
    }

    container.querySelector('[data-fm-type]')?.addEventListener('change', (event) => {
        state.typeFilter = event.target.value || 'all';
        renderExplorerUI(container);
        attachExplorerEvents(container);
    });

    container.querySelector('[data-fm-sort]')?.addEventListener('change', (event) => {
        state.sort = event.target.value || 'name_asc';
        renderExplorerUI(container);
        attachExplorerEvents(container);
    });

    container.querySelectorAll('[data-fm-view]').forEach((button) => {
        button.addEventListener('click', () => {
            state.view = button.dataset.fmView || 'list';
            setSavedViewMode(state.view);
            renderExplorerUI(container);
            attachExplorerEvents(container);
        });
    });

    container.querySelector('[data-fm-parent]')?.addEventListener('click', async () => {
        state.path = dirname(state.path);
        await reloadExplorer(container);
    });

    container.querySelector('[data-fm-refresh]')?.addEventListener('click', async () => {
        await reloadExplorer(container);
    });

    container.querySelector('[data-fm-create-folder]')?.addEventListener('click', () => {
        openTextPrompt({
            title: 'ایجاد پوشه جدید',
            placeholder: 'folder-name',
            submitText: 'ایجاد',
            onSubmit: async (name) => {
                try {
                    await apiPost(endpoints(container.dataset.explorerUrl).createFolder, {
                        path: state.path,
                        name,
                    });
                    showToast('پوشه ساخته شد.');
                    await reloadExplorer(container);
                } catch (error) {
                    showToast(error.message || 'ساخت پوشه انجام نشد.', 'error');
                }
            },
        });
    });

    const uploadInput = container.querySelector('[data-fm-upload]');
    if (uploadInput) {
        uploadInput.addEventListener('change', async () => {
            try {
                const files = Array.from(uploadInput.files || []);
                await uploadFiles(container, files);
                uploadInput.value = '';
                await reloadExplorer(container);
            } catch (error) {
                showToast(error.message || 'آپلود انجام نشد.', 'error');
            }
        });
    }

    const dropZone = container.querySelector('[data-fm-drop-zone]');
    if (dropZone) {
        dropZone.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropZone.classList.add('is-dragover');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('is-dragover'));
        dropZone.addEventListener('drop', async (event) => {
            event.preventDefault();
            dropZone.classList.remove('is-dragover');
            const files = Array.from(event.dataTransfer?.files || []);
            if (!files.length) return;
            try {
                await uploadFiles(container, files);
                await reloadExplorer(container);
            } catch (error) {
                showToast(error.message || 'آپلود انجام نشد.', 'error');
            }
        });
    }

    container.querySelector('[data-fm-select-all]')?.addEventListener('click', () => {
        const visiblePaths = [...container.querySelectorAll('[data-fm-row]')].map((x) => x.getAttribute('data-fm-row') || '').filter(Boolean);
        for (const p of visiblePaths) state.selected.add(p);
        renderExplorerUI(container);
        attachExplorerEvents(container);
    });

    container.querySelector('[data-fm-clear-selection]')?.addEventListener('click', () => {
        state.selected.clear();
        renderExplorerUI(container);
        attachExplorerEvents(container);
    });

    container.querySelector('[data-fm-bulk-delete]')?.addEventListener('click', async () => {
        await deleteEntries(container, [...state.selected]);
    });

    container.querySelector('[data-fm-bulk-move]')?.addEventListener('click', () => {
        openMoveCopy(container);
    });

    container.querySelector('[data-fm-insert-selected]')?.addEventListener('click', () => {
        const firstFilePath = [...state.selected].find((p) => {
            const entry = getEntryByPath(container, p);
            return entry?.kind === 'file';
        });
        if (!firstFilePath) {
            showToast('ابتدا یک فایل انتخاب کنید.', 'error');
            return;
        }
        const fileEntry = getEntryByPath(container, firstFilePath);
        const url = fileEntry ? getEntryUrl(container, fileEntry) : '';
        if (!url) {
            showToast('آدرس فایل معتبر نیست.', 'error');
            return;
        }
        window.dispatchEvent(new CustomEvent('admin-file-picked', { detail: { url: toRelativeUrl(url) } }));
    });

    container.querySelectorAll('[data-fm-move-copy]').forEach((button) => {
        button.addEventListener('click', () => {
            const path = button.dataset.fmMoveCopy || '';
            if (!path) return;
            openMoveCopy(container, [path]);
        });
    });

    container.querySelectorAll('[data-fm-preview]').forEach((button) => {
        button.addEventListener('click', () => {
            const entry = getEntryByPath(container, button.dataset.fmPreview || '');
            if (!entry) return;
            openPreview({ name: entry.name, url: getEntryUrl(container, entry), type: fileTypeByName(entry.name) });
        });
    });

    container.querySelectorAll('[data-fm-copy-url]').forEach((button) => {
        button.addEventListener('click', async () => {
            const text = button.dataset.fmCopyUrl || '';
            if (!text) return;
            try {
                await navigator.clipboard.writeText(text);
                showToast('لینک فایل کپی شد.');
            } catch (_error) {
                showToast('کپی نشد.', 'error');
            }
        });
    });

    container.querySelectorAll('[data-fm-rename]').forEach((button) => {
        button.addEventListener('click', async () => {
            await renameEntry(container, button.dataset.fmRename || '');
        });
    });

    container.querySelectorAll('[data-fm-delete]').forEach((button) => {
        button.addEventListener('click', async () => {
            await deleteEntries(container, [button.dataset.fmDelete || ''].filter(Boolean));
        });
    });

    if (state.pickMode) {
        container.querySelectorAll('[data-fm-pick-url]').forEach((button) => {
            button.addEventListener('click', () => {
                const url = button.dataset.fmPickUrl || '';
                if (!url) return;
                window.dispatchEvent(new CustomEvent('admin-file-picked', { detail: { url: toRelativeUrl(url) } }));
            });
        });
    }

    if (!container.__fmKeydownBound) {
        container.__fmKeydownBound = true;
        container.addEventListener('keydown', async (event) => {
            const currentState = getExplorerState(container);
            const target = event.target;
            const tag = String(target?.tagName || '').toLowerCase();
            const isTyping = tag === 'input' || tag === 'textarea' || tag === 'select' || target?.isContentEditable;
            if (isTyping) return;

            const selected = [...currentState.selected];
            const activePath = currentState.activePath || selected[0] || '';
            const activeEntry = activePath ? getEntryByPath(container, activePath) : null;

            if ((event.ctrlKey || event.metaKey) && !event.shiftKey && event.key.toLowerCase() === 'a') {
                event.preventDefault();
                const visiblePaths = [...container.querySelectorAll('[data-fm-row]')].map((x) => x.getAttribute('data-fm-row') || '').filter(Boolean);
                for (const p of visiblePaths) currentState.selected.add(p);
                renderExplorerUI(container);
                attachExplorerEvents(container);
                return;
            }
            if ((event.ctrlKey || event.metaKey) && !event.shiftKey && event.key.toLowerCase() === 'f') {
                event.preventDefault();
                container.querySelector('[data-fm-search]')?.focus();
                return;
            }
            if ((event.ctrlKey || event.metaKey) && !event.shiftKey && event.key.toLowerCase() === 'r') {
                event.preventDefault();
                await reloadExplorer(container);
                return;
            }
            if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key.toLowerCase() === 'n') {
                event.preventDefault();
                container.querySelector('[data-fm-create-folder]')?.click();
                return;
            }
            if ((event.ctrlKey || event.metaKey) && !event.shiftKey && event.key.toLowerCase() === 'c') {
                if (!selected.length) return;
                event.preventDefault();
                await navigator.clipboard.writeText(selected.join('\n')).catch(() => {});
                showToast('مسیر آیتم‌های انتخاب‌شده کپی شد.');
                return;
            }
            if (event.key === 'Delete' && selected.length) {
                event.preventDefault();
                await deleteEntries(container, selected);
                return;
            }
            if (event.key === 'F2' && activeEntry) {
                event.preventDefault();
                await renameEntry(container, activeEntry.path);
                return;
            }
            if (event.key === 'Enter' && activeEntry) {
                event.preventDefault();
                if (activeEntry.kind === 'dir') {
                    currentState.path = normalizePath(activeEntry.path);
                    await reloadExplorer(container);
                } else {
                    openPreview({ name: activeEntry.name, url: getEntryUrl(container, activeEntry), type: fileTypeByName(activeEntry.name) });
                }
                return;
            }
            if (event.key === 'Backspace') {
                event.preventDefault();
                currentState.path = dirname(currentState.path);
                await reloadExplorer(container);
            }
        });
    }
}

async function reloadExplorer(container) {
    try {
        await fetchExplorer(container);
        renderExplorerUI(container);
        attachExplorerEvents(container);
    } catch (error) {
        container.innerHTML = `<div class="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-200">${htmlEscape(error.message || 'بارگذاری فایل‌ها ناموفق بود.')}</div>`;
    }
}

function openPicker(button) {
    const dialog = document.getElementById('admin-file-picker-dialog');
    const container = dialog?.querySelector('[data-file-picker-explorer]');
    if (!dialog || !container) return;

    container.dataset.explorerUrl = button.dataset.explorerUrl || '';
    container.dataset.fileUrlBase = button.dataset.filesUrl || '';
    container.dataset.cdnBaseUrl = button.dataset.cdnBaseUrl || '';
    container.dataset.explorerPath = '';
    container.dataset.pickMode = '1';
    container.dataset.allowMultiSelect = button.dataset.filePickerMulti === '1' ? '1' : '0';
    container.dataset.viewMode = 'list';
    delete container.__fmState;

    const targetSelector = button.dataset.filePickTarget;
    const targetInput = targetSelector ? document.querySelector(targetSelector) : null;
    if (container.__pickerListener) {
        window.removeEventListener('admin-file-picked', container.__pickerListener);
    }

    const onPick = (event) => {
        const url = event.detail?.url || '';
        if (!url) return;
        if (targetInput) {
            targetInput.value = url;
            targetInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
        window.removeEventListener('admin-file-picked', onPick);
        delete container.__pickerListener;
        dialog.close();
    };

    container.__pickerListener = onPick;
    window.addEventListener('admin-file-picked', onPick);
    reloadExplorer(container);
    dialog.showModal();
}

function closePickerDialog() {
    const dialog = document.getElementById('admin-file-picker-dialog');
    if (dialog?.open) dialog.close();
}

function initExplorerContainers() {
    document.querySelectorAll('[data-module-explorer][data-explorer-url]').forEach((container) => {
        container.dataset.pickMode = '0';
        container.dataset.allowMultiSelect = '1';
        if (!container.dataset.viewMode) container.dataset.viewMode = 'list';
        delete container.__fmState;
        reloadExplorer(container);
    });
}

function openContextMenu(container, clientX, clientY, entry) {
    document.querySelectorAll('.fm-context-menu').forEach((node) => node.remove());
    const menu = document.createElement('div');
    menu.className = `fm-context-menu ${isLightTheme() ? 'is-light' : 'is-dark'}`;
    const url = getEntryUrl(container, entry);
    const isDir = entry.kind === 'dir';
    menu.innerHTML = `
        ${!isDir ? `<button type="button" data-cm-action="preview">Preview</button>` : ''}
        ${!isDir ? `<button type="button" data-cm-action="copy-url">Copy URL</button>` : ''}
        <button type="button" data-cm-action="move-copy">Move/Copy</button>
        ${isDir ? `<button type="button" data-cm-action="rename">Rename</button>` : ''}
        <button type="button" data-cm-action="delete" class="is-danger">Delete</button>
    `;
    document.body.appendChild(menu);
    menu.style.left = `${clientX}px`;
    menu.style.top = `${clientY}px`;

    const close = () => menu.remove();
    setTimeout(() => {
        document.addEventListener('click', close, { once: true });
    }, 0);

    menu.querySelector('[data-cm-action="preview"]')?.addEventListener('click', () => {
        openPreview({ name: entry.name, url, type: fileTypeByName(entry.name) });
        close();
    });
    menu.querySelector('[data-cm-action="copy-url"]')?.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(url); showToast('لینک فایل کپی شد.'); } catch (_e) { showToast('کپی نشد.', 'error'); }
        close();
    });
    menu.querySelector('[data-cm-action="move-copy"]')?.addEventListener('click', () => {
        openMoveCopy(container, [entry.path]);
        close();
    });
    menu.querySelector('[data-cm-action="rename"]')?.addEventListener('click', async () => {
        await renameEntry(container, entry.path);
        close();
    });
    menu.querySelector('[data-cm-action="delete"]')?.addEventListener('click', async () => {
        await deleteEntries(container, [entry.path]);
        close();
    });
}

function refreshExplorerTheme() {
    document.querySelectorAll('[data-module-explorer][data-explorer-url], [data-file-picker-explorer][data-explorer-url]').forEach((container) => {
        if (!container.__fmState?.raw) return;
        renderExplorerUI(container);
        attachExplorerEvents(container);
    });
}

function initFileManager() {
    document.addEventListener('click', (event) => {
        const pickerButton = event.target.closest('[data-file-picker]');
        if (pickerButton) {
            openPicker(pickerButton);
            return;
        }

        if (event.target.closest('[data-admin-refresh-explorer]')) {
            initExplorerContainers();
            return;
        }

        if (event.target.closest('[data-file-picker-close]')) {
            closePickerDialog();
        }
    });

    window.addEventListener('admin-theme-changed', () => {
        refreshExplorerTheme();
    });

    initExplorerContainers();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFileManager);
} else {
    initFileManager();
}

window.AdminFileExplorer = {
    reloadExplorer: initExplorerContainers,
    openPicker,
};
