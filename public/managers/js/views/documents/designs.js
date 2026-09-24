// Propuestas A/B/C de orden y factura: el ancho de cada barra viene en
// data-width (porcentaje) para no usar estilos inline en Blade.
$(function () {
    $('[data-width]').each(function () {
        var $bar = $(this);
        requestAnimationFrame(function () {
            $bar.css('width', Math.max(0, Math.min(100, parseFloat($bar.attr('data-width')) || 0)) + '%');
        });
    });
});
