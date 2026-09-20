Dropzone.autoDiscover = false;

$(document).on('submit', '#formMetadata', function (e) {
    e.preventDefault();
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
    $('#meta_description').val(text);
});

$(document).ready(function () {
    var urls = $('#formMetadata').data('urls');

    $('#meta_keywords').tagsinput({
        maxTags: 15
    });

    $('#formMetadata').validate({
        ignore: '.ignore',
        rules: {
            meta_title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            meta_description: {
                required: true,
                minlength: 3,
                maxlength: 500,
            },
            'meta_keywords[]': {
                required: true,
            },
            metadata: {
                required: function () {
                    return $('#statuMetas').val() === '';
                }
            }
        },
        messages: {
            meta_title: {
                required: 'El parámetro es necesario.',
                minlength: 'Debe contener al menos 3 caracteres',
                maxlength: 'Debe contener como máximo 100 caracteres',
            },
            'meta_keywords[]': {
                required: 'El parámetro es necesario.',
            },
            meta_description: {
                required: 'El parámetro es necesario.',
                minlength: 'Debe contener al menos 3 caracteres',
                maxlength: 'Debe contener como máximo 500 caracteres',
            },
            metadata: {
                required: 'Es necesario una imagen.',
            }
        },
        errorPlacement: function (error, element) {
            if (element.attr('id') == 'metadata') {
                error.insertAfter('#metadata');
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            var $form = $('#formMetadata');
            var formData = new FormData($form[0]);
            var metaTitle = $('#meta_title').val();
            var metaKeywords = $('#meta_keywords').val();
            var metaDescription = $('#meta_description').val();

            formData.append('meta_title', metaTitle);
            formData.append('meta_keywords', metaKeywords);
            formData.append('meta_description', metaDescription);

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
                        myMetadata.processQueue();

                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location = urls.dashboard;
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
                error: function (xhr, status, error) {
                    toastr.error('Ha ocurrido un error. Por favor, inténtelo de nuevo.', 'Error', {
                        closeButton: true,
                        progressBar: true,
                        positionClass: 'toast-bottom-right'
                    });
                }
            });
        }
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var myMetadata = new Dropzone('div#metadata', {
        paramName: 'file',
        url: urls.store,
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
            var myMetadata = this;
            var item = $('#slack').val();

            $.getJSON(urls.get.replace(':item', item), function (data) {
                $.each(data, function (key, value) {
                    var mockFile = {
                        id: value.id,
                        uuid: value.uuid,
                        name: value.file,
                        size: value.size,
                        path: value.path,
                        file: value.file
                    };

                    myMetadata.options.addedfile.call(myMetadata, mockFile);
                    myMetadata.options.thumbnail.call(myMetadata, mockFile, value.path);
                    myMetadata.options.complete.call(myMetadata, mockFile);
                    myMetadata.options.success.call(myMetadata, mockFile);
                });
            });

            myMetadata.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            myMetadata.on('sending', function (file, xhr, formData) {
                var setting = document.getElementById('slack').value;
                formData.append('setting', setting);
            });

            myMetadata.on('addedfile', function (file) {
                $('#metadata').val(file.name);
                $('#formMetadata').validate().element('#metadata');
            });

            myMetadata.on('removedfile', function (file) {
                $('#metadata').val('');
                $('#formMetadata').validate().element('#metadata');

                if (file.id) {
                    $.ajax({
                        type: 'DELETE',
                        url: urls.delete.replace(':id', file.id),
                        success: function (result) {
                            $('#status').val('false');
                        }
                    });
                }
            });

            myMetadata.on('resetFiles', function () {
                $('#status').val('false');
                myMetadata.removeAllFiles();
            });

            myMetadata.on('success', function (file, response) {
            });

            myMetadata.on('queuecomplete', function () {
            });

            myMetadata.on('complete', function () {
            });
        }
    });
});
