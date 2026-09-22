$(function () {

    var $page = $('#courses-chapters-index');
    var config = $page.data('config') || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = $page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    // ── Filters modal ────────────────────────────────────────────────────────
    function initCoursesChaptersTable() {
        FilterToolbar.init({
        fields: { filterAvailable: 'popover_Available' },
    });
        BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'tema(s)',
    });
    }

    initCoursesChaptersTable();

    AjaxTable.init({ onLoaded: initCoursesChaptersTable });

    // ── Eliminar individual vía modal ────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Bulk selection ───────────────────────────────────────────────────────

    // ── Reordenar por drag&drop ──────────────────────────────────────────────
    var $sortable = $('#chapters-sortable');
    if ($sortable.length && $.fn.sortable) {
        $sortable.sortable({
            handle: '.drag-handle',
            items: '> tr',
            axis: 'y',
            placeholder: 'sortable-placeholder',
            helper: function (e, tr) {
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

    // ── Crear / Editar tema (modal) ──────────────────────────────────────────
    var chapterQuill = null;
    var chapterMode = 'create';
    var nextPosition = config.nextPosition;

    function initChapterQuill() {
        if (chapterQuill) return;
        chapterQuill = new Quill('#chapterDescription', {
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
    function initChapterSelect2() {
        var $select = $('#chapterAvailable');
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({ dropdownParent: $('#chapter-modal'), width: '100%' });
    }

    function resetChapterForm() {
        $('#chapterSlack').val('');
        $('#chapterTitle').val('').removeClass('is-invalid');
        $('#chapterPosition').val(nextPosition);
        $('#chapterAvailable').val('1').trigger('change');
        if (chapterQuill) chapterQuill.setText('');
    }

    // Abrir en modo "crear"
    $('.btn-new-chapter').on('click', function () {
        chapterMode = 'create';
        $('#chapterModalTitle').text('Nuevo tema');
    });

    $('#chapter-modal').on('shown.bs.modal', function () {
        initChapterQuill();
        initChapterSelect2();
        if (chapterMode === 'create') {
            resetChapterForm();
        }
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-chapter', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON(config.routes.editBase + '/' + slack, function (data) {
            chapterMode = 'edit';
            $('#chapterModalTitle').text('Editar tema');
            $('#chapter-modal').modal('show');

            var applyData = function () {
                initChapterQuill();
                initChapterSelect2();
                $('#chapterSlack').val(data.slack);
                $('#chapterTitle').val(data.title);
                $('#chapterPosition').val(data.position);
                $('#chapterAvailable').val(String(data.available)).trigger('change');
                chapterQuill.root.innerHTML = data.description || '';
            };

            if ($('#chapter-modal').hasClass('show')) {
                applyData();
            } else {
                $('#chapter-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar el tema.');
        });
    });

    // Guardar (crear o actualizar según el modo)
    $('#btnSaveChapter').on('click', function () {
        var title = $.trim($('#chapterTitle').val());
        if (!title) {
            $('#chapterTitle').addClass('is-invalid');
            toastr.warning('El título es obligatorio.');
            return;
        }

        var description = chapterQuill ? chapterQuill.root.innerHTML.replace('<p><br></p>', '') : '';
        var isEdit = chapterMode === 'edit';
        var url = isEdit ? config.routes.update : config.routes.store;

        var payload = {
            title: title,
            position: $('#chapterPosition').val(),
            description: description,
            available: $('#chapterAvailable').val(),
            course: config.courseSlack,
        };
        if (isEdit) payload.slack = $('#chapterSlack').val();

        var $btn = $(this).prop('disabled', true);
        var originalText = $btn.text();
        $btn.text('Guardando...');

        $.ajax({
            url: url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: payload,
            success: function (res) {
                $btn.prop('disabled', false).text(originalText);
                if (res.success) {
                    $('#chapter-modal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.warning(res.message || 'No se pudo guardar.');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text(originalText);
                toastr.error('Ocurrió un error al guardar el tema.');
            },
        });
    });

});
