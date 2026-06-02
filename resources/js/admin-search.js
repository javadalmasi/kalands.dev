(function () {
    const trigger = document.getElementById('admin-header-search-trigger');
    const dialog = document.getElementById('admin-search-dialog');
    const input = document.getElementById('admin-search-input');
    const filterSelect = document.getElementById('admin-search-filter');
    const resultsContainer = document.getElementById('admin-search-results');
    const closeBtn = document.querySelector('[data-search-close]');

    if (!trigger || !dialog || !input) return;

    let debounceTimer;
    let selectedIndex = -1;

    const openSearch = () => {
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
            setTimeout(() => input.focus(), 100);
        }
    };

    const closeSearch = () => {
        dialog.close();
    };

    trigger.addEventListener('click', openSearch);
    closeBtn?.addEventListener('click', closeSearch);

    // Shortcut Ctrl+K
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            openSearch();
        }
    });

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = input.value.trim();
        const filter = filterSelect.value;

        if (q.length < 2) {
            renderPlaceholder('عبارت مورد نظر خود را تایپ کنید...');
            return;
        }

        debounceTimer = setTimeout(() => performSearch(q, filter), 300);
    });

    filterSelect.addEventListener('change', () => {
        const q = input.value.trim();
        if (q.length >= 2) {
            performSearch(q, filterSelect.value);
        }
    });

    async function performSearch(q, filter) {
        resultsContainer.innerHTML = '<div class="p-8 text-center text-slate/40"><div class="admin-spinner mx-auto mb-2"></div><p class="text-sm">در حال جستجو...</p></div>';

        try {
            const baseUrl = input.getAttribute('data-url');
            const url = `${baseUrl}?q=${encodeURIComponent(q)}&filter=${filter}`;

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Search failed');

            const data = await response.json();
            renderResults(data.results);
        } catch (error) {
            console.error('Search error:', error);
            renderPlaceholder('خطا در برقراری ارتباط با سرور', 'error_outline');
        }
    }

    function renderResults(results) {
        const resultKeys = Object.keys(results);

        if (resultKeys.length === 0) {
            renderPlaceholder('نتیجه‌ای یافت نشد', 'search_off');
            return;
        }

        let html = '<div class="space-y-4 p-2">';

        for (const key in results) {
            const group = results[key];
            html += `
                <div class="search-group">
                    <h4 class="px-3 py-1 text-[10px] font-bold text-primary uppercase tracking-wider bg-primary/5 rounded mb-2">${group.label}</h4>
                    <div class="space-y-1">
                        ${group.items.map((item, idx) => `
                            <a href="${item.url}" class="search-item flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group" data-index="${selectedIndex++}">
                                <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-white/10 flex items-center justify-center text-slate/50 group-hover:text-primary group-hover:bg-primary/10 transition-colors">
                                    <span class="material-icons">${item.icon}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-slate truncate">${item.title}</p>
                                    <p class="text-[10px] text-slate/60 truncate">${item.description}</p>
                                </div>
                                <span class="material-icons text-slate/20 group-hover:text-primary transition-colors !text-sm">chevron_left</span>
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        html += '</div>';
        resultsContainer.innerHTML = html;
        selectedIndex = -1; // Reset for keyboard nav if implemented later
    }

    function renderPlaceholder(message, icon = 'search') {
        resultsContainer.innerHTML = `
            <div class="p-8 text-center text-slate/40">
                <span class="material-icons !text-4xl mb-2">${icon}</span>
                <p class="text-sm">${message}</p>
            </div>
        `;
    }

    // Keyboard Navigation in results
    input.addEventListener('keydown', (e) => {
        const items = resultsContainer.querySelectorAll('.search-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = (selectedIndex + 1) % items.length;
            updateSelection(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = (selectedIndex - 1 + items.length) % items.length;
            updateSelection(items);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            items[selectedIndex].click();
        }
    });

    function updateSelection(items) {
        items.forEach((item, idx) => {
            if (idx === selectedIndex) {
                item.classList.add('bg-slate-50', 'dark:bg-white/5', 'ring-1', 'ring-primary/30');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-slate-50', 'dark:bg-white/5', 'ring-1', 'ring-primary/30');
            }
        });
    }

})();
