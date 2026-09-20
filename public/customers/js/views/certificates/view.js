document.addEventListener('DOMContentLoaded', function () {
    var printBtn = document.querySelector('.js-print');
    if (printBtn) {
        printBtn.addEventListener('click', function () { window.print(); });
    }
});
