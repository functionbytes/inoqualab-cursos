$(function () {

    var $page = $('#courses-announcements-index');
    var config = $page.data('config') || {};
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    var flashSuccess = $page.data('flash-success');
    if (flashSuccess) { toastr.success(flashSuccess); }

    // ── Filters modal ────────────────────────────────────────────────────────
    $('#applyFiltersBtn').on('click', function () {
        $('#filterAvailable').val($('#modalAvailable').val());
        $('#filters-modal').modal('hide');
        $('#searchForm').submit();
    });

    // ── Bulk selection ───────────────────────────────────────────────────────
    BulkActions.init({
        url: config.routes.bulkAction,
        entityLabel: 'anuncio(s)',
    });

    // ── Eliminar individual via modal ─────────────────────────────────────────
    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        var $btn = $(this);
        $('#delete-modal .modal-title').text($btn.data('title'));
        $('#delete-form').attr('action', $btn.data('url'));
        $('#delete-modal').modal('show');
    });

    // ── Crear / Editar anuncio (modal) ────────────────────────────────────────
    var announcementQuill = null;
    var announcementMode = 'create';

    function initAnnouncementQuill() {
        if (announcementQuill) return;
        announcementQuill = new Quill('#announcementDescription', {
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
    function initAnnouncementSelect2() {
        var $select = $('#announcementAvailable');
        if ($select.hasClass('select2-hidden-accessible')) $select.select2('destroy');
        $select.select2({ dropdownParent: $('#announcement-modal'), width: '100%' });
    }

    function resetAnnouncementForm() {
        $('#announcementSlack').val('');
        $('#announcementTitle').val('').removeClass('is-invalid');
        $('#announcementAvailable').val('1').trigger('change');
        if (announcementQuill) announcementQuill.setText('');
    }

    // Abrir en modo "crear"
    $('#btnNewAnnouncement, .btn-new-announcement').on('click', function () {
        announcementMode = 'create';
        $('#announcementModalTitle').text('Nuevo anuncio');
    });

    $('#announcement-modal').on('shown.bs.modal', function () {
        initAnnouncementQuill();
        initAnnouncementSelect2();
        if (announcementMode === 'create') {
            resetAnnouncementForm();
        }
    });

    // Abrir en modo "editar": trae los datos vía AJAX y precarga el modal.
    $(document).on('click', '.btn-edit-announcement', function (e) {
        e.preventDefault();
        var slack = $(this).data('slack');

        $.getJSON(config.routes.editBase + '/' + slack, function (data) {
            announcementMode = 'edit';
            $('#announcementModalTitle').text('Editar anuncio');
            $('#announcement-modal').modal('show');

            var applyData = function () {
                initAnnouncementQuill();
                initAnnouncementSelect2();
                $('#announcementSlack').val(data.slack);
                $('#announcementTitle').val(data.title);
                $('#announcementAvailable').val(String(data.available)).trigger('change');
                announcementQuill.root.innerHTML = data.description || '';
            };

            if ($('#announcement-modal').hasClass('show')) {
                applyData();
            } else {
                $('#announcement-modal').one('shown.bs.modal', applyData);
            }
        }).fail(function () {
            toastr.error('No se pudo cargar el anuncio.');
        });
    });

    // Guardar (crear o actualizar según el modo)
    $('#btnSaveAnnouncement').on('click', function () {
        var title = $.trim($('#announcementTitle').val());
        if (!title) {
            $('#announcementTitle').addClass('is-invalid');
            toastr.warning('El título es obligatorio.');
            return;
        }

        var description = announcementQuill ? announcementQuill.root.innerHTML.replace('<p><br></p>', '') : '';
        var isEdit = announcementMode === 'edit';
        var url = isEdit ? config.routes.update : config.routes.store;

        var payload = {
            title: title,
            description: description,
            available: $('#announcementAvailable').val(),
            course: config.courseSlack,
        };
        if (isEdit) payload.slack = $('#announcementSlack').val();

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
                    $('#announcement-modal').modal('hide');
                    toastr.success(res.message);
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    toastr.warning(res.message || 'No se pudo guardar.');
                }
            },
            error: function () {
                $btn.prop('disabled', false).text(originalText);
                toastr.error('Ocurrió un error al guardar el anuncio.');
            },
        });
    });

});
