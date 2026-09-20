(function () {
    // El <ul> de navegación se clona una sola vez (con los cursos ya
    // renderizados por el servidor) dentro del offcanvas, para no duplicar
    // el @foreach de cursos en el Blade ni desincronizar ambos menús.
    var $source = $('#mainNavigation').clone().removeAttr('id');
    $('.vl-offcanvas-menu nav').append($source);

    // Submenú ("Cursos"): en vez del hover de escritorio, un botón que
    // expande/colapsa la lista de cursos in-place.
    $('.vl-offcanvas-menu nav > ul > li').each(function () {
        var $li = $(this);
        if ($li.find('> ul').length) {
            $li.append('<button type="button" class="vl-menu-close" aria-label="Mostrar submenú"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>');
        }
    });
    $('.vl-offcanvas-menu').on('click', '.vl-menu-close', function () {
        var $li = $(this).parent();
        $li.toggleClass('active');
        $li.children('ul').slideToggle(200);
    });

    function openOffcanvas() {
        $('.vl-offcanvas').addClass('vl-offcanvas-open');
        $('.vl-offcanvas-overlay').addClass('vl-offcanvas-overlay-open');
        $('.vl-offcanvas-toggle').attr('aria-expanded', 'true');
    }
    function closeOffcanvas() {
        $('.vl-offcanvas').removeClass('vl-offcanvas-open');
        $('.vl-offcanvas-overlay').removeClass('vl-offcanvas-overlay-open');
        $('.vl-offcanvas-toggle').attr('aria-expanded', 'false');
    }
    $('.vl-offcanvas-toggle').on('click', openOffcanvas);
    $('.vl-offcanvas-close-toggle, .vl-offcanvas-overlay').on('click', closeOffcanvas);
})();
