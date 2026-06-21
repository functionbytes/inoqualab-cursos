@extends('layouts.pages')

@section('title', 'Cursos')

@push('css')
    <link rel="stylesheet" href="{{ url('/pages/css/cursos.css') }}?v={{ @filemtime(public_path('pages/css/cursos.css')) ?: '1' }}">
@endpush

@section('content')

@php
    $totalCount   = $courses->count();
    $premiumCount = $courses->where('payment', 1)->count();
    $freeCount    = $totalCount - $premiumCount;

    // Niveles presentes en el catálogo (solo los que tienen cursos)
    $levels = ['Principiante', 'Intermedio', 'Avanzado'];
    $levelCounts = [];
    foreach ($levels as $lv) {
        $c = $courses->where('level', $lv)->count();
        if ($c > 0) { $levelCounts[$lv] = $c; }
    }
    $hasLevels  = count($levelCounts) > 0;
    $hasRatings = $courses->where('rating', '>', 0)->count() > 0;
    $count5 = $courses->where('rating', '>=', 5)->count();
    $count4 = $courses->where('rating', '>=', 4)->count();
@endphp

<div class="cursos-page">

    {{-- ===== Band ===== --}}
    <div class="band">
        <div class="cx-container">
            <div class="crumb"><a href="{{ route('index') }}" style="color:inherit">INICIO</a> <span class="sep">/</span> <span class="cur">CURSOS</span></div>
            <h1>Nuestros cursos</h1>
            <p class="lede">Capacitaciones en inocuidad y buenas prácticas, certificadas y 100% virtuales.</p>
        </div>
    </div>

    {{-- ===== Catalog ===== --}}
    <main class="catalog">
        <div class="cx-container">
            <div class="cat-layout">

                {{-- Filtros --}}
                <aside class="cat-side">
                    <div class="fblock">
                        <h4>Buscar</h4>
                        <div class="fsearch">
                            <span class="ic" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
                            <input id="catSearch" type="text" placeholder="Buscar un curso…" aria-label="Buscar un curso" value="{{ request('search') }}">
                        </div>
                    </div>

                    @if ($categories->isNotEmpty())
                        <div class="fblock">
                            <h4>Categorías</h4>
                            @foreach ($categories as $cat)
                                @php $catCount = $courses->where('categorie_id', $cat->id)->count(); @endphp
                                <button type="button" class="fopt" data-fcat="{{ $cat->title }}">
                                    <span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                                    <span class="lbl">{{ $cat->title }}</span>
                                    <span class="cnt">{{ $catCount }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="fblock">
                        <h4>Precio</h4>
                        <button type="button" class="fopt round on" data-fprice="all"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl">Todos</span><span class="cnt">{{ $totalCount }}</span></button>
                        <button type="button" class="fopt round" data-fprice="premium"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl">Premium</span><span class="cnt">{{ $premiumCount }}</span></button>
                        <button type="button" class="fopt round" data-fprice="free"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl">Gratis</span><span class="cnt">{{ $freeCount }}</span></button>
                        @php $discountCount = $courses->filter(fn ($c) => $c->payment == 1 && $c->promotion == 1 && $c->discount < $c->price)->count(); @endphp
                        <button type="button" class="fopt round" data-fprice="discount"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl">En oferta</span><span class="cnt">{{ $discountCount }}</span></button>
                    </div>

                    @if ($hasLevels)
                        <div class="fblock">
                            <h4>Nivel</h4>
                            <button type="button" class="fopt round on" data-flevel="all"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl">Todos</span><span class="cnt">{{ $totalCount }}</span></button>
                            @foreach ($levelCounts as $lv => $cnt)
                                <button type="button" class="fopt round" data-flevel="{{ $lv }}"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl">{{ $lv }}</span><span class="cnt">{{ $cnt }}</span></button>
                            @endforeach
                        </div>
                    @endif

                    @if ($hasRatings)
                        @php
                            $starFull = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.9 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>';
                        @endphp
                        <div class="fblock">
                            <h4>Calificación</h4>
                            <button type="button" class="fopt round on" data-frating="0"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl">Todas</span></button>
                            <button type="button" class="fopt round" data-frating="5"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl"><span class="stars">{!! str_repeat($starFull, 5) !!}</span></span><span class="cnt">{{ $count5 }}</span></button>
                            <button type="button" class="fopt round" data-frating="4"><span class="box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span><span class="lbl"><span class="stars">{!! str_repeat($starFull, 4) !!}<span class="off">{!! $starFull !!}</span></span></span><span class="cnt">{{ $count4 }}</span></button>
                        </div>
                    @endif

                    <div class="fblock" style="padding-top:14px;padding-bottom:14px;">
                        <button type="button" class="fclear" data-clear>Limpiar filtros</button>
                    </div>
                </aside>

                {{-- Resultados --}}
                <div class="cat-main">
                    <div class="cat-toolbar">
                        <div class="cat-count">Mostrando <b id="catCount">{{ $totalCount }}</b> de <b>{{ $totalCount }}</b> cursos</div>
                        <div class="cat-tools">
                            <div class="view-toggle">
                                <button type="button" class="on" data-view="grid" aria-label="Cuadrícula"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></button>
                                <button type="button" data-view="list" aria-label="Lista"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></button>
                            </div>
                            <select class="cat-sort" id="catSort">
                                <option value="recent">Más recientes</option>
                                <option value="price-asc">Precio: menor a mayor</option>
                                <option value="price-desc">Precio: mayor a menor</option>
                                <option value="name">Nombre (A–Z)</option>
                            </select>
                        </div>
                    </div>

                    <div class="cat-grid" id="catGrid">
                        @foreach ($courses as $course)
                            @php
                                $isFree   = $course->payment != 1;
                                $onSale   = ! $isFree && $course->promotion == 1 && $course->discount < $course->price;
                                $effPrice = $isFree ? 0 : ($course->promotion == 1 ? $course->discount : $course->price);
                                $offPct   = $onSale ? round(($course->price - $course->discount) / $course->price * 100) : 0;
                                $thumb    = $course->getFirstMedia('thumbnail');
                                $rating   = (float) ($course->rating ?? 0);
                                $starSvg  = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.9 6.1 20.5l1.2-6.5L2.5 9.4l6.6-.9z"/></svg>';
                            @endphp
                            <a class="ccard" href="{{ route('courses.view', [$course->slack]) }}"
                               data-cat="{{ $course->categorie->title ?? '' }}"
                               data-price="{{ $effPrice }}"
                               data-discount="{{ $onSale ? 1 : 0 }}"
                               data-level="{{ $course->level ?? '' }}"
                               data-rating="{{ $rating ? floor($rating) : 0 }}"
                               data-title="{{ \Illuminate\Support\Str::lower($course->title) }}">
                                <div class="ccard-media">
                                    @if ($thumb)
                                        <img src="{{ $thumb->getFullUrl() }}" alt="{{ $course->title }}" loading="lazy"
                                             onerror="this.style.display='none';this.nextElementSibling.style.display='';">
                                        <div class="ph" style="display:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9 12 4 2 9l10 5 10-5z"/><path d="M6 11v5c0 1 2.7 3 6 3s6-2 6-3v-5"/></svg></div>
                                    @else
                                        <div class="ph"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9 12 4 2 9l10 5 10-5z"/><path d="M6 11v5c0 1 2.7 3 6 3s6-2 6-3v-5"/></svg></div>
                                    @endif
                                    <span class="ccard-badge {{ $isFree ? 'free' : 'premium' }}">{{ $isFree ? 'Gratis' : 'Premium' }}</span>
                                    @if ($onSale)<span class="ccard-off">-{{ $offPct }}%</span>@endif
                                </div>
                                <div class="ccard-body">
                                    <div class="ccard-cathead">
                                        @if ($course->categorie)
                                            <div class="ccard-cat">{{ $course->categorie->title }}</div>
                                        @endif
                                    </div>
                                    <div class="ccard-title">{{ str($course->title)->lower()->ucfirst() }}</div>
                                    <div class="ccard-rating">
                                        <span class="stars">
                                            @for ($s = 1; $s <= 5; $s++)
                                                <span class="{{ $rating > 0 && $s <= round($rating) ? '' : 'off' }}">{!! $starSvg !!}</span>
                                            @endfor
                                        </span>
                                        @if ($rating > 0)
                                            <span class="num">{{ number_format($rating, 1) }}</span>
                                        @else
                                            <span class="num-empty">Sin calificaciones</span>
                                        @endif
                                    </div>
                                    <div class="ccard-meta">
                                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z" fill="currentColor" stroke="none"/></svg> {{ $course->lessons_count ?? 0 }} clases</span>
                                        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/></svg> {{ $course->chapters_count ?? 0 }} temas</span>
                                                    </div>
                                    <div class="ccard-foot">
                                        @if ($isFree)
                                            <span class="ccard-price free">Gratis</span>
                                        @elseif ($course->promotion == 1)
                                            <span class="ccard-price has-sale"><span class="price-new">$ {{ number_format($course->discount, 0, ',', '.') }} <small>COP</small></span><del class="price-old">$ {{ number_format($course->price, 0, ',', '.') }}</del></span>
                                        @else
                                            <span class="ccard-price">$ {{ number_format($course->price, 0, ',', '.') }} <small>COP</small></span>
                                        @endif
                                        <span class="ccard-cta">Ver curso <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="cat-empty" id="catEmpty" hidden>
                        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></div>
                        <p>No encontramos cursos con esos filtros.</p>
                        <button type="button" data-clear>Limpiar filtros</button>
                    </div>
                </div>

            </div>

            {{-- Franja de paquetes --}}
            @include('pages.partials.sections.bundles-strip')

        </div>
    </main>

</div>
@endsection

@push('scripts')
<script>
    (function () {
        var grid = document.getElementById('catGrid');
        if (!grid) return;
        var cards = Array.prototype.slice.call(grid.querySelectorAll('.ccard'));
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
</script>
@endpush
