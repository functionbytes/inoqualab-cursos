$(document).ready(function () {
    const $form = $('#formReport');

    $form.validate({
        rules: { modalitie: { required: true } },
        messages: { modalitie: { required: 'Selecciona una modalidad.' } },
        submitHandler: function () {
            const query = {
                modalitie: $('#modalitie').val(),
                enterprise: $('#enterprise').val(),
                course: $('#course').val(),
            };

            window.location = $form.data('generate-url') + '?' + $.param(query);
        },
    });
});
