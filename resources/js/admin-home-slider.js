import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade, EffectCube, EffectCoverflow, EffectFlip, EffectCards, EffectCreative, Keyboard, Mousewheel } from 'swiper/modules';
import 'swiper/css/bundle';
import Sortable from 'sortablejs';

const DEFAULTS = {
  pagination: 'bullets', dynamicBullets: false, direction: 'horizontal', effect: 'slide', navigation: true,
  mousewheel: false, keyboard: true, draggable: true, loop: true, centeredSlides: false, autoplay: true,
  enabled: true,
  autoplayDelay: 3000, speed: 600, spaceBetween: 0, rewind: false, grabCursor: true, pauseOnMouseEnter: true,
  breakpoints: { mobile: { slidesPerView: 1, spaceBetween: 0 }, tablet: { slidesPerView: 1, spaceBetween: 0 }, desktop: { slidesPerView: 1, spaceBetween: 0 } },
};

const parse = (raw, fallback) => { try { return JSON.parse(raw || ''); } catch { return fallback; } };
const normalizeConfig = (cfg = {}) => {
  const normalized = { ...cfg };
  if (typeof normalized.pagination === 'string') {
    normalized.pagination = { type: normalized.pagination };
  } else if (!normalized.pagination || typeof normalized.pagination !== 'object') {
    normalized.pagination = { type: 'bullets' };
  }
  return normalized;
};
const merge = (cfg) => ({ ...DEFAULTS, ...normalizeConfig(cfg), breakpoints: { ...DEFAULTS.breakpoints, ...(cfg?.breakpoints || {}) } });

class App {
  constructor(root) {
    this.root = root;
    this.state = {
      desktopConfig: merge(parse(root.querySelector('[data-slider-desktop-config-json]')?.value, {})),
      mobileConfig: merge(parse(root.querySelector('[data-slider-mobile-config-json]')?.value, {})),
      desktopSlides: parse(root.querySelector('[data-slider-desktop-slides-json]')?.value, []),
      mobileSlides: parse(root.querySelector('[data-slider-mobile-slides-json]')?.value, []),
      editDevice: 'desktop', editIndex: null,
    };
  }

  toast(msg, type = 'success') {
    const wrap = document.getElementById('admin-toast-wrap');
    if (!wrap) return;
    const node = document.createElement('div');
    node.className = 'admin-toast' + (type === 'error' ? ' admin-toast-error' : '');
    node.textContent = msg;
    wrap.appendChild(node);
    setTimeout(() => node.remove(), 4000);
  }

  init() {
    document.getElementById('global-save-btn')?.addEventListener('click', () => {
      const activeTab = document.querySelector('#home-tabs button.border-primary')?.getAttribute('data-tab-target');
      if (activeTab === 'tab-slider-desktop' || activeTab === 'tab-slider-mobile') {
          this.saveAjax();
      }
    });

    this.desktopList = this.root.querySelector('[data-slides-list="desktop"]');
    this.mobileList = this.root.querySelector('[data-slides-list="mobile"]');
    this.previewDesktopEl = this.root.querySelector('[data-slider-preview-desktop]');
    this.previewMobileEl = this.root.querySelector('[data-slider-preview-mobile]');
    this.modal = document.querySelector('[data-slide-modal]');
    this.bindConfig('desktop');
    this.bindConfig('mobile');
    this.bindSlides();
    this.initSortable('desktop');
    this.initSortable('mobile');
    this.render();
  }

  initSortable(device) {
    const el = device === 'mobile' ? this.mobileList : this.desktopList;
    if (!el) return;
    Sortable.create(el, {
      animation: 150,
      handle: '.slide-drag-handle',
      ghostClass: 'opacity-50',
      onEnd: (evt) => {
        const arr = device === 'mobile' ? this.state.mobileSlides : this.state.desktopSlides;
        const [moved] = arr.splice(evt.oldIndex, 1);
        arr.splice(evt.newIndex, 0, moved);
        this.render(true);
      }
    });
  }

