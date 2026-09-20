var checkoutData = $('#checkoutPage').data();

$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// Errores generales del checkout (sin input propio al que anclarse) se
// muestran inline sobre el botón de pago, nunca con alert() nativo.
function showFormError(msg) {
    $('#form-error').removeClass('d-none').text(msg);
    $('html,body').animate({ scrollTop: $('#form-error').offset().top - 130 }, 300);
}

// Conversión: InitiateCheckout al abrir el checkout (si hay pixels cargados).
(function () {
    var value = parseFloat(checkoutData.total) || 0;
    if (typeof fbq !== 'undefined') {
        fbq('track', 'InitiateCheckout', { value: value, currency: 'COP' });
    }
    if (typeof ttq !== 'undefined') {
        ttq.track('InitiateCheckout', { value: value, currency: 'COP' });
    }
    if (typeof gtag !== 'undefined') {
        gtag('event', 'begin_checkout', { value: value, currency: 'COP' });
    }
})();

// Captura temprana de "carrito incompleto": se registra el correo apenas
// se conoce, sin esperar a que termine el formulario ni a que exista una
// orden -- así un clic de pauta que no llega a comprar sigue siendo
// recuperable por correo. Autenticado: se conoce el correo desde ya. Invitado:
// se captura al salir del campo #email (si lo que escribió parece un correo).
var leadEventFired = false;
function captureCartLead(email) {
    $.post(checkoutData.captureLeadUrl, { email: email }).done(function (res) {
        // Un mismo checkout puede disparar varios blur/loads (invitado
        // corrigiendo el correo, autenticado recargando la página); el
        // servidor registra cada captura igual, pero el evento de
        // remarketing solo debe contarse una vez por visita para no
        // inflar el público con leads duplicados del mismo carrito.
        if (leadEventFired || !res || !res.tracked) {
            return;
        }
        leadEventFired = true;

        var value = parseFloat(checkoutData.total) || 0;
        if (typeof fbq !== 'undefined') {
            fbq('track', 'Lead', { value: value, currency: 'COP' });
        }
        if (typeof ttq !== 'undefined') {
            ttq.track('SubmitForm', { value: value, currency: 'COP' });
        }
        if (typeof gtag !== 'undefined') {
            gtag('event', 'generate_lead', { value: value, currency: 'COP' });
        }
    });
}
if (checkoutData.isAuth === '1' || checkoutData.isAuth === 1) {
    captureCartLead(checkoutData.authEmail);
} else {
    $(document).on('blur', '#email', function () {
        var val = $(this).val().trim();
        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            captureCartLead(val);
        }
    });
}

