$(function () {
    var $content = $('#coursesContent');

    function load(url, pushState) {
        $content.css('opacity', .5);
        $.get(url, function (data) {
            $content.html(data.html);
            if (window.applyDynamicStyleVars) {
                window.applyDynamicStyleVars($content[0]);
            }
            $content.css('opacity', 1);
            $('#cxStatNumber').text(data.total);
            $('#cxStatLabel').text(data.label);
            if (pushState !== false) {
                window.history.pushState({ coursesAjax: true }, '', url);
            }
        });
    }

    // Mismo criterio que Certificados/Mis pedidos/Documentos: cualquier
    // link dentro del contenido que apunte al mismo path (el filtro por
    // estado, el paginador, "Ver todos los cursos") cambia solo el query
    // string -- se recarga por AJAX en vez de refrescar toda la página.
    $(document).on('click', '#coursesContent a', function (e) {
        if (this.pathname !== window.location.pathname) { return; }
        e.preventDefault();
        load(this.href);
    });

    $(document).on('submit', '#coursesContent .pnl-search', function (e) {
        e.preventDefault();
        load(this.action + '?' + $(this).serialize());
    });

    window.addEventListener('popstate', function () {
        load(window.location.href, false);
    });
});
