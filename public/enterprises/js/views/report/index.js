$(document).ready(function () {
    var $form = $('#formReport');

    $form.validate({
        rules: { modalitie: { required: true } },
        messages: { modalitie: { required: 'Selecciona una modalidad.' } },
        submitHandler: function () {
            var query = {
                modalitie: $('#modalitie').val(),
                enterprise: $('#enterprise_id').val(),
                course: $('#course_id').val(),
            };

            window.location = $form.data('generate-url') + '?' + $.param(query);
        },
    });
});
