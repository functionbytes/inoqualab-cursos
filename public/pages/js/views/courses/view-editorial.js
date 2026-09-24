// Detalle de curso — Modalidad 2 "Editorial con imagen y ruta".
// El acordeón y "Expandir todo" los maneja view.js (misma página, mismas clases).
$(document).ready(function () {

    // "Comprar ahora" (hero y barra fija) vs "Agregar al carrito": ambos envían
    // #courseBuyForm, que layout-cart.js manda por AJAX.
    $(document).on('click', '.js-buy-now', function () { $('#buyNow').val('1'); });
    $(document).on('click', '.js-add-cart', function () { $('#buyNow').val(''); });

    // Objetivos: mostrar / ocultar los que pasan de los primeros 6
    $(document).on('click', '.js-objectives-toggle', function () {
        var $btn = $(this);
        var $extra = $('#cdeObjectives .is-extra');
        var opening = $extra.first().is('[hidden]');
        $extra.prop('hidden', !opening);
        $btn.text(opening ? $btn.attr('data-label-less') : $btn.attr('data-label-more'));
    });

    // Contenido: cada módulo muestra 8 clases; el botón despliega el resto
    $(document).on('click', '.js-lessons-toggle', function () {
        var $btn = $(this);
        var $extra = $btn.closest('.cde-acc-body').find('.cde-lesson.is-extra');
        var opening = $extra.first().is('[hidden]');
        $extra.prop('hidden', !opening);
        $btn.toggleClass('is-open', opening)
            .find('span').text(opening ? $btn.attr('data-label-less') : $btn.attr('data-label-more'));
    });

    // aria-expanded de cada módulo (el abrir/cerrar lo hace view.js)
    $(document).on('click', '.js-acc, #expandAll', function () {
        setTimeout(function () {
            $('.js-acc').each(function () { this.setAttribute('aria-expanded', $(this).hasClass('open') ? 'true' : 'false'); });
        }, 0);
    });

    // Barras de la ruta y del desglose por módulo: el ancho viene en data-width (sin estilos inline en Blade)
    $('.cde-step-bar span, .cde-mix-bar span').each(function () {
        var $bar = $(this);
        requestAnimationFrame(function () { $bar.css('width', ($bar.attr('data-width') || 0) + '%'); });
    });

    // Barra de compra fija: aparece cuando los botones del hero salen de pantalla
    var bar = document.getElementById('cdeBar');
    var heroActions = document.getElementById('cdeHeroActions');
    if (bar && heroActions && 'IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            var hidden = !entries[0].isIntersecting && entries[0].boundingClientRect.top < 0;
            bar.classList.toggle('is-visible', hidden);
            document.body.classList.toggle('cde-bar-on', hidden);
            bar.setAttribute('aria-hidden', hidden ? 'false' : 'true');
            $(bar).find('a, button').attr('tabindex', hidden ? null : '-1');
        }).observe(heroActions);
    }
});
