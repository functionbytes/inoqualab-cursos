$(function () {

    var $page = $('#courses-lessons-index');
    var config = $page.data('config') || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = $page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filterChapter').val($('#modalChapter').val());
        $('#filterType').val($('#modalType').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Selección masiva ─────────────────────────────────────────────────────
    BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'clase(s)',
    });

    // ── Reordenar por drag&drop ──────────────────────────────────────────────
    var $sortable = $('#lessons-sortable');
    if ($sortable.length && $.fn.sortable) {
        $sortable.sortable({
            handle: '.drag-handle',
            items: '> tr',
            axis: 'y',
            placeholder: 'sortable-placeholder',
            helper: function (e, tr) {
                // Fija el ancho de las celdas al arrastrar para que no colapsen.
                var $originals = tr.children();
                var $helper = tr.clone();
                $helper.children().each(function (i) { $(this).width($originals.eq(i).width()); });
                return $helper;
            },
            update: function () {
                var ids = $sortable.find('> tr').map(function () { return $(this).data('id'); }).get();
                $.ajax({
                    url: $sortable.data('reorder-url'),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    data: { ids: ids },
                    success: function (res) { toastr.success(res.message || 'Orden actualizado.'); },
                    error: function () { toastr.error('No se pudo guardar el orden.'); },
                });
            },
        });
    }

    // ── Crear / Editar clase (modal) ─────────────────────────────────────────
    Dropzone.autoDiscover = false;

    var lessonQuill = null;
    var lessonMode = 'create';
    var nextPosition = config.nextPosition;

    function initLessonQuill() {
        if (lessonQuill) return;
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{ 'header': 1 }, { 'header': 2 }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'script': 'sub' }, { 'script': 'super' }],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            [{ 'direction': 'rtl' }],
            [{ 'size': ['small', false, 'large', 'huge'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['link', 'image', 'video', 'formula'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'font': [] }],
            [{ 'align': [] }],
            ['clean'],
        ];
        lessonQuill = new Quill('#details', {
            modules: { toolbar: toolbarOptions },
            placeholder: 'Escriba aquí...',
            theme: 'snow',
        });
    }

    // El script global (select2.init.js) ya auto-inicializa ".select2" en
    // documentReady SIN dropdownParent (el select vive oculto dentro del modal
    // en ese momento) — su dropdown terminaría flotando sobre <body> en vez del
    // modal. Por eso se destruye esa instancia y se re-crea con las opciones
    // correctas cada vez que el modal (o el campo condicional) se muestra.
    function initLessonSelect2(selector) {
        var $select = $(selector);
        if (!$select.length) return;
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({ dropdownParent: $('#lesson-modal'), width: '100%', placeholder: $select.data('placeholder') });
    }

    function toggleVisibility(value) {
        $('.divFiles, .divAudiovisual, .divAudio').addClass('d-none');
        if (value == 1) { $('.divAudiovisual').removeClass('d-none'); initLessonSelect2('#platform'); }
        else if (value == 2) $('.divAudio').removeClass('d-none');
        else if (value == 3 || value == 4 || value == 5) $('.divFiles').removeClass('d-none');
    }

    function detectVideoPlatform() {
        var url = $('#url').val();
        var platform = $('#platform').val();
        var $label = $('#url-platform');
        if (!url) { $label.text('').attr('class', 'd-block mt-1'); return; }
        var yt = /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/.test(url);
        var vm = /^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/.test(url);
        if (platform === 'youtube') {
            $label.text(yt ? '✔ Enlace de YouTube válido' : '⚠ El enlace no corresponde a YouTube')
                  .attr('class', 'd-block mt-1 fw-semibold ' + (yt ? 'text-success' : 'text-warning'));
        } else if (platform === 'vimeo') {
            $label.text(vm ? '✔ Enlace de Vimeo válido' : '⚠ El enlace no corresponde a Vimeo')
                  .attr('class', 'd-block mt-1 fw-semibold ' + (vm ? 'text-success' : 'text-warning'));
        } else {
            var ok = yt || vm;
            $label.text(ok ? (yt ? '✔ YouTube detectado' : '✔ Vimeo detectado') : '⚠ Debe ser un enlace de YouTube o Vimeo')
                  .attr('class', 'd-block mt-1 fw-semibold ' + (ok ? 'text-success' : 'text-warning'));
        }
    }

    $('#type').on('change', function () {
        toggleVisibility($(this).val());
        detectVideoPlatform();
    });
    $('#url, #platform').on('input change', function () { detectVideoPlatform(); });

    // Autocompleta el tamaño (MB) al elegir un archivo, requerido para imagen/zip/pdf.
    $('#file').on('change', function () {
        var file = this.files[0];
        if (file && ['3', '4', '5'].includes($('#type').val())) {
            $('#size').val((file.size / 1048576).toFixed(2));
        }
    });

    $.validator.addMethod('videoUrl', function (value) {
        if (!value) return true;
        var platform = $('#platform').val();
        var yt = /^(https?:\/\/)?(www\.)?(youtube\.com\/(watch\?v=|embed\/)|youtu\.be\/)[\w\-]+/.test(value);
        var vm = /^(https?:\/\/)?(www\.)?(vimeo\.com\/|player\.vimeo\.com\/video\/)[\d]+/.test(value);
        if (platform === 'youtube') return yt;
        if (platform === 'vimeo') return vm;
        return yt || vm;
    }, function () {
        var p = $('#platform').val();
        if (p === 'youtube') return 'El enlace debe ser de YouTube';
        if (p === 'vimeo') return 'El enlace debe ser de Vimeo';
        return 'Ingresa un enlace válido de YouTube o Vimeo';
    });

    var lessonValidator = $('#formLessons').validate({
        ignore: '.ignore',
        rules: {
            title: { required: true, minlength: 3, maxlength: 100 },
            position: { required: true, number: true, minlength: 1, maxlength: 10 },
            chapter: { required: true },
            type: { required: true },
            available: { required: true },
            platform: { required: function () { return $('#type').val() == '1'; } },
            url: {
                required: function () { return $('#type').val() == '1'; },
                videoUrl: function () { return $('#type').val() == '1'; },
            },
            duration: { required: function () { return ['1', '2'].includes($('#type').val()); } },
            size: { required: function () { return ['3', '4', '5'].includes($('#type').val()); } },
            // El archivo solo es obligatorio al crear: al editar se conserva el existente
            // si no se selecciona uno nuevo (ver LessonsController::update()).
            file: {
                required: function () {
                    return lessonMode === 'create' && ['2', '3', '4', '5'].includes($('#type').val());
                },
            },
        },
        messages: {
            title: { required: 'El título es obligatorio.', minlength: 'Debe contener al menos 3 caracteres.', maxlength: 'Debe contener como máximo 100 caracteres.' },
            position: { required: 'La posición es obligatoria.', number: 'Solo se pueden ingresar números.' },
            chapter: { required: 'Selecciona un tema.' },
            type: { required: 'Selecciona un tipo.' },
            available: { required: 'Selecciona un estado.' },
            platform: { required: 'Selecciona la plataforma del video.' },
            url: { required: 'El enlace es obligatorio.', videoUrl: 'Ingresa un enlace válido de YouTube o Vimeo.' },
            duration: { required: 'La duración es obligatoria.' },
            size: { required: 'El tamaño es obligatorio.' },
            file: { required: 'Selecciona un archivo.' },
        },
        errorPlacement: function (error, element) {
            error.insertAfter(element).addClass('error').removeClass('d-none');
        },
        submitHandler: function (form) {
            $('#detail').val(lessonQuill ? lessonQuill.root.innerHTML.replace('<p><br></p>', '') : '');

            var formData = new FormData(form);
            var url = lessonMode === 'edit' ? config.routes.update : config.routes.store;

            var $submitButton = $('#formLessons button[type="submit"]').prop('disabled', true);
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
                        $('#lesson-modal').modal('hide');
                        toastr.success(response.message);
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        toastr.warning(response.message || 'No se pudo guardar.');
                    }
                },
                error: function () {
                    $submitButton.prop('disabled', false).text(submitOriginalText);
                    toastr.error('Ocurrió un error al guardar la clase.');
                },
            });
        },
    });

    function resetLessonForm() {
        $('#formLessons')[0].reset();
        lessonValidator.resetForm();
        $('#formLessons .is-invalid').removeClass('is-invalid');
        $('#lessonSlack').val('');
        $('#current-file-hint').addClass('d-none').text('');
        $('#url-platform').text('').attr('class', 'd-block mt-1');
        $('#position').val(nextPosition);
        // .reset() no dispara 'change': select2 necesita el evento para refrescar su UI.
        $('#chapter, #type, #available').trigger('change');
        toggleVisibility('');
        if (lessonQuill) lessonQuill.setText('');
    }

    function populateLessonForm(data) {
        $('#lessonSlack').val(data.slack);
        $('#title').val(data.title);
        $('#chapter').val(data.chapter_id).trigger('change');
        $('#type').val(data.type_id).trigger('change');
        $('#position').val(data.position);
        $('#available').val(String(data.available)).trigger('change');
        $('#platform').val(data.platform || '').trigger('change');
        $('#url').val(data.url || '');
        $('#duration').val(data.duration || '');
        $('#size').val(data.size || '');
        lessonQuill.root.innerHTML = data.detail || '';

        toggleVisibility(data.type_id);
        detectVideoPlatform();

        if (data.has_file) {
            $('#current-file-hint').text('Archivo actual: ' + data.file_name).removeClass('d-none');
        } else {
            $('#current-file-hint').addClass('d-none').text('');
        }
    }

    // Abrir en modo "crear"
    $(document).on('click', '.btn-new-lesson', function () {
        lessonMode = 'create';
        $('#lessonModalTitle').text('Nueva clase');
    });

    $('#lesson-modal').on('shown.bs.modal', function () {
        initLessonQuill();
        initLessonSelect2('#chapter');
        initLessonSelect2('#type');
        initLessonSelect2('#available');
        if (lessonMode === 'create') resetLessonForm();
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-lesson', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON(config.routes.editBase + '/' + slack, function (data) {
            lessonMode = 'edit';
            $('#lessonModalTitle').text('Editar clase');
            $('#lesson-modal').modal('show');

            var applyData = function () {
                initLessonQuill();
                initLessonSelect2('#chapter');
                initLessonSelect2('#type');
                initLessonSelect2('#available');
                populateLessonForm(data);
            };

            if ($('#lesson-modal').hasClass('show')) {
                applyData();
            } else {
                $('#lesson-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar la clase.');
        });
    });

});
