function showPageOverlay(message) {
    var overlay = document.getElementById('page-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'page-overlay';
        overlay.className = 'page-overlay';
        overlay.innerHTML =
            '<div class="page-overlay-box">' +
            '<div class="spinner-border" role="status"></div>' +
            '<div class="page-overlay-message"></div>' +
            '</div>';
        document.body.appendChild(overlay);
    }
    overlay.querySelector('.page-overlay-message').textContent = message;
    overlay.classList.add('show');
}

document.addEventListener('DOMContentLoaded', function () {
    var syncForm = document.getElementById('staff-sync-form');
    if (syncForm) {
        syncForm.addEventListener('submit', function (e) {
            if (!confirm('Sync staff data from staff_gwidb now? This may take a moment.')) {
                e.preventDefault();
                return;
            }
            showPageOverlay('Syncing staff data from staff_gwidb…');
            var btn = document.getElementById('staff-sync-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Syncing...';
        });
    }
});