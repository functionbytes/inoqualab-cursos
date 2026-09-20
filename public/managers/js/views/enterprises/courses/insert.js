Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formInclide');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            'user[]': { required: true },
        },
        messages: {
            'user[]': { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var enterprise = $('#enterprises').val();
            var course = $('#course').val();
            var slack = $('#slack').val();
            var users = $('#users').val();

            formData.append('slack', slack);
            formData.append('enterprises', enterprise);
            formData.append('users', users);
            formData.append('course', course);

            $.ajax({
                url: $form.data('include-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function () {
                    window.location.href = $form.data('redirect-url');
                }
            });
        }
    });
});
