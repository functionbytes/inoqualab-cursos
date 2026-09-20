$(document).ready(function () {

    var config     = $('#mails-view').data('config') || {};
    var isReadOnly = !!config.readOnly;
    var csrfToken  = $('meta[name="csrf-token"]').attr('content');

    // Select2 AJAX para asignar revisor
    $('#assign-user-select').select2({
        placeholder: 'Buscar revisor...',
        allowClear: true,
        ajax: {
            url: config.routes.reviewers,
            dataType: 'json', delay: 300,
            data: function (p) { return { q: p.term || '' }; },
            processResults: function (d) { return { results: d }; },
            cache: true
        }
    });

    $('#assign-btn').on('click', function () {
        var userId = $('#assign-user-select').val();
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: config.routes.assign,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ user_id: userId || null }),
            success: function (r) {
                $btn.prop('disabled', false);
                r.success
                    ? toastr.success(r.message, '', { positionClass: 'toast-bottom-right' })
                    : toastr.error(r.message, '', { positionClass: 'toast-bottom-right' });
            },
            error: function () {
                $btn.prop('disabled', false);
                toastr.error('Error al asignar.', '', { positionClass: 'toast-bottom-right' });
            }
        });
    });

    $('#enterprise_id').select2({ placeholder: 'Seleccionar empresa', allowClear: true });
    initCourseSelects();

    function initCourseSelects() {
        $('.course-select').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ placeholder: 'Seleccionar curso', allowClear: true });
            }
        });
    }

    // Cambio de empresa → recargar cursos
    $('#enterprise_id').on('change', function () {
        var enterpriseId = $(this).val();
        if (!enterpriseId) return;
        $.ajax({
            url: config.routes.courses,
            method: 'GET', data: { enterprise_id: enterpriseId },
            success: function (courses) {
                $('.course-select').each(function () {
                    var $sel = $(this);
                    $sel.select2('destroy').empty().append('<option value="">— Seleccionar curso —</option>');
                    $.each(courses, function (i, c) {
                        $sel.append($('<option>', { value: c.id, text: c.text }));
                    });
                    $sel.select2({ placeholder: 'Seleccionar curso', allowClear: true });
                });
            },
            error: function () { toastr.error('Error al cargar los cursos.', 'Error', { positionClass: 'toast-bottom-right' }); }
        });
    });

    // Notas internas
    $('#notes-textarea').on('input', function () {
        $('#notes-chars').text($(this).val().length);
    });
    $('#save-notes-btn').on('click', function () {
        var $btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');
        $.ajax({
            url: config.routes.note,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify({ notes: $('#notes-textarea').val() }),
            success: function (r) {
                $btn.prop('disabled', false).html('Guardar nota');
                r.success ? toastr.success(r.message, '', { positionClass: 'toast-bottom-right' })
                           : toastr.error(r.message, '', { positionClass: 'toast-bottom-right' });
            },
            error: function () {
                $btn.prop('disabled', false).html('Guardar nota');
                toastr.error('Error al guardar la nota.', '', { positionClass: 'toast-bottom-right' });
            }
        });
    });

    // Atajos de teclado (solo cuando no hay foco en inputs)
    $(document).on('keydown', function (e) {
        if ($(e.target).is('input, textarea, select, button, a')) return;
        switch (e.key) {
            case 'Enter':
                if (!isReadOnly) { e.preventDefault(); $('#confirmForm').trigger('submit'); }
                break;
            case 'd': case 'D':
                e.preventDefault();
                $('#discard-modal').modal('show');
                break;
            case 'r': case 'R':
                if (!isReadOnly) { e.preventDefault(); $('#reparse-btn').trigger('click'); }
                break;
            case 'n': case 'N':
                e.preventDefault();
                $('#notes-textarea').focus();
                break;
        }
    });

    // Toggle panel de overrides
    $('#toggle-overrides').on('click', function () {
        var open = $('#overrides-panel').toggleClass('d-none').hasClass('d-none') === false;
        $(this).html(open ? 'Ocultar campos' : 'Editar campos extraídos');
    });

    // Confirmar orden
    $('#confirmForm').on('submit', function (e) {
        e.preventDefault();
        var enterpriseId = $('#enterprise_id').val();
        if (!enterpriseId) {
            toastr.warning('Debe seleccionar una empresa.', 'Advertencia', { positionClass: 'toast-bottom-right' });
            return;
        }
        var courseMap = {};
        $('.course-select').each(function () {
            var val = $(this).val();
            if (val) courseMap[$(this).data('course-text')] = val;
        });
        var payloadOverrides = {};
        $('.payload-override').each(function () {
            var val = $.trim($(this).val());
            if (val !== '') payloadOverrides[$(this).data('key')] = val;
        });
        var payload = {
            enterprise_id: enterpriseId,
            course_map: courseMap,
            save_alias: $('#save_alias').is(':checked') ? 1 : 0,
            payload_overrides: payloadOverrides,
        };
        $('#confirm-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');
        $.ajax({
            url: config.routes.confirm,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function (r) {
                $('#confirm-btn').prop('disabled', false).html('Confirmar y crear orden');
                if (r.success) {
                    toastr.success(r.message, 'Listo', { positionClass: 'toast-bottom-right' });
                    var target = r.order_slack
                        ? config.routes.orderView.replace(':slack', r.order_slack)
                        : config.routes.index;
                    setTimeout(function () { window.location.href = target; }, 1200);
                } else {
                    toastr.error(r.message, 'Error', { positionClass: 'toast-bottom-right' });
                }
            },
            error: function (xhr) {
                $('#confirm-btn').prop('disabled', false).html('Confirmar y crear orden');
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (f, m) {
                        toastr.warning(m[0], 'Validación', { positionClass: 'toast-bottom-right' });
                    });
                } else {
                    toastr.error('Error al procesar la solicitud.', 'Error', { positionClass: 'toast-bottom-right' });
                }
            }
        });
    });

    // Descartar (ambos botones abren el mismo modal)
    $('#discard-btn, #discard-btn-form').on('click', function () {
        $('#discard-modal').modal('show');
    });

    $('#discard-confirm-btn').on('click', function () {
        $.ajax({
            url: config.routes.discard,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (r) {
                $('#discard-modal').modal('hide');
                r.success ? toastr.success(r.message, 'Listo', { positionClass: 'toast-bottom-right' })
                           : toastr.error(r.message, 'Error', { positionClass: 'toast-bottom-right' });
                if (r.success) setTimeout(function () { window.location.href = config.routes.index; }, 1200);
            },
            error: function () {
                $('#discard-modal').modal('hide');
                toastr.error('Error al procesar la solicitud.');
            }
        });
    });

    // Re-analizar — actualiza UI sin recargar
    function updateCourseSelects(courseMatches) {
        if (!courseMatches || !courseMatches.length) return;
        $.each(courseMatches, function (i, cm) {
            var $sel = $('.course-select').filter(function () {
                return $(this).data('course-text') === cm.text;
            });
            if ($sel.length && cm.matched_id) {
                $sel.val(cm.matched_id).trigger('change.select2');
            }
        });
    }

    function reloadCoursesForEnterprise(enterpriseId, courseMatches) {
        $.ajax({
            url: config.routes.courses,
            data: { enterprise_id: enterpriseId },
            success: function (courses) {
                $('.course-select').each(function () {
                    var $sel = $(this);
                    $sel.select2('destroy').empty().append('<option value="">— Seleccionar curso —</option>');
                    $.each(courses, function (i, c) {
                        $sel.append($('<option>', { value: c.id, text: c.text }));
                    });
                    $sel.select2({ placeholder: 'Seleccionar curso', allowClear: true });
                });
                updateCourseSelects(courseMatches);
            }
        });
    }

    $('#reparse-btn').on('click', function () {
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Analizando...');
        $.ajax({
            url: config.routes.reparse,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (r) {
                $('#reparse-btn').prop('disabled', false).html('Re-analizar correo');
                if (!r.success) { toastr.error(r.message, 'Error', { positionClass: 'toast-bottom-right' }); return; }

                if (r.auto_confirmed) {
                    toastr.success(r.message, 'Auto-confirmado', { positionClass: 'toast-bottom-right', timeOut: 3000 });
                    var target = r.order_slack
                        ? config.routes.orderView.replace(':slack', r.order_slack)
                        : config.routes.index;
                    setTimeout(function () { window.location.href = target; }, 1800);
                    return;
                }

                var currentEntId = $('#enterprise_id').val();
                var newEntId = r.enterprise_id ? String(r.enterprise_id) : '';

                if (newEntId && newEntId !== currentEntId) {
                    if ($('#enterprise_id option[value="' + newEntId + '"]').length === 0) {
                        $('#enterprise_id').append(new Option(r.enterprise_name, newEntId));
                    }
                    $('#enterprise_id').val(newEntId).trigger('change.select2');
                    reloadCoursesForEnterprise(newEntId, r.course_matches);
                } else {
                    updateCourseSelects(r.course_matches);
                }

                var msg = r.message + (r.enterprise_name ? ' — <strong>' + $('<span>').text(r.enterprise_name).html() + '</strong>' : '');
                toastr.success(msg, 'Listo', { positionClass: 'toast-bottom-right', allowHtml: true });
            },
            error: function () {
                $('#reparse-btn').prop('disabled', false).html('Re-analizar correo');
                toastr.error('Error al re-analizar.', 'Error', { positionClass: 'toast-bottom-right' });
            }
        });
    });

    // Toggle texto / raw del cuerpo
    $('#btnFormatted').on('click', function () {
        $('#bodyFormatted').removeClass('d-none');
        $('#bodyRaw').addClass('d-none');
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });
    $('#btnRaw').on('click', function () {
        $('#bodyRaw').removeClass('d-none');
        $('#bodyFormatted').addClass('d-none');
        $('.btn-group .btn').removeClass('active');
        $(this).addClass('active');
    });

});
