Dropzone.autoDiscover = false;

$(document).on('submit', '#formTestimonies', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formTestimonies').data('urls');

    $('#formTestimonies').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            firstname: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            lastname: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            available: {
                required: true,
            },
            description: {
                required: false,
            },
        },
        messages: {
            firstname: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            lastname: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            available: {
                required: 'Es necesario un estado.',
            },
            description: {
                required: 'La descripción es necesario.',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formTestimonies');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var firstname = $('#firstname').val();
            var lastname = $('#lastname').val();
            var role = $('#role').val();
            var icon = $('#icon').val();
            var rating = $('#rating').val();
            var benefit = $('#benefit').val();
            var position = $('#position').val();
            var counterValue = $('#counter_value').val();
            var counterSuffix = $('#counter_suffix').val();
            var description = $('#description').val();
            var available = $('#available').val();

            formData.append('slack', slack);
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('role', role);
            formData.append('icon', icon);
            formData.append('rating', rating);
            formData.append('benefit', benefit);
            formData.append('counter_value', counterValue);
            formData.append('counter_suffix', counterSuffix);
            formData.append('position', position);
            formData.append('description', description);
            formData.append('available', available);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: urls.update,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location = urls.index;
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        $('.errors').text(response.message);
                        $('.errors').removeClass('d-none');
                    }
                }
            });
        }
    });
});

var toolbarOption = [
    ['clean']
];

var description = new Quill('#descriptions', {
    modules: {
        toolbar: toolbarOption,
        clipboard: {
            matchVisual: false
        }
    },
    placeholder: 'Escriba aquí...',
    theme: 'snow'
});

description.on('selection-change', function (range, oldRange, source) {
    if (range === null && oldRange !== null) {
        $('body').removeClass('overlay-disabled');
    } else if (range !== null && oldRange === null) {
        $('body').addClass('overlay-disabled');
    }
});

description.on('text-change', function (delta, oldDelta, source) {
    var text = description.container.firstChild.innerHTML.replaceAll('<p><br></p>', '');
    $('#description').val(text);
});
