import { VisitorHelper } from './utils/visitor-helper.js';

const headerDesktopNavbarItems = document.querySelectorAll("#header-desktop-navbar ul");
const headerDesktopIndicator = document.querySelector("#header-desktop-navbar-indicator");
if (headerDesktopNavbarItems && headerDesktopIndicator) {
  const updateIndicatorStyle = (offsetWidth, offsetLeft, dataset) => {
    const isDarkMode = document.documentElement.classList.contains("dark");
    const backgroundColor = isDarkMode ? dataset.colorDark || "#34d399" : dataset.colorLight || "#10b981";
    headerDesktopIndicator.style.cssText = "width: " + offsetWidth + "px; left: " + offsetLeft + "px; background-color: " + backgroundColor + ";";
  };
  headerDesktopNavbarItems.forEach((item) => {
    item.addEventListener("mouseover", () => updateIndicatorStyle(item.offsetWidth, item.offsetLeft, item.dataset));
    item.addEventListener("mouseleave", () => (headerDesktopIndicator.style.width = 0));
  });
  if (headerDesktopNavbarItems[0]) updateIndicatorStyle(0, headerDesktopNavbarItems[0].offsetLeft, headerDesktopNavbarItems[0].dataset);
}

const scrollTopFooter = document.getElementById("scroll-top-button-footer");
if (scrollTopFooter) scrollTopFooter.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

const theme = {
  current: localStorage.getItem("theme"),
  toggleDesktop: document.getElementById("toggleThemeDesktop"),
  toggleMobile: document.getElementById("toggleThemeMobile"),
  text: document.getElementById("themeText")
};
if (theme.toggleDesktop || theme.toggleMobile) {
  const setThemeInfo = (mode) => {
    if (theme.text) theme.text.innerHTML = "حالت " + (mode === "dark" ? "شب" : "روز");
  };
  setThemeInfo(theme.current);
  const toggleTheme = () => {
    const isDark = document.documentElement.classList.toggle("dark");
    setThemeInfo(isDark ? "dark" : "light");
    localStorage.setItem("theme", isDark ? "dark" : "light");
  };
  const handleToggle = (e) => {
    if (!document.startViewTransition) { toggleTheme(); return; }
    const x = e.clientX, y = e.clientY, endRadius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
    const transition = document.startViewTransition(() => toggleTheme());
    transition.ready.then(() => {
      const clipPath = ["circle(0px at " + x + "px " + y + "px)", "circle(" + endRadius + "px at " + x + "px " + y + "px)"];
      document.documentElement.animate({ clipPath: document.documentElement.classList.contains("dark") ? [...clipPath].reverse() : clipPath }, { duration: 500, easing: "ease-in", pseudoElement: document.documentElement.classList.contains("dark") ? "::view-transition-old(root)" : "::view-transition-new(root)" });
    });
  };
  if (theme.toggleDesktop) theme.toggleDesktop.addEventListener("click", handleToggle);
  if (theme.toggleMobile) theme.toggleMobile.addEventListener("click", handleToggle);
}

window.openNav = function () {
  const s = document.getElementById("mySidenav"), c = document.getElementById("closeBtn"), o = document.getElementById("overlay");
  if (s) s.classList.add("sidenav-open");
  if (c) c.classList.remove("hidden");
  if (o) o.classList.remove("hidden");
};
window.closeNav = function () {
  const s = document.getElementById("mySidenav"), c = document.getElementById("closeBtn"), o = document.getElementById("overlay");
  if (s) s.classList.remove("sidenav-open");
  if (c) c.classList.add("hidden");
  if (o) o.classList.add("hidden");
  setTimeout(() => {
    const m = document.getElementById("main-container"), sub = document.getElementById("sub-container");
    if (m) { m.classList.remove("main-away"); }
    if (sub) { sub.classList.remove("sub-open"); }
  }, 300);
};

