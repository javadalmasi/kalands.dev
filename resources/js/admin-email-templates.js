document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#email-tabs [data-tab-target]');
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
            if (content.id === targetId) {
                content.classList.remove('hidden');
            } else {
                content.classList.add('hidden');
            }
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

    // Template switching in sidebar
    const templateLinks = document.querySelectorAll('[data-template-tab]');
    templateLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const templateKey = link.getAttribute('data-template-tab');

            // Update active state in sidebar
            templateLinks.forEach(l => {
                if (l === link) {
                    l.classList.add('active');
                } else {
                    l.classList.remove('active');
                }
            });

            // Show selected template panel and hide others
            document.querySelectorAll('[data-template-panel]').forEach(panel => {
                if (panel.getAttribute('data-template-panel') === templateKey) {
                    panel.classList.remove('hidden');
                } else {
                    panel.classList.add('hidden');
                }
            });
        });
    });

    // Live preview functionality
    const updateLivePreview = () => {
        const activePanel = document.querySelector('[data-template-panel]:not(.hidden)');
        if (!activePanel) return;

        const subjectInput = activePanel.querySelector('input[name="subject"]');
        const bodyInput = activePanel.querySelector('textarea[name="body_html"]');

        if (!subjectInput || !bodyInput) return;

        const subject = subjectInput.value || 'بدون عنوان';
        const body = bodyInput.value || '<p>محتوایی برای نمایش وجود ندارد</p>';

        const htmlContent = `
            <!DOCTYPE html>
            <html dir="rtl">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f9fafb; color: #1f2937; line-height: 1.6; }
                    .wrapper { background: #f9fafb; padding: 20px; }
                    .container { max-width: 600px; margin: 0 auto; background: white; }
                    .header { background: #f3f4f6; padding: 20px 30px; border-bottom: 1px solid #e5e7eb; }
                    .header h2 { margin: 0; font-size: 18px; color: #111827; }
                    .content { padding: 30px; }
                    .content h1, .content h2, .content h3 { margin: 20px 0 12px; color: #111827; font-size: 16px; }
                    .content h1 { font-size: 24px; }
                    .content h2 { font-size: 20px; }
                    .content p { margin: 12px 0; }
                    .content ul, .content ol { margin: 12px 0 12px 20px; }
                    .content li { margin: 6px 0; }
                    .content a { color: #2563eb; text-decoration: none; }
                    .content a:hover { text-decoration: underline; }
                    .content strong { font-weight: 600; }
                    .footer { background: #f3f4f6; padding: 20px 30px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #6b7280; }
                </style>
            </head>
            <body>
                <div class="wrapper">
                    <div class="container">
                        <div class="header">
                            <h2>${escapeHtml(subject)}</h2>
                        </div>
                        <div class="content">
                            ${body}
                        </div>
                        <div class="footer">
                            <p>این ایمیل به صورت خودکار ارسال شده است</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        `;

        const iframe = document.getElementById('email-live-preview');
        if (iframe) {
            try {
                iframe.contentDocument.open();
                iframe.contentDocument.write(htmlContent);
                iframe.contentDocument.close();
            } catch (e) {
                console.error('Preview update error:', e);
            }
        }
    };

    // Helper function to escape HTML
    const escapeHtml = (text) => {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Update preview on template panel change
    document.querySelectorAll('[data-template-panel]').forEach(panel => {
        const subjectInput = panel.querySelector('input[name="subject"]');
        const bodyInput = panel.querySelector('textarea[name="body_html"]');

        if (subjectInput) {
            subjectInput.addEventListener('input', updateLivePreview);
        }
        if (bodyInput) {
            bodyInput.addEventListener('input', updateLivePreview);
        }
    });

    // Also update preview when switching templates
    templateLinks.forEach(link => {
        link.addEventListener('click', () => {
            setTimeout(updateLivePreview, 0);
        });
    });

    // Update preview only when in templates-tab
    const observer = new MutationObserver(() => {
        const templatesTab = document.getElementById('templates-tab');
        if (templatesTab && !templatesTab.classList.contains('hidden')) {
            updateLivePreview();
        }
    });

    const emailTabs = document.getElementById('email-tabs');
    if (emailTabs) {
        emailTabs.addEventListener('click', () => {
            setTimeout(() => {
                const templatesTab = document.getElementById('templates-tab');
                if (templatesTab && !templatesTab.classList.contains('hidden')) {
                    updateLivePreview();
                }
            }, 0);
        });
    }

    // Initial preview update
    updateLivePreview();

    // Useful links management
    const addLinkBtn = document.getElementById('add-useful-link');
    const linksContainer = document.getElementById('useful-links-container');
    const MAX_LINKS = 5;

    const createLinkRow = (index = '') => {
        const rowId = index || `new-${Date.now()}`;
        const row = document.createElement('div');
        row.className = 'grid gap-2 grid-cols-1 md:grid-cols-[1fr_1fr_40px] items-end useful-link-row';
        row.innerHTML = `
            <div>
                <label class="text-[10px] text-slate block mb-1">عنوان لینک</label>
                <input name="useful_links[${rowId}][label]" placeholder="مثلا: درباره ما" class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10">
            </div>
            <div>
                <label class="text-[10px] text-slate block mb-1">آدرس (URL)</label>
                <input name="useful_links[${rowId}][url]" placeholder="https://..." class="rounded border border-slate bg-slate p-2 w-full dark:bg-slate-800 dark:text-white dark:border-white/10">
            </div>
            <button type="button" class="remove-link-row h-[42px] flex items-center justify-center text-danger hover:bg-danger/10 rounded transition-colors">
                <span class="material-icons">delete</span>
            </button>
        `;
        return row;
    };

    const updateAddButtonState = () => {
        const rows = document.querySelectorAll('.useful-link-row');
        addLinkBtn.disabled = rows.length >= MAX_LINKS;
        if (rows.length >= MAX_LINKS) {
            addLinkBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            addLinkBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    };

    if (addLinkBtn && linksContainer) {
        addLinkBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (document.querySelectorAll('.useful-link-row').length < MAX_LINKS) {
                const newRow = createLinkRow();
                linksContainer.appendChild(newRow);
                attachRemoveListener(newRow.querySelector('.remove-link-row'));
                updateAddButtonState();
            }
        });
    }

    const attachRemoveListener = (btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            btn.closest('.useful-link-row').remove();
            updateAddButtonState();
        });
    };

    // Attach listeners to existing remove buttons
    document.querySelectorAll('.remove-link-row').forEach(btn => {
        attachRemoveListener(btn);
    });

    updateAddButtonState();

    // Preview toggle functionality
    const previewCard = document.getElementById('live-preview-card');
    const previewContainer = document.getElementById('preview-container');
    const previewToggleBtn = document.getElementById('preview-toggle-btn');
    const previewCloseBtn = document.getElementById('preview-close-btn');
    const templatesGrid = document.getElementById('templates-grid');
    const editorSection = document.getElementById('editor-section');

    if (previewToggleBtn && previewCloseBtn && previewCard) {
        previewToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Show preview card
            previewCard.classList.remove('hidden');
            // Change grid to show editor and preview side by side
            templatesGrid.classList.remove('lg:grid-cols-[280px_1fr]');
            templatesGrid.classList.add('lg:grid-cols-[280px_1fr_400px]');
            // Move preview inside the grid
            const tempDiv = document.createElement('div');
            tempDiv.appendChild(previewCard);
            templatesGrid.appendChild(previewCard);
            previewCard.classList.remove('fixed', 'bottom-6', 'right-6', 'w-96');
            previewCard.classList.add('h-fit', 'sticky', 'top-4');
        });

        previewCloseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            // Hide preview card
            previewCard.classList.add('hidden');
            // Change grid back to normal
            templatesGrid.classList.add('lg:grid-cols-[280px_1fr]');
            templatesGrid.classList.remove('lg:grid-cols-[280px_1fr_400px]');
        });
    }

    // Header/Footer details toggle - keep only one open
    const headerFooterGroups = document.querySelectorAll('.header-footer-group');
    headerFooterGroups.forEach(group => {
        const summary = group.querySelector('summary');
        summary.addEventListener('click', (e) => {
            setTimeout(() => {
                // If this group is opened, close the other
                if (group.hasAttribute('open')) {
                    headerFooterGroups.forEach(otherGroup => {
                        if (otherGroup !== group && otherGroup.hasAttribute('open')) {
                            otherGroup.removeAttribute('open');
                        }
                    });
                }
            }, 0);
        });
    });
});
