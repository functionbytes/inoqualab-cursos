$(document).ready(function () {
    var $form = $('#formAction');

    $('.daterange').daterangepicker();

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            range: { required: true },
            course: { required: true },
        },
        messages: {
            range: { required: 'Es necesario una opción.' },
            course: { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            toastr.success('Se ha generado el reporte.', 'Operación exitosa', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right'
            });

            var query = {
                range: $('#range').val(),
                enterprise: $('#enterprise').val(),
                course: $('#course').val(),
            };

            window.location = $form.data('incoming-url') + '?' + $.param(query);
        }
    });
});
