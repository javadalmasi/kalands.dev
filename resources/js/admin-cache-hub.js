document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('[data-tab-target]');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab-target');
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById(target).classList.remove('hidden');

            tabs.forEach(t => {
                t.classList.remove('text-primary', 'border-b-2', 'border-primary', 'font-bold');
                t.classList.add('text-slate', 'font-medium');
            });
            tab.classList.add('text-primary', 'border-b-2', 'border-primary', 'font-bold');
            tab.classList.remove('text-slate', 'font-medium');

            const url = new URL(window.location);
            url.searchParams.set('tab', target);
            window.history.pushState({}, '', url);
        });
    });

    const params = new URLSearchParams(window.location.search);
    const activeTab = params.get('tab');
    if (activeTab) {
        const tabBtn = document.querySelector(`[data-tab-target="${activeTab}"]`);
        if (tabBtn) tabBtn.click();
    }

    const swCacheLookup = document.getElementById('sw-cache-lookup');
    const cacheOptions = document.getElementById('cache-lookup-options');
    if (swCacheLookup && cacheOptions) {
        swCacheLookup.addEventListener('change', () => {
            cacheOptions.classList.toggle('opacity-50', !swCacheLookup.checked);
            cacheOptions.classList.toggle('pointer-events-none', !swCacheLookup.checked);
        });
    }

    const swAutocompleteCustom = document.getElementById('sw-autocomplete-custom');
    const autocompleteFields = document.getElementById('autocomplete-custom-fields');
    if (swAutocompleteCustom && autocompleteFields) {
        swAutocompleteCustom.addEventListener('change', () => {
            autocompleteFields.classList.toggle('hidden', !swAutocompleteCustom.checked);
        });
    }

    const swVisitorCustom = document.getElementById('sw-visitor-info-custom');
    const visitorFields = document.getElementById('visitor-info-custom-fields');
    if (swVisitorCustom && visitorFields) {
        swVisitorCustom.addEventListener('change', () => {
            visitorFields.classList.toggle('hidden', !swVisitorCustom.checked);
        });
    }

    const swAffiliateCustom = document.getElementById('sw-affiliate-custom');
    const affiliateFields = document.getElementById('affiliate-custom-fields');
    if (swAffiliateCustom && affiliateFields) {
        swAffiliateCustom.addEventListener('change', () => {
            affiliateFields.classList.toggle('hidden', !swAffiliateCustom.checked);
        });
    }

    const swProductCustom = document.getElementById('sw-product-custom');
    const productFields = document.getElementById('product-custom-fields');
    if (swProductCustom && productFields) {
        swProductCustom.addEventListener('change', () => {
            productFields.classList.toggle('hidden', !swProductCustom.checked);
        });
    }
});
