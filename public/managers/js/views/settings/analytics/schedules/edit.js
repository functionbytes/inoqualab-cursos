$(function () {
    $('.select2').select2({ width: '100%' });

    var flashSuccess = $('#scheduleForm').data('flash-success');
    if (flashSuccess) {
        toastr.success(flashSuccess);
    }
});
