document.addEventListener('DOMContentLoaded', () => {
    const tabsContainer = document.querySelector('#artisan-tabs');
    const tabs = tabsContainer ? tabsContainer.querySelectorAll('[data-tab-target]') : [];
    let activeTab = 'tab-commands';

    const switchTab = (target) => {
        tabs.forEach(t => {
            t.className = 'px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2';
        });
        const activeBtn = document.querySelector(`[data-tab-target="${target}"]`);
        if (activeBtn) {
            activeBtn.className = 'px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2';
        }

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const targetEl = document.getElementById(target);
        if (targetEl) targetEl.classList.remove('hidden');
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const target = tab.getAttribute('data-tab-target');
            switchTab(target);
            activeTab = target;
        });
    });

    switchTab(activeTab);

    const modal = document.getElementById('artisan-command-modal');
    const modalTitle = document.getElementById('artisan-modal-title');
    const modalCommand = document.getElementById('artisan-modal-command');
    const modalDescription = document.getElementById('artisan-modal-description');
    const modalIcon = document.getElementById('artisan-modal-icon');
    const modalOutput = document.getElementById('artisan-modal-output');
    const modalOutputText = document.getElementById('artisan-modal-output-text');
    const modalError = document.getElementById('artisan-modal-error');
    const modalErrorText = document.getElementById('artisan-modal-error-text');
    const modalActions = document.getElementById('artisan-modal-actions');
    const modalExecuting = document.getElementById('artisan-modal-executing');
    const executeBtn = document.getElementById('artisan-modal-execute');
    const executeBtnIcon = document.getElementById('artisan-modal-execute-icon');
    const executeBtnText = document.getElementById('artisan-modal-execute-text');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const pathParts = window.location.pathname.split('/');
    const authkeyIndex = pathParts.indexOf('admin') + 1;
    const authkey = authkeyIndex > 0 && authkeyIndex < pathParts.length ? pathParts[authkeyIndex] : '';

    const executeUrl = '/' + ['dash', 'admin', authkey, 'artisan-commands', 'execute'].filter(Boolean).join('/');

    let pendingClose = null;

    document.querySelectorAll('.artisan-command-card').forEach(card => {
        card.addEventListener('click', () => {
            const cmd = {
                command: card.getAttribute('data-command'),
                label: card.getAttribute('data-label'),
                description: card.getAttribute('data-description'),
                icon: card.getAttribute('data-icon'),
                danger: card.getAttribute('data-danger') === 'true',
                warningMessage: card.getAttribute('data-warning-message'),
            };

            if (cmd.danger) {
                const confirmDialog = document.getElementById('admin-confirm-dialog');
                const confirmTitle = document.getElementById('admin-confirm-title');
                const confirmMessage = document.getElementById('admin-confirm-message');
                const confirmSubmit = document.getElementById('admin-confirm-submit');

                if (confirmDialog && typeof confirmDialog.showModal === 'function') {
                    if (confirmTitle) confirmTitle.textContent = 'هشدار: ' + cmd.label;
                    if (confirmMessage) confirmMessage.textContent = cmd.warningMessage || 'آیا از انجام این عملیات اطمینان دارید؟';
                    if (confirmSubmit) confirmSubmit.textContent = 'تایید و ادامه';

                    confirmDialog.showModal();

                    const onConfirm = () => {
                        confirmSubmit.removeEventListener('click', onConfirm);
                        confirmDialog.removeEventListener('close', onClose);
                        openExecutionModal(cmd);
                    };

                    const onClose = () => {
                        confirmSubmit.removeEventListener('click', onConfirm);
                        confirmDialog.removeEventListener('close', onClose);
                    };

                    confirmSubmit.addEventListener('click', onConfirm);
                    confirmDialog.addEventListener('close', onClose);
                } else {
                    if (window.confirm(cmd.warningMessage || 'آیا از انجام این عملیات اطمینان دارید؟')) {
                        openExecutionModal(cmd);
                    }
                }
            } else {
                openExecutionModal(cmd);
            }
        });
    });

    function openExecutionModal(cmd) {
        pendingClose = null;

        modalTitle.textContent = cmd.label;
        modalCommand.textContent = cmd.command;
        modalDescription.textContent = cmd.description;

        modalIcon.textContent = cmd.icon;

        modalOutput.classList.add('hidden');
        modalOutputText.textContent = '';
        modalError.classList.add('hidden');
        modalErrorText.textContent = '';

        executeBtnIcon.textContent = 'play_arrow';
        executeBtnText.textContent = 'تایید و اجرا';
        executeBtn.className = 'admin-btn';

        modalActions.classList.remove('hidden');
        modalExecuting.classList.add('hidden');

        if (typeof modal.showModal === 'function') {
            modal.showModal();
        }
    }

    function closeModal() {
        if (typeof modal.close === 'function') {
            modal.close();
        }
    }

    document.querySelectorAll('[data-artisan-modal-close]').forEach(el => {
        el.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    executeBtn.addEventListener('click', () => {
        if (pendingClose) {
            closeModal();
            return;
        }

        const title = modalTitle.textContent;
        const command = modalCommand.textContent;
        if (!command) return;

        modalActions.classList.add('hidden');
        modalExecuting.classList.remove('hidden');
        modalOutput.classList.add('hidden');
        modalError.classList.add('hidden');

        fetch(executeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ command: command }),
        })
        .then(res => res.json())
        .then(data => {
            modalExecuting.classList.add('hidden');
            modalActions.classList.remove('hidden');

            if (data.output) {
                modalOutput.classList.remove('hidden');
                modalOutputText.textContent = data.output;
            }

            if (data.ok) {
                executeBtnIcon.textContent = 'check_circle';
                executeBtnText.textContent = 'بستن';
                executeBtn.className = 'admin-btn';
            } else {
                modalError.classList.remove('hidden');
                modalErrorText.textContent = data.message || 'خطا در اجرای دستور';
                executeBtnIcon.textContent = 'close';
                executeBtnText.textContent = 'بستن';
                executeBtn.className = 'admin-btn admin-btn-secondary';
            }

            pendingClose = true;
        })
        .catch(() => {
            modalExecuting.classList.add('hidden');
            modalActions.classList.remove('hidden');
            modalError.classList.remove('hidden');
            modalErrorText.textContent = 'خطا در برقراری ارتباط با سرور';
            executeBtnIcon.textContent = 'close';
            executeBtnText.textContent = 'بستن';
            executeBtn.className = 'admin-btn admin-btn-secondary';
            pendingClose = true;
        });
    });

    const logOutputModal = document.getElementById('artisan-log-output-modal');
    const logOutputText = document.getElementById('artisan-log-output-text');

    document.querySelectorAll('.view-log-output').forEach(btn => {
        btn.addEventListener('click', () => {
            const output = btn.getAttribute('data-output');
            if (logOutputText) logOutputText.textContent = output;
            if (logOutputModal && typeof logOutputModal.showModal === 'function') {
                logOutputModal.showModal();
            }
        });
    });

    document.querySelectorAll('[data-log-modal-close]').forEach(el => {
        el.addEventListener('click', () => {
            if (logOutputModal && typeof logOutputModal.close === 'function') {
                logOutputModal.close();
            }
        });
    });

    if (logOutputModal) {
        logOutputModal.addEventListener('click', (e) => {
            if (e.target === logOutputModal) {
                logOutputModal.close();
            }
        });
    }
});