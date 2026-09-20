$(document).ready(function () {
    Dropzone.autoDiscover = false;

    var $form = $('#formDocuments');
    var updateUrl = $form.data('update-url');
    var indexUrl = $form.data('index-url');
    var uploadUrl = $form.data('upload-url');
    var getUrlTemplate = $form.data('get-url');
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
            available: {
                required: true,
            },
            files: {
                required: function () {
                    return $('#status').val() === 'false';
                },
            },
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
            var available = $('#available').val();

            formData.append('slack', slack);
            formData.append('title', title);
            formData.append('available', available);

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
                        $('#slack').val(response.slack);
                        myFiles.processQueue();

                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right',
                        });

                        setTimeout(function () {
                            window.location = indexUrl;
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

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    });

    var myFiles = new Dropzone('div#files', {
        paramName: 'file',
        url: uploadUrl,
        method: 'POST',
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.pdf',
        parallelUploads: 1,
        maxFiles: 1,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        init: function () {
            var myFiles = this;
            var item = $('#slack').val();

            $.getJSON(getUrlTemplate.replace(':item', item), function (data) {
                if (data.length === 0) {
                    return;
                }

                $('#status').val('true');
                $('#files').val(data[0].file);

                $.each(data, function (key, value) {
                    var mockFile = {
                        id: value.id,
                        uuid: value.uuid,
                        name: value.file,
                        size: value.size,
                        path: value.path,
                        file: value.file,
                    };

                    myFiles.options.addedfile.call(myFiles, mockFile);
                    myFiles.options.files.call(myFiles, mockFile, value.path);
                    myFiles.options.complete.call(myFiles, mockFile);
                    myFiles.options.success.call(myFiles, mockFile);
                });
            });

            myFiles.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            myFiles.on('sending', function (file, xhr, formData) {
                var documents = document.getElementById('slack').value;
                formData.append('documents', documents);
            });

            myFiles.on('addedfile', function (file) {
                $('#files').val(file.name);
                $('#status').val('true');
                $('#formDocuments').validate().element('#files');
            });

            myFiles.on('removedfile', function (file) {
                $('#files').val('');
                $('#status').val('false');
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
    // FIXME: la vista no tiene un contenedor #descriptions (falta la sección
    // de "Descripción" en el formulario). Bug preexistente: sin este guard,
    // `new Quill('#descriptions', ...)` lanza una excepción porque el
    // selector no matchea ningún elemento. Se preserva el comportamiento
    // (el campo description nunca se completa) hasta que se agregue el
    // contenedor real al blade.
    if ($('#descriptions').length === 0) {
        return;
    }

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
