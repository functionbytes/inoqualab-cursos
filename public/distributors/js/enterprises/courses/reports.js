$(document).ready(function () {
    var $form = $('#formReport');

    $form.validate({
        rules: { modalitie: { required: true } },
        messages: { modalitie: { required: 'Selecciona una modalidad.' } },
        submitHandler: function () {
            var query = {
                modalitie: $('#modalitie').val(),
                enterprise: $('#enterprise').val(),
                course: $('#course').val(),
            };

            window.location = $form.data('generateUrl') + '?' + $.param(query);
        }
    });
});
