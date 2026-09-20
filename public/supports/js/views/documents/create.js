$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formDocuments');
    var storeUrl = $form.data('store-url');
    var indexUrl = $form.data('index-url');
    var uploadUrl = $form.data('upload-url');
    var deleteUrlTemplate = $form.data('delete-url');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            files: {
                required: true,
            },
        },
        messages: {
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            files: {
                required: 'Es necesario un documento.',
            },
        },
        errorPlacement: function (error, element) {
            if (element.attr('id') === 'files') {
                error.insertAfter('#files');
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            var formData = new FormData(form);
            var slack = $('#slack').val();
            var title = $('#title').val();

            formData.append('slack', slack);
            formData.append('title', title);

            var $submitButton = $('button[type="submit"]');
            $submitButton.prop('disabled', true);

            $.ajax({
                url: storeUrl,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        $('#slack').val(response.slack);
                        myFiles.processQueue();

                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        myFiles.on('queuecomplete', function () {
                            setTimeout(function () {
                                window.location = indexUrl;
                            }, 2000);
                        });
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

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    });

    var myFiles = new Dropzone('div#files', {
        paramName: 'file',
        url: uploadUrl,
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.pdf',
        parallelUploads: 1,
        maxFiles: 1,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        init: function () {
            var myFiles = this;

            myFiles.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            myFiles.on('sending', function (file, xhr, formData) {
                var documents = document.getElementById('slack').value;
                formData.append('documents', documents);
            });

            myFiles.on('addedfile', function (file) {
                $('#files').val(file.name);
                $('#formDocuments').validate().element('#files');
            });

            myFiles.on('removedfile', function (file) {
                $('#files').val('');
                $('#formDocuments').validate().element('#files');

                if (file.id) {
                    $.ajax({
                        type: 'DELETE',
                        url: deleteUrlTemplate.replace(':id', file.id),
                        success: function (result) {
                            $('#status').val('false');
                        },
                    });
                }
            });

            myFiles.on('resetFiles', function () {
                $('#status').val('false');
                myFiles.removeAllFiles();
            });

            myFiles.on('success', function (file, response) {
                $('#status').val('true');
            });
        },
    });
});

$(function () {
    var description = new Quill('#descriptions', {
        modules: {
            toolbar: [['clean']],
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
