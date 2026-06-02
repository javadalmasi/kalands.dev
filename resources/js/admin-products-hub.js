document.addEventListener('DOMContentLoaded', () => {
    // Bulk Selection
    const selectAll = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            productCheckboxes.forEach(cb => cb.checked = e.target.checked);
        });
    }

    // Individual Status Toggle (via AJAX)
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const showToast = window.showToast;

    document.querySelectorAll('.product-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', async () => {
            const productId = toggle.getAttribute('data-id');
            const url = toggle.getAttribute('data-url');
            const labelWrap = toggle.closest('.flex.flex-col');

            toggle.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                });

                const data = await res.json();

                if (data.ok) {
                    showToast('وضعیت محصول تغییر یافت.');
                } else {
                    toggle.checked = !toggle.checked;
                    showToast('خطا در تغییر وضعیت محصول.');
                }
            } catch (err) {
                toggle.checked = !toggle.checked;
                showToast('خطا در ارتباط با سرور.');
            } finally {
                toggle.disabled = false;
            }
        });
    });
});