$(document).ready(function () {

    function loaderCheckout() {
        // #discount/#total ya vienen calculados desde el controller (respetan
        // un cupón aplicado que sigue vigente al cargar/recargar la página).
        var subtotal = parseFloat($('#subtotal').val()) || 0;
        var discount = parseFloat($('#discount').val()) || 0;
        var total = parseFloat($('#total').val()) || subtotal;
        $('.subtotal-price').text('$ ' + subtotal.toLocaleString('es-CO') + ' COP');
        if (discount > 0) {
            $('.discount-price').text('$ ' + discount.toLocaleString('es-CO') + ' COP');
        }
        $('.total-price').text('$ ' + total.toLocaleString('es-CO') + ' COP');
    }
    loaderCheckout();

    // Habilitar "Realizar pago" sólo al aceptar términos y condiciones
    function syncPayButton() {
        var accepted = $('#terms').is(':checked');
        $('#addPayments').prop('disabled', !accepted).toggleClass('btn-disabled', !accepted);
    }
    syncPayButton();
    $(document).on('change', '#terms', syncPayButton);

    // Toggle cupón
    $('#toggleCoupon').on('click', function () {
        $('#couponFields').removeClass('d-none');
        $(this).addClass('open');
        setTimeout(function () { $('#code').trigger('focus'); }, 60);
    });

    // Toggle sandbox
    $('#sandboxHead').on('click', function () {
        $('#sandboxBody').toggle();
        $(this).find('.chev').toggleClass('open');
    });

    // Toggle password
    $('#pwToggle').on('click', function () {
        var $i = $('#password');
        var type = $i.attr('type') === 'password' ? 'text' : 'password';
        $i.attr('type', type);
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });

    // select2 ciudad
    $('#citie').select2({
        placeholder: 'Busca tu ciudad…',
        width: '100%',
        minimumInputLength: 0,
        allowClear: false,
        language: {
            searching: function () { return 'Buscando…'; },
            noResults: function () { return 'Sin resultados'; },
            errorLoading: function () { return 'No se pudieron cargar las ciudades'; }
        },
        ajax: {
            dataType: 'json',
            url: checkoutData.citiesUrl,
            delay: 250,
            data: function (params) { return { term: $.trim(params.term) }; },
            processResults: function (data) { return { results: data }; },
            cache: true
        }
    });

    // select2 tipo de documento
    $('#identification_type').select2({
        placeholder: 'Tipo de documento',
        minimumResultsForSearch: Infinity,
        width: '100%'
    });

    // Revalidar los select2 al cambiar para limpiar el error
    $('#citie, #identification_type').on('change', function () {
        if ($('#formCheckouts').data('validator')) {
            $(this).valid();
        }
    });
});

// Quitar cupón
$(document).on('click', '.clear-coupon-btn', function () {
    $.ajax({
        url: checkoutData.couponClearUrl,
        type: 'POST',
        contentType: false,
        processData: false,
        data: [],
        success: function () {
            $('#code').val('');
            $('.discount-price').text('$ 0 COP');
            var subtotal = parseFloat($('#subtotal').val()) || 0;
            $('#discount').val(0);
            $('#total').val(subtotal);
            $('.total-price').text('$ ' + subtotal.toLocaleString('es-CO') + ' COP');
            $('.coupon-applied').remove();
            $('.apply-coupon-btn').removeClass('d-none').prop('disabled', false).text('Aplicar');
            $('.coupon-input').prop('disabled', false);
        }
    });
});

jQuery.validator.addMethod('emailExt', function (value) {
    return value.match(/^[a-zA-Z0-9_\.%\+\-]+@[a-zA-Z0-9\.\-]+\.[a-zA-Z]{2,3}$/);
}, 'Porfavor ingrese email valido');

// Celular colombiano: 10 dígitos que empiezan por 3 (tolera +57 / 57 / espacios)
jQuery.validator.addMethod('coCel', function (value, element) {
    var v = (value || '').replace(/\D/g, '').replace(/^57/, '');
    return this.optional(element) || /^3\d{9}$/.test(v);
}, 'Ingresa un celular válido de Colombia (10 dígitos, empieza por 3)');

// Normalizar el celular al salir del campo (quita +57, espacios, etc.)
$(document).on('blur', '#cellphone', function () {
    var v = ($(this).val() || '').replace(/\D/g, '').replace(/^57/, '').slice(0, 10);
    $(this).val(v);
});

function updateCouponPrice(data) {
    var discount = parseFloat(data['discount']) || 0;
    var total = parseFloat(data['total']) || 0;
    $('#code').val(data['code']);
    $('#discount').val(data['discount']);
    $('#total').val(data['total']);
    $('.discount-price').text('$ ' + discount.toLocaleString('es-CO') + ' COP');
    $('.total-price').text('$ ' + total.toLocaleString('es-CO') + ' COP');
}

