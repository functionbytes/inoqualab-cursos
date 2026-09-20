$(document).ready(function () {

    // Copy token to clipboard
    $(document).on('click', '.btn-copy-token', function () {
        const token = $(this).data('token');
        const $btn = $(this);

        navigator.clipboard.writeText(token).then(function () {
            const original = $btn.html();
            $btn.html('<i class="fas fa-check"></i>');
            setTimeout(function () { $btn.html(original); }, 2000);
            toastr.success('Token copiado al portapapeles', 'Copiado', { timeOut: 2000 });
        });
    });

    // Copy code blocks to clipboard
    $(document).on('click', '.btn-copy-code', function () {
        const target = $(this).data('target');
        const code = $('#' + target).find('code').text();
        const $btn = $(this);

        navigator.clipboard.writeText(code).then(function () {
            const original = $btn.html();
            $btn.html('Copiado!');
            setTimeout(function () { $btn.html(original); }, 2000);
        });
    });
});
