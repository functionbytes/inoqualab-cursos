(function () {
    var wrap = document.getElementById('rateStars');
    if (!wrap) return;
    var stars  = wrap.querySelectorAll('.star');
    var input  = document.getElementById('rateValue');
    var submit = document.getElementById('rateSubmit');

    function paint(val) {
        stars.forEach(function (s) {
            var v = parseInt(s.getAttribute('data-val'), 10);
            s.querySelector('i').className = 'fa-solid fa-star' + (v <= val ? ' is-filled' : '');
        });
    }
    stars.forEach(function (s) {
        var v = parseInt(s.getAttribute('data-val'), 10);
        s.addEventListener('mouseenter', function () { paint(v); });
        s.addEventListener('click', function () {
            input.value = v;
            paint(v);
            if (submit) submit.disabled = false;
        });
    });
    wrap.addEventListener('mouseleave', function () { paint(parseInt(input.value, 10) || 0); });
})();
