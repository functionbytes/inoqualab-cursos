Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formReport');

    $('.daterange').daterangepicker();

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            range: {
                required: true,
            },
        },
        messages: {
            range: {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function (form) {
            toastr.success('Se ha generado el reporte.', 'Operación exitosa', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right',
            });

            var query = {
                range: $('#range').val(),
                distributor: $('#distributor').val(),
                available: $('#available').val(),
            };

            window.location = $form.data('generate-url') + '?' + $.param(query);
        },
    });
});
