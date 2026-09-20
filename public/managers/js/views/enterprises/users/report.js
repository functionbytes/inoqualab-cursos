Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formReport');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            modalitie: { required: true },
        },
        messages: {
            modalitie: { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            toastr.success('Se ha generado el reporte.', 'Operación exitosa', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });

            var query = {
                modalitie: $('#modalitie').val(),
                enterprise: $('#enterprise').val(),
            };

            window.location = $form.data('generate-url') + '?' + $.param(query);
        }
    });
});
