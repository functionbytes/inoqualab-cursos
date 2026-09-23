$(function () {

    var config = $('#cart-abandonments-index').data('config') || {};

    // ── Filtros + selección masiva ───────────────────────────────────────────
    function initCartAbandonmentsTable() {
        FilterToolbar.init({
            fields: { filterStatus: 'popover_status' },
        });

        BulkActions.init({
            url: config.routes.bulkAction,
            entityLabel: 'registro(s)',
        });
    }

    initCartAbandonmentsTable();

    AjaxTable.init({ onLoaded: initCartAbandonmentsTable });

    // ── Ver detalle (email, datos del cliente, carrito completo) ────────────
    var statusLabels = { pending: 'Sin recordar', reminded: 'Recordado', converted: 'Convertido' };
    var statusBadgeClasses = {
        pending: 'bg-secondary-subtle text-secondary',
        reminded: 'bg-primary-subtle text-primary',
        converted: 'bg-success-subtle text-success',
    };
    var typeLabels = { course: 'Curso', bundle: 'Paquete' };

    $(document).on('click', '.btn-view-abandonment', function () {
        var $btn = $(this);
        var items = $btn.data('items') || [];
        var status = $btn.data('status');
        var name = $btn.data('name');

        $('#abandonment-detail-name').text(name || 'Invitado (sin cuenta)');
        $('#abandonment-detail-email').text($btn.data('email') || '');
        $('#abandonment-detail-avatar').text(
            (name || $btn.data('email') || '?').trim().charAt(0).toUpperCase()
        );
        $('#abandonment-detail-cellphone').text($btn.data('cellphone') || 'Sin teléfono');
        $('#abandonment-detail-identification').text($btn.data('identification') ? 'Doc: ' + $btn.data('identification') : '');
        $('#abandonment-detail-status')
            .text(statusLabels[status] || status)
            .attr('class', 'badge ' + (statusBadgeClasses[status] || 'bg-secondary-subtle text-secondary'));
        $('#abandonment-detail-created').text($btn.data('created') || '—');

        var followup = 'Sin contactar';
        if ($btn.data('converted')) {
            followup = 'Convertido: ' + $btn.data('converted');
        } else if ($btn.data('reminded')) {
            followup = 'Recordado: ' + $btn.data('reminded');
        }
        $('#abandonment-detail-followup').text(followup);

        var $items = $('#abandonment-detail-items').empty();
        items.forEach(function (line) {
            var $row = $('<tr></tr>');
            $row.append($('<td></td>').text(line.title));
            $row.append($('<td class="text-center"></td>').text(typeLabels[line.type] || line.type || '—'));
            $row.append($('<td class="text-center"></td>').text(line.qty || 1));
            $row.append($('<td class="text-end"></td>').text('$ ' + Number(line.amount || 0).toLocaleString('es-CO')));
            $items.append($row);
        });

        $('#abandonment-detail-total').text($btn.data('total'));
        $('#abandonment-detail-header-total').text($btn.data('total'));
        $('#abandonment-detail-cart-link').attr('href', $btn.data('cartUrl'));

        var $remindBtn = $('#abandonment-detail-remind-btn');
        $remindBtn.data('url', $btn.data('remindUrl'));
        $remindBtn.prop('disabled', status === 'converted').text(
            status === 'converted' ? 'Ya convertido' : 'Enviar recordatorio ahora'
        );

        $('#abandonment-detail-modal').modal('show');
    });

    $(document).on('click', '#abandonment-detail-remind-btn', function () {
        var $btn = $(this);
        var url = $btn.data('url');
        if (!url) { return; }

        $btn.prop('disabled', true).text('Enviando...');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                toastr.success(res.message || 'Recordatorio enviado');
                $btn.text('Enviado');
                setTimeout(function () {
                    $('#abandonment-detail-modal').modal('hide');
                    location.reload();
                }, 900);
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al enviar el recordatorio');
                $btn.prop('disabled', false).text('Enviar recordatorio ahora');
            }
        });
    });

});
