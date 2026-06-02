(function() {
    const TABS_WITH_FILTERS = new Set([
        'tab-overview', 'tab-reports', 'tab-content',
        'tab-search', 'tab-goals', 'tab-users', 'tab-errors'
    ]);

    const filterCard = document.getElementById('analytics-filter-card');
    const filterToggle = document.getElementById('analytics-filter-toggle');
    const filterSecondary = document.getElementById('analytics-filter-secondary');
    const filterToggleIcon = document.getElementById('analytics-filter-toggle-icon');
    const filterToggleText = document.getElementById('analytics-filter-toggle-text');
    let filterExpanded = false;

    const updateFilterVisibility = (tabId) => {
        if (!filterCard) return;
        if (TABS_WITH_FILTERS.has(tabId)) {
            filterCard.style.display = '';
        } else {
            filterCard.style.display = 'none';
        }
    };

    const toggleFilters = () => {
        filterExpanded = !filterExpanded;
        filterSecondary.classList.toggle('hidden', !filterExpanded);
        filterToggleIcon.textContent = filterExpanded ? 'expand_less' : 'expand_more';
        filterToggleText.textContent = filterExpanded ? 'کمتر' : 'بیشتر';
    };

    if (filterToggle) {
        filterToggle.addEventListener('click', toggleFilters);
    }

    const switchTabContent = (targetId) => {
        document.querySelectorAll('.analytics-tab-content').forEach((el) => {
            el.classList.toggle('hidden', el.id !== targetId);
        });
        document.querySelectorAll('#analytics-tabs [data-tab-target]').forEach((btn) => {
            const isActive = btn.getAttribute('data-tab-target') === targetId;
            btn.classList.toggle('text-success', isActive);
            btn.classList.toggle('border-success', isActive);
            btn.classList.toggle('font-bold', isActive);
            btn.classList.toggle('font-medium', !isActive);
            btn.classList.toggle('text-slate', !isActive);
            btn.classList.toggle('hover:text-success', !isActive);
        });
    };

    const dispatchTabEvent = (targetId) => {
        if (!targetId) return;
        switchTabContent(targetId);
        updateFilterVisibility(targetId);
        window.dispatchEvent(new CustomEvent('analytics-tab-switched', { detail: { tab: targetId } }));
    };

    const initAnalyticsTabs = () => {
        const tabs = document.querySelectorAll('#analytics-tabs [data-tab-target]');
        if (!tabs.length) return;

        const urlParams = new URLSearchParams(window.location.search);
        let activeTab = urlParams.get('tab');

        if (!activeTab || !document.getElementById(activeTab)) {
            activeTab = tabs[0].getAttribute('data-tab-target');
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', (event) => {
                const targetId = tab.getAttribute('data-tab-target');
                dispatchTabEvent(targetId);
            });
        });

        switchTabContent(activeTab);
        updateFilterVisibility(activeTab);
        dispatchTabEvent(activeTab);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnalyticsTabs);
    } else {
        initAnalyticsTabs();
    }
})();
