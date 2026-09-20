$(function () {
    var $content = $('#certificatesContent');

    function load(url, pushState) {
        $content.css('opacity', .5);
        $.get(url, function (data) {
            $content.html(data.html);
            $content.css('opacity', 1);
            $('#cxStatNumber').text(data.total);
            $('#cxStatLabel').text(data.label);
            if (pushState !== false) {
                window.history.pushState({ certificatesAjax: true }, '', url);
            }
        });
    }

    // Mismo criterio que Mis pedidos/Documentos: cualquier link dentro
    // del contenido que apunte al mismo path (el filtro por estado, el
    // paginador, "Ver todos los certificados") cambia solo el query
    // string -- se recarga por AJAX en vez de refrescar toda la página.
    $(document).on('click', '#certificatesContent a', function (e) {
        if (this.pathname !== window.location.pathname) { return; }
        e.preventDefault();
        load(this.href);
    });

    // Buscador: mismo criterio que Mis pedidos -- se resuelve por AJAX
    // en vez de refrescar toda la página.
    $(document).on('submit', '#certificatesContent .pnl-search', function (e) {
        e.preventDefault();
        load(this.action + '?' + $(this).serialize());
    });

    window.addEventListener('popstate', function () {
        load(window.location.href, false);
    });
});
