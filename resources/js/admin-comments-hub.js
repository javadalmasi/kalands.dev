document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#comment-tabs [data-tab-target]');
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

    document.getElementById('select-all-comments')?.addEventListener('change', function (event) {
        document.querySelectorAll('.comment-item-checkbox').forEach(function (checkbox) {
            checkbox.checked = event.target.checked;
        });
    });
});
