$(document).ready(function () {
    $('.iq-faq-item-header').on('click', function () {
        $('.iq-faq-item-header').addClass('collapsed');
        $('.iq-faq-item .collapse').removeClass('show');

        if ($(this).hasClass('collapsed')) {
            $(this).removeClass('collapsed');
            $($(this).attr('data-target')).addClass('show');
        }
    });
});
