Dropzone.autoDiscover = false;

$(document).on('submit', '#formCoupons', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formCoupons').data('urls');

    $('#courses').select2({ placeholder: 'Seleccionar cursos (opcional)', allowClear: true });
    $('#bundles').select2({ placeholder: 'Seleccionar paquetes (opcional)', allowClear: true });

    function updateAmountHint() {
        var type = $('#type').val();
        if (type == '1') {
            $('#amount-hint').text('Porcentaje de descuento (1–100)');
            $('#amount').attr('placeholder', 'Ej: 20');
        } else {
            $('#amount-hint').text('Monto fijo en COP');
            $('#amount').attr('placeholder', 'Ej: 5000');
        }
    }
    $('#type').on('change', updateAmountHint);
    updateAmountHint();

    $('.date-picker').flatpickr({ enableTime: true });

    $('.date-range-picker').each(function () {
        $(this).flatpickr({
            mode: 'range',
            showMonths: 2,
            dateFormat: 'm/d/Y'
        });
    });

    $('#formCoupons').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            code: {
                required: true,
                maxlength: 50,
            },
            limit: {
                required: false,
                number: true,
                min: 0,
                max: 999999,
            },
            amount: {
                required: true,
                number: true,
                min: 1,
                max: function () { return $('#type').val() == '1' ? 100 : 9999999; },
            },
            min_price: {
                required: false,
                number: true,
                min: 0,
                max: 999999,
            },
            description: {
                required: false,
                minlength: 0,
                maxlength: 2000,
            },
            type: {
                required: true,
            },
            available: {
                required: true,
            },
            'bundles[]': {
                required: false,
            },
            'courses[]': {
                required: false,
            },
            date_var: {
                required: false,
            },
        },
        messages: {
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            code: {
                required: 'El parametro es necesario.',
                maxlength: 'Debe contener maximo 50 caracteres',
            },
            limit: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 999999 caracter',
            },
            amount: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 999999 caracter',
            },
            min_price: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 999999 caracter',
            },
            available: {
                required: 'Es necesario un opción.',
            },
            type: {
                required: 'Es necesario un opción.',
            },
            courses: {
                required: 'Es necesario un opción.',
            },
            bundles: {
                required: 'Es necesario un opción.',
            },
            date_var: {
                required: 'Es necesario un opción.',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formCoupons');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var title = $('#title').val();
            var description = $('#description').val();
            var code = $('#code').val();
            var type = $('#type').val();
            var amount = $('#amount').val();
            var bundles = $('#bundles').val();
            var courses = $('#courses').val();
            var minPrice = $('#min_price').val();
            var dateVar = $('#date_var').val();
            var available = $('#available').val();
            var limit = $('#limit').val();

            formData.append('slack', slack);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('code', code);
            formData.append('type', type);
            formData.append('amount', amount);
            formData.append('date_var', dateVar);
            formData.append('min_price', minPrice);
            formData.append('available', available);
            formData.append('limit', limit);
            formData.append('courses', Array.isArray(courses) ? courses.join(',') : '');
            formData.append('bundles', Array.isArray(bundles) ? bundles.join(',') : '');

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: urls.store,
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
                },
                error: function (xhr) {
                    $submitButton.prop('disabled', false);

                    var error = 'Ocurrió un error al crear el cupón.';

                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        error = Object.values(xhr.responseJSON.errors).flat().join(' ');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        error = xhr.responseJSON.message;
                    }

                    toastr.error(error, 'Operación fallida', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right'
                    });

                    $('.errors').text(error);
                    $('.errors').removeClass('d-none');
                }
            });
        }
    });
});

var toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],
    ['blockquote', 'code-block'],
    [{ header: 1 }, { header: 2 }],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ script: 'sub' }, { script: 'super' }],
    [{ indent: '-1' }, { indent: '+1' }],
    [{ direction: 'rtl' }],
    [{ size: ['small', false, 'large', 'huge'] }],
    [{ header: [1, 2, 3, 4, 5, 6, false] }],
    ['link', 'image', 'video'],
    [{ color: [] }, { background: [] }],
    [{ font: [] }],
    [{ align: [] }],
    ['clean']
];

var description = new Quill('#descriptions', {
    modules: {
        toolbar: toolbarOptions
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
    $('#description').text(description.container.firstChild.innerHTML);
});
