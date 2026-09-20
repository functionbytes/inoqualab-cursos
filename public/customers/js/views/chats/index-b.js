$(function () {
    var $shell = $('.nb-shell');
    var routes = $shell.data();
    var seleccionada = null;

    function pintar($row) {
        seleccionada = $row;

        $('.nb-row').removeClass('is-active');
        $row.addClass('is-active');

        $('#nbTitle').text($row.data('title'));
        $('#nbMessage').text($row.data('message'));
        $('#nbDate').text($row.data('date'));

        var link = $row.data('link');
        $('#nbLink').toggle(!! link).attr('href', link || '#');
        $('#nbMarkRead').toggle($row.hasClass('is-new'));
    }

    function marcar(id, $item, cb) {
        $.ajax({
            url: routes.markUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { id: id },
            success: function () {
                $item.removeClass('is-new');
                $item.find('.unread-badge').remove();
                if (cb) { cb(); }
            },
            error: function () {
                toastr.error('Error al marcar la notificación');
            }
        });
    }

    $('.nb-row').on('click', function () { pintar($(this)); });

    $('#nbMarkRead').on('click', function () {
        if (! seleccionada) { return; }

        marcar(seleccionada.data('id'), seleccionada, function () {
            $('#nbMarkRead').hide();
            toastr.success('Notificación marcada como leída');
        });
    });

    $('#ntReadAll').on('click', function () {
        var $pendientes = $('.nb-row.is-new');

        if (! $pendientes.length) { return; }

        $pendientes.each(function () {
            var $item = $(this);
            marcar($item.data('id'), $item);
        });

        $('#nbMarkRead').hide();
        $(this).remove();
        $('.nb-list-head .pill').remove();
        toastr.success('Notificaciones marcadas como leídas');
    });

    // Estado inicial: la primera notificación de la lista.
    var $primera = $('.nb-row').first();
    if ($primera.length) { pintar($primera); }
});
