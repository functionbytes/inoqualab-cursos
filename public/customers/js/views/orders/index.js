$(function () {
    var $content = $('#ordersContent');

    function load(url, pushState) {
        $content.css('opacity', .5);
        $.get(url, function (data) {
            $content.html(data.html);
            $content.css('opacity', 1);
            $('#cxStatNumber').text(data.total);
            $('#cxStatLabel').text(data.label);
            if (pushState !== false) {
                window.history.pushState({ ordersAjax: true }, '', url);
            }
        });
    }

    // Cualquier link DENTRO del contenido que apunte al mismo path (el
    // filtro de estado, el paginador, "Quitar búsqueda", "Ver todos los
    // pedidos") cambia solo el query string -- se recarga por AJAX en vez
    // de refrescar toda la página. Un link a otro path (Ver detalle,
    // Pagar, Explorar el catálogo) sigue su comportamiento normal.
    $(document).on('click', '#ordersContent a', function (e) {
        if (this.pathname !== window.location.pathname) { return; }
        e.preventDefault();
        load(this.href);
    });

    $(document).on('submit', '#ordersContent .pnl-search', function (e) {
        e.preventDefault();
        load(this.action + '?' + $(this).serialize());
    });

    window.addEventListener('popstate', function () {
        load(window.location.href, false);
    });
});
