(function () {
    var root = document.documentElement;
    var sidebar = document.getElementById('sidebar');
    var openBtn = document.getElementById('sidebarOpen');
    var closeBtn = document.getElementById('sidebarClose');
    var themeBtn = document.getElementById('themeToggle');

    function toggleSidebar(open) {
        if (!sidebar) return;
        if (open === true) sidebar.classList.add('is-open');
        else if (open === false) sidebar.classList.remove('is-open');
        else sidebar.classList.toggle('is-open');
    }

    function toggleTheme() {
        var isLight = root.classList.toggle('light');
        try { localStorage.setItem('theme', isLight ? 'light' : 'dark'); } catch (e) {}
    }

    // Init
    try {
        var savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') root.classList.add('light');
    } catch (e) {}

    if (openBtn) openBtn.addEventListener('click', function () { toggleSidebar(true); });
    if (closeBtn) closeBtn.addEventListener('click', function () { toggleSidebar(false); });
    if (themeBtn) themeBtn.addEventListener('click', toggleTheme);

    // Click outside sidebar to close on mobile
    document.addEventListener('click', function (e) {
        if (!sidebar) return;
        if (!sidebar.classList.contains('is-open')) return;
        var withinSidebar = sidebar.contains(e.target) || (openBtn && openBtn.contains(e.target));
        if (!withinSidebar) toggleSidebar(false);
    });
})();


