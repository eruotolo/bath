(function () {
    'use strict';

    var sidebar = document.getElementById('sb-sidebar');
    var backdrop = document.getElementById('sb-backdrop');
    if (!sidebar || !backdrop) return;

    function open() {
        sidebar.classList.add('is-open');
        backdrop.classList.add('is-visible');
    }

    function close() {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
    }

    function toggle() {
        if (sidebar.classList.contains('is-open')) {
            close();
        } else {
            open();
        }
    }

    var hamburger = document.getElementById('vertical-menu-btn');
    if (hamburger) {
        hamburger.addEventListener('click', function (e) {
            e.preventDefault();
            toggle();
        });
    }

    var closeBtn = document.getElementById('sb-close-btn');
    if (closeBtn) closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('is-open')) close();
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
})();
