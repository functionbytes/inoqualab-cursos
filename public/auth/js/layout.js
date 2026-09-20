$(function () {
    'use strict';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('click', '.confirm-delete', function (e) {
        e.preventDefault();
        var url = $(this).data('href');
        $('#delete-modal').modal('show');
        $('#delete-link').attr('href', url);
    });
});
