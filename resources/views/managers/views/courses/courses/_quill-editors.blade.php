{{-- Inicialización de los 5 editores Quill del form de curso (whos/learns/shorts/requirements/details).
     Compartido entre create.blade.php y edit.blade.php: era ~134 líneas idénticas en cada uno.
     Sin envoltura <script> propia: se incluye dentro del <script> ya abierto por la vista que
     lo usa, para preservar el orden de ejecución y el scope de variables exacto. --}}
    var toolbarOptions = [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'script': 'sub' }, { 'script': 'super' }],
        [{ 'indent': '-1' }, { 'indent': '+1' }],
        [{ 'direction': 'rtl' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [ 'link', 'image', 'video' ],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'font': [] }],
        [{ 'align': [] }],

        ['clean']
    ];


    var toolbarOption = [
        ['clean']
    ];


    var who = new Quill('#whos', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow'
    });


    who.on('selection-change', function (range, oldRange, source) {
        if (range === null && oldRange !== null) {
            $('body').removeClass('overlay-disabled');
        } else if (range !== null && oldRange === null) {
            $('body').addClass('overlay-disabled');
        }
    });

    who.on('text-change', function(delta, oldDelta, source) {
        $('#who').text(who.container.firstChild.innerHTML);
    });



    var learn = new Quill('#learns', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow'
    });


    learn.on('selection-change', function (range, oldRange, source) {
        if (range === null && oldRange !== null) {
            $('body').removeClass('overlay-disabled');
        } else if (range !== null && oldRange === null) {
            $('body').addClass('overlay-disabled');
        }
    });

    learn.on('text-change', function(delta, oldDelta, source) {
        $('#learn').text(learn.container.firstChild.innerHTML);
    });



    var short = new Quill('#shorts', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow'
    });


    short.on('selection-change', function (range, oldRange, source) {
        if (range === null && oldRange !== null) {
            $('body').removeClass('overlay-disabled');
        } else if (range !== null && oldRange === null) {
            $('body').addClass('overlay-disabled');
        }
    });

    short.on('text-change', function(delta, oldDelta, source) {
        $('#short').text(short.container.firstChild.innerHTML);
    });


    var requirement = new Quill('#requirements', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow'
    });


    requirement.on('selection-change', function (range, oldRange, source) {
        if (range === null && oldRange !== null) {
            $('body').removeClass('overlay-disabled');
        } else if (range !== null && oldRange === null) {
            $('body').addClass('overlay-disabled');
        }
    });

    requirement.on('text-change', function(delta, oldDelta, source) {
        $('#requirement').text(requirement.container.firstChild.innerHTML);
    });


    var detail = new Quill('#details', {
        modules: {
            toolbar: toolbarOptions
        },
        placeholder: 'Escriba aquí...',
        theme: 'snow'
    });


    detail.on('selection-change', function (range, oldRange, source) {
        if (range === null && oldRange !== null) {
            $('body').removeClass('overlay-disabled');
        } else if (range !== null && oldRange === null) {
            $('body').addClass('overlay-disabled');
        }
    });

    detail.on('text-change', function(delta, oldDelta, source) {
        $('#detail').text(detail.container.firstChild.innerHTML);
    });
