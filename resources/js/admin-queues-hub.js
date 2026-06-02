document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#queue-tabs [data-tab-target]');
    const urlParams = new URLSearchParams(window.location.search);
    let activeTab = urlParams.get('tab');

    const switchTab = (targetId) => {
        if (!targetId || !document.getElementById(targetId)) return;
        
        tabs.forEach(t => {
            const isActive = t.getAttribute('data-tab-target') === targetId;
            if (isActive) {
                t.classList.add('border-b-2', 'border-primary', 'text-primary', 'font-bold');
                t.classList.remove('text-slate', 'font-medium');
            } else {
                t.classList.remove('border-b-2', 'border-primary', 'text-primary', 'font-bold');
                t.classList.add('text-slate', 'font-medium');
            }
        });

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.toggle('hidden', content.id !== targetId);
        });
    };

    if (tabs.length > 0) {
        if (!activeTab || !document.getElementById(activeTab)) {
            activeTab = tabs[0].getAttribute('data-tab-target');
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
            e.preventDefault();
                const targetId = tab.getAttribute('data-tab-target');
                switchTab(targetId);
                const newUrl = new URL(window.location);
                newUrl.searchParams.set('tab', targetId);
                window.history.pushState({}, '', newUrl.toString());
            });
        });

        switchTab(activeTab);
    }
});
