$(function () {
    function aplicar(f) {
        var visibles = 0;

        $('#cursosCards .cx-row').each(function () {
            var ver = (f === 'todos') || ($(this).data('status') === f);
            $(this).prop('hidden', !ver);
            if (ver) { visibles++; }
        });

        $('#cursosVacio').prop('hidden', visibles !== 0);
    }

    $('#cursosFilter').on('click', 'button', function () {
        var $btn = $(this);
        $btn.siblings().removeClass('active').attr('aria-pressed', 'false');
        $btn.addClass('active').attr('aria-pressed', 'true');
        aplicar($btn.data('f'));
    });

    // El aviso del panel lateral filtra la lista en vez de llevar a otra página.
    $('.cx-filter-expired').on('click', function () {
        $('#cursosFilter button[data-f="expired"]').trigger('click');
    });
});
