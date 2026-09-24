(function () {
    var grid = document.getElementById('catGrid');
    if (!grid) return;
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.crs-card'));
    var countEl = document.getElementById('catCount');
    var emptyEl = document.getElementById('catEmpty');
    var searchEl = document.getElementById('catSearch');
    var sortEl = document.getElementById('catSort');

    var state = { search: '', cats: [], price: 'all', level: 'all', rating: 0, sort: 'recent' };

    function norm(s) { return (s || '').toLowerCase(); }

    function setGroup(group, value) {
        var btns = document.querySelectorAll('[data-' + group + ']');
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.toggle('on', btns[i].getAttribute('data-' + group) === value);
        }
    }

    function apply() {
        var visible = 0;
        cards.forEach(function (c) {
            var ok = true;
            var title = c.getAttribute('data-title');
            var cat = c.getAttribute('data-cat');
            var price = Number(c.getAttribute('data-price'));
            var lvl = c.getAttribute('data-level');
            var rating = Number(c.getAttribute('data-rating'));
            if (state.search && title.indexOf(state.search) === -1) ok = false;
            if (state.cats.length && state.cats.indexOf(cat) === -1) ok = false;
            if (state.price === 'free' && price !== 0) ok = false;
            if (state.price === 'premium' && price === 0) ok = false;
            if (state.price === 'discount' && c.getAttribute('data-discount') !== '1') ok = false;
            if (state.level !== 'all' && lvl !== state.level) ok = false;
            if (state.rating && rating < state.rating) ok = false;
            c.hidden = !ok;
            if (ok) visible++;
        });

        var cmp = {
            'price-asc': function (a, b) { return a.getAttribute('data-price') - b.getAttribute('data-price'); },
            'price-desc': function (a, b) { return b.getAttribute('data-price') - a.getAttribute('data-price'); },
            'name': function (a, b) { return a.getAttribute('data-title').localeCompare(b.getAttribute('data-title')); }
        }[state.sort];
        var ordered = cmp ? cards.slice().sort(cmp) : cards.slice();
        ordered.forEach(function (c) { grid.appendChild(c); });

        if (countEl) countEl.textContent = visible;
        if (emptyEl) emptyEl.hidden = visible > 0;
        grid.hidden = visible === 0;
    }

    if (searchEl) {
        searchEl.addEventListener('input', function (e) { state.search = norm(e.target.value); apply(); });
        if (searchEl.value) { state.search = norm(searchEl.value); }
    }

    document.querySelectorAll('[data-fcat]').forEach(function (b) {
        b.addEventListener('click', function () {
            var v = b.getAttribute('data-fcat');
            var idx = state.cats.indexOf(v);
            if (idx === -1) { state.cats.push(v); b.classList.add('on'); }
            else { state.cats.splice(idx, 1); b.classList.remove('on'); }
            apply();
        });
    });

    function radio(group, key, parse) {
        document.querySelectorAll('[data-' + group + ']').forEach(function (b) {
            b.addEventListener('click', function () {
                var v = b.getAttribute('data-' + group);
                state[key] = parse ? parse(v) : v;
                setGroup(group, v);
                apply();
            });
        });
    }
    radio('fprice', 'price');
    radio('flevel', 'level');
    radio('frating', 'rating', Number);

    document.querySelectorAll('[data-view]').forEach(function (b) {
        b.addEventListener('click', function () {
            var v = b.getAttribute('data-view');
            grid.classList.toggle('list', v === 'list');
            document.querySelectorAll('[data-view]').forEach(function (x) { x.classList.toggle('on', x === b); });
        });
    });

    if (sortEl) sortEl.addEventListener('change', function (e) { state.sort = e.target.value; apply(); });

    document.querySelectorAll('[data-clear]').forEach(function (b) {
        b.addEventListener('click', function () {
            state = { search: '', cats: [], price: 'all', level: 'all', rating: 0, sort: state.sort };
            if (searchEl) searchEl.value = '';
            document.querySelectorAll('[data-fcat]').forEach(function (x) { x.classList.remove('on'); });
            setGroup('fprice', 'all');
            setGroup('flevel', 'all');
            setGroup('frating', '0');
            apply();
        });
    });

    apply();
})();
