(function () {
    var STORAGE_KEY = 'sidebarCollapsed';

    function applyState(collapsed) {
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        document.querySelectorAll('#sidebar-toggle i').forEach(function (icon) {
            icon.className = collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
        });
    }

    var saved = localStorage.getItem(STORAGE_KEY) === '1';
    applyState(saved);

    document.addEventListener('DOMContentLoaded', function () {
        function toggle() {
            var collapsed = !document.body.classList.contains('sidebar-collapsed');
            applyState(collapsed);
            localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        }

        var btn = document.getElementById('sidebar-toggle');
        if (btn) btn.addEventListener('click', toggle);

        var reopenBtn = document.getElementById('sidebar-reopen');
        if (reopenBtn) reopenBtn.addEventListener('click', toggle);

        // Remember the sidebar's scroll position across full page reloads,
        // since every menu click here is a real navigation, not an in-page
        // route change - without this, the sidebar snaps back to the top
        // every time you click a link.
        var SCROLL_KEY = 'sidebarScrollTop';
        var sidebar = document.querySelector('.app-sidebar');
        if (sidebar) {
            var savedScroll = sessionStorage.getItem(SCROLL_KEY);
            if (savedScroll !== null) {
                sidebar.scrollTop = parseInt(savedScroll, 10) || 0;
            }
            sidebar.addEventListener('scroll', function () {
                sessionStorage.setItem(SCROLL_KEY, sidebar.scrollTop);
            });
        }
    });
})();