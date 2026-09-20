$(document).ready(function () {

    var config = $('#blogs-categories-create').data('config') || {};

    $("#formCategories").validate({
        submit: false,
        ignore: ".ignore",
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            available: {
                required: true,
            },

        },
        messages: {
            title: {
                required: "El parametro es necesario.",
                minlength: "Debe contener al menos 3 caracter",
                maxlength: "Debe contener al menos 100 caracter",
            },
            available: {
                required: "Es necesario un estado.",
            },
        },
        submitHandler: function (form) {

            var $form = $('#formCategories');
            var formData = new FormData($form[0]);
            var title = $("#title").val();
            var available = $("#available").val();

            formData.append('title', title);
            formData.append('available', available);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: config.routes.store,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {

                    if (response.success == true) {

                        message = response.message;

                        toastr.success(message, "Operación exitosa", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        setTimeout(function () {
                            window.location = config.routes.index;
                        }, 2000);

                    } else {

                        $submitButton.prop('disabled', false);
                        error = response.message;

                        toastr.warning(error, "Operación fallida", {
                            closeButton: true,
                            progressBar: true,
                            positionClass: "toast-bottom-right"
                        });

                        $('.errors').text(error);
                        $('.errors').removeClass('d-none');

                    }

                }
            });

        }

    });

});
