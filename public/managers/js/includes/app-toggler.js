document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var toggler = document.querySelector('.app-toggler');
    var menubar = document.getElementById('appMenubar');
    if (!toggler || !menubar) return;

    var MINI_BREAKPOINT = 1480;

    function isDesktop() {
        return window.innerWidth > MINI_BREAKPOINT;
    }

    function syncAria() {
        if (isDesktop()) {
            var collapsed = document.documentElement.getAttribute('data-app-sidebar') === 'mini';
            toggler.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        } else {
            toggler.setAttribute('aria-expanded', menubar.classList.contains('open') ? 'true' : 'false');
        }
    }

    toggler.addEventListener('click', function () {
        if (isDesktop()) {
            var isMini = document.documentElement.getAttribute('data-app-sidebar') === 'mini';
            if (isMini) {
                document.documentElement.removeAttribute('data-app-sidebar');
                localStorage.setItem('mc-app-sidebar', 'full');
            } else {
                document.documentElement.setAttribute('data-app-sidebar', 'mini');
                localStorage.setItem('mc-app-sidebar', 'mini');
            }
        } else {
            var isOpen = menubar.classList.toggle('open');
            toggler.classList.toggle('active', isOpen);
        }
        syncAria();
    });

    // Cerrar el cajón móvil al pulsar fuera. Por debajo de 576px el propio
    // botón ya trae su backdrop (::after en nav.css); esto cubre el rango
    // 576-1480px, donde el cajón empuja el contenido en vez de flotar.
    document.addEventListener('click', function (e) {
        if (isDesktop() || !menubar.classList.contains('open')) return;
        if (menubar.contains(e.target) || toggler.contains(e.target)) return;
        menubar.classList.remove('open');
        toggler.classList.remove('active');
        syncAria();
    });

    // Mini-hover: al pasar el ratón sobre el riel contraído en escritorio,
    // desplegar el panel temporalmente sin mover el contenido (ver el
    // bloque [data-app-sidebar=mini-hover] en nav.css).
    menubar.addEventListener('mouseenter', function () {
        if (isDesktop() && document.documentElement.getAttribute('data-app-sidebar') === 'mini') {
            document.documentElement.setAttribute('data-app-sidebar', 'mini-hover');
        }
    });
    menubar.addEventListener('mouseleave', function () {
        if (document.documentElement.getAttribute('data-app-sidebar') === 'mini-hover') {
            document.documentElement.setAttribute('data-app-sidebar', 'mini');
        }
    });

    window.addEventListener('resize', syncAria);
    syncAria();
});
