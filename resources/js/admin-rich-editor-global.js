function isAdminTextArea(textarea) {
    if (!(textarea instanceof HTMLTextAreaElement)) return false;
    if (!textarea.hasAttribute('data-rich-editor')) return false;
    if (textarea.dataset.noEditor === '1') return false;
    if (textarea.dataset.editorDisabled === '1') return false;
    if (textarea.dataset.enhancedEditor === '1') return false;
    if (!textarea.closest('.admin-content')) return false;
    return true;
}

function escapeHtml(value) {
    return String(value || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');
}

function escapeAttribute(value) {
    return String(value || '')
        .replaceAll('&', '&amp;')
        .replaceAll('"', '&quot;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');
}

function triggerInput(textarea) {
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.dispatchEvent(new Event('change', { bubbles: true }));
}

function focusEditable(editable) {
    editable.focus({ preventScroll: true });
}

function runCommand(editable, command, value = null) {
    focusEditable(editable);
    try {
        document.execCommand(command, false, value);
    } catch (_error) {
        return false;
    }
    editable.dispatchEvent(new Event('input', { bubbles: true }));
    return true;
}

function formatBlock(editable, block) {
    const value = String(block || 'p').toLowerCase();
    if (['p', 'h1', 'h2', 'h3', 'h4', 'pre', 'blockquote'].includes(value)) {
        return runCommand(editable, 'formatBlock', `<${value}>`);
    }
    return runCommand(editable, 'formatBlock', value);
}

function createButton(icon, title, onClick, stateCommand = '') {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'editor-tool admin-editor-control';
    button.title = title;
    button.innerHTML = `<span class="material-icons">${icon}</span>`;
    if (stateCommand) {
        button.dataset.cmdState = stateCommand;
    }
    button.addEventListener('mousedown', (event) => event.preventDefault());
    button.addEventListener('click', onClick);
    return button;
}

function createSelect(options, title, onChange) {
    const select = document.createElement('select');
    select.title = title;
    select.className = 'editor-select admin-editor-control';
    select.innerHTML = options.map((opt) => `<option value="${opt.value}">${opt.label}</option>`).join('');
    select.addEventListener('change', () => onChange(select.value));
    return select;
}

function resolvePickerContext(textarea) {
    const nearestPicker = textarea.closest('form, .admin-card, .admin-content')?.querySelector('[data-file-picker][data-explorer-url][data-files-url]');
    const firstPicker = document.querySelector('[data-file-picker][data-explorer-url][data-files-url]');
    const source = nearestPicker || firstPicker;

    return {
        explorerUrl: source?.dataset.explorerUrl || '',
        filesUrl: source?.dataset.filesUrl || '',
        cdnBaseUrl: source?.dataset.cdnBaseUrl || '',
    };
}

function openTextDialog({ title, placeholder, submitText = 'ثبت', onSubmit }) {
    const dialog = document.getElementById('admin-explorer-input-dialog');
    const titleNode = document.getElementById('admin-explorer-input-title');
    const inputNode = document.getElementById('admin-explorer-input-value');
    const submitBtn = document.getElementById('admin-explorer-input-submit');
    if (!dialog || !titleNode || !inputNode || !submitBtn) return;

    titleNode.textContent = title;
    inputNode.placeholder = placeholder || '';
    inputNode.value = '';
    submitBtn.innerHTML = `<span class="material-icons">check_circle</span><span>${escapeHtml(submitText)}</span>`;

    dialog.querySelectorAll('[data-explorer-input-close]').forEach((button) => {
        button.onclick = () => dialog.close();
    });

    submitBtn.onclick = () => {
        const value = inputNode.value.trim();
        if (!value) return;
        dialog.close();
        onSubmit(value);
    };

    dialog.showModal();
    setTimeout(() => inputNode.focus(), 10);
}

function buildIframeHtml(rawValue) {
    const value = String(rawValue || '').trim();
    if (!value) return '';

    if (/^<iframe[\s>]/i.test(value)) {
        return value;
    }

    const safeUrl = escapeAttribute(value);
    return `<iframe src="${safeUrl}" style="width:100%;min-height:360px;border:0;border-radius:10px" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>`;
}

function mediaTypeFromUrl(rawUrl) {
    const clean = String(rawUrl || '').split('?')[0].split('#')[0].toLowerCase();
    const ext = clean.includes('.') ? clean.split('.').pop() : '';
    if (['jpg', 'jpeg', 'png', 'webp', 'avif', 'apng', 'gif', 'bmp', 'svg'].includes(ext)) return 'image';
    if (['mp4', 'webm', 'mov', 'mkv', 'avi'].includes(ext)) return 'video';
    if (['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac'].includes(ext)) return 'audio';
    return 'file';
}

function ensureMediaSizeDialog() {
    let dialog = document.getElementById('admin-editor-media-size-dialog');
    if (dialog) return dialog;
    dialog = document.createElement('dialog');
    dialog.id = 'admin-editor-media-size-dialog';
    dialog.className = 'admin-dialog w-[min(100vw-16px,520px)] max-w-[520px]';
    dialog.innerHTML = `
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title">تنظیم اندازه رسانه</h3>
                <button type="button" class="admin-toggle inline-flex" data-ms-close><span class="material-icons">close</span></button>
            </div>
            <div class="grid gap-2 md:grid-cols-2">
                <label class="text-sm">عرض (px)<input class="admin-dialog-input" id="ms-width" type="number" min="1"></label>
                <label class="text-sm">ارتفاع (px)<input class="admin-dialog-input" id="ms-height" type="number" min="1"></label>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" id="ms-lock" checked> قفل نسبت ابعاد</label>
            <label class="text-sm hidden" id="ms-aspect-wrap">نسبت تصویر
                <select class="admin-dialog-input" id="ms-aspect">
                    <option value="">پیش‌فرض</option>
                    <option value="16/9">16/9</option>
                    <option value="1/1">1/1</option>
                    <option value="4/3">4/3</option>
                </select>
            </label>
            <div class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-ms-close><span class="material-icons">close</span><span>انصراف</span></button>
                <button type="button" class="admin-btn" id="ms-submit"><span class="material-icons">check_circle</span><span>درج</span></button>
            </div>
        </div>
    `;
    document.body.appendChild(dialog);
    return dialog;
}

function readMediaMeta(url, type) {
    return new Promise((resolve) => {
        if (type === 'image') {
            const img = new Image();
            img.onload = () => resolve({ width: img.naturalWidth || 0, height: img.naturalHeight || 0 });
            img.onerror = () => resolve({ width: 0, height: 0 });
            img.src = url;
            return;
        }
        if (type === 'video') {
            const video = document.createElement('video');
            video.preload = 'metadata';
            video.onloadedmetadata = () => resolve({ width: video.videoWidth || 0, height: video.videoHeight || 0 });
            video.onerror = () => resolve({ width: 0, height: 0 });
            video.src = url;
            return;
        }
        resolve({ width: 0, height: 0 });
    });
}

function openMediaSizeDialog({ type, width, height, onSubmit }) {
    const dialog = ensureMediaSizeDialog();
    const widthInput = dialog.querySelector('#ms-width');
    const heightInput = dialog.querySelector('#ms-height');
    const lockInput = dialog.querySelector('#ms-lock');
    const aspectWrap = dialog.querySelector('#ms-aspect-wrap');
    const aspectInput = dialog.querySelector('#ms-aspect');
    const submitBtn = dialog.querySelector('#ms-submit');
    if (!widthInput || !heightInput || !lockInput || !submitBtn || !aspectWrap || !aspectInput) return;

    widthInput.value = String(width || (type === 'video' ? 640 : 800));
    heightInput.value = String(height || (type === 'video' ? 360 : 600));
    lockInput.checked = true;
    aspectInput.value = '';
    aspectWrap.classList.toggle('hidden', type !== 'video');

    const ratio = Number(widthInput.value) > 0 && Number(heightInput.value) > 0 ? Number(widthInput.value) / Number(heightInput.value) : null;
    const applyLock = (source) => {
        if (!lockInput.checked || !ratio) return;
        if (source === 'w') heightInput.value = String(Math.max(1, Math.round(Number(widthInput.value || 0) / ratio)));
        if (source === 'h') widthInput.value = String(Math.max(1, Math.round(Number(heightInput.value || 0) * ratio)));
    };
    widthInput.oninput = () => applyLock('w');
    heightInput.oninput = () => applyLock('h');
    aspectInput.onchange = () => {
        if (!aspectInput.value) return;
        const parts = aspectInput.value.split('/').map(Number);
        if (parts.length !== 2 || !parts[0] || !parts[1]) return;
        const w = Number(widthInput.value || 0) || 640;
        const h = Math.max(1, Math.round((w * parts[1]) / parts[0]));
        heightInput.value = String(h);
    };

    dialog.querySelectorAll('[data-ms-close]').forEach((b) => { b.onclick = () => dialog.close(); });
    submitBtn.onclick = () => {
        onSubmit({
            width: Math.max(1, Number(widthInput.value || 0) || 1),
            height: Math.max(1, Number(heightInput.value || 0) || 1),
            aspect: aspectInput.value || '',
        });
        dialog.close();
    };
    dialog.showModal();
}

function mediaEmbedHtml(url, options = {}) {
    const safe = escapeAttribute(url);
    const type = mediaTypeFromUrl(url);
    if (type === 'image') {
        const width = Number(options.width || 0);
        const height = Number(options.height || 0);
        const style = width && height ? `width:${width}px;height:${height}px;max-width:100%;object-fit:cover` : 'max-width:100%;height:auto';
        return `<img src="${safe}" alt="" style="${style}" />`;
    }
    if (type === 'video') {
        const width = Number(options.width || 0);
        const height = Number(options.height || 0);
        const aspect = options.aspect ? `aspect-ratio:${options.aspect};` : '';
        const style = width && height ? `width:${width}px;height:${height}px;max-width:100%;${aspect}` : `max-width:100%;height:auto;${aspect}`;
        return `<video controls style="${style}"><source src="${safe}"></video>`;
    }
    if (type === 'audio') {
        return `<audio controls style="width:100%"><source src="${safe}"></audio>`;
    }
    return `<a href="${safe}" target="_blank" rel="noopener noreferrer">دانلود فایل</a>`;
}

function openFilePicker({ explorerUrl, filesUrl, cdnBaseUrl, onPick }) {
    if (!explorerUrl || !filesUrl) {
        openTextDialog({
            title: 'ورود دستی لینک فایل',
            placeholder: 'https://example.com/file.png',
            onSubmit: onPick,
        });
        return;
    }

    const tempInput = document.createElement('input');
    tempInput.type = 'text';
    tempInput.id = `admin-editor-pick-${Math.random().toString(36).slice(2)}`;
    tempInput.className = 'hidden';
    document.body.appendChild(tempInput);

    const tempBtn = document.createElement('button');
    tempBtn.type = 'button';
    tempBtn.dataset.filePicker = '1';
    tempBtn.dataset.explorerUrl = explorerUrl;
    tempBtn.dataset.filesUrl = filesUrl;
    tempBtn.dataset.cdnBaseUrl = cdnBaseUrl || '';
    tempBtn.dataset.filePickTarget = `#${tempInput.id}`;

    const listener = (event) => {
        const url = event.detail?.url || '';
        if (url) onPick(url);
        window.removeEventListener('admin-file-picked', listener);
        tempInput.remove();
    };

    window.addEventListener('admin-file-picked', listener);

    if (window.AdminFileExplorer?.openPicker) {
        window.AdminFileExplorer.openPicker(tempBtn);
    } else {
        window.removeEventListener('admin-file-picked', listener);
        tempInput.remove();
        openTextDialog({
            title: 'ورود دستی لینک فایل',
            placeholder: 'https://example.com/file.png',
            onSubmit: onPick,
        });
    }
}

function saveSelection(editable) {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0) return null;
    const range = selection.getRangeAt(0);
    const commonAncestor = range.commonAncestorContainer;
    if (!editable.contains(commonAncestor)) return null;
    return range.cloneRange();
}

