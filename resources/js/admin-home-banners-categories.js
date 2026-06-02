import Sortable from 'sortablejs';
const parse = (raw, fallback) => { try { return JSON.parse(raw || ''); } catch { return fallback; } };

class HomeBannersCategoriesAdmin {
  toast(msg, type = 'success') {
    const wrap = document.getElementById('admin-toast-wrap');
    if (!wrap) return;
    const node = document.createElement('div');
    node.className = 'admin-toast' + (type === 'error' ? ' admin-toast-error' : '');
    node.textContent = msg;
    wrap.appendChild(node);
    setTimeout(() => node.remove(), 4000);
  }

  async saveAjax(form) {
    if (!form || form.dataset.saving === 'true') return;

    const globalBtn = document.getElementById('global-save-btn');
    const formBtn = form.querySelector('button[type="submit"]');
    const btns = [globalBtn, formBtn].filter(Boolean);

    form.dataset.saving = 'true';
    btns.forEach(b => { b.disabled = true; b.dataset.oldHtml = b.innerHTML; b.innerHTML = '<span class="material-icons animate-spin">sync</span> در حال ذخیره...'; });

    try {
      const formData = new FormData(form);
      const res = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
      });
      const data = await res.json();
      if (res.ok) {
        const isAuto = document.getElementById('auto-save-toggle')?.checked;
        this.toast(isAuto ? 'بروز رسانی خودکار انجام شد.' : (data.message || 'تغییرات با موفقیت ذخیره شد.'));
      } else {
        throw new Error(data.message || 'خطایی در ذخیره‌سازی رخ داد.');
      }
    } catch (e) {
      this.toast(e.message, 'error');
    } finally {
      form.dataset.saving = 'false';
      btns.forEach(b => { b.disabled = false; b.innerHTML = b.dataset.oldHtml || ''; });
    }
  }

  constructor(root) {
    this.root = root;
    this.state = {
      banners: parse(root.querySelector('[data-banners-json]')?.value, []),
      categories: parse(root.querySelector('[data-categories-json]')?.value, []),
    };
  }

  init() {
    document.getElementById('global-save-btn')?.addEventListener('click', () => {
      const activeTab = document.querySelector('#home-tabs button.border-primary')?.getAttribute('data-tab-target');
      if (activeTab === 'tab-banners' || activeTab === 'tab-categories') {
          const form = this.root.closest('form') || this.root.querySelector('form');
          if (form) this.saveAjax(form);
      }
    });

    const form = this.root.closest('form') || this.root.querySelector('form');
    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        this.saveAjax(form);
    });

    this.initSortable('top');
    this.initSortable('bottom');

    this.categoriesList = this.root.querySelector('[data-categories-list]');
    if (!this.categoriesList) {
        // If categoriesList is null, we might be in the new home-items view
        // which handles categories differently (managed via Blade directly for dual sections)
        this.bindBannersOnly();
        return;
    }
    this.bind();
    this.render();
  }

  bindBannersOnly() {
    const form = this.root.closest('form') || this.root.querySelector('form');
    this.root.querySelector('[name="banners_enabled"]')?.addEventListener('change', () => {
        if (document.getElementById('auto-save-toggle')?.checked) {
            this.saveAjax(form);
        }
    });

    this.root.querySelectorAll('[data-banner-field]').forEach((el) => {
      const update = () => {
        const idx = Number(el.getAttribute('data-banner-index'));
        const key = el.getAttribute('data-banner-field');
        this.state.banners[idx] = this.state.banners[idx] || { image: '', link: '#', alt: '' };
        this.state.banners[idx][key] = el.value || '';
        this.sync();
        if (document.getElementById('auto-save-toggle')?.checked) {
            const form = this.root.closest('form') || this.root.querySelector('form');
            if (form) this.saveAjax(form);
        }
      };
      el.addEventListener('input', update);
      el.addEventListener('change', update); // For file picker updates
    });
  }

  bind() {
    this.bindBannersOnly();

    this.root.querySelector('[data-add-category]')?.addEventListener('click', () => {
      this.state.categories.push({ title: '', link: '', image: '', alt: '', description: '' });
      this.render();
    });

    this.categoriesList?.addEventListener('click', (e) => {
      const row = e.target.closest('[data-category-item]');
      if (!row) return;
      const i = Number(row.getAttribute('data-category-item'));
      const act = e.target.getAttribute('data-act');
      if (act === 'delete') this.state.categories.splice(i, 1);
      if (act === 'up' && i > 0) [this.state.categories[i - 1], this.state.categories[i]] = [this.state.categories[i], this.state.categories[i - 1]];
      if (act === 'down' && i < this.state.categories.length - 1) [this.state.categories[i + 1], this.state.categories[i]] = [this.state.categories[i], this.state.categories[i + 1]];
      this.render();
    });

    this.categoriesList?.addEventListener('input', (e) => {
      const row = e.target.closest('[data-category-item]');
      if (!row) return;
      const i = Number(row.getAttribute('data-category-item'));
      const key = e.target.getAttribute('data-key');
      if (!key) return;
      this.state.categories[i][key] = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
      this.sync();
      if (document.getElementById('auto-save-toggle')?.checked) {
          const form = this.root.closest('form') || this.root.querySelector('form');
          if (form) this.saveAjax(form);
      }
    });
  }

  render() {
    if (this.categoriesList) {
        this.categoriesList.innerHTML = this.state.categories.map((item, i) => `
          <div class="admin-card is-surface !p-4 space-y-3 relative group" data-category-item="${i}">
            <div class="flex items-center justify-between border-b border-slate/5 pb-2 mb-2">
                <span class="text-[10px] font-black text-primary bg-primary/5 px-2 py-0.5 rounded uppercase">Category Item #${i+1}</span>
                <div class="flex items-center gap-1">
                  <button type="button" class="admin-btn admin-btn-secondary !p-1 !min-h-0 rounded hover:!bg-primary/10 transition-colors" data-act="up" title="Up"><span class="material-icons !text-sm">arrow_upward</span></button>
                  <button type="button" class="admin-btn admin-btn-secondary !p-1 !min-h-0 rounded hover:!bg-primary/10 transition-colors" data-act="down" title="Down"><span class="material-icons !text-sm">arrow_downward</span></button>
                  <button type="button" class="admin-btn admin-btn-danger !p-1 !min-h-0 rounded hover:!bg-danger/20 transition-colors" data-act="delete" title="Delete"><span class="material-icons !text-sm">delete</span></button>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
              <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60">Title</label>
                <input class="w-full rounded border border-slate/20 bg-slate/5 p-2 text-xs focus:bg-white dark:focus:bg-slate-800 transition-colors" placeholder="عنوان" data-key="title" value="${item.title || ''}">
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60">Link</label>
                <input class="w-full rounded border border-slate/20 bg-slate/5 p-2 text-xs admin-ltr focus:bg-white dark:focus:bg-slate-800 transition-colors" placeholder="لینک" data-key="link" value="${item.link || ''}">
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60">Image URL</label>
                <input class="w-full rounded border border-slate/20 bg-slate/5 p-2 text-xs admin-ltr focus:bg-white dark:focus:bg-slate-800 transition-colors" placeholder="تصویر" data-key="image" value="${item.image || ''}">
              </div>
              <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60">Alt Text</label>
                <input class="w-full rounded border border-slate/20 bg-slate/5 p-2 text-xs focus:bg-white dark:focus:bg-slate-800 transition-colors" placeholder="متن alt" data-key="alt" value="${item.alt || ''}">
              </div>
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60">Hover Description</label>
                <input class="w-full rounded border border-slate/20 bg-slate/5 p-2 text-xs focus:bg-white dark:focus:bg-slate-800 transition-colors" placeholder="توضیح روی hover" data-key="description" value="${item.description || ''}">
            </div>
          </div>
        `).join('');
    }

    this.root.querySelectorAll('[data-banner-field]').forEach((el) => {
      const idx = Number(el.getAttribute('data-banner-index'));
      const key = el.getAttribute('data-banner-field');
      el.value = this.state.banners[idx]?.[key] || '';
    });

    this.sync();
  }

  initSortable(type) {
    const el = document.getElementById(`cat-items-${type}`);
    if (!el) return;
    Sortable.create(el, {
      animation: 150,
      handle: '.cat-drag-handle',
      ghostClass: 'opacity-50',
      onEnd: () => {
        // When drag ends, we don't necessarily need to do anything here
        // as the DOM order is updated, and when the form is submitted,
        // Laravel's indexed names (e.g., categories_top[items][0][title])
        // will still point to their old indices unless we re-index them.
        this.reindexCategories(type);
        if (document.getElementById('auto-save-toggle')?.checked) {
          const form = this.root.closest('form') || this.root.querySelector('form');
          if (form) this.saveAjax(form);
        }
      }
    });
  }

  reindexCategories(type) {
    const container = document.getElementById(`cat-items-${type}`);
    if (!container) return;
    container.querySelectorAll('.cat-row').forEach((row, idx) => {
      row.querySelectorAll('input, select, textarea').forEach(input => {
        const name = input.getAttribute('name');
        if (name) {
          const newName = name.replace(/\[items\]\[\d+\]/, `[items][${idx}]`);
          input.setAttribute('name', newName);
        }
      });
    });
  }

  sync() {
    const bannersJson = this.root.querySelector('[data-banners-json]');
    if (bannersJson) bannersJson.value = JSON.stringify(this.state.banners);

    const categoriesJson = this.root.querySelector('[data-categories-json]');
    if (categoriesJson) categoriesJson.value = JSON.stringify(this.state.categories);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const roots = document.querySelectorAll('[data-home-banners-categories-root]');
  roots.forEach(root => new HomeBannersCategoriesAdmin(root).init());
});
