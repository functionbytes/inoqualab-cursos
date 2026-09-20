$(function () {
    var $content = $('#documentsContent');

    function load(url, pushState) {
        $content.css('opacity', .5);
        $.get(url, function (data) {
            $content.html(data.html);
            $content.css('opacity', 1);
            $('#cxStatNumber').text(data.total);
            $('#cxStatLabel').text(data.label);
            if (pushState !== false) {
                window.history.pushState({ documentsAjax: true }, '', url);
            }
        });
    }

    // Mismo criterio que Mis pedidos: cualquier link dentro del contenido
    // que apunte al mismo path (el paginador, "Quitar búsqueda", "Ver
    // todos los documentos") cambia solo el query string -- se recarga
    // por AJAX en vez de refrescar toda la página.
    $(document).on('click', '#documentsContent a', function (e) {
        if (this.pathname !== window.location.pathname) { return; }
        e.preventDefault();
        load(this.href);
    });

    $(document).on('submit', '#documentsContent .pnl-search', function (e) {
        e.preventDefault();
        load(this.action + '?' + $(this).serialize());
    });

    window.addEventListener('popstate', function () {
        load(window.location.href, false);
    });
});
