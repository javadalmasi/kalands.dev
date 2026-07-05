/**
 * Help Offcanvas Sidebar Manager
 * Handles opening, closing, and keyboard interaction for module help sidebars
 */

export function initAllHelpOffcanvas() {
    const adminSidebar = document.getElementById('admin-sidebar');

    document.querySelectorAll('[id$="-backdrop"]').forEach((backdrop) => {
        const randomId = backdrop.id.replace('-backdrop', '');
        const helpOffcanvas = document.getElementById(randomId);
        const helpBtn = document.getElementById(`help-btn-${randomId}`);
        const closeBtn = helpOffcanvas?.querySelector('[data-close-offcanvas]');

        if (!helpOffcanvas) return;

        const closeOffcanvas = () => {
            helpOffcanvas.classList.add('hidden');
            backdrop.classList.add('hidden');
            // Show admin sidebar again
            if (adminSidebar) {
                adminSidebar.classList.add('pointer-events-auto');
                adminSidebar.style.opacity = '1';
            }
        };

        // Open on button click
        if (helpBtn) {
            helpBtn.addEventListener('click', () => {
                helpOffcanvas.classList.remove('hidden');
                backdrop.classList.remove('hidden');
                // Hide admin sidebar
                if (adminSidebar) {
                    adminSidebar.classList.add('pointer-events-none');
                    adminSidebar.style.opacity = '0.5';
                }
            });
        }

        // Close on close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', closeOffcanvas);
        }

        // Keep offcanvas and backdrop in sync
        const observer = new MutationObserver(() => {
            if (helpOffcanvas.classList.contains('hidden')) {
                backdrop.classList.add('hidden');
                if (adminSidebar) {
                    adminSidebar.classList.add('pointer-events-auto');
                    adminSidebar.style.opacity = '1';
                }
            } else {
                backdrop.classList.remove('hidden');
                if (adminSidebar) {
                    adminSidebar.classList.add('pointer-events-none');
                    adminSidebar.style.opacity = '0.5';
                }
            }
        });

        observer.observe(helpOffcanvas, { attributes: true, attributeFilter: ['class'] });

        // Close on ESC key
        const escHandler = (e) => {
            if (e.key === 'Escape' && !helpOffcanvas.classList.contains('hidden')) {
                closeOffcanvas();
            }
        };

        document.addEventListener('keydown', escHandler);

        // Close on backdrop click
        backdrop.addEventListener('click', closeOffcanvas);
    });
}
