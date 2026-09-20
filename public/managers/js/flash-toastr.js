$(function () {
    var $flash = $('[data-flash-success], [data-flash-error]').first();
    if (!$flash.length) {
        return;
    }

    var success = $flash.data('flash-success');
    var error = $flash.data('flash-error');

    if (success) {
        toastr.success(success, $flash.data('flash-success-title') || '');
    }
    if (error) {
        toastr.error(error, $flash.data('flash-error-title') || '');
    }
});
