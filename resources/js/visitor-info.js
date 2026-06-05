window.__visitorInfo = fetch('/api/info').then(r => r.text()).then(text => {
    const data = Object.fromEntries(text.trim().split('\n').map(l => l.split('=', 2)));
    window.__visitorInfoData = data;
    return data;
}).catch(() => {
    window.__visitorInfoData = {};
    return {};
});
