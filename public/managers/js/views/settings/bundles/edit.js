Dropzone.autoDiscover = false;

$(document).on('submit', '#formBundles', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formBundles').data('urls');
    var selectedCourses = $('#formBundles').data('selected-courses') || [];

    $('#meta_keywords').tagsinput({
        maxTags: 15
    });

    $('#courses').select2({ placeholder: 'Seleccionar cursos...', allowClear: true });
    if (selectedCourses.length > 0) {
        $('#courses').val(selectedCourses).trigger('change');
    }

    $('#formBundles').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            price: {
                required: true,
                number: true,
                minlength: 3,
                maxlength: 100,
            },
            description: {
                required: false,
                minlength: 0,
                maxlength: 2000,
            },
            available: {
                required: true,
            },
            'courses[]': {
                required: true,
            },
            meta_title: {
                required: false,
                minlength: 3,
                maxlength: 100,
            },
            meta_description: {
                required: false,
                minlength: 0,
                maxlength: 2000,
            },
            'meta_keywords[]': {
                required: false,
            },
            start_date: {
                required: false,
            },
            expire_at: {
                required: false,
            },
            thumbnail: {
                required: function () {
                    return $('#status').val() == 'false' ? true : false;
                }
            }
        },
        messages: {
            meta_title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            price: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            meta_keywords: {
                required: 'Es necesario un opción.',
            },
            courses: {
                required: 'Es necesario un opción.',
            },
            available: {
                required: 'Es necesario un opción.',
            },
            start_date: {
                required: 'Es necesario un opción.',
            },
            expire_at: {
                required: 'Es necesario un opción.',
            },
            description: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 2000 caracter',
            },
            meta_description: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 1 caracter',
                maxlength: 'Debe contener al menos 2000 caracter',
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
            var $form = $('#formBundles');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var title = $('#title').val();
            var price = $('#price').val();
            var metaTitle = $('#meta_title').val();
            var metaKeywords = $('#meta_keywords').val();
            var metaDescription = $('#meta_description').val();
            var description = $('#description').val();
            var expireAt = $('#expire_at').val();
            var startDate = $('#start_date').val();
            var available = $('#available').val();
            var courses = $('#courses').val();

            formData.append('slack', slack);
            formData.append('title', title);
            formData.append('price', price);
            formData.append('meta_title', metaTitle);
            formData.append('meta_keywords', metaKeywords);
            formData.append('meta_description', metaDescription);
            formData.append('description', description);
            formData.append('expire_at', expireAt);
            formData.append('start_date', startDate);
            formData.append('available', available);
            formData.append('courses', Array.isArray(courses) ? courses.join(',') : (courses || ''));

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
                var bundle = document.getElementById('slack').value;
                formData.append('bundle', bundle);
            });

            myThumbnail.on('addedfile', function (file) {
                $('#thumbnail').val(file.name);
                $('#formBundles').validate().element('#thumbnail');
            });

            myThumbnail.on('removedfile', function (file) {
                $('#thumbnail').val('');
                $('#formBundles').validate().element('#thumbnail');

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
                $('#status').val('true');
            });

            myThumbnail.on('complete', function () {
                $('#status').val('true');
            });
        }
    });
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

var description = new Quill('#descriptions', {
    modules: {
        toolbar: toolbarOptions
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
    $('#description').text(description.container.firstChild.innerHTML);
});
