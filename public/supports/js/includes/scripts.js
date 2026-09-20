"use strict";
$(function () {

    var deleteUrl = null;

    // Abrir modal de confirmacion y guardar la URL destino
    $(document).on("click", ".confirm-delete", function (e) {
        e.preventDefault();
        deleteUrl = $(this).data("href");
        $("#delete-modal").modal("show");
    });

    // Confirmar: enviar peticion DELETE via form (preserva el redirect del controlador)
    $(document).on("click", "#delete-link", function (e) {
        e.preventDefault();
        if (!deleteUrl) return;

        var token = $('meta[name="csrf-token"]').attr('content');

        var $form = $('<form>', { method: 'POST', action: deleteUrl, 'class': 'd-none' });
        $form.append($('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }));
        $form.append($('<input>', { type: 'hidden', name: '_token', value: token }));

        $('body').append($form);
        $form.trigger('submit');
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
