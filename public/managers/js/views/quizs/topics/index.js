$(function () {

    var $page = $('#quizs-topics-index');
    var config = $page.data('config') || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initQuizsTopicsTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'pregunta(s)',
    });
    }

    initQuizsTopicsTable();

    AjaxTable.init({ onLoaded: initQuizsTopicsTable });

    // ── Bulk selection ───────────────────────────────────────────────────────

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Crear / Editar pregunta (modal) ───────────────────────────────────────
    var questionMode = 'create';
    var answerIsMultiple = config.answerIsMultiple;

    // El script global (select2.init.js) ya auto-inicializa ".select2"/selects
    // sin dropdownParent en documentReady (el modal está oculto en ese momento,
    // su dropdown terminaría flotando sobre <body>). Por eso se destruye esa
    // instancia y se re-crea con las opciones correctas cada vez que el modal se abre.
    function initQuestionSelect2() {
        [$('#available'), $('#answer')].forEach(function ($select) {
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
            $select.select2({
                width: '100%',
                dropdownParent: $('#question-modal'),
                placeholder: $select.data('placeholder'),
            });
        });
    }

    var questionValidator = $('#formQuestion').validate({
        ignore: '.ignore',
        rules: {
            question: { required: true, minlength: 3, maxlength: 400 },
            available: { required: true },
            answer: { required: true },
            a: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
            b: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
            c: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
            d: { required: answerIsMultiple, minlength: 3, maxlength: 200 },
        },
        messages: {
            question: { required: 'La pregunta es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 400 caracteres.' },
            available: { required: 'Selecciona un estado.' },
            answer: { required: 'Selecciona la respuesta correcta.' },
            a: { required: 'La respuesta A es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            b: { required: 'La respuesta B es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            c: { required: 'La respuesta C es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            d: { required: 'La respuesta D es obligatoria.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element).addClass('error').removeClass('d-none');
        },
        submitHandler: function (form) {
            var formData = new FormData(form);

            // El <select> de "answer" no se serializa automáticamente: si es múltiple,
            // el navegador enviaría varias entradas con la misma key y el backend solo
            // recibiría la última. Se recalcula y se une con comas explícitamente.
            var answerValue = $('#answer').val();
            formData.delete('answer');
            formData.append('answer', Array.isArray(answerValue) ? answerValue.join(',') : (answerValue || ''));

            var url = questionMode === 'edit' ? config.routes.update : config.routes.store;

            var $submitButton = $('#formQuestion button[type="submit"]').prop('disabled', true);
            var submitOriginalText = $submitButton.text();
            $submitButton.text('Guardando...');

            $.ajax({
                url: url,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    if (response.success) {
                        $('#question-modal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(response.message || 'No se pudo guardar.');
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    toastr.error('Ocurrió un error al guardar la pregunta.');
                },
            });
        },
    });

    function resetQuestionForm() {
        $('#formQuestion')[0].reset();
        questionValidator.resetForm();
        $('#formQuestion .is-invalid').removeClass('is-invalid');
        $('#questionSlack').val('');
        $('#available').val('1').trigger('change');
        $('#answer').val(answerIsMultiple ? [] : '').trigger('change');
    }

    function populateQuestionForm(data) {
        $('#questionSlack').val(data.slack);
        $('#question').val(data.question);
        $('#available').val(String(data.available)).trigger('change');
        $('#answer').val(answerIsMultiple ? (data.answer ? data.answer.split(',') : []) : data.answer).trigger('change');
        $('#a').val(data.a || '');
        $('#b').val(data.b || '');
        $('#c').val(data.c || '');
        $('#d').val(data.d || '');
    }

    // Abrir en modo "crear"
    $(document).on('click', '.btn-new-question', function () {
        questionMode = 'create';
        $('#questionModalTitle').text('Nueva pregunta');
    });

    $('#question-modal').on('shown.bs.modal', function () {
        initQuestionSelect2();
        if (questionMode === 'create') resetQuestionForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-question', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON(config.routes.editBase + '/' + slack, function (data) {
            questionMode = 'edit';
            $('#questionModalTitle').text('Editar pregunta');
            $('#question-modal').modal('show');

            var applyData = function () {
                initQuestionSelect2();
                populateQuestionForm(data);
            };

            if ($('#question-modal').hasClass('show')) {
                applyData();
            } else {
                $('#question-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar la pregunta.');
        });
    });

});