document.addEventListener("DOMContentLoaded", async () => {
  // Visitor status aware UI updates
  const isBot = await VisitorHelper.isBot();

  if (!isBot) {
    // Handle specialized links
    document.querySelectorAll('.js-special-link').forEach(link => {
        const humanHref = link.getAttribute('data-human-href');
        if (humanHref) link.setAttribute('href', humanHref);
    });

    // Handle status aware buttons/links with text changes
    document.querySelectorAll('.js-status-aware-btn').forEach(btn => {
      const textEl = btn.querySelector('.js-btn-text');
      const humanHref = btn.getAttribute('data-human-href');
      const humanText = btn.getAttribute('data-human-text');
      const humanStyle = btn.getAttribute('data-human-style');

      if (humanHref) {
          btn.setAttribute('href', humanHref);
      }

      if (humanStyle) {
          const innerBtn = btn.querySelector('button');
          const target = innerBtn || btn;
          // Clean common button classes and apply human-specific one
          target.classList.remove('btn-primary', 'btn-secondary', 'btn-subtle');
          target.classList.add(humanStyle);
      }

      if (textEl && humanText) textEl.textContent = humanText;
    });
  }

const desktopMegamenuWrapper = document.getElementById("desktopMegamenuWrapper");
const desktopMegamenu = document.getElementById("desktopMegamenu");
const headerOverlay = document.getElementById("header-overlay");
const megaParentsEl = document.getElementById("mega-menu-parents");
const megaChildsEl = document.getElementById("mega-menu-childs");
const mobileMenuListEl = document.getElementById("mobile-menu-list");
const mobileSubContentEl = document.getElementById("sub-container-content");

const megamenuState = {
  current: 'idle',
  timeout: null,
  mousePos: { x: 0, y: 0 },
  prevMousePos: { x: 0, y: 0 },
  lastMouseMoveTime: 0
};
window.megamenuState = megamenuState; // Expose for scroll listener

const updateMegamenuState = (newState) => {
  megamenuState.current = newState;
  if (newState === 'open') {
    headerOverlay.classList.remove("hidden");
    desktopMegamenu.classList.remove("hidden");
    // Trigger reflow to ensure animation works if it was just display: none
    void desktopMegamenu.offsetWidth;
    desktopMegamenu.classList.add("megamenu-animate-in");
    desktopMegamenu.classList.remove("megamenu-animate-out");
  } else if (newState === 'idle') {
    headerOverlay.classList.add("hidden");
    desktopMegamenu.classList.add("hidden");
    desktopMegamenu.classList.remove("megamenu-animate-in", "megamenu-animate-out");
  }
};

const openMegamenu = (immediate = false) => {
  if (megamenuState.timeout) clearTimeout(megamenuState.timeout);

  if (megamenuState.current === 'open') return;
  if (!immediate && megamenuState.current === 'opening') return;

  if (immediate) {
    updateMegamenuState('open');
  } else {
    updateMegamenuState('opening');
    megamenuState.timeout = setTimeout(() => {
      updateMegamenuState('open');
    }, 180);
  }
};

const closeMegamenu = (immediate = false) => {
  if (megamenuState.timeout) clearTimeout(megamenuState.timeout);

  if (megamenuState.current === 'idle' || megamenuState.current === 'closing') return;

  if (immediate) {
    updateMegamenuState('idle');
    return;
  }

  megamenuState.current = 'closing';

  const attemptClose = () => {
    if (isMovingTowardMenu()) {
      megamenuState.timeout = setTimeout(attemptClose, 100);
      return;
    }

    // Start visual close animation
    desktopMegamenu.classList.add("megamenu-animate-out");
    desktopMegamenu.classList.remove("megamenu-animate-in");
    headerOverlay.classList.add("hidden");

    megamenuState.timeout = setTimeout(() => {
      updateMegamenuState('idle');
    }, 140); // Match close animation duration
  };

  megamenuState.timeout = setTimeout(attemptClose, 420); // 420ms close delay
};

let mouseMoveRaf = null;
const handleMouseMove = (e) => {
  if (mouseMoveRaf) return;
  mouseMoveRaf = requestAnimationFrame(() => {
    megamenuState.prevMousePos = { ...megamenuState.mousePos };
    megamenuState.mousePos = { x: e.clientX, y: e.clientY };
    megamenuState.lastMouseMoveTime = Date.now();

    document.querySelectorAll(".border-gradient").forEach((item) => {
      const { left, top } = item.getBoundingClientRect();
      item.style.setProperty("--x", (e.clientX - left) + "px");
      item.style.setProperty("--y", (e.clientY - top) + "px");
    });

    mouseMoveRaf = null;
  });
};

document.addEventListener("mousemove", handleMouseMove);

if (desktopMegamenuWrapper && desktopMegamenu && headerOverlay) {
  desktopMegamenuWrapper.addEventListener("mouseenter", () => {
    if (megamenuState.current === 'closing') {
      updateMegamenuState('open');
      if (megamenuState.timeout) clearTimeout(megamenuState.timeout);
    } else {
      openMegamenu();
    }
  });

  desktopMegamenuWrapper.addEventListener("mouseleave", () => {
    if (megamenuState.current === 'opening') {
      updateMegamenuState('idle');
      if (megamenuState.timeout) clearTimeout(megamenuState.timeout);
    } else {
      closeMegamenu();
    }
  });

  desktopMegamenu.addEventListener("mouseenter", () => {
    if (megamenuState.timeout) clearTimeout(megamenuState.timeout);
    updateMegamenuState('open');
  });

  desktopMegamenu.addEventListener("mouseleave", () => {
    closeMegamenu();
  });

  desktopMegamenuWrapper.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation(); // Prevent immediate close from document mousedown if using click
    if (megamenuState.current === 'open' || megamenuState.current === 'opening') {
        closeMegamenu(true);
    } else {
        openMegamenu(true);
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && megamenuState.current === 'open') closeMegamenu(true);
  });

  document.addEventListener("mousedown", (e) => {
    if (megamenuState.current !== 'open') return;

    // Check if click is inside the trigger
    if (desktopMegamenuWrapper.contains(e.target)) return;

    // Check if click is inside the ACTUAL menu panel (the container with content)
    // because desktopMegamenu is a full-width fixed wrapper that might block clicks
    const menuContent = desktopMegamenu.querySelector('.container');
    if (menuContent && menuContent.contains(e.target)) return;

    closeMegamenu(true);
  });
}

