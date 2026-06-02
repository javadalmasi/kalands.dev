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

    @php
        $analyticsSettings = app(\App\Services\InternalAnalyticsService::class)->settings();
    @endphp
    @if(($analyticsSettings['enabled'] ?? true) && ($analyticsSettings['tracking_script_enabled'] ?? true))
        <script>
            (function() {
                if (window.location.pathname.indexOf('/dash/admin/') !== -1) {
                    return;
                }

                var endpoint = "{{ route('analytics.collect') }}";
                var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                var visitorKey = 'internal_analytics_visitor_id';
                var visitorId = localStorage.getItem(visitorKey);
                var errorTrackingEnabled = @json($analyticsSettings['error_tracking_enabled'] ?? true);
                var queue = [];
                var timer = null;
                var sentErrors = 0;

                if (!visitorId) {
                    visitorId = (window.crypto && window.crypto.randomUUID) ? window.crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2);
                    localStorage.setItem(visitorKey, visitorId);
                }

                var withBase = function(payload) {
                    return Object.assign({
                        visitor_id: visitorId,
                        url: window.location.href,
                        path: window.location.pathname,
                        title: document.title,
                        referrer: document.referrer
                    }, payload || {});
                };

                var sendNow = function(body) {
                    fetch(endpoint, {
                        method: 'POST',
                        credentials: 'same-origin',
                        keepalive: true,
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify(body)
                    }).catch(function() {});
                };

                var flush = function() {
                    timer = null;
                    if (!queue.length) return;
                    var batch = queue.splice(0, 10).map(withBase);
                    sendNow(batch.length === 1 ? batch[0] : {events: batch});
                    if (queue.length) {
                        timer = window.setTimeout(flush, 250);
                    }
                };

                var enqueue = function(payload, immediate) {
                    queue.push(payload);
                    if (timer) return;
                    timer = window.setTimeout(flush, immediate ? 0 : 600);
                };

                window.internalAnalytics = {
                    trackPageView: function(extra) {
                        enqueue(Object.assign({event_type: 'pageview'}, extra || {}), false);
                    },
                    trackGoal: function(goalKey, extra) {
                        enqueue(Object.assign({event_type: 'goal', goal_key: goalKey}, extra || {}), true);
                    },
                    trackError: function(error, extra) {
                        if (!errorTrackingEnabled || sentErrors >= 5) return;
                        sentErrors++;
                        enqueue(Object.assign({
                            event_type: 'error',
                            error_message: String(error && error.message ? error.message : error || 'خطای سمت کاربر').slice(0, 1000),
                            error_source: error && error.filename ? error.filename : window.location.href,
                            error_line: error && error.lineno ? error.lineno : null
                        }, extra || {}), true);
                    }
                };

                window.internalAnalytics.trackPageView();

                window.addEventListener('error', function(event) {
                    window.internalAnalytics.trackError(event);
                });

                window.addEventListener('unhandledrejection', function(event) {
                    window.internalAnalytics.trackError(event.reason || 'Unhandled promise rejection');
                });

                window.addEventListener('pagehide', flush);
                document.addEventListener('visibilitychange', function() {
                    if (document.visibilityState === 'hidden') {
                        flush();
                    }
                });
            })();
        </script>
    @endif
</head>
