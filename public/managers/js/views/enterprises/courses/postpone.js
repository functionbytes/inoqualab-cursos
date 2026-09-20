Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formAction');

    $('.daterange').daterangepicker({
        startDate: $form.data('enroll-start'),
        endDate: $form.data('enroll-expire'),
        locale: { format: 'MM/DD/YYYY' }
    });

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            range: { required: true },
        },
        messages: {
            range: { required: 'Es necesario una opción.' },
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var order = $('#order').val();
            var range = $('#range').val();

            formData.append('order', order);
            formData.append('range', range);

            $.ajax({
                url: $form.data('update-url'),
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
            });
        }
    });
});
