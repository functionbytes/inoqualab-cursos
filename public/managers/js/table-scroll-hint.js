/*
 * Pista visual de scroll horizontal para .table-responsive (ver comentario
 * de cabecera en managers/css/table-scroll-hint.css).
 *
 * Alterna la clase .has-scroll-right en cada .table-responsive segun su
 * estado real de scroll (scrollWidth/clientWidth/scrollLeft). Se auto-
 * inicializa para todas las instancias presentes al cargar la pagina y usa
 * un MutationObserver para detectar tablas insertadas despues via AJAX
 * (algunas vistas del panel cargan su tabla asi) sin tener que tocar cada
 * vista una por una.
 */
$(function () {
    'use strict';

    var SELECTOR = '.table-responsive';
    var END_THRESHOLD = 4; // margen en px para evitar parpadeo por redondeo subpixel

    function updateHint($container) {
        var el = $container[0];
        var hasOverflow = el.scrollWidth > el.clientWidth;
        var atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - END_THRESHOLD;
        $container.toggleClass('has-scroll-right', hasOverflow && !atEnd);
    }

    function bind($container) {
        if ($container.data('scroll-hint-bound')) {
            return;
        }
        $container.data('scroll-hint-bound', true);
        $container.on('scroll', function () {
            updateHint($container);
        });
        updateHint($container);
    }

    function initAll() {
        $(SELECTOR).each(function () {
            bind($(this));
        });
    }

    function recalcAll() {
        $(SELECTOR).each(function () {
            updateHint($(this));
        });
    }

    initAll();

    // Fuentes/imagenes pueden reflow la tabla despues del DOM ready inicial.
    $(window).on('load', recalcAll);

    var resizeTimer = null;
    $(window).on('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(recalcAll, 150);
    });

    // Vistas que insertan su tabla via AJAX despues de la carga inicial.
    if (window.MutationObserver) {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType !== 1) {
                        return;
                    }
                    if (node.matches && node.matches(SELECTOR)) {
                        bind($(node));
                    }
                    if (node.querySelectorAll) {
                        $(node.querySelectorAll(SELECTOR)).each(function () {
                            bind($(this));
                        });
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }
});
