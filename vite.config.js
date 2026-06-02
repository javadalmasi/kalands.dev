import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/admin-app.js',
                'resources/js/product-page.js',
                'resources/js/admin-icons.js',
                'resources/js/admin-file-manager.js',
                'resources/js/admin-email-templates.js',
                'resources/js/admin-rich-editor-global.js',
                'resources/js/admin-home-slider.js',
                'resources/js/admin-analytics.js',
                'resources/js/admin-home-banners-categories.js',
                'resources/js/admin-search.js',
                'resources/js/admin-megamenu.js',
                'resources/js/admin-error-pages.js',
                'resources/js/admin-search-hub.js',
                'resources/js/admin-communication.js',
                'resources/js/admin-analytics-hub.js',
                'resources/js/admin-home-items-hub.js',
                'resources/js/admin-megamenu-hub.js',
                'resources/css/admin-megamenu-hub.css',
                'resources/js/admin-comments-hub.js',
                'resources/js/admin-comments.js',
                'resources/js/admin-tickets-hub.js',
                'resources/js/admin-tickets.js',
                'resources/js/admin-contact-hub.js',
                'resources/js/admin-robots-hub.js',
                'resources/js/admin-geoip-hub.js',
                'resources/js/admin-file-manager-hub.js',
                'resources/js/admin-error-pages-hub.js',
                'resources/js/admin-products-hub.js',
                'resources/js/admin-products-checker.js',
                'resources/js/admin-queues-hub.js',
                'resources/js/admin-admins.js',
                'resources/js/admin-users.js',
                'resources/js/admin-roles-edit.js',
                'resources/js/admin-affiliate-settings.js',
                'resources/js/admin-faq-hub.js',
                'resources/js/admin-object-cache-hub.js',
                'resources/js/admin-artisan-commands-hub.js',
                'resources/js/admin-visitor-intelligence.js',
                'resources/js/error-pages.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        if (id.includes('highcharts')) {
                            return 'vendor-highcharts';
                        }
                        if (id.includes('swiper')) {
                            return 'vendor-swiper';
                        }
                        return 'vendor';
                    }
                }
            }
        },
        chunkSizeWarningLimit: 1000,
    }
});
