Dropzone.autoDiscover = false;

$(document).on('submit', '#formSlider', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formSlider').data('urls');

    $('#formSlider').validate({
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
                minlength: 0,
                maxlength: 1000,
            },
            subtitle: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },
            available: {
                required: false,
            },
            ubication: {
                required: false,
            },
            description: {
                required: false,
            },
            thumbnail: {
                required: function () {
                    return $('#status').val() == 'false' ? true : false;
                }
            }
        },
        messages: {
            title: {
                required: 'El parámetro es necesario.',
                minlength: 'Debe contener al menos 3 caracteres.',
                maxlength: 'Debe contener menos de 100 caracteres.',
            },
            subtitle: {
                required: 'El parámetro es necesario.',
                minlength: 'Debe contener al menos 3 caracteres.',
                maxlength: 'Debe contener menos de 100 caracteres.',
            },
            url: {
                required: 'El parámetro es necesario.',
                url: 'Debe ingresar una URL válida.',
                minlength: 'Debe contener al menos 3 caracteres.',
                maxlength: 'Debe contener menos de 1000 caracteres.',
            },
            available: {
                required: 'Es necesario un estado.',
            },
            ubication: {
                required: 'Es necesaria una ubicación.',
            },
            description: {
                required: 'La descripción es necesaria.',
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
            var $form = $('#formSlider');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var title = $('#title').val();
            var subtitle = $('#subtitle').val();
            var description = $('#description').val();
            var ubication = $('#ubication').val();
            var available = $('#available').val();

            formData.append('slack', slack);
            formData.append('title', title);
            formData.append('subtitle', subtitle);
            formData.append('subtitle', subtitle);
            formData.append('ubication', ubication);
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
                        $('#slack').val(response.slack);
                        myThumbnail.processQueue();

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
        acceptedFiles: 'image/*',
        parallelUploads: 1,
        maxFiles: 1,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        init: function () {
            var myThumbnail = this;
            var item = $('#slack').val();

            $.getJSON(urls.thumbnailsGet.replace(':item', item), function (data) {
                $.each(data, function (key, value) {
                    var mockFile = {
                        id: value.id,
                        uuid: value.uuid,
                        name: value.file,
                        size: value.size,
                        path: value.path,
                        file: value.file
                    };

                    myThumbnail.options.addedfile.call(myThumbnail, mockFile);
                    myThumbnail.options.thumbnail.call(myThumbnail, mockFile, value.path);
                    myThumbnail.options.complete.call(myThumbnail, mockFile);
                    myThumbnail.options.success.call(myThumbnail, mockFile);
                });
            });

            myThumbnail.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            myThumbnail.on('sending', function (file, xhr, formData) {
                var slider = document.getElementById('slack').value;
                formData.append('slider', slider);
            });

            myThumbnail.on('addedfile', function (file) {
                $('#thumbnail').val(file.name);
                $('#formSlider').validate().element('#thumbnail');
            });

            myThumbnail.on('removedfile', function (file) {
                $('#thumbnail').val('');
                $('#formSlider').validate().element('#thumbnail');

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

            myThumbnail.on('resetFiles', function () {
                $('#status').val('false');
                myThumbnail.removeAllFiles();
            });

            myThumbnail.on('success', function (file, response) {
                $('#status').val('true');
            });

            myThumbnail.on('queuecomplete', function () {
            });

            myThumbnail.on('complete', function () {
            });
        }
    });
});
