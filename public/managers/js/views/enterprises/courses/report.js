Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formReport');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            modalitie: { required: true },
        },
        messages: {
            modalitie: { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            var query = {
                modalitie: $('#modalitie').val(),
                enterprise: $('#enterprise').val(),
                course: $('#course').val(),
            };

            window.location = $form.data('generate-url') + '?' + $.param(query);
        }
    });
});
