$(document).ready(function () {
    var $bulkConfig = $('#bulk-config');

    function initMailerTemplatesTable() {
        BulkActions.init({
        url: $bulkConfig.data('bulk-url'),
        entityLabel: 'plantilla(s)',
    });
    }

    initMailerTemplatesTable();

    AjaxTable.init({ onLoaded: initMailerTemplatesTable });

    // select2 sobre el <select> del modal compartido; requiere dropdownParent
    // porque el select vive oculto dentro de #bulk-modal hasta que se abre.
    $('#bulk-action-select').select2({ dropdownParent: $('#bulk-modal'), width: '100%' });
    $('#bulk-modal').on('hide.bs.modal', function () {
        $('#bulk-action-select').trigger('change');
    });

    $('.delete-btn').on('click', function () {
        $('#delete-modal .modal-title').text($(this).data('title'));
        $('#delete-form').attr('action', $(this).data('url'));
        $('#delete-form input[name="_method"]').val('DELETE');
    });

    $(document).on('click', '.btn-send-test', function () {
        const uid = $(this).data('template-uid');
        const name = $(this).data('template-name');
        const subject = $(this).data('template-subject');

        $('#sendTestTemplateName').text(name);
        $('#sendTestTemplateSubject').text(subject);
        $('#sendTestForm').attr('action', $bulkConfig.data('send-test-base-url') + '/' + uid + '/send-test');
        $('#send_test_email').val('');

        new bootstrap.Modal(document.getElementById('modalSendTest')).show();
    });
});
