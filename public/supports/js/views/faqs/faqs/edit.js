$(function () {
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'script': 'sub' }, { 'script': 'super' }],
        [{ 'indent': '-1' }, { 'indent': '+1' }],
        [{ 'direction': 'rtl' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        ['link', 'image', 'video'],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'font': [] }],
        [{ 'align': [] }],
        ['clean'],
    ];

    var description = new Quill('#descriptions', {
        modules: {
            toolbar: toolbarOptions,
            clipboard: { matchVisual: false },
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow',
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
});

$(document).ready(function () {

    var $form = $('#formFaqs');
    var updateUrl = $form.data('update-url');
    var redirectUrl = $form.data('redirect-url');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            description: {
                required: true,
                minlength: 3,
                maxlength: 1000,
            },
            available: {
                required: true,
            },
            categorie: {
                required: true,
            },
        },
        messages: {
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            description: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 1000 caracter',
            },
            available: {
                required: 'Es necesario un estado.',
            },
            categorie: {
                required: 'Es necesario un estado.',
            },
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            formData.append('id', $('#id').val());
            formData.append('title', $('#title').val());
            formData.append('description', $('#description').val());
            formData.append('available', $('#available').val());
            formData.append('categorie', $('#categorie').val());

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: updateUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location = redirectUrl;
                        }, 2000);
                    } else {
                        $submitButton.prop('disabled', false);

                        toastr.warning(response.message, 'Operación fallida', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        $('.errors').text(response.message).removeClass('d-none');
                    }
                },
            });
        },
    });
});
