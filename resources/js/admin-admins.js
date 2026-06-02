document.addEventListener('DOMContentLoaded', () => {
    // Admin Status Toggle
    document.querySelectorAll('.admin-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', () => {
            const adminId = toggle.getAttribute('data-id');
            const form = document.getElementById(`toggle-form-${adminId}`);
            if (form) form.submit();
        });
    });
});
