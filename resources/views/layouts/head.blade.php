<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite($entryPoint ?? 'resources/js/app.js')
    <script>
        // Set Theme from Localstorage at site load
        (function() {
            const isAdmin = window.location.pathname.includes('/dash/admin/');
            if (!isAdmin) {
                document.documentElement.classList.toggle(
                    "dark",
                    localStorage.getItem("theme") === "dark",
                );
            } else {
                const adminTheme = localStorage.getItem('admin.theme') || 'dark';
                document.documentElement.classList.toggle("dark", adminTheme === 'dark');
                document.documentElement.classList.remove("admin-light", "admin-dark");
                document.documentElement.classList.add(adminTheme === 'light' ? 'admin-light' : 'admin-dark');
            }
        })();

        // Suppress Livewire error modals and alerts
        window.addEventListener('DOMContentLoaded', function() {
            // Override Livewire error handling to prevent modal/alert popups
            if (window.Livewire) {
                // Catch Livewire errors and handle them silently
                Livewire.on('error', (errors) => {
                    console.error('Livewire error:', errors);
                    // Prevent default error behavior (modal/alert)
                    // Just log to console instead of showing popup
                });
            }

            // Also override global error handlers that might trigger modals
            window.addEventListener('unhandledrejection', event => {
                console.error('Unhandled promise rejection:', event.reason);
                event.preventDefault(); // Prevent default error handling
            });
        });
    </script>
    @yield('head')
    @stack('head')
    @stack('styles')
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('/assets/images/icons/apple-touch-icon.png?v=1.0') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('/assets/images/icons/favicon-32x32.png?v=1.0') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('/assets/images/icons/favicon-16x16.png?v=1.0') }}">
    <link rel="manifest" href="{{ asset('/assets/images/icons/site.webmanifest?v=1.0') }}">
    <link rel="mask-icon" href="{{ asset('/assets/images/icons/safari-pinned-tab.svg?v=1.0') }}" color="#fc562f">
    <link rel="shortcut icon" href="{{ asset('/assets/images/icons/favicon.ico?v=1.0') }}">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-TileImage" content="{{ asset('/assets/images/icons/mstile-144x144.png?v=1.0') }}">
    <meta name="msapplication-config" content="{{ asset('/assets/images/icons/browserconfig.xml?v=1.0') }}">
    <meta name="theme-color" content="#da532c">
</head>
