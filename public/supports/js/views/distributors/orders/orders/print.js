document.addEventListener('DOMContentLoaded', function () {
    var printBtn = document.querySelector('.btn-print');
    var backBtn = document.querySelector('.btn-back');

    if (printBtn) {
        printBtn.addEventListener('click', function () {
            window.print();
        });
    }

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            window.history.back();
        });
    }
});
