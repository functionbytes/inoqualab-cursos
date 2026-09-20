$(function () {
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
});