$('#formCoupons').validate({
    submit: false,
    rules: { code: { required: true, minlength: 6, maxlength: 6 } },
    messages: {
        code: {
            required: 'El codigo es necesario',
            minlength: 'El codigo debe contener 6 caracteres',
            maxlength: 'El codigo debe contener 6 caracteres'
        }
    },
    errorPlacement: function (error, element) {
        $('#' + element.attr('id') + '-error').removeClass('d-none').text(error.text());
    },
    submitHandler: function () {
        $('.apply-coupon-btn').prop('disabled', true).text('Aplicando...');
        $('#code-error').addClass('d-none').text('');

        var formData = new FormData($('#formCoupons')[0]);
        formData.append('code', $('#code').val());

        $.ajax({
            url: checkoutData.couponApplyUrl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: formData,
            success: function (data) {
                if (data['success'] == false) {
                    $('#code-error').removeClass('d-none').text(data['message']);
                    $('.apply-coupon-btn').prop('disabled', false).text('Aplicar');
                } else {
                    updateCouponPrice(data);
                    var code = (data['code'] || $('#code').val() || '').toUpperCase();
                    $('.apply-coupon-btn').prop('disabled', false).text('Aplicar');
                    $('#toggleCoupon').addClass('d-none');
                    $('#couponFields').addClass('d-none');
                    $('.coupon-applied').remove();
                    $('.coupon').prepend(
                        '<div class="coupon-applied">' +
                            '<div class="ic"><i class="fas fa-check"></i></div>' +
                            '<div class="txt"><b>Cupón ' + code + ' aplicado</b><span>Descuento aplicado a tu orden</span></div>' +
                            '<button type="button" class="rm clear-coupon-btn" aria-label="Quitar cupón"><i class="fas fa-times"></i></button>' +
                        '</div>'
                    );
                }
            }
        });
    }
});

