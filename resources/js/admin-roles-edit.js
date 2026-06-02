document.addEventListener('DOMContentLoaded', () => {
    // Select All in Module
    document.querySelectorAll('.select-all-module').forEach(btn => {
        btn.addEventListener('click', () => {
            const moduleSlug = btn.getAttribute('data-module');
            const container = document.getElementById(`module-${moduleSlug}`);
            if (container) {
                const checkboxes = container.querySelectorAll('input[type="checkbox"]');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => cb.checked = !allChecked);
                btn.textContent = !allChecked ? 'لغو انتخاب' : 'انتخاب همه';
            }
        });
    });
});
