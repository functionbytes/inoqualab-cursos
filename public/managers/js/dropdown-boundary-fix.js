/*
 * Los dropdowns de acciones (fa-ellipsis-vertical) dentro de una tabla
 * envuelta en .table-responsive (overflow-x: auto) se recortan visualmente
 * cuando la fila esta cerca del borde inferior/derecho del contenedor con
 * scroll, porque Popper posiciona el .dropdown-menu con position:absolute
 * dentro de ese contenedor y el overflow lo recorta.
 *
 * Forzar strategy:'fixed' en Popper hace que el menu se posicione respecto
 * al viewport (escapa del overflow de ancestros sin transform/filter), sin
 * tener que tocar data-bs-boundary en cada vista. Se auto-inicializa para
 * los triggers presentes al cargar la pagina y observa el DOM para los que
 * insertan su tabla via AJAX despues (ver managers/js/ajax-table.js).
 */
$(function () {
    'use strict';

    var SELECTOR = '[data-bs-toggle="dropdown"]';

    function fixedStrategy(defaultBsPopperConfig) {
        return Object.assign({}, defaultBsPopperConfig, { strategy: 'fixed' });
    }

    function initOne(el) {
        if (bootstrap.Dropdown.getInstance(el)) {
            return;
        }
        new bootstrap.Dropdown(el, { popperConfig: fixedStrategy });
    }

    function initAll(root) {
        (root || document).querySelectorAll(SELECTOR).forEach(initOne);
    }

    initAll();

    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) {
                        return;
                    }
                    if (node.matches && node.matches(SELECTOR)) {
                        initOne(node);
                    }
                    if (node.querySelectorAll) {
                        initAll(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }
});
