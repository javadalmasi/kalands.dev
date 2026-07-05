/**
 * Help Offcanvas Sidebar Manager
 * Handles opening, closing, and keyboard interaction for module help sidebars
 */

export function initAllHelpOffcanvas() {
    document.querySelectorAll('[id$="-backdrop"]').forEach((backdrop) => {
        const randomId = backdrop.id.replace('-backdrop', '');
        const sidebar = document.getElementById(randomId);
        const helpBtn = document.getElementById(`help-btn-${randomId}`);
        const closeBtn = sidebar?.querySelector('[data-close-offcanvas]');

        if (!sidebar) return;

        const closeOffcanvas = () => {
            sidebar.classList.add('hidden');
            backdrop.classList.add('hidden');
        };

        // Open on button click
        if (helpBtn) {
            helpBtn.addEventListener('click', () => {
                sidebar.classList.remove('hidden');
                backdrop.classList.remove('hidden');
            });
        }

        // Close on close button click
        if (closeBtn) {
            closeBtn.addEventListener('click', closeOffcanvas);
        }

        // Keep sidebar and backdrop in sync
        const observer = new MutationObserver(() => {
            if (sidebar.classList.contains('hidden')) {
                backdrop.classList.add('hidden');
            } else {
                backdrop.classList.remove('hidden');
            }
        });

        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });

        // Close on ESC key
        const escHandler = (e) => {
            if (e.key === 'Escape' && !sidebar.classList.contains('hidden')) {
                closeOffcanvas();
            }
        };

        document.addEventListener('keydown', escHandler);

        // Close on backdrop click
        backdrop.addEventListener('click', closeOffcanvas);
    });
}
