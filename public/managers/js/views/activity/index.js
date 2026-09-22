$(function () {
    var page = $('#activityPage');

    function fmt(v) {
        if (v === null || v === undefined) return '<span class="text-muted">—</span>';
        if (typeof v === 'object') return '<code>' + $('<div>').text(JSON.stringify(v)).html() + '</code>';
        return $('<div>').text(String(v)).html();
    }

    $(document).on('click', '.btn-detail', function () {
        var props = $(this).data('props') || {};
        var attrs = props.attributes || {};
        var old   = props.old || {};
        var keys  = Object.keys(attrs).length ? Object.keys(attrs) : Object.keys(old);

        var $body = $('#detail-body').empty();
        $('#detail-meta').text($(this).data('meta') || '');

        if (!keys.length) {
            $body.append('<tr><td colspan="3" class="text-muted text-center">Sin propiedades registradas</td></tr>');
        } else {
            keys.forEach(function (k) {
                $body.append('<tr><td class="fw-semibold">' + fmt(k) + '</td><td>' + fmt(old[k]) + '</td><td>' + fmt(attrs[k]) + '</td></tr>');
            });
        }

        $('#detailModal').modal('show');
    });

    // ── Filtros avanzados ───────────────────────────────────────────────
    $('.select2-filter-modal').select2({ dropdownParent: $('#activity-filter-modal'), width: '100%' });

    $('#activity-filter-apply-btn').on('click', function () {
        $('#filter-event').val($('#modal-event').val());
        $('#filter-log-name').val($('#modal-log-name').val());
        $('#filter-subject-type').val($('#modal-subject-type').val());
        $('#filter-causer').val($('#modal-causer').val());
        $('#filter-date-from').val($('#modal-date-from').val());
        $('#filter-date-to').val($('#modal-date-to').val());
        $('#activity-filter-modal').modal('hide');
        $('#activity-filter-form').submit();
    });

    $('#activity-filter-clear-btn').on('click', function () {
        window.location = $('#activity-filter-form').attr('action');
    });

    // ── Bulk actions ─────────────────────────────────────────────────────
    function initActivityTable() {
        BulkActions.init({
        url: page.data('bulk-action-url'),
        entityLabel: 'registro(s) de auditoría',
    });
    }

    initActivityTable();

    AjaxTable.init({ form: '#activity-filter-form', onLoaded: initActivityTable });

    // ── Refrescar stats ──────────────────────────────────────────────────
    $('#refresh-stats-btn').on('click', function () {
        var $btn  = $(this);
        var $icon = $btn.find('i');
        $btn.prop('disabled', true);
        $icon.addClass('fa-spin');

        $.getJSON(page.data('stats-url'))
            .done(function (data) {
                $('[data-stat="total"]').text(new Intl.NumberFormat().format(data.total));
                $('[data-stat="created"]').text(new Intl.NumberFormat().format(data.created));
                $('[data-stat="updated"]').text(new Intl.NumberFormat().format(data.updated));
                $('[data-stat="deleted"]').text(new Intl.NumberFormat().format(data.deleted));
                toastr.success('Stats actualizados');
            })
            .fail(function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al refrescar.');
            })
            .always(function () {
                $btn.prop('disabled', false);
                $icon.removeClass('fa-spin');
            });
    });
});
