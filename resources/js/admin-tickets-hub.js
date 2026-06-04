document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#ticket-tabs [data-tab-target]');
    const urlParams = new URLSearchParams(window.location.search);
    let activeTab = urlParams.get('tab') || 'tab-moderation';

    if (!document.getElementById(activeTab)) activeTab = 'tab-moderation';

    const switchTab = (target) => {
        tabs.forEach(t => {
            t.className = 'px-6 py-4 text-sm font-medium transition-colors text-slate hover:text-primary flex items-center gap-2';
        });
        const activeBtn = document.querySelector(`[data-tab-target="${target}"]`);
        if(activeBtn) activeBtn.className = 'px-6 py-4 text-sm font-bold transition-colors border-b-2 border-primary text-primary flex items-center gap-2';

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const targetEl = document.getElementById(target);
        if(targetEl) targetEl.classList.remove('hidden');
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            const target = tab.getAttribute('data-tab-target');
            switchTab(target);
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', target);
            window.history.pushState({}, '', newUrl);
        });
    });

    switchTab(activeTab);

    document.getElementById('select-all-tickets')?.addEventListener('change', function (event) {
        document.querySelectorAll('.ticket-item-checkbox').forEach(function (checkbox) {
            checkbox.checked = event.target.checked;
        });
    });

    // Live User Search
    const searchInput = document.getElementById('user-search-input');
    const searchResults = document.getElementById('user-search-results');
    const blockForm = document.getElementById('block-user-form');
    const selectedName = document.getElementById('selected-user-name');
    const selectedMeta = document.getElementById('selected-user-meta');
    const selectedId = document.getElementById('selected-user-id');
    const searchContainer = document.getElementById('user-search-container');
    const cancelBtn = document.getElementById('cancel-selection');

    if (searchInput) {
        const authkey = searchInput.closest('[data-authkey]')?.dataset.authkey;
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`/dash/admin/${authkey}/users?q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) throw new Error('خطا در ارتباط با سرور');
                    return response.json();
                })
                .then(data => {
                    const users = data.data || [];
                    if (users.length === 0) {
                        searchResults.innerHTML = '<div class="p-4 text-center text-xs opacity-50">کاربری یافت نشد.</div>';
                    } else {
                        searchResults.innerHTML = users.map(user => `
                            <div class="p-3 hover:bg-slate/5 cursor-pointer border-b border-slate/5 last:border-0 transition-colors" data-user-id="${user.id}" data-user-name="${user.name}" data-user-meta="${user.email || user.phone || ''}">
                                <p class="text-sm font-bold">${user.name}</p>
                                <p class="text-[10px] opacity-60">${user.email || ''} ${user.phone ? ' | ' + user.phone : ''}</p>
                            </div>
                        `).join('');

                    }
                    searchResults.classList.remove('hidden');
                })
                .catch(() => {
                    searchResults.innerHTML = '<div class="p-4 text-center text-xs opacity-50">خطا در جستجو. مجددا تلاش کنید.</div>';
                    searchResults.classList.remove('hidden');
                });
            }, 300);
        });

        searchResults?.addEventListener('click', function(e) {
            const item = e.target.closest('[data-user-id]');
            if (!item) return;

            const id = item.dataset.userId;
            const name = item.dataset.userName;
            const meta = item.dataset.userMeta;

            selectedId.value = id;
            selectedName.textContent = name;
            selectedMeta.textContent = meta;

            searchContainer.classList.add('hidden');
            blockForm.classList.remove('hidden');
            searchResults.classList.add('hidden');
            searchInput.value = '';
        });

        cancelBtn?.addEventListener('click', function() {
            searchContainer.classList.remove('hidden');
            blockForm.classList.add('hidden');
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchContainer?.contains(e.target)) {
                searchResults?.classList.add('hidden');
            }
        });
    }
});
