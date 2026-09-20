Dropzone.autoDiscover = false;

$(document).on('submit', '#formCertification', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formCertification').data('urls');

    $('#formCertification').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            available: {
                required: true,
            },
            description: {
                required: true,
            },
            thumbnail: {
                required: function () {
                    return $('#status').val() == 'false' ? true : false;
                }
            }
        },
        messages: {
            title: {
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
            thumbnail: {
                required: 'Es necesario una documento.',
            }
        },
        submitHandler: function (form) {
            var $form = $('#formCertification');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var title = $('#title').val();
            var description = $('#description').val();
            var available = $('#available').val();

            formData.append('slack', slack);
            formData.append('title', title);
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
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.jpg,.png',
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
                var certification = document.getElementById('slack').value;
                formData.append('certification', certification.replace('"', ''));
            });

            myThumbnail.on('addedfile', function (file) {
                $('#thumbnail').val(file.name);
                $('#formCertification').validate().element('#thumbnail');
            });

            myThumbnail.on('removedfile', function (file) {
                $('#thumbnail').val('');
                $('#formCertification').validate().element('#thumbnail');

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
