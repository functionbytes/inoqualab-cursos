$(document).ready(function () {
    $('#mail_status').on('change', function () {
        $('#smtpFields').toggleClass('d-none', !this.checked);
    });

    $('#imap_status').on('change', function () {
        $('#imapFields').toggleClass('d-none', !this.checked);
    });

    $('#formEmails').on('submit', function (e) {
        e.preventDefault();

        var updateUrl = $(this).data('update-url');
        var formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // SMTP
        formData.append('mail_status', $('#mail_status').is(':checked') ? 'true' : 'false');
        formData.append('mail_host', $('#mail_host').val());
        formData.append('mail_port', $('#mail_port').val());
        formData.append('mail_encryption', $('#mail_encryption').val());
        formData.append('mail_username', $('#mail_username').val());
        formData.append('mail_from_address', $('#mail_from_address').val());
        formData.append('mail_from_name', $('#mail_from_name').val());
        var pwd = $('#mail_password').val();
        if (pwd) formData.append('mail_password', pwd);

        // IMAP
        formData.append('imap_host', $('#imap_host').val());
        formData.append('imap_port', $('#imap_port').val());
        formData.append('imap_encryption', $('#imap_encryption').val());
        formData.append('imap_protocol', $('#imap_protocol').val());
        formData.append('imap_username', $('#imap_username').val());
        formData.append('imap_status', $('#imap_status').is(':checked') ? 'true' : 'false');
        var imapPwd = $('#imap_password').val();
        if (imapPwd) formData.append('imap_password', imapPwd);

        $.ajax({
            url: updateUrl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: formData,
            success: function (res) {
                if (res.success) {
                    toastr.success(res.message, 'Operación exitosa', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right',
                    });
                }
            },
            error: function (xhr) {
                var errors = xhr.responseJSON && xhr.responseJSON.errors;
                if (errors) {
                    var first = Object.values(errors)[0][0];
                    toastr.error(first, 'Error de validación', { closeButton: true, positionClass: 'toast-bottom-right' });
                } else {
                    toastr.error('Error al guardar.', 'Error', { closeButton: true, positionClass: 'toast-bottom-right' });
                }
            }
        });
    });
});
