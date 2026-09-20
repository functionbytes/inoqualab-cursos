Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formCourse');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            course: { required: true },
            'user[]': { required: true },
        },
        messages: {
            course: { required: 'Es necesario una opción.' },
            'user[]': { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var old = $('#old').val();
            var enterprise = $('#enterprises').val();
            var user = $('#user').val();

            formData.append('slack', slack);
            formData.append('old', old);
            formData.append('enterprises', enterprise);
            formData.append('user', user);

            $.ajax({
                url: $form.data('store-url'),
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
