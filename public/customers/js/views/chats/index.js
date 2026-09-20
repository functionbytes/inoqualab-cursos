$(function () {
    var $content = $('#chatsContent');
    var routes = $content.data();

    function load(url, pushState) {
        $content.css('opacity', .5);
        $.get(url, function (data) {
            $content.html(data.html);
            $content.css('opacity', 1);
            if (pushState !== false) {
                window.history.pushState({ chatsAjax: true }, '', url);
            }
        });
    }

    // Buscador: mismo criterio que Mis pedidos/Certificados/Mis cursos -- se
    // resuelve por AJAX en vez de refrescar toda la página.
    $(document).on('submit', '#chatsContent .pnl-search', function (e) {
        e.preventDefault();
        load(this.action + '?' + $(this).serialize());
    });

    $(document).on('click', '#chatsContent .od-searching a', function (e) {
        if (this.pathname !== window.location.pathname) { return; }
        e.preventDefault();
        load(this.href);
    });

    window.addEventListener('popstate', function () {
        load(window.location.href, false);
    });

    function marcar(id, $item, cb) {
        $.ajax({
            url: routes.markUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { id: id },
            success: function () {
                $item.removeClass('is-new');
                $item.find('.unread-badge, .btn-mark-read').remove();
                if (cb) { cb(); }
            },
            error: function () {
                toastr.error('Error al marcar la notificación');
            }
        });
    }

    $(document).on('click', '.btn-mark-read', function () {
        var $btn = $(this);
        marcar($btn.data('id'), $btn.closest('.notification-item'), function () {
            toastr.success('Notificación marcada como leída');
        });
    });

    // Marcar todas: una petición por notificación sin leer, que es lo que
    // acepta la ruta actual; el botón desaparece cuando no queda ninguna.
    $(document).on('click', '#ntReadAll', function () {
        var $pendientes = $('.notification-item.is-new');

        if (! $pendientes.length) { return; }

        $(this).prop('disabled', true);

        $pendientes.each(function () {
            var $item = $(this);
            marcar($item.data('id'), $item);
        });

        $(this).remove();
        toastr.success('Notificaciones marcadas como leídas');
    });

    // Eliminar: quita la tarjeta (y el día completo si quedó sin
    // notificaciones) sin recargar la página.
    $(document).on('click', '.nt-del', function () {
        var $btn = $(this);
        var $item = $btn.closest('.notification-item');
        var $group = $item.closest('.nt-group');

        $btn.prop('disabled', true);

        $.ajax({
            url: routes.deleteUrl,
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { id: $item.data('id') },
            success: function () {
                var $day = $group.prev('.nt-day');
                $item.remove();
                if (! $group.children('.notification-item').length) {
                    $group.remove();
                    $day.remove();
                }
                toastr.success('Notificación eliminada');
            },
            error: function () {
                $btn.prop('disabled', false);
                toastr.error('No se pudo eliminar la notificación');
            }
        });
    });
});
