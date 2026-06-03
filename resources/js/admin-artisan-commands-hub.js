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

    const passwordSection = document.getElementById('artisan-modal-password-section');
    const modalPasswordInput = document.getElementById('artisan-modal-password-input');
    const modalPasswordToggle = document.getElementById('artisan-modal-password-toggle');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const pathParts = window.location.pathname.split('/');
    const authkeyIndex = pathParts.indexOf('admin') + 1;
    const authkey = authkeyIndex > 0 && authkeyIndex < pathParts.length ? pathParts[authkeyIndex] : '';

    const executeUrl = '/' + ['dash', 'admin', authkey, 'artisan-commands', 'execute'].filter(Boolean).join('/');
    const verifyPasswordUrl = '/' + ['dash', 'admin', authkey, 'artisan-commands', 'verify-password'].filter(Boolean).join('/');

    let pendingClose = null;
    let currentCmd = null;

    modalPasswordToggle.addEventListener('click', () => {
        const isPassword = modalPasswordInput.type === 'password';
        modalPasswordInput.type = isPassword ? 'text' : 'password';
        modalPasswordToggle.querySelector('.material-icons').textContent = isPassword ? 'visibility' : 'visibility_off';
    });

    document.querySelectorAll('.artisan-command-card').forEach(card => {
        card.addEventListener('click', () => {
            const cmd = {
                command: card.getAttribute('data-command'),
                label: card.getAttribute('data-label'),
                description: card.getAttribute('data-description'),
                icon: card.getAttribute('data-icon'),
                danger: card.getAttribute('data-danger') === 'true',
            };

            currentCmd = cmd;
            openExecutionModal(cmd);
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

        if (cmd.danger) {
            passwordSection.classList.remove('hidden');
            modalPasswordInput.value = '';
            modalPasswordInput.type = 'password';
            const toggleIcon = modalPasswordToggle.querySelector('.material-icons');
            if (toggleIcon) toggleIcon.textContent = 'visibility_off';
        } else {
            passwordSection.classList.add('hidden');
        }

        executeBtnIcon.textContent = 'play_arrow';
        executeBtnText.textContent = 'تایید و اجرا';
        executeBtn.className = 'admin-btn';
        executeBtn.disabled = false;

        modalActions.classList.remove('hidden');
        modalExecuting.classList.add('hidden');

        if (typeof modal.showModal === 'function') {
            modal.showModal();
            if (cmd.danger) {
                setTimeout(() => modalPasswordInput.focus(), 100);
            }
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

    modalPasswordInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            executeBtn.click();
        }
    });

    executeBtn.addEventListener('click', () => {
        if (pendingClose) {
            closeModal();
            return;
        }

        const command = modalCommand.textContent;
        if (!command) return;

        if (currentCmd && currentCmd.danger) {
            const password = modalPasswordInput.value;
            if (!password) {
                modalError.classList.remove('hidden');
                modalErrorText.textContent = 'برای دستورات خطرناک، وارد کردن رمز عبور الزامی است.';
                modalPasswordInput.focus();
                return;
            }

            executeBtn.disabled = true;
            executeBtnIcon.textContent = 'hourglass_empty';
            executeBtnText.textContent = 'در حال بررسی رمز...';
            modalError.classList.add('hidden');

            fetch(verifyPasswordUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ password: password }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    executeBtnIcon.textContent = 'play_arrow';
                    executeBtnText.textContent = 'تایید و اجرا';
                    executeBtn.disabled = false;
                    doExecute(command, password);
                } else {
                    modalError.classList.remove('hidden');
                    modalErrorText.textContent = data.message || 'رمز عبور اشتباه است.';
                    executeBtnIcon.textContent = 'play_arrow';
                    executeBtnText.textContent = 'تایید و اجرا';
                    executeBtn.disabled = false;
                    modalPasswordInput.value = '';
                    modalPasswordInput.focus();
                }
            })
            .catch(() => {
                modalError.classList.remove('hidden');
                modalErrorText.textContent = 'خطا در برقراری ارتباط با سرور.';
                executeBtnIcon.textContent = 'play_arrow';
                executeBtnText.textContent = 'تایید و اجرا';
                executeBtn.disabled = false;
            });
        } else {
            doExecute(command, null);
        }
    });

    function doExecute(command, password) {
        modalActions.classList.add('hidden');
        modalExecuting.classList.remove('hidden');
        modalOutput.classList.add('hidden');
        modalError.classList.add('hidden');

        const body = { command: command };
        if (password) {
            body.password = password;
        }

        fetch(executeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
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
    }

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
