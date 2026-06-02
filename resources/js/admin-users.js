document.addEventListener('DOMContentLoaded', () => {
    // Standard User Modals
    document.querySelectorAll('.user-modal-open').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal-id');
            const modal = document.getElementById(modalId);
            if (modal) modal.showModal();
        });
    });

    document.querySelectorAll('.user-modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal-id');
            const modal = document.getElementById(modalId);
            if (modal) modal.close();
        });
    });

    // Close on backdrop click
    document.querySelectorAll('dialog.admin-dialog').forEach(dialog => {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
    });

    // User Status Toggle
    document.querySelectorAll('.user-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', () => {
            const userId = toggle.getAttribute('data-id');
            const form = document.getElementById(`toggle-form-${userId}`);
            if (form) form.submit();
        });
    });
});
