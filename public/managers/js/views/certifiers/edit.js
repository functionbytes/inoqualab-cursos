Dropzone.autoDiscover = false;

$(document).ready(function () {
    var $form = $('#formCertifier');
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    $form.validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            firstname: { required: true, minlength: 3, maxlength: 100 },
            lastname: { required: true, minlength: 3, maxlength: 100 },
            identification: { required: true, minlength: 3, maxlength: 100 },
            profession: { required: true, minlength: 3, maxlength: 100 },
            available: { required: true },
            description: { required: true },
            thumbnail: {
                required: function () {
                    return $('#statuThumbnails').val() == 'false';
                }
            },
            signature: {
                required: function () {
                    return $('#statuSignatures').val() == 'false';
                }
            }
        },
        messages: {
            firstname: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            lastname: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            identification: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            profession: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            available: { required: 'Es necesario un estado.' },
            description: { required: 'La descripción es necesario.' },
            thumbnail: { required: 'Es necesario una imagen.' },
            signature: { required: 'Es necesario una firma.' },
        },
        errorPlacement: function (error, element) {
            if (element.attr('id') == 'thumbnail') {
                error.insertAfter('#thumbnail');
            } else if (element.attr('id') == 'signature') {
                error.insertAfter('#signature');
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function () {
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var firstname = $('#firstname').val();
            var lastname = $('#lastname').val();
            var identification = $('#identification').val();
            var profession = $('#profession').val();
            var description = $('#description').val();
            var available = $('#available').val();

            formData.append('slack', slack);
            formData.append('firstname', firstname);
            formData.append('lastname', lastname);
            formData.append('identification', identification);
            formData.append('profession', profession);
            formData.append('description', description);
            formData.append('available', available);

            var $submitButton = $('button[type="submit"]').prop('disabled', true);

            $.ajax({
                url: $form.data('update-url'),
                headers: { 'X-CSRF-TOKEN': csrfToken },
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function (response) {
                    if (response.success == true) {
                        $('#slack').val(response.slack);
                        myThumbnail.processQueue();
                        mySignature.processQueue();

                        toastr.success(response.message, 'Operación exitosa', {
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-bottom-right'
                        });

                        setTimeout(function () {
                            window.location = $form.data('redirect-url');
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
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });

    var myThumbnail = new Dropzone('div#thumbnail', {
        paramName: 'file',
        url: $form.data('thumbnail-url'),
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.jpg, .jpeg',
        parallelUploads: 1,
        maxFiles: 1,
        headers: { 'X-CSRF-TOKEN': csrfToken },
        init: function () {
            var myThumbnail = this;
            var item = $('#slack').val();

            $.getJSON($form.data('thumbnail-get-url').replace(':item', item), function (data) {
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
                formData.append('certifier', $('#slack').val());
            });

            myThumbnail.on('addedfile', function (file) {
                $('#thumbnail').val(file.name);
                $form.validate().element('#thumbnail');
            });

            myThumbnail.on('removedfile', function (file) {
                $('#thumbnail').val('');
                $form.validate().element('#thumbnail');

                if (file.id) {
                    $.ajax({
                        type: 'GET',
                        url: $form.data('thumbnail-delete-url').replace(':id', file.id),
                        success: function () {
                            $('#statuThumbnails').val('false');
                        }
                    });
                }
            });

            myThumbnail.on('resetFiles', function () {
                $('#statuThumbnails').val('false');
                myThumbnail.removeAllFiles();
            });

            myThumbnail.on('success', function () {
                $('#statuThumbnails').val('true');
            });
        }
    });

    var mySignature = new Dropzone('div#signature', {
        paramName: 'file',
        url: $form.data('signature-url'),
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.png',
        parallelUploads: 1,
        maxFiles: 1,
        headers: { 'X-CSRF-TOKEN': csrfToken },
        init: function () {
            var mySignature = this;
            var item = $('#slack').val();

            $.getJSON($form.data('signature-get-url').replace(':item', item), function (data) {
                $.each(data, function (key, value) {
                    var mockFile = {
                        id: value.id,
                        uuid: value.uuid,
                        name: value.file,
                        size: value.size,
                        path: value.path,
                        file: value.file
                    };

                    mySignature.options.addedfile.call(mySignature, mockFile);
                    mySignature.options.thumbnail.call(mySignature, mockFile, value.path);
                    mySignature.options.complete.call(mySignature, mockFile);
                    mySignature.options.success.call(mySignature, mockFile);
                });
            });

            mySignature.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            mySignature.on('sending', function (file, xhr, formData) {
                var certifier = document.getElementById('slack').value;
                formData.append('certifier', certifier.replace('"', ''));
            });

            mySignature.on('addedfile', function (file) {
                $('#signature').val(file.name);
                $form.validate().element('#thumbnail');
            });

            mySignature.on('removedfile', function (file) {
                $('#signature').val('');
                $form.validate().element('#signature');

                if (file.id) {
                    $.ajax({
                        type: 'GET',
                        url: $form.data('signature-delete-url').replace(':id', file.id),
                        success: function () {
                            $('#statuSignatures').val('false');
                        }
                    });
                }
            });

            mySignature.on('resetFiles', function () {
                $('#statuSignatures').val('false');
                mySignature.removeAllFiles();
            });

            mySignature.on('success', function () {
                $('#statuSignatures').val('true');
            });
        }
    });
});