  bindConfig(device) {
    const cfg = device === 'mobile' ? this.state.mobileConfig : this.state.desktopConfig;
    this.root.querySelectorAll(`[data-config-${device}]`).forEach((el) => {
      const key = el.getAttribute(`data-config-${device}`);
      const path = key.split('.');
      const value = path.reduce((acc, part) => (acc && acc[part] !== undefined ? acc[part] : undefined), cfg);
      if (el.type === 'checkbox') el.checked = Boolean(value); else if (value !== undefined && value !== null) el.value = value;
      const onChange = () => {
        let ref = cfg;
        for (let i = 0; i < path.length - 1; i += 1) { ref[path[i]] = ref[path[i]] || {}; ref = ref[path[i]]; }
        ref[path[path.length - 1]] = el.type === 'checkbox' ? el.checked : (el.type === 'number' ? Number(el.value || 0) : el.value);
        this.render(true);
      };
      el.addEventListener('input', onChange);
      el.addEventListener('change', onChange);
    });

    this.root.querySelectorAll(`[data-breakpoint-${device}]`).forEach((el) => {
      const [name, field] = el.getAttribute(`data-breakpoint-${device}`).split('.');
      const current = cfg.breakpoints?.[name]?.[field];
      if (current !== undefined) el.value = current;
      el.addEventListener('input', () => {
        cfg.breakpoints = cfg.breakpoints || {};
        cfg.breakpoints[name] = cfg.breakpoints[name] || {};
        cfg.breakpoints[name][field] = Number(el.value || 0);
        this.render(true);
      });
    });
  }

