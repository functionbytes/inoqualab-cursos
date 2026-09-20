$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formReport');

    $('.daterange').daterangepicker();

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            modalitie: { required: true },
            course: { required: true },
            range: { required: true },
        },
        messages: {
            modalitie: { required: 'Es necesario una opción.' },
            course: { required: 'Es necesario una opción.' },
            range: { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            toastr.success('Se esta generando el reporte.', 'Operación exitosa', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });

            var query = {
                range: $('#range').val(),
                enterprise: $('#enterprises').val(),
                course: $('#course').val(),
            };

            window.location = $form.data('generateUrl') + '?' + $.param(query);
        }
    });
});