const isMovingTowardMenu = () => {
  if (!desktopMegamenu || megamenuState.current === 'idle') return false;

  const rect = desktopMegamenu.querySelector('.container').getBoundingClientRect();
  const top = rect.top;
  const left = rect.left;
  const right = rect.right;
  const bottom = rect.bottom;

  const p = megamenuState.mousePos;
  const prev = megamenuState.prevMousePos;

  // If mouse didn't move or moved very little, don't consider it moving toward menu
  if (p.x === prev.x && p.y === prev.y) return false;

  // Simple triangle logic:
  // We want to know if the cursor is moving from the trigger area towards the menu panel area.
  // The trigger is above the menu.

  // If moving upwards, definitely not toward menu
  if (p.y < prev.y) return false;

  // Check if current trajectory intersects with the menu panel
  // We can use the slope of the mouse movement
  const dx = p.x - prev.x;
  const dy = p.y - prev.y;

  if (dy <= 0) return false; // Not moving down

  // Predict where the mouse will be when it reaches the top of the menu panel
  const distToTop = top - p.y;
  if (distToTop <= 0) return true; // Already in or below top line

  const predictedX = p.x + (dx / dy) * distToTop;

  // If predicted X is within menu panel bounds (with some padding), return true
  const padding = 50;
  return predictedX >= (left - padding) && predictedX <= (right + padding);
};

const elementsWithScrollClass = document.querySelectorAll("[data-onscrollclass]");
let prevScrollPos = document.documentElement.scrollTop;
window.addEventListener("scroll", () => {
  const current = document.documentElement.scrollTop;
  const isMegamenuOpen = window.megamenuState && window.megamenuState.current === 'open';
  if (current > prevScrollPos && !isMegamenuOpen && current > 60) {
    elementsWithScrollClass.forEach((el) => el.classList.add(el.dataset.onscrollclass));
  } else {
    elementsWithScrollClass.forEach((el) => el.classList.remove(el.dataset.onscrollclass));
  }
  prevScrollPos = current;
});

