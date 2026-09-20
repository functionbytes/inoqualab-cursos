$(function () {
    // El índice lateral marca la sección que se está mirando.
    var $enlaces = $('.cfg-side a');
    var secciones = $enlaces.map(function () { return $($(this).attr('href')); }).get();

    function marcarVisible() {
        var top = $(window).scrollTop() + 140;
        var activo = 0;

        $.each(secciones, function (i, $sec) {
            if ($sec.length && $sec.offset().top <= top) {
                activo = i;
            }
        });

        $enlaces.removeClass('is-on').eq(activo).addClass('is-on');
    }

    $(window).on('scroll', marcarVisible);
    marcarVisible();

    // Mostrar/ocultar contraseña
    $('#cfgPwToggle').on('click', function () {
        var $i = $('#password');
        $i.attr('type', $i.attr('type') === 'password' ? 'text' : 'password');
        $(this).toggleClass('is-on');
    });

    $('#formUsers').on('submit', function (e) {
        e.preventDefault();

        var password = $('#password').val();
        var confirm = $('#password_confirmation').val();

        if (password && password.length < 8) {
            toastr.warning('La contraseña debe tener al menos 8 caracteres.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            return;
        }
        if (password && password !== confirm) {
            toastr.warning('Las contraseñas no coinciden.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            return;
        }

        var formData = new FormData(this);
        var $btns = $('#formUsers button[type="submit"]');
        $btns.prop('disabled', true);

        $.ajax({
            url: $(this).data('update-url'),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            type: 'POST',
            contentType: false,
            processData: false,
            data: formData,
            success: function (response) {
                $btns.prop('disabled', false);
                if (response.success === true) {
                    toastr.success(response.message, 'Operación exitosa', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    $('#password, #password_confirmation').val('');
                }
            },
            error: function (xhr) {
                $btns.prop('disabled', false);
                var msg = 'Se ha generado un error.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).map(function (e) { return e[0]; }).join(' ');
                }
                toastr.warning(msg, 'Operación fallida', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            }
        });
    });
});
