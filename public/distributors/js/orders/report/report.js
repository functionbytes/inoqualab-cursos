$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formReport');

    $('#enterprises').select2({
        placeholder: 'Seleccionar una empresa',
        minimumResultsForSearch: Infinity
    });

    $('.daterange').daterangepicker();

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            enterprise: { required: true },
            type: { required: true },
            condition: { required: true },
            method: { required: true },
            range: { required: true },
        },
        messages: {
            enterprise: { required: 'Es necesario una opción.' },
            type: { required: 'Es necesario una opción.' },
            condition: { required: 'Es necesario una opción.' },
            method: { required: 'Es necesario una opción.' },
            range: { required: 'Es necesario una opción.' },
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
                type: $('#type').val(),
                methods: $('#method').val(),
                condition: $('#condition').val(),
            };

            window.location = $form.data('generateUrl') + '?' + $.param(query);
        }
    });
});
