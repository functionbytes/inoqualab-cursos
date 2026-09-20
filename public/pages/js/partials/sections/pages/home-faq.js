$(document).ready(function () {
    $('#homeFaqAccordion .iq-faq-item-header').on('click', function () {
        $('#homeFaqAccordion .iq-faq-item-header').addClass('collapsed');
        $('#homeFaqAccordion .collapse').removeClass('show');

        if ($(this).hasClass('collapsed')) {
            $(this).removeClass('collapsed');
            $($(this).attr('data-target')).addClass('show');
        }
    });
});
