document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#error-pages-tabs [data-tab-target]');
    const urlParams = new URLSearchParams(window.location.search);
    let activeTab = urlParams.get('tab') || 'tab-links';

    if (!document.getElementById(activeTab)) activeTab = 'tab-links';

    const switchTab = (target) => {
        tabs.forEach(t => {
            t.classList.remove('border-primary', 'text-primary', 'font-bold', 'border-b-2');
            t.classList.add('text-slate', 'hover:text-primary', 'font-medium');
        });
        const activeBtn = document.querySelector(`[data-tab-target="${target}"]`);
        if (activeBtn) {
            activeBtn.classList.add('border-primary', 'text-primary', 'font-bold', 'border-b-2');
            activeBtn.classList.remove('text-slate', 'hover:text-primary', 'font-medium');
        }

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const targetEl = document.getElementById(target);
        if (targetEl) targetEl.classList.remove('hidden');
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

    // Layout Radio Buttons Interactivity
    const layoutRadios = document.querySelectorAll('input[name="settings[icons_per_row]"]');
    layoutRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            layoutRadios.forEach(r => {
                const label = r.closest('label');
                label.classList.remove('border-primary', 'bg-primary/5', 'text-primary');
                label.classList.add('border-slate/10', 'hover:border-slate/30', 'text-slate/60');
            });

            if (radio.checked) {
                const label = radio.closest('label');
                label.classList.add('border-primary', 'bg-primary/5', 'text-primary');
                label.classList.remove('border-slate/10', 'hover:border-slate/30', 'text-slate/60');
            }
        });
    });

    // Icon Picker Logic
    window.currentLinkIndex = null;
    window.iconsList = [
        'smartphone', 'laptop', 'tablet_mac', 'support_agent', 'home', 'search', 'shopping_cart', 'person',
        'settings', 'notifications', 'favorite', 'share', 'delete', 'edit', 'check_circle', 'error',
        'info', 'help', 'arrow_back', 'arrow_forward', 'menu', 'close', 'email', 'phone',
        'location_on', 'calendar_today', 'star', 'thumb_up', 'visibility', 'lock', 'cloud_upload', 'download',
        'camera_alt', 'videocam', 'mic', 'volume_up', 'play_arrow', 'pause', 'stop', 'replay',
        'headset', 'mouse', 'keyboard', 'memory', 'router', 'tv', 'watch', 'devices',
        'local_shipping', 'inventory_2', 'payments', 'account_balance', 'credit_card', 'receipt', 'assessment', 'campaign',
        'forum', 'chat', 'contact_support', 'live_help', 'description', 'article', 'folder', 'attachment',
        'build', 'construction', 'engineering', 'science', 'psychology', 'public', 'language',
        'school', 'history_edu', 'military_tech', 'emoji_events', 'celebration', 'cake', 'restaurant', 'coffee',
        'directions_car', 'flight', 'train', 'hotel', 'medical_services', 'local_hospital', 'security', 'shield'
    ];

    window.openIconPicker = function(index) {
        window.currentLinkIndex = index;
        renderIcons(window.iconsList);
        document.getElementById('icon-picker-modal').showModal();
        document.getElementById('icon-search').value = '';
    };

    function renderIcons(list) {
        const grid = document.getElementById('icons-grid');
        if (!grid) return;
        grid.innerHTML = list.map(icon => `
            <button type="button" onclick="selectIcon('${icon}')" class="flex flex-col items-center gap-1 p-2 hover:bg-primary/10 rounded transition-colors group">
                <span class="material-symbols-outlined text-2xl text-slate-400 group-hover:text-primary">${icon}</span>
                <span class="text-[8px] text-slate-400 truncate w-full">${icon}</span>
            </button>
        `).join('');
    }

    window.filterIcons = function(query) {
        const filtered = window.iconsList.filter(icon => icon.toLowerCase().includes(query.toLowerCase()));
        renderIcons(filtered);
    };

    window.selectIcon = function(icon) {
        if (window.currentLinkIndex !== null) {
            document.getElementById(`icon-input-${window.currentLinkIndex}`).value = icon;
            const preview = document.querySelector(`.icon-preview-${window.currentLinkIndex}`);
            if (preview) preview.innerText = icon;
            document.getElementById('icon-picker-modal').close();
        }
    };
});
