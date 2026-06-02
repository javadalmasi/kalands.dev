import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
    const listEl = document.getElementById('faq-sortable-list');
    const reorderUrl = listEl?.dataset.reorderUrl || '';
    const saveOrderBtn = document.getElementById('faq-save-order');
    let pendingOrderChanges = false;

    const persistOrder = async () => {
        if (!listEl || !reorderUrl) return;
        const ids = Array.from(listEl.querySelectorAll('.faq-item')).map((item) => Number(item.dataset.faqId));
        if (!ids.length) return;

        await fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ids }),
        });
        pendingOrderChanges = false;
    };

    if (listEl) {
        Sortable.create(listEl, {
            animation: 150,
            handle: '.faq-drag-handle',
            ghostClass: 'opacity-60',
            onEnd: () => {
                pendingOrderChanges = true;
            },
        });
    }

    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', async () => {
            if (!pendingOrderChanges) return;
            await persistOrder();
            window.location.reload();
        });
    }

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('.faq-expand-trigger');
        if (!trigger) return;
        const card = trigger.closest('.faq-item');
        if (!card) return;
        const body = card.querySelector('.faq-expand-body');
        const icon = card.querySelector('.faq-expand-icon');
        if (!body) return;

        body.classList.toggle('hidden');
        if (icon) icon.textContent = body.classList.contains('hidden') ? 'expand_more' : 'expand_less';
    });

    document.querySelectorAll('.faq-quick-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const toggleUrl = btn.dataset.toggleUrl;
            if (!toggleUrl) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = toggleUrl;
            form.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });

    const localSearch = document.getElementById('faq-local-search');
    if (localSearch) {
        localSearch.addEventListener('input', () => {
            const q = localSearch.value.trim().toLowerCase();
            document.querySelectorAll('.faq-item').forEach((item) => {
                const haystack = item.dataset.faqSearch || '';
                item.classList.toggle('hidden', q !== '' && !haystack.includes(q));
            });
        });
    }

    const selectAll = document.getElementById('select-all-faqs');
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.faq-item-checkbox').forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
        });
    }

    const bulkRunBtn = document.getElementById('faq-bulk-run');
    if (bulkRunBtn) {
        bulkRunBtn.addEventListener('click', () => {
            const bulkForm = document.getElementById('faq-bulk-form');
            const bulkAction = document.getElementById('faq-bulk-action');
            const bulkActionInput = document.getElementById('faq-bulk-action-input');
            if (!bulkForm || !bulkAction || !bulkActionInput) return;

            bulkForm.querySelectorAll('input[name="faq_ids[]"]').forEach((el) => el.remove());
            const selectedIds = Array.from(document.querySelectorAll('.faq-item-checkbox:checked')).map((el) => el.value);
            if (!selectedIds.length) return;

            bulkActionInput.value = bulkAction.value;
            selectedIds.forEach((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'faq_ids[]';
                input.value = id;
                bulkForm.appendChild(input);
            });
            bulkForm.submit();
        });
    }
});
