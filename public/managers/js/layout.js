$(function () {
    'use strict';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.confirm-delete', function (e) {
        e.preventDefault();
        var url = $(this).data('href');
        $('#delete-form').attr('action', url);
        $('#delete-modal').modal('show');
    });

    $(document).ajaxError(function (event, xhr) {
        var options = { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' };

        if (xhr.status === 419) {
            toastr.error('Sesión expirada. Por favor recargue la página.', 'Sesión expirada', options);
        } else if (xhr.status === 422) {
            try {
                var response = JSON.parse(xhr.responseText);
                toastr.warning(response.message || 'Error de validación.', 'Advertencia', options);
            } catch (e) {}
        } else if (xhr.status >= 500) {
            toastr.error('Error al procesar la solicitud. Intente de nuevo.', 'Error', options);
        }
    });

    // Fix: dropdown clipped by overflow:hidden en .table-responsive
    document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (el) {
        new bootstrap.Dropdown(el, {
            popperConfig: function (defaultConfig) {
                return Object.assign({}, defaultConfig, { strategy: 'fixed' });
            }
        });
    });

    // Fallback de avatar de perfil roto en el header
    document.querySelectorAll('img[data-fallback-src]').forEach(function (img) {
        img.addEventListener('error', function () {
            img.src = img.dataset.fallbackSrc;
        }, { once: true });
    });

    // Marcar todas las notificaciones como leídas desde el header
    $(document).on('click', '#markAllReadHeader', function (e) {
        e.preventDefault();
        var url = $('#main-wrapper').data('notifications-mark-all-read-url');
        $.get(url, function () {
            $('#notifBadge').remove();
            $('#markAllReadHeader').closest('.d-flex').find('#markAllReadHeader').remove();
            toastr.success('Notificaciones marcadas como leídas', '', { timeOut: 2000 });
        });
    });
});
