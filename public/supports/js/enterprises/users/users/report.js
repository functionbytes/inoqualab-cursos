$(document).ready(function () {
    const $form = $('#formReport');

    $form.validate({
        rules: { modalitie: { required: true } },
        messages: { modalitie: { required: 'Selecciona un estado.' } },
        submitHandler: function () {
            const query = {
                modalitie: $('#modalitie').val(),
                enterprise: $('#enterprise').val(),
            };

            window.location = $form.data('generate-url') + '?' + $.param(query);
        },
    });
});
