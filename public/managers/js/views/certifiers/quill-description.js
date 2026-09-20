var toolbarOption = ['clean'];

var description = new Quill('#descriptions', {
    modules: {
        toolbar: toolbarOption,
        clipboard: { matchVisual: false }
    },
    placeholder: 'Escriba aquí...',
    theme: 'snow'
});

description.on('selection-change', function (range, oldRange) {
    if (range === null && oldRange !== null) {
        $('body').removeClass('overlay-disabled');
    } else if (range !== null && oldRange === null) {
        $('body').addClass('overlay-disabled');
    }
});

description.on('text-change', function () {
    var text = description.container.firstChild.innerHTML.replaceAll('<p><br></p>', '');
    $('#description').val(text);
});
