$(document).on('submit', '#formInstructions', function (e) {
    e.preventDefault();
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

$('#tags').tagsinput({
    maxTags: 15
});

var description = new Quill('#descriptions', {
    modules: {
        toolbar: toolbarOptions,
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

var short = new Quill('#shorts', {
    modules: {
        toolbar: toolbarOptions,
        clipboard: {
            matchVisual: false
        }
    },
    placeholder: 'Escriba aquí...',
    theme: 'snow'
});

short.on('selection-change', function (range, oldRange, source) {
    if (range === null && oldRange !== null) {
        $('body').removeClass('overlay-disabled');
    } else if (range !== null && oldRange === null) {
        $('body').addClass('overlay-disabled');
    }
});

short.on('text-change', function (delta, oldDelta, source) {
    var text = short.container.firstChild.innerHTML.replaceAll('<p><br></p>', '');
    $('#short').val(text);
});

$(document).ready(function () {
    var urls = $('#formInstructions').data('urls');

    $('#formInstructions').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
            },
            short: {
                required: false,
                minlength: 3,
                maxlength: 500,
            },
            description: {
                required: false,
                minlength: 3,
            },
            available: {
                required: true,
            },
            categorie: {
                required: true,
            },
            'tags[]': {
                required: false,
            },
        },
        messages: {
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
            },
            short: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al maximo 500 caracter',
            },
            description: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
            },
            available: {
                required: 'Es necesario un estado.',
            },
            categorie: {
                required: 'Es necesario un estado.',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formInstructions');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var short = $('#short').val();
            var description = $('#description').val();
            var title = $('#title').val();
            var available = $('#available').val();
            var categorie = $('#categorie').val();
            var tags = $('#tags').val();

            formData.append('slack', slack);
            formData.append('title', title);
            formData.append('description', description);
            formData.append('short', short);
            formData.append('available', available);
            formData.append('categorie', categorie);
            formData.append('tags', tags);

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
