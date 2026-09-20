Dropzone.autoDiscover = false;

$(document).on('submit', '#formContacs', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formContacs').data('urls');

    $('#formContacs').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            available: {
                required: true,
            },
        },
        messages: {
            reviewed: {
                required: 'Es necesario un estado.',
            },
        },
        submitHandler: function (form) {
            var $form = $('#formContacs');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var reviewed = $('#reviewed').val();

            formData.append('slack', slack);
            formData.append('reviewed', reviewed);

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

var description = new Quill('#messages', {
    modules: {
        toolbar: toolbarOption,
        clipboard: {
            matchVisual: false
        }
    },
    placeholder: 'Escriba aquí...',
    theme: 'snow'
});

$('.ql-editor').addClass('disabled');
$('.ql-editor').attr('contenteditable', false);
