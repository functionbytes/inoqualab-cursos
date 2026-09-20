$('#matrixFilter').on('input', function () {
    var term = $(this).val().toLowerCase();

    $('.perm-row').each(function () {
        var name = ($(this).data('name') + '').toLowerCase();
        $(this).toggle(term === '' || name.includes(term));
    });

    // Oculta el encabezado de módulo si no le quedan permisos visibles
    $('.module-row').each(function () {
        var next = $(this).next('.perm-row');
        var anyVisible = false;
        while (next.length) {
            if (next.is(':visible')) { anyVisible = true; break; }
            next = next.next('.perm-row');
        }
        $(this).toggle(anyVisible || term === '');
    });
});