if (megaParentsEl && megaChildsEl) {
  const config = window.megaMenuConfig || [];

  const desktopCategories = [];
  config.forEach(group => {
    if (group.show_desktop === false) return;
    (group.categories || []).forEach(cat => {
      if (cat.show_desktop !== false) desktopCategories.push(cat);
    });
  });

  const renderDesktop = (cat) => {
    let html = "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-10'>";
    (cat.sub_sections || []).forEach(section => {
      if (section.show_desktop === false) return;
      html += "<div><h3 class='mb-4 text-sm font-black text-primary border-r-2 border-primary pr-2'>" + section.title + "</h3><div class='space-y-2'>";
      const sortedItems = [...(section.items || [])].sort((a, b) => (a.title.includes("تمامی") ? 1 : b.title.includes("تمامی") ? -1 : 0));
      sortedItems.forEach(l => {
        if (l.show_desktop === false) return;
        const isAll = l.title.includes("تمامی");
        const cls = isAll ? "inline-block w-full rounded-squircle bg-primary/10 px-3 py-1.5 text-xs font-black text-primary transition-all hover:bg-primary hover:text-white mt-2" : "block text-sm font-medium text-slate-600 transition-all hover:text-primary dark:text-slate-300";
        html += "<a href=\"" + l.href + "\" class=\"" + cls + "\">" + l.title + "</a>";
      });
      html += "</div></div>";
    });
    return html + "</div>";
  };

  megaParentsEl.innerHTML = desktopCategories.map((cat, idx) => {
    const active = idx === 0 ? "mega-menu-active" : "";
    return "<li data-idx=\"" + idx + "\" class=\"" + active + " cursor-pointer rounded-squircle px-3 py-2 text-sm font-bold text-slate-700 dark:text-slate-200 transition-colors hover:text-primary\">" + cat.title + "</li>";
  }).join("");

  megaChildsEl.innerHTML = desktopCategories.map((cat, idx) => {
    const hidden = idx === 0 ? "" : "hidden";
    return "<div class=\"" + hidden + "\">" + renderDesktop(cat) + "</div>";
  }).join("");

  const ps = megaParentsEl.querySelectorAll("li[data-idx]"), cs = megaChildsEl.querySelectorAll(":scope > div");
  const act = (i) => { ps.forEach(l => l.classList.remove("mega-menu-active")); cs.forEach(p => p.classList.add("hidden")); if (ps[i]) ps[i].classList.add("mega-menu-active"); if (cs[i]) cs[i].classList.remove("hidden"); };
  ps.forEach((l, i) => l.addEventListener("mouseenter", () => act(i)));

  if (mobileMenuListEl) {
    let currentGroupIdx = null;
    let currentCatIdx = null;
    let currentLevel = 1; // 1: Groups, 2: Categories, 3: Items
    const mc = document.getElementById("main-container");
    const sc = document.getElementById("sub-container");
    const backBtn = document.getElementById("mainMenu");

    const renderLevel2 = (gIdx) => {
      currentLevel = 2;
      currentGroupIdx = gIdx;
      const group = config[gIdx];
      mobileSubContentEl.innerHTML = (group.categories || []).map((cat, cIdx) => {
        if (cat.show_mobile === false) return "";
        return "<li class='group'><a data-cat-idx='" + cIdx + "' class='flex w-full items-center justify-between rounded-squircle px-3 py-2.5 text-slate-600 transition-all hover:bg-primary/5 hover:text-primary dark:text-slate-200 font-bold'><span>" + cat.title + "</span><svg class='h-4 w-4 transition-transform group-hover:-translate-x-1'><use xlink:href='#chevron-left'/></svg></a></li>";
      }).join("");
      if (backBtn) backBtn.innerHTML = "<svg class='h-5 w-5'><use xlink:href='#chevron-right'/></svg> بازگشت به منوی اصلی";
      mc.classList.add("main-away");
      sc.classList.add("sub-open");
      sc.scrollTo(0, 0);
    };

    const renderLevel3 = (cIdx) => {
      currentLevel = 3;
      currentCatIdx = cIdx;
      const cat = config[currentGroupIdx].categories[cIdx];
      let html = "";
      (cat.sub_sections || []).forEach((section) => {
        if (section.show_mobile === false) return;
        html += "<div class='mb-6'><h4 class='mb-3 text-xs font-black text-primary/70 border-r-2 border-primary/30 pr-2'>" + section.title + "</h4><ul class='space-y-1'>";
        const sorted = [...(section.items || [])].sort((a, b) => (a.title.includes("تمامی") ? 1 : b.title.includes("تمامی") ? -1 : 0));
        sorted.forEach((l) => {
          if (l.show_mobile === false) return;
          const isAll = l.title.includes("تمامی");
          const cls = isAll ? "bg-primary/10 font-bold text-primary mt-2" : "text-slate-600 dark:text-slate-200 hover:bg-primary/5 hover:text-primary font-bold";
          html += "<li class='group'><a class='flex w-full items-center justify-between rounded-squircle px-3 py-2.5 transition-all " + cls + "' href='" + l.href + "'><span>" + l.title + "</span>" + (isAll ? "" : "<svg class='h-4 w-4 transition-transform group-hover:-translate-x-1'><use xlink:href='#chevron-left'/></svg>") + "</a></li>";
        });
        html += "</ul></div>";
      });
      mobileSubContentEl.innerHTML = html;
      if (backBtn) backBtn.innerHTML = "<svg class='h-5 w-5'><use xlink:href='#chevron-right'/></svg> بازگشت به " + (config[currentGroupIdx]?.title || "قبل");
      sc.scrollTo(0, 0);
    };

    mobileMenuListEl.innerHTML = config.map((group, idx) => {
      if (group.show_mobile === false) return "";
      return "<li class='group'><a data-group-idx='" + idx + "' class='flex w-full items-center justify-between rounded-squircle px-3 py-2.5 text-slate-600 transition-all hover:bg-primary/5 hover:text-primary dark:text-slate-200 font-bold'><span>" + group.title + "</span><svg class='h-4 w-4 transition-transform group-hover:-translate-x-1'><use xlink:href='#chevron-left'/></svg></a></li>";
    }).join("");

    mobileMenuListEl.addEventListener("click", (e) => {
      const gLink = e.target.closest("a[data-group-idx]");
      if (gLink) { e.preventDefault(); renderLevel2(gLink.getAttribute("data-group-idx")); }
    });

    mobileSubContentEl.addEventListener("click", (e) => {
      const a = e.target.closest("a[data-cat-idx]");
      if (a) { e.preventDefault(); renderLevel3(a.getAttribute("data-cat-idx")); }
    });

    if (backBtn) backBtn.addEventListener("click", () => {
      if (currentLevel === 3) { renderLevel2(currentGroupIdx); }
      else {
        mc.classList.remove("main-away");
        sc.classList.remove("sub-open");
        currentLevel = 1;
        if (backBtn) backBtn.innerHTML = "<svg class='h-5 w-5'><use xlink:href='#chevron-right'/></svg> بازگشت به منوی اصلی";
      }
    });

    // Reset menu state when sidenav is closed
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === "class") {
          const s = document.getElementById("mySidenav");
          if (s && !s.classList.contains("sidenav-open") && currentLevel !== 1) {
            setTimeout(() => {
              mc.classList.remove("main-away");
              sc.classList.remove("sub-open");
              currentLevel = 1;
              if (backBtn) backBtn.innerHTML = "<svg class='h-5 w-5'><use xlink:href='#chevron-right'/></svg> بازگشت به منوی اصلی";
            }, 300);
          }
        }
      });
    });
    const sidenav = document.getElementById("mySidenav");
    if (sidenav) observer.observe(sidenav, { attributes: true });
  }
}

  const initSwipers = () => {
    if (typeof window.Swiper === "undefined") {
      setTimeout(initSwipers, 100);
      return;
    }

    const defaultOptions = {
      speed: 600,
      loop: true,
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
      },
      pagination: {
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
    };

    document.querySelectorAll(".banner-slider-desktop, .banner-slider-mobile, .banner-slider, .home-category-slider").forEach((el) => {
      let customConfig = {};
      try {
        customConfig = JSON.parse(el.getAttribute("data-slider-config") || "{}");
      } catch (e) {
        console.error("Error parsing Swiper config", e);
      }

      if (customConfig.enabled === false) return;

      const rawPagination = typeof customConfig.pagination === 'string'
        ? { type: customConfig.pagination }
        : (customConfig.pagination || {});
      const isPaginationEnabled = (rawPagination && rawPagination.type !== 'none');
      const paginationType = rawPagination.type === 'dynamic_bullets' ? 'bullets' : (rawPagination.type || 'bullets');
      const paginationEl = el.querySelector(".swiper-pagination");

      if (paginationEl && rawPagination.position) {
          paginationEl.className = 'swiper-pagination main-banner-pagination ' + rawPagination.position;
      }

      const options = {
        ...defaultOptions,
        ...customConfig,
        navigation: (customConfig.navigation === false) ? false : {
          nextEl: el.querySelector(".swiper-button-next"),
          prevEl: el.querySelector(".swiper-button-prev"),
        },
        pagination: isPaginationEnabled ? {
          el: paginationEl,
          clickable: true,
          dynamicBullets: rawPagination.type === 'dynamic_bullets',
          type: paginationType
        } : false,
      };

      const swiper = new Swiper(el, options);
      if (swiper?.navigation) {
        el.querySelectorAll(".swiper-button-next svg, .swiper-button-prev svg").forEach((node) => node.remove());
      }
    });

    // Product Sliders
    const productSliderOptions = {
      slidesPerView: 1.5,
      spaceBetween: 14,
      freeMode: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      breakpoints: {
        360: { slidesPerView: 2, spaceBetween: 10 },
        768: { slidesPerView: 3.5, spaceBetween: 10 },
        1024: { slidesPerView: 4.5, spaceBetween: 10 },
        1380: { slidesPerView: 6, spaceBetween: 10 },
      },
    };

    new Swiper(".product-slider", {
      ...productSliderOptions,
      navigation: {
        nextEl: document.querySelector(".product-slider .swiper-button-next"),
        prevEl: document.querySelector(".product-slider .swiper-button-prev"),
      },
    });
    new Swiper(".product-slider-wrapped", {
      ...productSliderOptions,
      slidesPerView: 1.7,
      spaceBetween: 2,
      breakpoints: {
        360: { slidesPerView: 2, spaceBetween: 2 },
        768: { slidesPerView: 3.5, spaceBetween: 2 },
        1024: { slidesPerView: 4.5, spaceBetween: 2 },
        1380: { slidesPerView: 6, spaceBetween: 2 },
      },
    });

    // Blog Slider
    new Swiper(".blog-slider", {
      slidesPerView: 1.7,
      spaceBetween: 14,
      freeMode: true,
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
      breakpoints: {
        360: { slidesPerView: 2, spaceBetween: 10 },
        1024: { slidesPerView: 4, spaceBetween: 20 },
      },
    });

    // Product Detail Gallery
    const thumbsSliderEl = document.querySelector(".product-image-desktop-2-swiper");
    let thumbsSlider = null;
    if (thumbsSliderEl) {
      thumbsSlider = new Swiper(thumbsSliderEl, {
        slidesPerView: 6,
        spaceBetween: 12,
        freeMode: true,
        watchSlidesProgress: true,
      });
    }

    const mainSliderEl = document.querySelector(".product-image-desktop-swiper");
    if (mainSliderEl) {
      new Swiper(mainSliderEl, {
        effect: "slide",
        thumbs: {
          swiper: thumbsSlider,
        },
        navigation: {
          nextEl: mainSliderEl.querySelector(".swiper-button-next"),
          prevEl: mainSliderEl.querySelector(".swiper-button-prev"),
        },
      });
    }

    // Mobile Product Gallery
    new Swiper(".product-image-mobile-swiper", {
        loop: true,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
  };
  initSwipers();

  const updateHeaderPadding = () => {
    const main = document.querySelector('main.flex-grow');
    const header = document.getElementById('main-header');
    if (main && header) {
        let height = header.offsetHeight;
        if (height > 0) {
            const isHomePage = window.location.pathname === '/' || window.location.pathname === '/index' || document.body.classList.contains('is-home');
            if (!isHomePage) {
                height += 24; // Standard spacing after header for non-home pages
            }
            main.style.setProperty('padding-top', height + 'px', 'important');
        }
    }
  };
  window.addEventListener('load', updateHeaderPadding);
  window.addEventListener('resize', updateHeaderPadding);
  setInterval(updateHeaderPadding, 1000); // Periodic check to catch layout shifts


if (typeof noUiSlider !== "undefined") {
  document.querySelectorAll("#shop-price-slider").forEach((item) => {
    noUiSlider.create(item, { start: [0, 100_000_000], direction: "rtl", connect: true, range: { min: 0, max: 100_000_000 }, format: { to: (v) => v.toLocaleString("en-US", { maximumFractionDigits: 0 }), from: (v) => parseFloat(v.replace(/,/g, "")) } });
    item.noUiSlider.on("update", (v, h) => { (h ? document.querySelectorAll("#shop-price-slider-max") : document.querySelectorAll("#shop-price-slider-min")).forEach(t => t.innerHTML = v[h]); });
  });
}

function initSearch(baseId, wrapperId, searchId, resultId, resultsDivId) {
  const b = document.getElementById(baseId), w = document.getElementById(wrapperId), s = document.getElementById(searchId), r = document.getElementById(resultId), rd = document.getElementById(resultsDivId);
  if (!b || !w || !s || !r) return;
  const hide = () => { w.classList.remove("border", "bg-muted", "rounded-b-none", "ring-4", "ring-primary/10", "z-50", "relative"); r.classList.add("hidden"); headerOverlay.classList.add("hidden"); };
  s.addEventListener("focus", () => {
    w.classList.add("border", "bg-muted", "rounded-b-none", "ring-4", "ring-primary/10", "z-50", "relative");
    if (s.value.trim().length > 0) {
        r.classList.remove("hidden");
        headerOverlay.classList.remove("hidden");
    }
  });

  let debounceTimer;
  s.addEventListener("input", () => {
    const q = s.value.trim();
    if (q.length === 0) {
        r.classList.add("hidden");
        headerOverlay.classList.add("hidden");
        return;
    }
    r.classList.remove("hidden");
    headerOverlay.classList.remove("hidden");

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        if (rd) rd.innerHTML = '<div class="p-4 text-center"><div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-primary border-t-transparent"></div></div>';
        fetch(`/api/services/autocomplete/?q=${encodeURIComponent(q)}`)
            .then(res => res.json())
            .then(res => {
                const data = res.data?.auto_complete || [];
                if (data.length === 0) {
                    if (rd) rd.innerHTML = '<div class="p-6 text-center text-slate-400 text-sm">نتیجه‌ای یافت نشد</div>';
                    return;
                }
                let html = '';
                data.forEach(item => {
                    const keyword = item.keyword || '';
                    if (!keyword) return;
                    html += `
                        <a href="/result/?q=${encodeURIComponent(keyword)}" class="flex items-center gap-3 p-3 rounded-squircle hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-white/10 text-slate-400 group-hover:bg-primary group-hover:text-white transition-all">
                                <span class="material-icons text-sm">search</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate group-hover:text-primary transition-colors">${keyword}</h4>
                            </div>
                            <span class="material-icons text-slate-300 group-hover:text-primary transition-colors text-lg">chevron_left</span>
                        </a>
                    `;
                });
                if (rd) rd.innerHTML = html;
            })
            .catch(() => {
                if (rd) rd.innerHTML = '<div class="p-6 text-center text-danger text-sm">خطا در برقراری ارتباط</div>';
            });
    }, 400);
  });

  return { b, hide };
}
const ds = initSearch("desktopHeaderSearchBase", "desktopHeaderSearchWrapper", "desktopHeaderSearch", "desktopHeaderSearchResult", "desktopAutocompleteResults");
const ms = initSearch("mobileHeaderSearchBase", "mobileHeaderSearchWrapper", "mobileHeaderSearch", "mobileHeaderSearchResult", "mobileAutocompleteResults");
document.addEventListener("mousedown", (e) => { if (ds && !ds.b.contains(e.target)) ds.hide(); if (ms && !ms.b.contains(e.target)) ms.hide(); });

