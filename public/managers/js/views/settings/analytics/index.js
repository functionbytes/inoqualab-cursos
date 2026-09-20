$(function () {
    // Toggle GA4 dependent fields
    $('#googleAnalyticsEnable').on('change', function () {
        $('#ga4Fields').toggleClass('d-none', !this.checked);
    });

    // Submit GA4 form
    $('#analyticsForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('[type=submit]');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

        $.ajax({
            url: $form.data('update-url'),
            method: 'POST',
            data: $form.serialize(),
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message);
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    toastr.error(res.message || 'Error al guardar.');
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON && xhr.responseJSON.errors;
                if (errors) {
                    Object.values(errors).flat().forEach(function (m) { toastr.error(m); });
                } else {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar.');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).html('Guardar configuración GA4');
            }
        });
    });

    // Submit pixels form
    $('#pixelsForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        var $btn = $form.find('[type=submit]');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

        $.ajax({
            url: $form.data('update-url'),
            method: 'POST',
            data: $form.serialize(),
            success: function (res) {
                if (res.success) { toastr.success(res.message); }
                else { toastr.error(res.message || 'Error al guardar.'); }
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar los pixels.');
            },
            complete: function () {
                $btn.prop('disabled', false).html('Guardar pixels');
            }
        });
    });

    // Clear analytics dashboard cache
    $('#clearCacheBtn').on('click', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Limpiando...');

        $.post($btn.data('url'), {
            _token: $('meta[name="csrf-token"]').attr('content')
        })
        .done(function (res) {
            toastr.success(res.message || 'Caché limpiado correctamente.');
        })
        .fail(function () {
            toastr.error('Error al limpiar el caché.');
        })
        .always(function () {
            $btn.prop('disabled', false).html('Limpiar caché del dashboard');
        });
    });

    // Validate JSON credentials
    $('#validateBtn').on('click', function () {
        var raw = $('#credentials').val().trim();
        if (!raw) { toastr.warning('Pega el JSON de credenciales primero.'); return; }

        try {
            var obj = JSON.parse(raw);
            if (obj.type !== 'service_account') {
                toastr.error('El JSON no es de tipo service_account.');
                return;
            }
            var missing = ['project_id', 'client_email', 'private_key'].filter(function (k) { return !obj[k]; });
            if (missing.length) {
                toastr.error('Faltan campos: ' + missing.join(', '));
                return;
            }
            toastr.success('JSON válido. Proyecto: ' + obj.project_id);
        } catch (err) {
            toastr.error('JSON inválido: ' + err.message);
        }
    });
});
