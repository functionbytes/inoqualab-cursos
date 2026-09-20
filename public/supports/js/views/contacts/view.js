$(function () {
    var description = new Quill('#messages', {
        modules: {
            toolbar: [['clean']],
            clipboard: { matchVisual: false },
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow',
    });

    $('.ql-editor').addClass('disabled').attr('contenteditable', false);
});
