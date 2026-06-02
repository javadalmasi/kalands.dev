document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('[data-tab-target]');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab-target');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById(target).classList.remove('hidden');

            tabs.forEach(t => {
                t.classList.remove('text-primary', 'border-b-2', 'border-primary', 'font-bold');
                t.classList.add('text-slate', 'font-medium');
            });
            tab.classList.add('text-primary', 'border-b-2', 'border-primary', 'font-bold');
            tab.classList.remove('text-slate', 'font-medium');

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', target);
            window.history.pushState({}, '', url);
        });
    });

    // Handle initial tab from URL
    const params = new URLSearchParams(window.location.search);
    const activeTab = params.get('tab');
    if (activeTab) {
        const tabBtn = document.querySelector(`[data-tab-target="${activeTab}"]`);
        if (tabBtn) tabBtn.click();
    }
});
