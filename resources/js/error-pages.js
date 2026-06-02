window.trackErrorToAnalytics = function(code, pageUrl) {
    if (!window.internalAnalytics) {
        return;
    }

    window.internalAnalytics.trackError('HTTP error ' + code, {
        url: pageUrl,
        path: window.location.pathname,
        title: document.title,
        error_message: 'HTTP error ' + code,
        error_source: pageUrl
    });
};
