Dropzone.autoDiscover = false;

$(document).on('submit', '#formSetting', function (e) {
    e.preventDefault();
});

$(document).ready(function () {
    var urls = $('#formSetting').data('urls');

    jQuery.validator.addMethod(
        'emailExt',
        function (value, element, param) {
            return value.match(
                /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i,
            );
        },
        'Porfavor ingrese email valido',
    );

    $('#formSetting').validate({
        submit: false,
        ignore: '.ignore',
        rules: {
            page_title: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            page_copyright: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            page_address: {
                required: true,
                minlength: 3,
                maxlength: 100,
            },
            page_whatsapp: {
                required: false,
                number: true,
                minlength: 6,
                maxlength: 12,
            },
            page_cellphone: {
                required: false,
                number: true,
                minlength: 6,
                maxlength: 12,
            },
            page_phone: {
                required: false,
                number: true,
                minlength: 6,
                maxlength: 12,
            },
            page_email: {
                required: true,
                email: true,
                emailExt: true,
            },
            social_media_facebook: {
                required: false,
                url: true,
                minlength: 3,
                maxlength: 100,
            },
            social_media_instagram: {
                required: false,
                url: true,
                minlength: 3,
                maxlength: 100,
            },
            social_media_linkedin: {
                required: false,
                url: true,
                minlength: 3,
                maxlength: 100,
            },
            social_media_twitter: {
                required: false,
                url: true,
                minlength: 3,
                maxlength: 100,
            },
            social_media_youtube: {
                required: false,
                url: true,
                minlength: 3,
                maxlength: 100,
            },
            page_map: {
                required: false,
                minlength: 3,
                maxlength: 2000,
            },
            page_description: {
                required: false,
            },
            page_term: {
                required: false,
            },
            page_politic: {
                required: false,
            },
            logo: {
                required: function () {
                    return $('#statuLogo').val() == 'true' ? false : true;
                }
            },
            favicon: {
                required: function () {
                    return $('#statuFavicon').val() == 'true' ? false : true;
                }
            }
        },
        messages: {
            page_title: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            page_map: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 2000 caracter',
            },
            page_copyright: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            page_address: {
                required: 'El parametro es necesario.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            social_media_facebook: {
                required: 'El parametro es necesario.',
                url: 'Debe ingresar una url valida.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            social_media_linkedin: {
                required: 'El parametro es necesario.',
                url: 'Debe ingresar una url valida.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            social_media_instagram: {
                required: 'El parametro es necesario.',
                url: 'Debe ingresar una url valida.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            social_media_twitter: {
                required: 'El parametro es necesario.',
                url: 'Debe ingresar una url valida.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            social_media_youtube: {
                required: 'El parametro es necesario.',
                url: 'Debe ingresar una url valida.',
                minlength: 'Debe contener al menos 3 caracter',
                maxlength: 'Debe contener al menos 100 caracter',
            },
            page_whatsapp: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 6 caracter',
                maxlength: 'Debe contener al menos 12 caracter',
            },
            page_cellphone: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 6 caracter',
                maxlength: 'Debe contener al menos 12 caracter',
            },
            page_phone: {
                required: 'El parametro es necesario.',
                number: 'Solo se puede ingresar números.',
                minlength: 'Debe contener al menos 6 caracter',
                maxlength: 'Debe contener al menos 12 caracter',
            },
            page_email: {
                required: 'Tu email ingresar correo electrónico es necesario.',
                email: 'Por favor, introduce una dirección de correo electrónico válida.',
            },
            page_description: {
                required: 'La descripción es necesario.',
            },
            page_term: {
                required: 'La descripción es necesario.',
            },
            page_politic: {
                required: 'La descripción es necesario.',
            },
            logo: {
                required: 'Es necesario una imagen.',
            },
            favicon: {
                required: 'Es necesario una imagen.',
            }
        },
        errorPlacement: function (error, element) {
            if (element.attr('id') == 'favicon') {
                error.insertAfter('#favicon');
            } else if (element.attr('id') == 'logo') {
                error.insertAfter('#logo');
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler: function (form) {
            var $form = $('#formSetting');
            var formData = new FormData($form[0]);
            var slack = $('#slack').val();
            var pageTitle = $('#page_title').val();
            var pageCopyright = $('#page_copyright').val();
            var pageEmail = $('#page_email').val();
            var pagePhone = $('#page_phone').val();
            var pageCellphone = $('#page_cellphone').val();
            var pageWhatsapp = $('#page_whatsapp').val();
            var pageAddress = $('#page_address').val();
            var socialMediaFacebook = $('#social_media_facebook').val();
            var socialMediaInstagram = $('#social_media_instagram').val();
            var socialMediaTwitter = $('#social_media_twitter').val();
            var socialMediaYoutube = $('#social_media_youtube').val();
            var socialMediaLinkedin = $('#social_media_linkedin').val();
            var pageDescription = $('#page_description').val();
            var pageTerm = $('#page_term').val();
            var pagePolitic = $('#page_politic').val();
            var pageMap = $('#page_map').val();
            var pageHourWeekend = $('#page_hour_weekend').val();
            var pageHourWeekends = $('#page_hour_weekends').val();
            var reviewsEnabled = $('#reviews_enabled').val();
            var contactNotifications = $('#contact_notifications').val();
            var registrationEnabled = $('#registration_enabled').val();
            var aulaVersion = $('#aula_version').val();

            formData.append('reviews_enabled', reviewsEnabled);
            formData.append('contact_notifications', contactNotifications);
            formData.append('registration_enabled', registrationEnabled);
            formData.append('aula_version', aulaVersion);
            formData.append('slack', slack);
            formData.append('page_title', pageTitle);
            formData.append('page_copyright', pageCopyright);
            formData.append('page_email', pageEmail);
            formData.append('page_phone', pagePhone);
            formData.append('page_cellphone', pageCellphone);
            formData.append('page_whatsapp', pageWhatsapp);
            formData.append('page_address', pageAddress);
            formData.append('page_description', pageDescription);
            formData.append('page_term', pageTerm);
            formData.append('page_politic', pagePolitic);
            formData.append('page_map', pageMap);
            formData.append('page_hour_weekend', pageHourWeekend);
            formData.append('page_hour_weekends', pageHourWeekends);
            formData.append('social_media_facebook', socialMediaFacebook);
            formData.append('social_media_instagram', socialMediaInstagram);
            formData.append('social_media_twitter', socialMediaTwitter);
            formData.append('social_media_youtube', socialMediaYoutube);
            formData.append('social_media_linkedin', socialMediaLinkedin);

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
                        myLogo.processQueue();
                        myFavicon.processQueue();

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

    var myLogo = new Dropzone('div#logo', {
        paramName: 'file',
        url: urls.logo,
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.png, .svg',
        parallelUploads: 1,
        maxFiles: 1,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        init: function () {
            var myLogo = this;
            var item = $('#page_logo').val();

            $.getJSON(urls.logoGet.replace(':item', item), function (data) {
                $.each(data, function (key, value) {
                    $('#statuLogo').val('true');

                    var mockFile = {
                        id: value.id,
                        uuid: value.uuid,
                        name: value.file,
                        size: value.size,
                        path: value.path,
                        file: value.file
                    };

                    myLogo.options.addedfile.call(myLogo, mockFile);
                    myLogo.options.thumbnail.call(myLogo, mockFile, value.path);
                    myLogo.options.complete.call(myLogo, mockFile);
                    myLogo.options.success.call(myLogo, mockFile);
                });
            });

            myLogo.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            myLogo.on('sending', function (file, xhr, formData) {
                var setting = document.getElementById('page_logo').value;
                formData.append('setting', setting);
            });

            myLogo.on('addedfile', function (file) {
                $('#logo').val(file.name);
                $('#formSetting').validate().element('#logo');
            });

            myLogo.on('removedfile', function (file) {
                $('#logo').val('');
                $('#formSetting').validate().element('#logo');

                if (file.id) {
                    $.ajax({
                        type: 'DELETE',
                        url: urls.logoDelete.replace(':id', file.id),
                        success: function (result) {
                            $('#status').val('false');
                        }
                    });
                }
            });

            myLogo.on('resetFiles', function () {
                $('#statuLogo').val('false');
                myLogo.removeAllFiles();
            });

            myLogo.on('success', function (file, response) {
                $('#statuLogo').val('true');
            });

            myLogo.on('queuecomplete', function () {
            });

            myLogo.on('complete', function () {
            });
        }
    });

    var myFavicon = new Dropzone('div#favicon', {
        paramName: 'file',
        url: urls.favicon,
        addRemoveLinks: true,
        autoProcessQueue: false,
        uploadMultiple: false,
        acceptedFiles: '.png, .svg, .favicon',
        parallelUploads: 1,
        maxFiles: 1,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        init: function () {
            var myFavicon = this;
            var item = $('#page_favicon').val();

            $.getJSON(urls.faviconGet.replace(':item', item), function (data) {
                $.each(data, function (key, value) {
                    $('#statuFavicon').val('true');

                    var mockFile = {
                        id: value.id,
                        uuid: value.uuid,
                        name: value.file,
                        size: value.size,
                        path: value.path,
                        file: value.file
                    };

                    myFavicon.options.addedfile.call(myFavicon, mockFile);
                    myFavicon.options.thumbnail.call(myFavicon, mockFile, value.path);
                    myFavicon.options.complete.call(myFavicon, mockFile);
                    myFavicon.options.success.call(myFavicon, mockFile);
                });
            });

            myFavicon.on('maxfilesexceeded', function (file) {
                this.removeFile(file);
            });

            myFavicon.on('sending', function (file, xhr, formData) {
                var setting = document.getElementById('page_favicon').value;
                formData.append('setting', setting);
            });

            myFavicon.on('addedfile', function (file) {
                $('#favicon').val(file.name);
                $('#formSetting').validate().element('#favicon');
            });

            myFavicon.on('removedfile', function (file) {
                $('#favicon').val('');
                $('#formSetting').validate().element('#favicon');

                if (file.id) {
                    $.ajax({
                        type: 'DELETE',
                        url: urls.faviconDelete.replace(':id', file.id),
                        success: function (result) {
                            $('#status').val('false');
                        }
                    });
                }
            });

            myFavicon.on('resetFiles', function () {
                $('#statuFavicon').val('false');
                myFavicon.removeAllFiles();
            });

            myFavicon.on('success', function (file, response) {
                $('#statuFavicon').val('true');
            });

            myFavicon.on('queuecomplete', function () {
            });

            myFavicon.on('complete', function () {
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
        toolbar: toolbarOptions,
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
    $('#page_description').val(text);
});

var term = new Quill('#terms', {
    modules: {
        toolbar: toolbarOptions,
        clipboard: {
            matchVisual: false
        }
    },
    placeholder: 'Escriba aquí...',
    theme: 'snow'
});

term.on('selection-change', function (range, oldRange, source) {
    if (range === null && oldRange !== null) {
        $('body').removeClass('overlay-disabled');
    } else if (range !== null && oldRange === null) {
        $('body').addClass('overlay-disabled');
    }
});

term.on('text-change', function (delta, oldDelta, source) {
    var text = term.container.firstChild.innerHTML.replaceAll('<p><br></p>', '');
    $('#page_term').val(text);
});

var politic = new Quill('#politics', {
    modules: {
        toolbar: toolbarOptions,
        clipboard: {
            matchVisual: false
        }
    },
    placeholder: 'Escriba aquí...',
    theme: 'snow'
});

politic.on('selection-change', function (range, oldRange, source) {
    if (range === null && oldRange !== null) {
        $('body').removeClass('overlay-disabled');
    } else if (range !== null && oldRange === null) {
        $('body').addClass('overlay-disabled');
    }
});

politic.on('text-change', function (delta, oldDelta, source) {
    var text = politic.container.firstChild.innerHTML.replaceAll('<p><br></p>', '');
    $('#page_politic').val(text);
});
