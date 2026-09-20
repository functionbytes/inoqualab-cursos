Dropzone.autoDiscover = false;

$(document).ready(function () {
    const generateUrl = $('#formReport').data('generate-url');

    $('.daterange').daterangepicker();

    $('#formReport').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            method: {
                required: true,
            },
            condition: {
                required: true,
            },
            distributor: {
                required: true,
            },
            range: {
                required: true,
            },
        },
        messages: {
            method: {
                required: 'Es necesario una opción.',
            },
            condition: {
                required: 'Es necesario una opción.',
            },
            distributor: {
                required: 'Es necesario una opción.',
            },
            range: {
                required: 'Es necesario una opción.',
            },
        },
        submitHandler: function () {
            toastr.success('Se ha generado el reporte.', 'Operación exitosa', {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-bottom-right',
            });

            const query = {
                range: $('#range').val(),
                distributor: $('#distributor').val(),
                methods: $('#method').val(),
                condition: $('#condition').val(),
            };

            window.location = generateUrl + '?' + $.param(query);
        },
    });
});
