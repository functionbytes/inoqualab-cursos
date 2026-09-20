$(document).ready(function () {

    $('#formRedirect').on('submit', function (e) {
        e.preventDefault();
    });

    // ── Abrir modal redirect con datos de la fila ────────────────────────────
    $(document).on('click', '.btn-create-redirect', function (e) {
        e.preventDefault();
        var url = $(this).data('url');

        var path = url;
        try {
            var parsed = new URL(url);
            path = parsed.pathname + (parsed.search || '');
        } catch (err) {
            path = url;
        }

        $('#redirect-source').val(path);
        $('#redirect-source-display').text(path);
        $('#redirect-target').val('').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        $('#redirect-code').val('301');

        $('#modalRedirect').modal('show');
    });

    // ── Guardar redirect ──────────────────────────────────────────────────────
    $('#btn-save-redirect').on('click', function () {
        var $btn = $(this).prop('disabled', true).text('Guardando...');

        $('#redirect-target').removeClass('is-invalid');
        $('.invalid-feedback').text('');

        $.ajax({
            url: $('#formRedirect').attr('action'),
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                source_path: $('#redirect-source').val(),
                target_path: $('#redirect-target').val(),
                status_code: $('#redirect-code').val(),
            },
            success: function (response) {
                toastr.success(response.message ?? 'Redirect creado correctamente');
                $('#modalRedirect').modal('hide');
                setTimeout(function () { window.location.reload(); }, 800);
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        var $input = $('#redirect-' + field.replace('_path', ''));
                        if ($input.length) {
                            $input.addClass('is-invalid')
                                  .next('.invalid-feedback').text(messages[0]);
                        } else {
                            toastr.error(messages[0]);
                        }
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message ?? 'Error al guardar el redirect');
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Guardar redirect');
            }
        });
    });

    // Limpiar modal al cerrarse
    $('#modalRedirect').on('hidden.bs.modal', function () {
        $('#formRedirect')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    });

});