document.querySelectorAll('button[data-action="decrement"]').forEach(b => b.addEventListener("click", (e) => { const t = e.target.closest(".flex").querySelector("input"); if (t && t.value > 1) t.value = Number(t.value) - 1; }));
document.querySelectorAll('button[data-action="increment"]').forEach(b => b.addEventListener("click", (e) => { const t = e.target.closest(".flex").querySelector("input"); if (t) t.value = Number(t.value) + 1; }));

document.querySelectorAll("[data-accordion-item]").forEach((item) => {
  const btn = item.querySelector("[data-accordion-button]");
  if (btn) btn.addEventListener("click", (e) => {
    e.stopPropagation(); const isOpen = item.classList.contains("open");
    Array.from(item.parentElement.children).forEach(s => { if (s !== item && s.classList.contains("open")) { s.classList.remove("open"); s.querySelector("[data-accordion-content]").style.maxHeight = null; } });
    if (isOpen) { item.classList.remove("open"); item.querySelector("[data-accordion-content]").style.maxHeight = null; }
    else { item.classList.add("open"); item.querySelector("[data-accordion-content]").style.maxHeight = item.querySelector("[data-accordion-content]").scrollHeight + "px"; }
  });
});

document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        const s = document.getElementById('desktopHeaderSearch');
        if (s) { e.preventDefault(); s.focus(); }
    }
    if (e.altKey && e.key === 't') {
        const t = document.getElementById('toggleThemeDesktop');
        if (t) { e.preventDefault(); t.click(); }
    }
    if (e.altKey && e.key === 'h') {
        e.preventDefault();
        window.location.href = '/';
    }
});
});
