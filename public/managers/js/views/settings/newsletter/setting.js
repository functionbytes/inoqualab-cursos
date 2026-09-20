(function () {
    var form = document.getElementById('formNewsletter');
    var updateUrl = form.dataset.updateUrl;

    // ── Show/hide condicional ─────────────────────────────────────────────────
    document.getElementById('newsletter_email_notifications')?.addEventListener('change', function () {
        document.getElementById('notification-email-fields').classList.toggle('d-none', !this.checked);
    });

    document.getElementById('newsletter_popup_enabled')?.addEventListener('change', function () {
        document.getElementById('popup-fields').classList.toggle('d-none', !this.checked);
    });

    document.getElementById('newsletter_mailjet_enabled')?.addEventListener('change', function () {
        document.getElementById('mailjet-fields').classList.toggle('d-none', !this.checked);
    });

    // ── Guardar via AJAX ──────────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('newsletter_enabled', document.getElementById('newsletter_enabled').checked ? 1 : 0);
        formData.append('newsletter_double_optin', document.getElementById('newsletter_double_optin').checked ? 1 : 0);
        formData.append('newsletter_email_notifications', document.getElementById('newsletter_email_notifications').checked ? 1 : 0);
        formData.append('newsletter_notification_email', document.getElementById('newsletter_notification_email').value);
        formData.append('newsletter_popup_enabled', document.getElementById('newsletter_popup_enabled').checked ? 1 : 0);
        formData.append('newsletter_popup_delay', document.getElementById('newsletter_popup_delay').value);
        formData.append('newsletter_mailjet_enabled', document.getElementById('newsletter_mailjet_enabled').checked ? 1 : 0);
        formData.append('newsletter_mailjet_api_key', document.getElementById('newsletter_mailjet_api_key').value);
        formData.append('newsletter_mailjet_api_secret', document.getElementById('newsletter_mailjet_api_secret').value);
        formData.append('newsletter_mailjet_list_id', document.getElementById('newsletter_mailjet_list_id').value);

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
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Error al guardar.';
                toastr.error(msg, 'Error', { closeButton: true, positionClass: 'toast-bottom-right' });
            }
        });
    });
})();