function restoreSelection(editable, range) {
    if (!range) {
        focusEditable(editable);
        return;
    }
    const selection = window.getSelection();
    if (!selection) return;
    selection.removeAllRanges();
    selection.addRange(range);
    focusEditable(editable);
}

function selectFirstMatch(editable, query) {
    if (!query) return false;
    const needle = query.toLowerCase();
    const walker = document.createTreeWalker(editable, NodeFilter.SHOW_TEXT);

    while (walker.nextNode()) {
        const node = walker.currentNode;
        const text = String(node.textContent || '');
        const index = text.toLowerCase().indexOf(needle);
        if (index === -1) continue;

        const range = document.createRange();
        range.setStart(node, index);
        range.setEnd(node, index + query.length);

        const selection = window.getSelection();
        if (!selection) return false;
        selection.removeAllRanges();
        selection.addRange(range);
        focusEditable(editable);
        return true;
    }

    return false;
}

function updateToolbarState(toolbar) {
    toolbar.querySelectorAll('[data-cmd-state]').forEach((button) => {
        const cmd = button.dataset.cmdState;
        let active = false;
        try {
            active = document.queryCommandState(cmd);
        } catch (_error) {
            active = false;
        }
        button.classList.toggle('is-active', !!active);
    });
}

function attachAdvancedEditor(textarea) {
    if (textarea.dataset.enhancedEditor === '1') return;
    textarea.dataset.enhancedEditor = '1';

    const context = resolvePickerContext(textarea);

    const wrapper = document.createElement('div');
    wrapper.className = 'editor-shell';

    const toolbarMain = document.createElement('div');
    toolbarMain.className = 'editor-toolbar editor-toolbar-main';

    const toolbarSecondary = document.createElement('div');
    toolbarSecondary.className = 'editor-toolbar editor-toolbar-secondary';

    const editable = document.createElement('div');
    editable.contentEditable = 'true';
    editable.className = 'editor-editable';
    editable.innerHTML = textarea.value || '';

    const sourceArea = document.createElement('textarea');
    sourceArea.className = 'editor-source hidden';

    const headingSelect = createSelect([
        { value: 'p', label: 'Paragraph' },
        { value: 'h1', label: 'Heading 1' },
        { value: 'h2', label: 'Heading 2' },
        { value: 'h3', label: 'Heading 3' },
        { value: 'h4', label: 'Heading 4' },
        { value: 'pre', label: 'Code Block' },
    ], 'Heading', (value) => formatBlock(editable, value));

    const fontSelect = createSelect([
        { value: '', label: 'Font' },
        { value: 'Tahoma', label: 'Tahoma' },
        { value: 'Vazirmatn', label: 'Vazirmatn' },
        { value: 'Arial', label: 'Arial' },
        { value: 'monospace', label: 'Monospace' },
    ], 'Font', (value) => {
        if (!value) return;
        runCommand(editable, 'fontName', value);
    });

    const sizeSelect = createSelect([
        { value: '', label: 'Size' },
        { value: '1', label: '10' },
        { value: '2', label: '12' },
        { value: '3', label: '14' },
        { value: '4', label: '16' },
        { value: '5', label: '18' },
        { value: '6', label: '22' },
        { value: '7', label: '28' },
    ], 'Size', (value) => {
        if (!value) return;
        runCommand(editable, 'fontSize', value);
    });

    const textColor = document.createElement('input');
    textColor.type = 'color';
    textColor.className = 'editor-color admin-editor-control';
    textColor.title = 'رنگ متن';
    textColor.addEventListener('input', () => runCommand(editable, 'foreColor', textColor.value));

    const bgColor = document.createElement('input');
    bgColor.type = 'color';
    bgColor.className = 'editor-color admin-editor-control';
    bgColor.title = 'رنگ پس‌زمینه';
    bgColor.addEventListener('input', () => runCommand(editable, 'hiliteColor', bgColor.value));

    let sourceMode = false;
    let sourceToggleButton = null;
    const toggleSourceMode = () => {
        sourceMode = !sourceMode;
        if (sourceMode) {
            sourceArea.value = editable.innerHTML;
            editable.classList.add('hidden');
            sourceArea.classList.remove('hidden');
            sourceArea.focus();
        } else {
            editable.innerHTML = sourceArea.value;
            sourceArea.classList.add('hidden');
            editable.classList.remove('hidden');
            focusEditable(editable);
        }
        textarea.value = sourceMode ? sourceArea.value : editable.innerHTML;
        triggerInput(textarea);
        if (sourceToggleButton) {
            sourceToggleButton.classList.toggle('is-active', sourceMode);
        }
    };

    const currentSelection = () => saveSelection(editable);

    toolbarMain.append(
        createButton('undo', 'Undo', () => runCommand(editable, 'undo')),
        createButton('redo', 'Redo', () => runCommand(editable, 'redo')),
        createButton('content_copy', 'Copy', () => runCommand(editable, 'copy')),
        createButton('content_cut', 'Cut', () => runCommand(editable, 'cut')),
        createButton('content_paste', 'Paste text', () => {
            const range = currentSelection();
            openTextDialog({
                title: 'چسباندن متن',
                placeholder: 'متن را اینجا وارد کنید',
                submitText: 'درج',
                onSubmit: (text) => {
                    restoreSelection(editable, range);
                    runCommand(editable, 'insertText', text);
                },
            });
        }),
        createButton('format_bold', 'Bold', () => runCommand(editable, 'bold'), 'bold'),
        createButton('format_italic', 'Italic', () => runCommand(editable, 'italic'), 'italic'),
        createButton('format_underlined', 'Underline', () => runCommand(editable, 'underline'), 'underline'),
        createButton('strikethrough_s', 'Strike', () => runCommand(editable, 'strikeThrough'), 'strikeThrough'),
        createButton('superscript', 'Superscript', () => runCommand(editable, 'superscript')),
        createButton('subscript', 'Subscript', () => runCommand(editable, 'subscript')),
        createButton('format_list_bulleted', 'Bulleted list', () => runCommand(editable, 'insertUnorderedList'), 'insertUnorderedList'),
        createButton('format_list_numbered', 'Numbered list', () => runCommand(editable, 'insertOrderedList'), 'insertOrderedList'),
        createButton('checklist', 'Checklist', () => runCommand(editable, 'insertHTML', '<ul><li><input type="checkbox"> آیتم</li></ul><p></p>')),
        createButton('format_indent_increase', 'Indent', () => runCommand(editable, 'indent')),
        createButton('format_indent_decrease', 'Outdent', () => runCommand(editable, 'outdent')),
        createButton('format_align_right', 'Align right', () => runCommand(editable, 'justifyRight'), 'justifyRight'),
        createButton('format_align_left', 'Align left', () => runCommand(editable, 'justifyLeft'), 'justifyLeft'),
        createButton('format_align_center', 'Align center', () => runCommand(editable, 'justifyCenter'), 'justifyCenter'),
        createButton('format_align_justify', 'Justify', () => runCommand(editable, 'justifyFull'), 'justifyFull'),
        createButton('format_quote', 'Block quote', () => formatBlock(editable, 'blockquote')),
        createButton('horizontal_rule', 'Horizontal line', () => runCommand(editable, 'insertHorizontalRule')),
        createButton('table_chart', 'Insert table', () => runCommand(editable, 'insertHTML', '<table style="width:100%;border-collapse:collapse"><tr><th style="border:1px solid #94a3b8;padding:6px">Header 1</th><th style="border:1px solid #94a3b8;padding:6px">Header 2</th></tr><tr><td style="border:1px solid #94a3b8;padding:6px">Cell</td><td style="border:1px solid #94a3b8;padding:6px">Cell</td></tr></table><p></p>')),
        createButton('code', 'Code block', () => {
            const selected = window.getSelection()?.toString() || 'code';
            runCommand(editable, 'insertHTML', `<pre style="background:#0f172a;color:#e2e8f0;padding:10px;border-radius:8px;direction:ltr;text-align:left"><code>${escapeHtml(selected)}</code></pre><p></p>`);
        }),
        createButton('link', 'Insert link', () => {
            const range = currentSelection();
            openTextDialog({
                title: 'درج لینک',
                placeholder: 'https://example.com',
                onSubmit: (url) => {
                    restoreSelection(editable, range);
                    runCommand(editable, 'createLink', url);
                },
            });
        }),
        createButton('link_off', 'Remove link', () => runCommand(editable, 'unlink')),
        createButton('image', 'Insert image', () => {
            const range = currentSelection();
            openFilePicker({
                ...context,
                onPick: async (url) => {
                    const type = mediaTypeFromUrl(url);
                    if (type === 'image' || type === 'video') {
                        const meta = await readMediaMeta(url, type);
                        openMediaSizeDialog({
                            type,
                            width: meta.width,
                            height: meta.height,
                            onSubmit: (sizing) => {
                                restoreSelection(editable, range);
                                runCommand(editable, 'insertHTML', mediaEmbedHtml(url, sizing));
                            },
                        });
                        return;
                    }
                    restoreSelection(editable, range);
                    runCommand(editable, 'insertHTML', mediaEmbedHtml(url));
                },
            });
        }),
        createButton('attach_file', 'Insert file link', () => {
            const range = currentSelection();
            openFilePicker({
                ...context,
                onPick: (url) => {
                    restoreSelection(editable, range);
                    const safe = escapeAttribute(url);
                    runCommand(editable, 'insertHTML', `<a href="${safe}" target="_blank" rel="noopener noreferrer">دانلود فایل</a>`);
                },
            });
        }),
        createButton('video_library', 'Embed media (iframe)', () => {
            const range = currentSelection();
            openTextDialog({
                title: 'لینک یا iframe رسانه',
                placeholder: 'https://... یا <iframe ...></iframe>',
                onSubmit: (value) => {
                    restoreSelection(editable, range);
                    const iframe = buildIframeHtml(value);
                    if (!iframe) return;
                    runCommand(editable, 'insertHTML', `${iframe}<p></p>`);
                },
            });
        }),
        createButton('search', 'Find', () => {
            openTextDialog({
                title: 'جستجو در متن',
                placeholder: 'عبارت مورد نظر',
                submitText: 'پیدا کن',
                onSubmit: (query) => {
                    const found = selectFirstMatch(editable, query);
                    if (!found) {
                        window.alert('عبارت موردنظر پیدا نشد.');
                    }
                },
            });
        }),
        createButton('format_clear', 'Clear format', () => runCommand(editable, 'removeFormat')),
    );
    sourceToggleButton = createButton('source', 'HTML source', toggleSourceMode);
    toolbarMain.append(sourceToggleButton);

    toolbarSecondary.append(
        headingSelect,
        fontSelect,
        sizeSelect,
        textColor,
        bgColor,
        createButton('fullscreen', 'Fullscreen', () => {
            wrapper.classList.toggle('fixed');
            wrapper.classList.toggle('inset-2');
            wrapper.classList.toggle('z-[80]');
            wrapper.classList.toggle('max-h-[95vh]');
            editable.classList.toggle('max-h-[70vh]');
            editable.classList.toggle('overflow-auto');
            sourceArea.classList.toggle('max-h-[70vh]');
            sourceArea.classList.toggle('overflow-auto');
        }),
        createButton('done_all', 'Select all', () => runCommand(editable, 'selectAll')),
        createButton('remove_red_eye', 'Preview HTML', () => {
            const html = sourceMode ? sourceArea.value : editable.innerHTML;
            const preview = document.getElementById('admin-explorer-preview-body');
            const title = document.getElementById('admin-explorer-preview-title');
            const dialog = document.getElementById('admin-explorer-preview-dialog');
            if (!preview || !title || !dialog) return;
            title.textContent = 'Preview HTML';
            preview.innerHTML = `<div class="admin-preview-html">${html}</div>`;
            dialog.querySelectorAll('[data-explorer-preview-close]').forEach((button) => {
                button.onclick = () => dialog.close();
            });
            dialog.showModal();
        }),
    );

    textarea.style.display = 'none';
    textarea.parentNode.insertBefore(wrapper, textarea);
    wrapper.appendChild(toolbarMain);
    wrapper.appendChild(toolbarSecondary);
    wrapper.appendChild(editable);
    wrapper.appendChild(sourceArea);

    editable.addEventListener('input', () => {
        textarea.value = editable.innerHTML;
        sourceArea.value = editable.innerHTML;
        triggerInput(textarea);
        updateToolbarState(wrapper);
    });

    editable.addEventListener('mouseup', () => updateToolbarState(wrapper));
    editable.addEventListener('keyup', () => updateToolbarState(wrapper));
    editable.addEventListener('focus', () => updateToolbarState(wrapper));

    sourceArea.addEventListener('input', () => {
        textarea.value = sourceArea.value;
        triggerInput(textarea);
    });

    editable.addEventListener('keydown', (event) => {
        const mod = event.ctrlKey || event.metaKey;
        if (!mod) return;
        const key = event.key.toLowerCase();

        if (key === 'b') { event.preventDefault(); runCommand(editable, 'bold'); return; }
        if (key === 'i') { event.preventDefault(); runCommand(editable, 'italic'); return; }
        if (key === 'u') { event.preventDefault(); runCommand(editable, 'underline'); return; }
        if (key === 'k') {
            event.preventDefault();
            const range = currentSelection();
            openTextDialog({
                title: 'درج لینک',
                placeholder: 'https://example.com',
                onSubmit: (url) => {
                    restoreSelection(editable, range);
                    runCommand(editable, 'createLink', url);
                },
            });
            return;
        }
        if (key === 'z' && !event.shiftKey) { event.preventDefault(); runCommand(editable, 'undo'); return; }
        if ((key === 'y') || (key === 'z' && event.shiftKey)) { event.preventDefault(); runCommand(editable, 'redo'); return; }
        if (key === 's') {
            event.preventDefault();
            textarea.value = sourceMode ? sourceArea.value : editable.innerHTML;
            triggerInput(textarea);
            return;
        }
        if (key === 'f') {
            event.preventDefault();
            openTextDialog({
                title: 'جستجو در متن',
                placeholder: 'عبارت مورد نظر',
                submitText: 'پیدا کن',
                onSubmit: (query) => {
                    const found = selectFirstMatch(editable, query);
                    if (!found) window.alert('عبارت موردنظر پیدا نشد.');
                },
            });
            return;
        }
        if (key === 'a') { event.preventDefault(); runCommand(editable, 'selectAll'); return; }
    });

    sourceArea.addEventListener('keydown', (event) => {
        const mod = event.ctrlKey || event.metaKey;
        if (!mod) return;
        if (event.key.toLowerCase() === 's') {
            event.preventDefault();
            textarea.value = sourceArea.value;
            triggerInput(textarea);
        }
    });

    updateToolbarState(wrapper);
}

function initAdminEditors(root = document) {
    root.querySelectorAll('textarea').forEach((textarea) => {
        if (isAdminTextArea(textarea)) {
            attachAdvancedEditor(textarea);
        }
    });
}

function observeEditorTargets() {
    const observer = new MutationObserver((records) => {
        records.forEach((record) => {
            record.addedNodes.forEach((node) => {
                if (!(node instanceof HTMLElement)) return;
                if (node.matches('textarea')) {
                    initAdminEditors(node.parentElement || node);
                    return;
                }
                if (node.querySelector('textarea')) {
                    initAdminEditors(node);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initAdminEditors();
        observeEditorTargets();
    });
} else {
    initAdminEditors();
    observeEditorTargets();
}

window.AdminRichEditor = {
    init: initAdminEditors,
};
