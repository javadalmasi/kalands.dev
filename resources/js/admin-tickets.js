(function() {
    document.getElementById('select-all-tickets')?.addEventListener('change', function (event) {
        document.querySelectorAll('.ticket-item-checkbox').forEach(function (checkbox) {
            checkbox.checked = event.target.checked;
        });
    });
})();