$('#formCheckouts').validate({
    submit: false,
    ignore: [],
    rules: {
        firstname: { required: true, minlength: 2, maxlength: 200 },
        lastname:  { required: true, minlength: 2, maxlength: 200 },
        identification_type: { required: true },
        identification: { required: true, number: true, minlength: 5, maxlength: 20 },
        address:   { required: true, minlength: 10, maxlength: 500 },
        company:   { required: false, minlength: 4, maxlength: 100 },
        cellphone: { required: true, coCel: true },
        email:     { required: true, email: true, emailExt: true },
        password:  { required: true },
        citie:     { required: true },
        terms:     { required: true }
    },
    messages: {
        firstname: { required: 'El nombres es necesario', minlength: 'Mínimo 2 caracteres', maxlength: 'Máximo 200 caracteres' },
        lastname:  { required: 'Los apellidos son necesarios', minlength: 'Mínimo 2 caracteres', maxlength: 'Máximo 200 caracteres' },
        identification_type: { required: 'Selecciona el tipo de documento' },
        identification: { required: 'El documento es necesario', number: 'Sólo números', minlength: 'Mínimo 5 dígitos', maxlength: 'Máximo 20 dígitos' },
        company:   { minlength: 'Mínimo 4 caracteres', maxlength: 'Máximo 100 caracteres' },
        address:   { required: 'La dirección es necesaria', minlength: 'Mínimo 10 caracteres', maxlength: 'Máximo 500 caracteres' },
        cellphone: { required: 'El celular es necesario', coCel: 'Celular inválido (10 dígitos, empieza por 3)' },
        email:     { required: 'El email es necesario', email: 'Por favor ingrese email valido' },
        citie:     { required: 'El campo ciudad es necesario' },
        password:  { required: 'La contraseña es necesaria' },
        terms:     { required: 'Debes aceptar los términos y condiciones' }
    },
    errorPlacement: function (error, element) {
        $('#' + element.attr('id') + '-error').text(error.text());
    },
    success: function (label, element) {
        $('#' + $(element).attr('id') + '-error').text('');
    },
    submitHandler: function () {

        $('#addPayments').addClass('btn-disabled-payment').prop('disabled', true)
            .html('<i class="fas fa-spinner fa-spin"></i> Generando url de pago...');

        var formData = new FormData($('#formCheckouts')[0]);
        formData.append('firstname', $('#firstname').val());
        formData.append('lastname', $('#lastname').val());
        formData.append('identification', $('#identification').val());
        formData.append('identification_type', $('#identification_type').val());
        formData.append('terms', $('#terms').is(':checked') ? '1' : '');
        formData.append('newsletter', $('#newsletter').is(':checked') ? '1' : '0');
        formData.append('cellphone', $('#cellphone').val());
        formData.append('address', $('#address').val());
        formData.append('citie', $('#citie').val());
        // email/password no existen en el DOM para un usuario ya
        // autenticado (ver "isAuth" arriba). Sin este
        // guard, $("#email").val() es undefined y FormData lo manda
        // como el STRING "undefined", que el backend rechaza (formato
        // de email inválido, y "undefined" está en una filtración de
        // contraseñas conocida) -- bloqueaba la recompra de todo
        // cliente ya logueado.
        if ($('#email').length) {
            formData.append('email', $('#email').val());
        }
        if ($('#password').length) {
            formData.append('password', $('#password').val());
        }

        $.ajax({
            url: checkoutData.registerUrl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: formData,
            success: function (data, textStatus, jqXHR) {

                // register() puede loguear un usuario nuevo -> Laravel regenera
                // la sesión y el token CSRF. Sin refrescar meta[csrf-token] aquí,
                // la siguiente llamada (checkout.generate) falla con 419.
                var freshToken = jqXHR.getResponseHeader('X-CSRF-TOKEN');
                if (freshToken) {
                    $('meta[name="csrf-token"]').attr('content', freshToken);
                    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': freshToken } });
                }

                if (data == 'email') {
                    var em = encodeURIComponent($('#email').val() || '');
                    $('#email-error').removeClass('d-none')
                        .html('Este correo ya está registrado. <a href="' + checkoutData.loginUrl + '?email=' + em + '" style="text-decoration:underline;font-weight:700">Inicia sesión</a> para continuar tu compra.');
                    $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                        .html('REALIZAR PAGO');
                    return;
                }

                if (data == 'success') {
                    $.ajax({
                        url: checkoutData.generateUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: { _token: $('meta[name="csrf-token"]').attr('content') },
                        success: function (res) {
                            if (res && res.redirect) {
                                // Curso gratis -> confirmación; con costo -> Web Checkout de Wompi
                                window.location.href = res.redirect;
                                return;
                            }
                            $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                                .html('REALIZAR PAGO');
                            showFormError('No se pudo generar la orden. Intenta de nuevo.');
                        },
                        error: function (jqXHR) {
                            $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                                .html('REALIZAR PAGO');
                            showFormError(jqXHR.status == 422 ? 'Tu carrito está vacío.' : 'Error al generar la orden de pago.');
                        }
                    });
                }
            },
            error: function (jqXHR) {
                $('#addPayments').removeClass('btn-disabled-payment').prop('disabled', false)
                    .html('REALIZAR PAGO');

                // Limpiar errores previos por campo
                $('label.error').addClass('d-none').text('');

                if (jqXHR.status === 422 && jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                    var errors = jqXHR.responseJSON.errors;
                    var firstField = null;
                    $.each(errors, function (field, messages) {
                        var msg = Array.isArray(messages) ? messages[0] : messages;
                        var $lbl = $('#' + field + '-error');
                        if ($lbl.length) {
                            $lbl.removeClass('d-none').text(msg);
                            if (!firstField) firstField = field;
                        }
                    });
                    if (firstField) {
                        var $el = $('#' + firstField);
                        if ($el.length) {
                            $el.trigger('focus');
                            $('html,body').animate({ scrollTop: $el.offset().top - 130 }, 300);
                        }
                    } else {
                        var first = Object.values(errors)[0];
                        showFormError(Array.isArray(first) ? first[0] : first);
                    }
                } else {
                    showFormError('No se pudo procesar el registro. Revisa tus datos e intenta de nuevo.');
                }
            }
        });
    }
});

$('#addPayments').on('click', function () {
    $('#formCheckouts').submit();
});
