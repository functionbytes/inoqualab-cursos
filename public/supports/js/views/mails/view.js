$(document).ready(function () {

    var $form = $('#confirmForm');
    var coursesUrl = $form.data('courses-url');
    var confirmUrl = $form.data('confirm-url');
    var discardUrl = $form.data('discard-url');
    var indexUrl = $form.data('index-url');

    function initCourseSelects() {
        $('.course-select').each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({ placeholder: 'Seleccionar curso', allowClear: true });
            }
        });
    }

    // Init select2 para empresa y cursos
    $('#enterprise_id').select2({ placeholder: 'Seleccionar empresa', allowClear: true });
    initCourseSelects();

    // Cambio de empresa → recargar opciones de curso
    $('#enterprise_id').on('change', function () {
        var enterpriseId = $(this).val();
        if (!enterpriseId) return;

        $.ajax({
            url: coursesUrl,
            method: 'GET',
            data: { enterprise_id: enterpriseId },
            success: function (courses) {
                $('.course-select').each(function () {
                    var $select = $(this);
                    $select.select2('destroy');
                    $select.empty().append('<option value="">-- Seleccionar curso --</option>');
                    $.each(courses, function (i, course) {
                        $select.append($('<option>', { value: course.id, text: course.text }));
                    });
                    $select.select2({ placeholder: 'Seleccionar curso', allowClear: true });
                });
            },
            error: function () {
                toastr.error('Error al cargar los cursos.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            },
        });
    });

    // Submit confirmar orden
    $form.on('submit', function (e) {
        e.preventDefault();

        var enterpriseId = $('#enterprise_id').val();

        if (!enterpriseId) {
            toastr.warning('Debe seleccionar una empresa.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            return;
        }

        var courseMap = {};
        $('.course-select').each(function () {
            var text = $(this).data('course-text');
            var val = $(this).val();
            if (val) courseMap[text] = val;
        });

        var payload = {
            enterprise_id: enterpriseId,
            course_map: courseMap,
            save_alias: $('#save_alias').is(':checked') ? 1 : 0,
        };

        $('#confirm-btn').prop('disabled', true).text('Procesando...');

        $.ajax({
            url: confirmUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function (response) {
                $('#confirm-btn').prop('disabled', false).text('Confirmar y crear orden');
                if (response.success) {
                    toastr.success(response.message, 'Listo', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    setTimeout(function () {
                        window.location.href = indexUrl;
                    }, 1200);
                } else {
                    toastr.error(response.message, 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            },
            error: function (xhr) {
                $('#confirm-btn').prop('disabled', false).text('Confirmar y crear orden');
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    $.each(xhr.responseJSON.errors, function (field, messages) {
                        toastr.warning(messages[0], 'Validación', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    });
                } else {
                    toastr.error('Error al procesar la solicitud.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            },
        });
    });

    // Botón descartar
    $('#discard-btn').on('click', function () {
        $('#discard-modal').modal('show');
    });

    $('#discard-confirm-btn').on('click', function () {
        $.ajax({
            url: discardUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                $('#discard-modal').modal('hide');
                if (response.success) {
                    toastr.success(response.message, 'Listo', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                    setTimeout(function () {
                        window.location.href = indexUrl;
                    }, 1200);
                } else {
                    toastr.error(response.message, 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
                }
            },
            error: function () {
                $('#discard-modal').modal('hide');
                toastr.error('Error al procesar la solicitud.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
            },
        });
    });

});