  async saveAjax() {
    const form = this.root.closest('form') || this.root.querySelector('form');
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

  bindSlides() {
    const form = this.root.closest('form') || this.root.querySelector('form');
    form?.addEventListener('submit', (e) => {
        e.preventDefault();
        this.saveAjax();
    });

    this.root.querySelectorAll('[data-add-slide]').forEach((btn) => btn.addEventListener('click', () => this.openModal(null, btn.getAttribute('data-add-slide') || 'desktop')));
    document.querySelector('[data-save-slide]')?.addEventListener('click', () => this.saveModal());
    document.querySelectorAll('[data-close-slide-modal]').forEach((b) => b.addEventListener('click', () => this.modal.close()));

    [this.desktopList, this.mobileList].forEach((listEl) => listEl?.addEventListener('click', (e) => {
      const card = e.target.closest('[data-slide-item]'); if (!card) return;
      const btn = e.target.closest('[data-act]'); if (!btn) return;

      const device = listEl.getAttribute('data-slides-list');
      const arr = device === 'mobile' ? this.state.mobileSlides : this.state.desktopSlides;
      const i = Number(card.getAttribute('data-slide-item'));
      const act = btn.getAttribute('data-act');

      if (act === 'delete') arr.splice(i, 1);
      if (act === 'edit') this.openModal(i, device);
      if (act === 'toggle') arr[i].is_active = arr[i].is_active === false ? true : false;
      this.render(true);
    }));
  }

  openModal(index, device) {
    this.state.editIndex = index;
    this.state.editDevice = device;
    const arr = device === 'mobile' ? this.state.mobileSlides : this.state.desktopSlides;
    const slide = index === null ? { is_active: true, slide_type: 'image', meta: {}, title: '', link: '', image: '', sort_order: arr.length } : { ...arr[index] };
    this.modal.querySelectorAll('[data-slide-field]').forEach((el) => {
      const k = el.getAttribute('data-slide-field');
      if (el.type === 'checkbox') el.checked = Boolean(slide[k]);
      else if (k === 'alt') el.value = slide.meta?.alt || '';
      else el.value = slide[k] ?? '';
    });
    this.modal.showModal();
  }

  saveModal() {
    const slide = { meta: {} };
    this.modal.querySelectorAll('[data-slide-field]').forEach((el) => {
      const k = el.getAttribute('data-slide-field');
      if (k === 'alt') {
        slide.meta = slide.meta || {};
        slide.meta.alt = el.value || '';
      } else if (el.type === 'checkbox') {
        slide[k] = el.checked;
      } else if (el.type === 'number') {
        slide[k] = Number(el.value || 0);
      } else {
        slide[k] = el.value || '';
      }
    });

    const arr = this.state.editDevice === 'mobile' ? this.state.mobileSlides : this.state.desktopSlides;
    if (this.state.editIndex === null) {
      arr.push(slide);
    } else {
      arr[this.state.editIndex] = slide;
    }

    this.modal.close();
    this.render(true);
  }

  card(slide, i) {
    const statusLabel = slide.is_active !== false ? 'فعال' : 'غیرفعال';
    const statusClass = slide.is_active !== false ? 'text-success' : 'text-danger';
    return `<div class="admin-card is-surface !p-3 group" data-slide-item="${i}">
      <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="h-10 w-6 flex items-center justify-center cursor-move text-slate/30 hover:text-primary slide-drag-handle">
            <span class="material-icons !text-sm">drag_indicator</span>
          </div>
          <div class="h-10 w-16 shrink-0 overflow-hidden rounded border border-slate/20 bg-slate/5">
            <img src="${slide.image || ''}" class="h-full w-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
          </div>
          <div class="overflow-hidden">
            <div class="flex items-center gap-2">
               <strong class="truncate text-xs font-bold text-slate">${slide.title || 'اسلاید بدون عنوان'}</strong>
               <span class="text-[9px] font-black ${statusClass} opacity-80 border border-current/20 px-1 rounded uppercase">${statusLabel}</span>
            </div>
            <p class="text-[10px] text-slate/50 truncate mt-0.5 admin-ltr">${slide.image || ''}</p>
          </div>
        </div>
        <div class="flex items-center gap-1 shrink-0">
          <button type="button" class="admin-btn admin-btn-secondary !p-1.5 !min-h-0 rounded hover:!bg-primary/10 hover:!text-primary transition-colors" data-act="edit" title="ویرایش"><span class="material-icons !text-sm">edit</span></button>
          <button type="button" class="admin-btn admin-btn-secondary !p-1.5 !min-h-0 rounded hover:!bg-primary/10 hover:!text-primary transition-colors" data-act="toggle" title="تغییر وضعیت"><span class="material-icons !text-sm">${slide.is_active !== false ? 'visibility' : 'visibility_off'}</span></button>
          <button type="button" class="admin-btn admin-btn-danger !p-1.5 !min-h-0 rounded hover:!bg-danger/20 transition-colors" data-act="delete" title="حذف"><span class="material-icons !text-sm">delete</span></button>
        </div>
      </div>
    </div>`;
  }

  swiperConfig(cfg, previewEl) {
    const paginationType = cfg.pagination?.type === 'dynamic_bullets' ? 'bullets' : (cfg.pagination?.type || 'bullets');
    const isPaginationEnabled = cfg.pagination?.type !== 'none';
    return {
      direction: cfg.direction || 'horizontal', effect: cfg.effect || 'slide', speed: Number(cfg.speed || 600),
      spaceBetween: Number(cfg.spaceBetween || 0), loop: Boolean(cfg.loop), rewind: Boolean(cfg.rewind), grabCursor: cfg.grabCursor !== false,
      creativeEffect: {
        prev: { shadow: true, translate: [0, 0, -400] },
        next: { translate: ['100%', 0, 0] },
      },
      centeredSlides: Boolean(cfg.centeredSlides), allowTouchMove: cfg.draggable !== false,
      direction: 'horizontal',
      navigation: Boolean(cfg.navigation) ? {
        nextEl: previewEl.querySelector('.swiper-button-next'),
        prevEl: previewEl.querySelector('.swiper-button-prev')
      } : false,
      pagination: isPaginationEnabled ? { el: previewEl.querySelector('.swiper-pagination'), clickable: true, dynamicBullets: cfg.pagination?.type === 'dynamic_bullets', type: paginationType } : false,
      mousewheel: Boolean(cfg.mousewheel), keyboard: Boolean(cfg.keyboard),
      autoplay: Boolean(cfg.autoplay) ? { delay: Number(cfg.autoplayDelay || 3000), disableOnInteraction: false, pauseOnMouseEnter: cfg.pauseOnMouseEnter !== false } : false,
      breakpoints: {
        0: { slidesPerView: Number(cfg.breakpoints?.mobile?.slidesPerView || 1), spaceBetween: Number(cfg.breakpoints?.mobile?.spaceBetween || cfg.spaceBetween || 0) },
        768: { slidesPerView: Number(cfg.breakpoints?.tablet?.slidesPerView || 1), spaceBetween: Number(cfg.breakpoints?.tablet?.spaceBetween || cfg.spaceBetween || 0) },
        1024: { slidesPerView: Number(cfg.breakpoints?.desktop?.slidesPerView || 1), spaceBetween: Number(cfg.breakpoints?.desktop?.spaceBetween || cfg.spaceBetween || 0) },
      },
      on: {
        init: function() {
            // Remove SVG icons injected by Swiper as we use CSS masks
            previewEl.querySelectorAll('.swiper-button-next svg, .swiper-button-prev svg').forEach(s => s.remove());
        },
        update: function() {
            previewEl.querySelectorAll('.swiper-button-next svg, .swiper-button-prev svg').forEach(s => s.remove());
        }
      }
    };
  }

  render(isUserAction = false) {
    if (!this.desktopList || !this.mobileList) return;
    this.state.desktopSlides = this.state.desktopSlides.map((s, i) => ({ ...s, sort_order: i }));
    this.state.mobileSlides = this.state.mobileSlides.map((s, i) => ({ ...s, sort_order: i }));
    this.desktopList.innerHTML = this.state.desktopSlides.map((s, i) => this.card(s, i)).join('');
    this.mobileList.innerHTML = this.state.mobileSlides.map((s, i) => this.card(s, i)).join('');

    const desktopSlides = this.state.desktopSlides.filter((s) => s.is_active !== false);
    const mobileSlides = this.state.mobileSlides.filter((s) => s.is_active !== false);
    this.previewDesktopEl.querySelector('.swiper-wrapper').innerHTML = desktopSlides.map((slide) => `<div class="swiper-slide"><img src="${slide.image || ''}" class="h-[230px] w-full object-cover" alt="${slide.meta?.alt || slide.title || 'اسلاید'}"></div>`).join('');
    this.previewMobileEl.querySelector('.swiper-wrapper').innerHTML = mobileSlides.map((slide) => `<div class="swiper-slide"><img src="${slide.image || ''}" class="h-[230px] w-full object-cover" alt="${slide.meta?.alt || slide.title || 'اسلاید'}"></div>`).join('');

    this.root.querySelector('[data-slider-desktop-config-json]').value = JSON.stringify(this.state.desktopConfig);
    this.root.querySelector('[data-slider-mobile-config-json]').value = JSON.stringify(this.state.mobileConfig);
    this.root.querySelector('[data-slider-desktop-slides-json]').value = JSON.stringify(this.state.desktopSlides);
    this.root.querySelector('[data-slider-mobile-slides-json]').value = JSON.stringify(this.state.mobileSlides);

    if (this.desktopSwiper) this.desktopSwiper.destroy(true, true);
    if (this.mobileSwiper) this.mobileSwiper.destroy(true, true);

    const modules = [Navigation, Pagination, Autoplay, EffectFade, EffectCube, EffectCoverflow, EffectFlip, EffectCards, EffectCreative, Keyboard, Mousewheel];

    this.desktopSwiper = new Swiper(this.previewDesktopEl, {
        ...this.swiperConfig(this.state.desktopConfig, this.previewDesktopEl),
        modules
    });
    this.mobileSwiper = new Swiper(this.previewMobileEl, {
        ...this.swiperConfig(this.state.mobileConfig, this.previewMobileEl),
        modules
    });

    if (isUserAction && document.getElementById('auto-save-toggle')?.checked) {
        this.saveAjax();
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-home-slider-root]');
  if (!root) return;
  new App(root).init();
});
