"use strict";
$(function () {

    // Abrir modal de confirmacion: el form real (#delete-form) envia el DELETE
    $(document).on("click", ".confirm-delete", function (e) {
        e.preventDefault();
        var url = $(this).data("href");
        $("#delete-form").attr("action", url);
        $("#delete-modal").modal("show");
    });
});

// Manejo global de errores AJAX: reactiva botones submit bloqueados y
// muestra mensajes de validacion en todas las vistas del panel de soporte.
$(document).ajaxError(function(event, xhr) {

    // Reactivar cualquier boton submit que la vista haya deshabilitado,
    // para que el usuario pueda reintentar tras un error.
    $('button[type="submit"]:disabled').prop('disabled', false);

    if (xhr.status === 419) {
        toastr.error('Sesión expirada. Por favor recargue la página.', 'Sesión expirada', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    } else if (xhr.status === 422) {
        var messages = [];
        var res = xhr.responseJSON;

        if (res && res.errors) {
            $.each(res.errors, function(field, errs) {
                messages.push(Array.isArray(errs) ? errs[0] : errs);
            });
        } else if (res && res.message) {
            messages.push(res.message);
        }

        // Pintar el detalle en el contenedor .errors si la vista lo tiene.
        var $box = $('.errors');
        if ($box.length && messages.length) {
            $box.removeClass('d-none').html(messages.join('<br>'));
        }

        toastr.warning(messages[0] || 'Error de validación.', 'Advertencia', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    } else if (xhr.status === 403) {
        toastr.error('No tienes autorización para realizar esta acción.', 'Acceso denegado', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    } else if (xhr.status >= 500) {
        toastr.error('Error al procesar la solicitud. Intente de nuevo.', 'Error', { closeButton: true, progressBar: true, positionClass: 'toast-bottom-right' });
    }
});
