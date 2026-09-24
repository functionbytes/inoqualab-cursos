$(document).ready(function () {
    var page = $('#analyticsNotificationsPage');

    function toggleEmpty($container) {
        $container.siblings('.repeater-empty').toggleClass('d-none', $container.children().length > 0);
    }

    // Botón + de cada sección: agrega una fila vacía a su lista
    $(document).on('click', '.add-email', function () {
        var $container = $($(this).attr('data-target'));
        var $row = $($('#emailRowTemplate').html().trim());

        $row.find('input').attr('name', $container.attr('data-name'));
        $container.append($row);
        toggleEmpty($container);
        $row.find('input').trigger('focus');
    });

    // Papelera: quita la fila (una lista vacía se guarda como "sin destinatarios")
    $(document).on('click', '.remove-email', function () {
        var $container = $(this).closest('.emails-container');
        $(this).closest('.email-row').remove();
        toggleEmpty($container);
    });

    var flashSuccess = page.attr('data-flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }
});
