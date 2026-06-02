document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('#home-tabs [data-tab-target]');
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

    const handleCatItems = (type) => {
            const container = document.getElementById(`cat-items-${type}`);
            const btn = document.querySelector(`[data-add-cat-item="${type}"]`);
            if(!container || !btn) return;
    
            btn.addEventListener('click', () => {
                const idx = container.querySelectorAll('.cat-row').length;
                const idPrefix = `cat-${type}-img-${idx}`;
                const explorerUrl = btn.getAttribute('data-explorer-url');
                const filesUrl = btn.getAttribute('data-files-url');
                const cdnUrl = btn.getAttribute('data-cdn-url');
                
                const html = `
                    <div class="admin-card is-surface !p-3 grid gap-3 md:grid-cols-[40px_200px_1fr_1fr_40px] items-end cat-row">
                        <div class="h-[38px] flex items-center justify-center cursor-move text-slate/30 hover:text-primary cat-drag-handle">
                            <span class="material-icons">drag_indicator</span>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] opacity-60 font-bold">تصویر (آیکون)</label>
                            <div class="flex">
                                <input id="${idPrefix}" name="categories_${type}[items][${idx}][image]" value="" class="flex-grow rounded-r-lg border border-l-0 border-slate p-2 text-xs dark:bg-slate-800 admin-ltr">
                                    <button type="button" class="admin-btn admin-btn-secondary !rounded-r-none !p-2 px-3" data-file-picker data-file-picker-multi="0" data-explorer-url="${explorerUrl}" data-files-url="${filesUrl}" data-cdn-base-url="${cdnUrl}" data-file-pick-target="#${idPrefix}"><span class="material-icons !text-sm">folder</span></button>
                            </div>
                        </div>
                        <div class="space-y-1"><label class="text-[10px] opacity-60 font-bold">عنوان</label><input name="categories_${type}[items][${idx}][title]" value="" class="w-full rounded-lg border border-slate p-2 text-xs dark:bg-slate-800"></div>
                        <div class="space-y-1"><label class="text-[10px] opacity-60 font-bold">لینک</label><input name="categories_${type}[items][${idx}][link]" value="" class="w-full rounded-lg border border-slate p-2 text-xs dark:bg-slate-800 admin-ltr"></div>
                        <button type="button" class="remove-cat-row h-[38px] text-danger hover:bg-danger/10 rounded"><span class="material-icons">delete</span></button>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });
    
            container.addEventListener('click', (e) => {
                if(e.target.closest('.remove-cat-row')) e.target.closest('.cat-row').remove();
            });
        };

    if (document.querySelector('[data-add-cat-item="top"]')) { handleCatItems('top'); handleCatItems('bottom'); }

});