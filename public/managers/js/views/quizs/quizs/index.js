$(function () {

    var $page = $('#quizs-index');
    var config = $page.data('config') || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = $page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterLesson').val($('#modalLesson').val());
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'quiz(zes)',
    });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Crear / Editar quiz (modal) ────────────────────────────────────────────
    var quizQuill = null;
    var quizMode = 'create';

    function initQuizQuill() {
        if (quizQuill) return;
        quizQuill = new Quill('#quizDescription', {
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
    // documentReady SIN dropdownParent (los selects viven ocultos dentro del
    // modal en ese momento) — su dropdown terminaría flotando sobre <body> en
    // vez del modal. Por eso se destruye cada instancia y se re-crea con las
    // opciones correctas cada vez que el modal se abre.
    function initQuizSelect2() {
        ['#quizLesson', '#quizType', '#quizDuration', '#quizAvailable'].forEach(function (selector) {
            var $select = $(selector);
            if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
            $select.select2({ dropdownParent: $('#quiz-modal'), width: '100%', placeholder: $select.data('placeholder') });
        });
    }

    var quizValidator = $('#formQuiz').validate({
        ignore: '.ignore',
        rules: {
            title: { required: true, minlength: 3, maxlength: 200 },
            lesson: { required: true },
            type: { required: true },
            duration: { required: true },
            day: { required: true, number: true, min: 0, max: 999 },
            timer: { required: true, number: true, min: 0, max: 999 },
            question: { required: true, number: true, min: 0, max: 999 },
            mark: { required: true, number: true, min: 0, max: 999 },
            available: { required: true },
        },
        messages: {
            title: { required: 'El título es obligatorio.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 200 caracteres.' },
            lesson: { required: 'Selecciona una clase.' },
            type: { required: 'Selecciona una modalidad.' },
            duration: { required: 'Selecciona una opción.' },
            day: { required: 'La vigencia es obligatoria.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            timer: { required: 'El tiempo límite es obligatorio.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            question: { required: 'La cantidad de preguntas es obligatoria.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            mark: { required: 'Las preguntas correctas son obligatorias.', number: 'Solo se permiten números.', min: 'Debe ser mayor o igual a 0.', max: 'Debe ser menor a 999.' },
            available: { required: 'Selecciona un estado.' },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element).addClass('error').removeClass('d-none');
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            var description = quizQuill ? quizQuill.root.innerHTML.replace('<p><br></p>', '') : '';
            formData.append('description', description);

            var url = quizMode === 'edit' ? config.routes.update : config.routes.store;

            var $submitButton = $('#formQuiz button[type="submit"]').prop('disabled', true);
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
                        $('#quiz-modal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(response.message || 'No se pudo guardar.');
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    toastr.error('Ocurrió un error al guardar el quiz.');
                },
            });
        },
    });

    function resetQuizForm() {
        $('#formQuiz')[0].reset();
        quizValidator.resetForm();
        $('#formQuiz .is-invalid').removeClass('is-invalid');
        $('#quizSlack').val('');
        $('#quizLesson').val('').trigger('change');
        $('#quizType').val('').trigger('change');
        $('#quizDuration').val('1').trigger('change');
        $('#quizAvailable').val('1').trigger('change');
        if (quizQuill) quizQuill.setText('');
    }

    function populateQuizForm(data) {
        $('#quizSlack').val(data.slack);
        $('#quizTitle').val(data.title);
        $('#quizLesson').val(data.lesson_id != null ? String(data.lesson_id) : '').trigger('change');
        $('#quizType').val(String(data.type)).trigger('change');
        $('#quizDuration').val(String(data.duration)).trigger('change');
        $('#quizDay').val(data.day);
        $('#quizTimer').val(data.timer);
        $('#quizQuestion').val(data.question);
        $('#quizMark').val(data.mark);
        $('#quizAvailable').val(String(data.available)).trigger('change');
        quizQuill.root.innerHTML = data.description || '';
    }

    // Abrir en modo "crear"
    $(document).on('click', '.btn-new-quiz', function () {
        quizMode = 'create';
        $('#quizModalTitle').text('Nuevo quiz');
    });

    $('#quiz-modal').on('shown.bs.modal', function () {
        initQuizQuill();
        initQuizSelect2();
        if (quizMode === 'create') resetQuizForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-quiz', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON(config.routes.editBase + '/' + slack, function (data) {
            quizMode = 'edit';
            $('#quizModalTitle').text('Editar quiz');
            $('#quiz-modal').modal('show');

            var applyData = function () {
                initQuizQuill();
                initQuizSelect2();
                populateQuizForm(data);
            };

            if ($('#quiz-modal').hasClass('show')) {
                applyData();
            } else {
                $('#quiz-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar el quiz.');
        });
    });

});
