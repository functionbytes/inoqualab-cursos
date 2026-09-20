$(function () {

    var $page = $('#exams-index');
    var config = $page.data('config') || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'examen(es)',
    });

    // ── Eliminar individual via modal ─────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Crear / Editar examen (modal) ──────────────────────────────────────────
    var examQuill = null;
    var examMode = 'create';

    function initExamQuill() {
        if (examQuill) return;
        examQuill = new Quill('#examDescription', {
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    ['link'],
                    ['clean'],
                ],
            },
            placeholder: 'Escriba aquí...',
            theme: 'snow',
        });
    }

    // El script global (select2.init.js) ya auto-inicializa ".select2" en
    // documentReady SIN dropdownParent (el select vive oculto dentro del modal
    // en ese momento) — su dropdown terminaría flotando sobre <body> en vez del
    // modal. Por eso se destruye esa instancia y se re-crea con las opciones
    // correctas cada vez que el modal se abre.
    function initExamSelect2(selector) {
        var $select = $(selector);
        if (!$select.length) return;
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({ dropdownParent: $('#exam-modal'), width: '100%', placeholder: $select.data('placeholder') });
    }

    function initExamSelects() {
        initExamSelect2('#examType');
        initExamSelect2('#examDuration');
        initExamSelect2('#examAvailable');
    }

    var examValidator = $('#formExam').validate({
        ignore: '.ignore',
        rules: {
            title: { required: true, minlength: 3, maxlength: 200 },
            type: { required: true },
            duration: { required: true },
            day: { required: true, number: true, min: 0, max: 999 },
            timer: { required: true, number: true, min: 0, max: 999 },
            question: { required: true, number: true, min: 0, max: 999 },
            mark: { required: true, number: true, min: 0, max: 999 },
            available: { required: true },
        },
        messages: {
            title: {
                required: 'El título es obligatorio.',
                minlength: 'Debe contener al menos 3 caracteres.',
                maxlength: 'Debe contener como máximo 200 caracteres.',
            },
            type: { required: 'Selecciona una opción.' },
            duration: { required: 'Selecciona una opción.' },
            day: { required: 'El campo es obligatorio.', number: 'Solo se pueden ingresar números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            timer: { required: 'El campo es obligatorio.', number: 'Solo se pueden ingresar números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            question: { required: 'El campo es obligatorio.', number: 'Solo se pueden ingresar números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            mark: { required: 'El campo es obligatorio.', number: 'Solo se pueden ingresar números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            available: { required: 'Selecciona un estado.' },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element).addClass('error').removeClass('d-none');
        },
        submitHandler: function (form) {
            var isEdit = examMode === 'edit';
            var url = isEdit ? config.routes.update : config.routes.store;

            var payload = $(form).serializeArray().reduce(function (acc, field) {
                acc[field.name] = field.value;
                return acc;
            }, {});
            payload.description = examQuill ? examQuill.root.innerHTML.replace('<p><br></p>', '') : '';

            var $submitButton = $('#formExam button[type="submit"]').prop('disabled', true);
            var submitOriginalText = $submitButton.text();
            $submitButton.text('Guardando...');

            $.ajax({
                url: url,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                data: payload,
                success: function (response) {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    if (response.success) {
                        $('#exam-modal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(response.message || 'No se pudo guardar.');
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    toastr.error('Ocurrió un error al guardar el examen.');
                },
            });
        },
    });

    function resetExamForm() {
        $('#formExam')[0].reset();
        examValidator.resetForm();
        $('#formExam .is-invalid').removeClass('is-invalid');
        $('#examSlack').val('');
        // .reset() no dispara 'change': select2 necesita el evento para refrescar su UI.
        $('#examType, #examDuration, #examAvailable').trigger('change');
        if (examQuill) examQuill.setText('');
    }

    function populateExamForm(data) {
        $('#examSlack').val(data.slack);
        $('#examTitle').val(data.title);
        $('#examType').val(String(data.type)).trigger('change');
        $('#examDuration').val(String(data.duration)).trigger('change');
        $('#examDay').val(data.day);
        $('#examTimer').val(data.timer);
        $('#examQuestion').val(data.question);
        $('#examMark').val(data.mark);
        $('#examAvailable').val(String(data.available)).trigger('change');
        examQuill.root.innerHTML = data.description || '';
    }

    // Abrir en modo "crear"
    $(document).on('click', '.btn-new-exam', function () {
        examMode = 'create';
        $('#examModalTitle').text('Nuevo examen');
    });

    $('#exam-modal').on('shown.bs.modal', function () {
        initExamQuill();
        initExamSelects();
        if (examMode === 'create') resetExamForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-exam', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON(config.routes.editBase + '/' + slack, function (data) {
            examMode = 'edit';
            $('#examModalTitle').text('Editar examen');
            $('#exam-modal').modal('show');

            var applyData = function () {
                initExamQuill();
                initExamSelects();
                populateExamForm(data);
            };

            if ($('#exam-modal').hasClass('show')) {
                applyData();
            } else {
                $('#exam-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar el examen.');
        });
    });

});
