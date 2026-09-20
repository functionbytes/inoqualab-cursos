(function () {
    'use strict';

    var $cartCard = $('#cartCard');
    var updateQtyUrl = $cartCard.data('update-qty-url');
    var removeUrl = $cartCard.data('remove-url');
    var clearUrl = $cartCard.data('clear-url');

    function fmtCOP(n) {
        return '$ ' + Number(n).toLocaleString('es-CO');
    }

    function updateHeaderBadge(count) {
        var $badge = $('.cart-count-badge');
        if (count > 0) {
            if ($badge.length) {
                $badge.text(count);
            } else {
                $('.cart-btn').append('<span class="cart-count-badge">' + count + '</span>');
            }
        } else {
            $badge.remove();
        }
    }

    // Actualizar cantidad
    function changeQty(key, delta) {
        var $row  = $('.cp-item[data-key="' + key + '"]');
        var $val  = $row.find('.qty-val');
        var $minus = $row.find('.js-qty-minus');
        var $plus  = $row.find('.js-qty-plus');
        var unit  = parseFloat($row.data('unit')) || 0;
        var current = parseInt($val.text(), 10) || 1;
        var next  = Math.max(1, Math.min(10, current + delta));

        if (next === current) return;

        $minus.add($plus).prop('disabled', true);

        $.ajax({
            url: updateQtyUrl,
            method: 'POST',
            data: { key: key, qty: next },
            success: function (res) {
                $val.text(res.qty);
                $minus.prop('disabled', res.qty <= 1);
                $plus.prop('disabled', res.qty >= 10);

                // Precio de la línea + precio unitario
                $row.find('.cp-price-cell .amt').text(fmtCOP(unit * res.qty));
                var $unit = $row.find('.cp-price-cell .unit');
                if (res.qty > 1) {
                    $unit.text(fmtCOP(unit) + ' c/u').removeClass('is-hidden');
                } else {
                    $unit.addClass('is-hidden');
                }

                // Totales del resumen
                $('#sumSubtotal').text(fmtCOP(res.cart_total));
                $('#sumTotal').text(fmtCOP(res.cart_total));

                // Contador de unidades en el subtotal
                var totalQty = 0;
                $('.qty-val').each(function () { totalQty += parseInt($(this).text(), 10) || 0; });
                $('#sumQty').text(totalQty > 1 ? ' (' + totalQty + ')' : '');
            },
            error: function () {
                $minus.prop('disabled', current <= 1);
                $plus.prop('disabled', current >= 10);
                if (typeof toastr !== 'undefined') toastr.error('No se pudo actualizar la cantidad.');
            }
        });
    }

    $(document).on('click', '.js-qty-plus', function () { changeQty($(this).data('key'), 1); });
    $(document).on('click', '.js-qty-minus', function () { changeQty($(this).data('key'), -1); });

    // Eliminar un item
    $(document).on('click', '.js-cart-remove', function () {
        var $btn = $(this);
        var key  = $btn.data('key');
        var $row = $('.cp-item[data-key="' + key + '"]');

        $btn.prop('disabled', true);

        $.ajax({
            url: removeUrl,
            method: 'POST',
            data: { key: key },
            success: function (res) {
                updateHeaderBadge(res.cart_count);

                // Si queda vacío o un solo item, recargamos para reflejar
                // estados (vacío / botón "Ir al pago") renderizados en servidor.
                if (res.cart_count <= 1) {
                    window.location.reload();
                    return;
                }

                $row.addClass('removing');
                setTimeout(function () {
                    $row.remove();
                    $('#cpCount').text(res.cart_count);
                    $('#cpCountWord').text(res.cart_count === 1 ? 'producto' : 'productos');
                    $('#sumSubtotal').text(fmtCOP(res.cart_total));
                    $('#sumTotal').text(fmtCOP(res.cart_total));
                    if (typeof toastr !== 'undefined') toastr.success(res.message);
                }, 260);
            },
            error: function () {
                $btn.prop('disabled', false);
                if (typeof toastr !== 'undefined') toastr.error('No se pudo eliminar el producto.');
            }
        });
    });

    // Vaciar carrito
    $(document).on('click', '.js-cart-clear', function () {
        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: clearUrl,
            method: 'POST',
            success: function (res) {
                updateHeaderBadge(0);
                window.location.reload();
            },
            error: function () {
                $btn.prop('disabled', false);
                if (typeof toastr !== 'undefined') toastr.error('No se pudo vaciar el carrito.');
            }
        });
    });
})();
