document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var menubar = document.getElementById('appMenubar');
    if (!menubar) return;

    // El ítem activo ya llega marcado con .active desde el servidor, pero
    // eso solo pinta la clase — nada movía el scroll del panel para que
    // quedara a la vista. En secciones largas (p. ej. Configuración) el
    // usuario tenía que desplazarse a mano para encontrar dónde estaba.
    function scrollActiveIntoView(pane) {
        var active = pane.querySelector('.menu-link.active');
        if (!active) return;

        var scrollEl = active.closest('[data-simplebar]');
        if (!scrollEl) return;

        var instance = SimpleBar.instances.get(scrollEl);
        var container = instance ? instance.getScrollElement() : scrollEl;

        var containerRect = container.getBoundingClientRect();
        var activeRect = active.getBoundingClientRect();
        var margin = 24;

        if (activeRect.top >= containerRect.top + margin && activeRect.bottom <= containerRect.bottom - margin) {
            return;
        }

        var offset = (activeRect.top - containerRect.top) + container.scrollTop
            - (containerRect.height / 2) + (activeRect.height / 2);
        container.scrollTop = Math.max(0, offset);
    }

    function recalcSimplebars(pane) {
        pane.querySelectorAll('[data-simplebar]').forEach(function (el) {
            var instance = SimpleBar.instances.get(el);
            if (instance) {
                instance.recalculate();
            } else {
                new SimpleBar(el);
            }
        });
    }

    document.querySelectorAll('#appMenubarTabs [data-bs-toggle="tab"]').forEach(function (tab) {
        tab.addEventListener('show.bs.tab', function () {
            menubar.classList.remove('no-sidebar-open');
        });

        tab.addEventListener('shown.bs.tab', function (e) {
            var pane = document.querySelector(e.target.getAttribute('href'));
            if (!pane) return;
            recalcSimplebars(pane);
            scrollActiveIntoView(pane);
        });
    });

    document.querySelectorAll('#appMenubarTabsContent .tab-pane.active').forEach(function (pane) {
        recalcSimplebars(pane);
        scrollActiveIntoView(pane);
    });
});
