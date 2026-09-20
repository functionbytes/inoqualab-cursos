$(document).ready(function () {

    // Acordeón (exclusivo por defecto, abre todos si expandAll activo)
    $(document).on('click', '.js-acc', function () {
        var $head = $(this);
        var $body = $head.next('.acc-body');

        if ($('body').hasClass('all-open')) {
            var willOpen = $body.is(':hidden');
            $head.toggleClass('open', willOpen);
            if (willOpen) $body.removeAttr('hidden').hide().slideDown(180);
            else $body.slideUp(180, function () { $(this).attr('hidden', ''); });
            return;
        }

        var isOpen = $head.hasClass('open');
        $('.js-acc').removeClass('open');
        // .not($body): si $body iba a reabrirse (el "if" de abajo), no se le
        // encola primero un slideUp -- animar cerrar y abrir en el mismo tick
        // sobre el mismo elemento pisaba la cola de efectos de jQuery y el
        // slideDown solo se veía reflejado hasta el segundo clic.
        $('.acc-body').not($body).slideUp(180, function () { $(this).attr('hidden', ''); });
        if (!isOpen) {
            $head.addClass('open');
            $body.stop(true, true).removeAttr('hidden').hide().slideDown(180);
        } else {
            $body.stop(true, true).slideUp(180, function () { $(this).attr('hidden', ''); });
        }
    });

    // Expandir / contraer todo
    $('#expandAll').on('click', function () {
        var open = $('body').toggleClass('all-open').hasClass('all-open');
        $('.js-acc').toggleClass('open', open);
        if (open) {
            $('.acc-body').removeAttr('hidden').hide().slideDown(180);
            $(this).html('<i class="fa-solid fa-angles-up"></i> Contraer todo');
        } else {
            $('.acc-body').slideUp(180, function () { $(this).attr('hidden', ''); });
            $(this).html('<i class="fa-solid fa-angles-down"></i> Expandir todo');
        }
    });

    // Stepper de cantidad del buy-card
    function setBuyQty(q) {
        q = Math.max(1, Math.min(10, q));
        $('#buyQty').val(q);
        $('#buyQtyVal').text(q);
        $('.js-buy-minus').prop('disabled', q <= 1);
        $('.js-buy-plus').prop('disabled', q >= 10);
    }
    $(document).on('click', '.js-buy-plus', function () { setBuyQty((parseInt($('#buyQty').val(), 10) || 1) + 1); });
    $(document).on('click', '.js-buy-minus', function () { setBuyQty((parseInt($('#buyQty').val(), 10) || 1) - 1); });

    // Diferenciar "Compra ahora" vs "Agregar al carrito"
    $(document).on('click', '#btnBuyNow', function () { $('#buyNow').val('1'); });
    $(document).on('click', '#btnAddCart', function () { $('#buyNow').val(''); });
});
