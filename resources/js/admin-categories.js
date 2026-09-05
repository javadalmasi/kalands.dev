document.addEventListener('DOMContentLoaded', () => {
    const authkey = document.querySelector('meta[name="authkey"]')?.getAttribute('content');
    if (!authkey) return;

    window.loadData = async function() {
        try {
            const res = await fetch(`/dash/admin/${authkey}/categories/tree`);
            const data = await res.json();
            renderTree('digikala-tree', data.digikala || []);
            renderTree('basalam-tree', data.basalam || []);
            renderTree('snappshop-tree', data.snappshop || []);
        } catch (e) {
            console.error('Error loading category tree', e);
        }
    };

    window.loadLinkedData = async function() {
        const container = document.getElementById('linked-content');
        if (!container) return;
        try {
            const res = await fetch(`/dash/admin/${authkey}/categories/linked`);
            const data = await res.json();
            if (!data.linked || !data.linked.length) {
                container.innerHTML = '<div class="col-span-full text-center py-10 opacity-50 text-xs">هیچ نگاشتی ثبت نشده است.</div>';
                return;
            }
            container.innerHTML = data.linked.map(item => `
                <div class="admin-card p-4 space-y-3">
                    <h4 class="font-bold text-sm text-primary">${item.digikala ? item.digikala.title : 'ناشناس'}</h4>
                    <div class="space-y-1 text-xs">
                        ${item.links.map(l => `
                            <div class="flex justify-between items-center py-1 border-b border-white/5 last:border-0">
                                <span>${l.category ? l.category.title : 'ناشناس'}</span>
                                <span class="text-[10px] px-2 py-0.5 rounded ${l.is_manual ? 'bg-blue-500/10 text-blue-500' : 'bg-emerald-500/10 text-emerald-500'}">
                                    ${l.is_manual ? 'دستی' : 'خودکار'}
                                </span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        } catch (e) {
            container.innerHTML = '<div class="col-span-full text-center py-10 text-danger text-xs">خطا در بارگذاری داده‌ها.</div>';
        }
    };

    window.toggleEngineSettings = function() {
        const engine = document.getElementById('vector_engine')?.value;
        const externalSection = document.getElementById('external-engine-settings');
        if (externalSection) {
            externalSection.classList.toggle('hidden', engine !== 'external');
        }
    };

    window.testAi = async function() {
        const input = document.getElementById('ai-test-input');
        const btn = document.getElementById('ai-test-btn');
        const result = document.getElementById('ai-test-result');
        if (!input || !input.value.trim()) return;

        btn.disabled = true;
        result.classList.remove('hidden');
        result.textContent = 'در حال پردازش...';

        try {
            const res = await fetch(`/dash/admin/${authkey}/categories/ai-test`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ text: input.value.trim() })
            });
            const data = await res.json();
            result.textContent = JSON.stringify(data, null, 2);
        } catch (e) {
            result.textContent = 'خطا در برقراری ارتباط با وب‌سرویس.';
        } finally {
            btn.disabled = false;
        }
    };

    window.startAutoSync = async function() {
        const btn = document.getElementById('sync-all-btn');
        if (!btn || !confirm('آیا از شروع نگاشت سراسری مطمئن هستید؟')) return;

        btn.disabled = true;
        try {
            const res = await fetch(`/dash/admin/${authkey}/categories/sync-all`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content }
            });
            const data = await res.json();
            if (window.showToast) window.showToast(`نگاشت با موفقیت انجام شد (${data.count || 0} مورد)`);
        } catch (e) {
            if (window.showToast) window.showToast('خطا در انجام عملیات نگاشت');
        } finally {
            btn.disabled = false;
        }
    };

    function renderTree(targetId, nodes) {
        const el = document.getElementById(targetId);
        if (!el) return;
        if (!nodes || !nodes.length) {
            el.innerHTML = '<div class="text-center py-10 opacity-50 text-xs">دسته‌بندی یافت نشد.</div>';
            return;
        }
        el.innerHTML = '<ul class="space-y-2 text-xs">' + nodes.map(renderNode).join('') + '</ul>';
    }

    function renderNode(node) {
        const hasChildren = node.children && node.children.length > 0;
        return `
            <li class="tree-node ${hasChildren ? 'has-children' : ''}">
                <div class="tree-node-header flex items-center gap-2 p-2 hover:bg-white/5 rounded-lg cursor-pointer">
                    ${hasChildren ? '<i class="material-icons expand-icon text-sm opacity-50">expand_more</i>' : '<span class="w-4"></span>'}
                    <span class="font-semibold">${node.title}</span>
                    <span class="text-[10px] opacity-40 font-mono">#${node.id}</span>
                </div>
                ${hasChildren ? `<div class="tree-node-children pr-4 border-r border-white/10 mt-1"><ul class="space-y-1">${node.children.map(renderNode).join('')}</ul></div>` : ''}
            </li>
        `;
    }

    if (document.getElementById('digikala-tree')) {
        loadData();
    }
});
