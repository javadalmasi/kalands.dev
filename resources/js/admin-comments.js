(function() {
    document.getElementById('select-all-comments')?.addEventListener('change', function (event) {
        document.querySelectorAll('.comment-item-checkbox').forEach(function (checkbox) {
            checkbox.checked = event.target.checked;
        });
    });
})();
