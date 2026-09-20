Dropzone.autoDiscover = false;

$(document).on('submit', '#formTrusted', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formTrusted').data('urls');

    $('#formTrusted').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            url: {
                required: false,
                url: true,
                minlength: 3,
                maxlength: 1000,
            },
            available: {
                required: true,
            },
            description: {
                required: true,
            },
            thumbnail: {
                required: true
            }
        },
        messages: {
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            url: {
                required: 'El parametro es necesario.',
                url: 'Debe ingresar una url valida.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 1000 caracter',
            },
            available: {
                required: 'Es necesario un estado.',
            },
            description: {
                required: 'La descripción es necesario.',
            },
            thumbnail: {
                required: 'Es necesario una imagen.',
            }
        },
        errorPlacement: function (error, element) {
            if (element.attr('id') == 'thumbnail') {
                error.insertAfter('#thumbnail');
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            var $form = $('#formTrusted');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var title = $('#title').val();
            var url = $('#url').val();
            var available = $('#available').val();

            formData.append('slack', slack);
            formData.append('title', title);
            formData.append('url', url);
            formData.append('available', available);

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
                        $('#slack').val(response.slack);
                        myThumbnail.processQueue();

                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        myThumbnail.on('queuecomplete', function () {
                            setTimeout(function () {
                                window.location = urls.index;
                            }, 2000);
                        });
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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var myThumbnail = new Dropzone('div#thumbnail', {
        paramName: 'file',
        url: urls.thumbnails,
        method: 'POST',
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.png',
        parallelUploads: 1,
        maxFiles: 1,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        init: function () {
            var myDropzone = this;

            myDropzone.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            myDropzone.on('sending', function (file, xhr, formData) {
                var trusted = document.getElementById('slack').value;
                formData.append('trusted', trusted.replace('"', ''));
            });

            myDropzone.on('addedfile', function (file) {
                $('#thumbnail').val(file.name);
                $('#formTrusted').validate().element('#thumbnail');
            });

            myDropzone.on('removedfile', function (file) {
                $('#thumbnail').val('');
                $('#formTrusted').validate().element('#thumbnail');

                if (file.id) {
                    $.ajax({
                        type: 'DELETE',
                        url: urls.thumbnailDelete.replace(':id', file.id),
                        success: function (result) {
                            $('#status').val('false');
                        }
                    });
                }
            });

            myDropzone.on('resetFiles', function () {
                $('#status').val('false');
                myDropzone.removeAllFiles();
            });

            myDropzone.on('success', function (file, response) {
                $('#status').val('true');
            });

            myDropzone.on('queuecomplete', function () {
            });
        }
    });
});
