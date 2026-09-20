$(document).ready(function () {
    var $form = $('#formReport');

    $form.validate({
        rules: { modalitie: { required: true } },
        messages: { modalitie: { required: 'Selecciona un estado.' } },
        submitHandler: function () {
            var query = {
                modalitie: $('#modalitie').val(),
                enterprise: $('#enterprise').val(),
            };

            window.location = $form.data('generateUrl') + '?' + $.param(query);
        }
    });
});
