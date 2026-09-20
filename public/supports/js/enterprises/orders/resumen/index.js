$(document).ready(function () {
    Dropzone.autoDiscover = false;

    const $form = $('#formReport');

    $('#enterprise').select2({
        placeholder: 'Seleccionar una empresa',
        minimumResultsForSearch: Infinity,
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
            const query = {
                range: $('#range').val(),
                enterprise: $('#enterprise').val(),
                distributor: $('#distributor').val(),
                type: $('#type').val(),
                methods: $('#method').val(),
                condition: $('#condition').val(),
            };

            window.location = $form.data('generate-url') + '?' + $.param(query);
        },
    });
});
