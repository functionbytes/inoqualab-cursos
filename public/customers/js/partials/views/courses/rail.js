$(function () {
    var $active = $('.lv-lesson.active, .lesson-row.active').first();
    if ($active.length) {
        var $rail = $active.closest('.lv-rail, .aula-side');
        if ($rail.length) {
            var target = $active.offset().top - $rail.offset().top + $rail.scrollTop() - ($rail.innerHeight() / 2) + ($active.outerHeight() / 2);
            $rail.scrollTop(Math.max(0, target));
        }
    }
});

// Toggle del rail en mobile (.lv-rail-toggle, ver lesson-content.blade.php
// y quiz-questions.blade.php). En desktop .lv-rail es sticky y siempre
// visible -- este código no tiene efecto ahí (el botón está oculto por
// CSS), solo aplica al breakpoint donde .lv-rail pasa a position:fixed.
$(function () {
    var $rail = $('#lvRail');
    var $backdrop = $('#lvRailBackdrop');
    var $toggles = $('.lv-rail-toggle');
    if (!$rail.length || !$toggles.length) return;

    function setOpen(open) {
        $rail.toggleClass('open', open);
        $backdrop.toggleClass('open', open);
        $toggles.attr('aria-expanded', open ? 'true' : 'false');
        document.body.style.overflow = open ? 'hidden' : '';
        // El header del sitio (logo/avatar) es position:relative con su propio
        // z-index -- el backdrop lo cubre para clics, pero al ser blanco+navy
        // (muy parecido al color del overlay) se veía "encima" a simple vista.
        // Se oculta del todo mientras la hoja está abierta, en vez de confiar
        // en el oscurecido del backdrop.
        document.body.classList.toggle('lv-rail-open', open);
    }

    $toggles.on('click', function () { setOpen(!$rail.hasClass('open')); });
    $backdrop.on('click', function () { setOpen(false); });
    // Al elegir una clase del rail, se navega de todos modos -- cerrarlo
    // solo evita el parpadeo del panel abierto durante esa navegación.
    $rail.on('click', '.lv-lesson:not(.pe-none)', function () { setOpen(false); });
    // Esc para cerrar, igual que el menú de usuario (ese lo trae gratis de
    // Bootstrap; este toggle es JS propio y no lo tenía).
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $rail.hasClass('open')) { setOpen(false); }
    });
});
