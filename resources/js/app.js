import './bootstrap'
import 'material-icons/iconfont/material-icons.css'
import 'material-symbols/outlined.css'
import '../css/fonts.css'
import 'swiper/css';
import 'flowbite/dist/flowbite.css';
// import 'nouislider/dist/nouislider.min.css';
import '../css/theme.css'
import '../css/app.css'

import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, Thumbs, EffectFade, EffectCube, EffectCoverflow, EffectFlip, EffectCards, Keyboard, Mousewheel } from 'swiper/modules';
import { initFlowbite } from 'flowbite';
import noUiSlider from 'nouislider';

// Import Swiper styles
import 'swiper/css/bundle';

// Make libraries available globally
window.Swiper = Swiper;
window.noUiSlider = noUiSlider;
window.initFlowbite = initFlowbite;
initFlowbite();

const closeModalRoot = (modalRoot) => {
    if (!modalRoot) {
        return;
    }

    const modalId = modalRoot.getAttribute('id');
    const scopedCloseButton = modalRoot.querySelector('[data-modal-hide="' + modalId + '"]');

    if (scopedCloseButton) {
        scopedCloseButton.click();
        return;
    }

    modalRoot.classList.add('hidden');
    modalRoot.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
};

document.addEventListener('click', (event) => {
    const modalRoot = event.target.closest('[data-modal-root]');

    if (!modalRoot || modalRoot.classList.contains('hidden')) {
        return;
    }

    const clickedOverlay = !!event.target.closest('[data-modal-overlay]');
    const content = modalRoot.querySelector('[data-modal-content]');
    const clickedInsideContent = !!(content && content.contains(event.target));

    if (clickedOverlay || !clickedInsideContent) {
        closeModalRoot(modalRoot);
    }
}, true);

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    const openModals = Array.from(document.querySelectorAll('[data-modal-root]'))
        .filter((modal) => !modal.classList.contains('hidden'));

    if (!openModals.length) {
        return;
    }

    const lastOpenModal = openModals[openModals.length - 1];
    closeModalRoot(lastOpenModal);
});

// Register Swiper modules
Swiper.use([Navigation, Pagination, Autoplay, Thumbs, EffectFade, EffectCube, EffectCoverflow, EffectFlip, EffectCards, Keyboard, Mousewheel]);

document.addEventListener('click', function(e) {
    const target = e.target.closest('[data-analytics-goal]');
    if (!target) return;

    const goalAlias = target.getAttribute('data-analytics-goal');
    if (!goalAlias || !window.internalAnalytics) return;

    window.internalAnalytics.trackGoal(goalAlias, {
        product_id: target.getAttribute('data-product-id') || null,
        search_term: target.getAttribute('data-search-term') || null
    });
});

import './mount'
import './theme'
