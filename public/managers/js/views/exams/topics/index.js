$(function () {

    var $page = $('#exams-topics-index');
    var config = $page.data('config') || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = $page.data('flash-success');
    var flashError = $page.data('flash-error');
    if (flashSuccess) { toastr.success(flashSuccess); }
    if (flashError) { toastr.error(flashError); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initExamsTopicsTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'pregunta(s)',
    });
    }

    initExamsTopicsTable();

    AjaxTable.init({ onLoaded: initExamsTopicsTable });

    // ── Bulk selection ───────────────────────────────────────────────────────

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Crear / Editar pregunta (modal) ────────────────────────────────────────
    // El tipo de pregunta lo define el topic (examen) y es fijo: 1 = selección
    // múltiple (A/B/C/D), 0 = falso/verdadero. El formulario ya se renderizó
    // con la variante correcta desde el servidor.
    var questionMode = 'create';
    var questionType = config.questionType;

    // select2 se inicializa perezosamente en "shown.bs.modal": el modal está
    // oculto al cargar la página y select2 calcula mal el ancho sobre
    // elementos ocultos. dropdownParent evita que el desplegable quede
    // clipeado o mal posicionado dentro del modal.
    $('#question-modal').on('shown.bs.modal', function () {
        if (typeof $.fn.select2 === 'undefined') return;

        if (!$('#questionAvailable').hasClass('select2-hidden-accessible')) {
            $('#questionAvailable').select2({ width: '100%', dropdownParent: $('#question-modal') });
        }
        if (!$('#questionAnswer').hasClass('select2-hidden-accessible')) {
            $('#questionAnswer').select2({
                width: '100%',
                dropdownParent: $('#question-modal'),
                placeholder: $('#questionAnswer').data('placeholder'),
            });
        }
    });

    function resetQuestionForm() {
        $('#questionSlack').val('');
        $('#questionText, #questionA, #questionB, #questionC, #questionD').val('');
        $('#questionAvailable').val('1');
        $('#questionAnswer').val(null).trigger('change');
        $('#formQuestion').validate().resetForm();
        $('#formQuestion .is-invalid').removeClass('is-invalid');
    }

    $('.btn-new-question').on('click', function () {
        questionMode = 'create';
        $('#questionModalTitle').text('Nueva pregunta');
        resetQuestionForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-question', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON(config.routes.editBase + '/' + slack, function (data) {
            questionMode = 'edit';
            $('#questionModalTitle').text('Editar pregunta');
            $('#questionSlack').val(data.slack);
            $('#questionText').val(data.question);
            $('#questionAvailable').val(String(data.available));
            $('#questionA').val(data.a);
            $('#questionB').val(data.b);
            $('#questionC').val(data.c);
            $('#questionD').val(data.d);

            var answerValue = questionType === 1 && data.answer ? data.answer.split(',') : data.answer;
            $('#questionAnswer').val(answerValue).trigger('change');

            $('#question-modal').modal('show');
        }).fail(function () {
            toastr.error('No se pudo cargar la pregunta.');
        });
    });

    // Reglas de validación: A/B/C/D solo aplican si el topic es de selección múltiple.
    var questionRules = {
        question: { required: true, minlength: 3, maxlength: 400 },
        answer: { required: true },
    };
    var questionMessages = {
        question: {
            required: 'La pregunta es obligatoria.',
            minlength: 'Debe contener al menos 3 caracteres.',
            maxlength: 'Debe contener como máximo 400 caracteres.',
        },
        answer: { required: 'Selecciona la respuesta correcta.' },
    };

    if (questionType === 1) {
        ['a', 'b', 'c', 'd'].forEach(function (field) {
            questionRules[field] = { required: true, minlength: 3, maxlength: 200 };
            questionMessages[field] = {
                required: 'La respuesta ' + field.toUpperCase() + ' es obligatoria.',
                minlength: 'Debe contener al menos 3 caracteres.',
                maxlength: 'Debe contener como máximo 200 caracteres.',
            };
        });
    }

    $('#formQuestion').validate({
        rules: questionRules,
        messages: questionMessages,
        errorElement: 'label',
        errorPlacement: function (error, element) {
            error.removeClass('d-none');
            var target = element.hasClass('select2-hidden-accessible') ? element.next('.select2-container') : element;
            error.insertAfter(target);
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler: function () {
            var answer = $('#questionAnswer').val();
            if (Array.isArray(answer)) {
                answer = answer.join(',');
            }

            var formData = new FormData();
            formData.append('slack', $('#questionSlack').val());
            formData.append('question', $('#questionText').val());
            formData.append('available', $('#questionAvailable').val());
            formData.append('answer', answer);
            formData.append('topic', config.topicSlack);
            if (questionType === 1) {
                formData.append('a', $('#questionA').val());
                formData.append('b', $('#questionB').val());
                formData.append('c', $('#questionC').val());
                formData.append('d', $('#questionD').val());
            }

            var isEdit = questionMode === 'edit';
            var url = isEdit ? config.routes.update : config.routes.store;

            var $btn = $('#btnSaveQuestion').prop('disabled', true);
            var btnOriginalText = $btn.text();
            $btn.text('Guardando...');

            $.ajax({
                url: url,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: false,
                processData: false,
                data: formData,
                success: function (res) {
                    $btn.prop('disabled', false).text(btnOriginalText);
                    if (res.success) {
                        $('#question-modal').modal('hide');
                        toastr.success(res.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(res.message || 'No se pudo guardar.');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).text(btnOriginalText);
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function (field, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Ocurrió un error al guardar la pregunta.');
                    }
                },
            });
        },
    });

});
