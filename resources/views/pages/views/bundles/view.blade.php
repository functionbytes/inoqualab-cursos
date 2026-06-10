@extends('layouts.pages')

@section('title', "$bundle->title")

@section('head')
    @php $url = URL::current(); @endphp
    <meta name="title" content="{{ $bundle->title }}">
    <meta name="description" content="{{ $bundle->meta_description ?? $bundle->description }}">
    <meta property="og:title" content="{{ $bundle->title }}">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:description" content="{{ $bundle->meta_description ?? $bundle->description }}">
    <link rel="canonical" href="{{ url()->full() }}" />
@endsection

@push('css')
    <link rel="stylesheet" href="{{ url('/pages/css/bundle-detalle.css') }}?v={{ @filemtime(public_path('pages/css/bundle-detalle.css')) ?: '1' }}">
@endpush

@section('content')

<div class="bundle-page">

    {{-- ===== Band ===== --}}
    <div class="band">
        <div class="cx-container">
            <div class="crumb">
                <a href="{{ route('index') }}" style="color:inherit">INICIO</a> <span class="sep">/</span>
                <a href="{{ route('bundles') }}" style="color:inherit">PAQUETES</a> <span class="sep">/</span>
                <span class="cur">DETALLE</span>
            </div>
            <span class="b-tag">Paquete</span>
            <h1>{{ $bundle->title }}</h1>
            @if ($bundle->description)
                <p class="lede">{{ \Illuminate\Support\Str::limit(strip_tags($bundle->description), 180) }}</p>
            @endif
            <div class="b-stats">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z" fill="currentColor" stroke="none"/></svg> {{ $courses->count() }} {{ $courses->count() == 1 ? 'curso' : 'cursos' }}</span>
                @if ($bundle->duration)
                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg> {{ $bundle->duration }} horas</span>
                @endif
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Certificado al finalizar</span>
            </div>
        </div>
    </div>

    {{-- ===== Contenido ===== --}}
    <div class="bp-wrap">
        <div class="cx-container">
            <div class="bp-layout">

                {{-- Izquierda --}}
                <div class="bp-col">
                    @if ($bundle->description)
                        <div class="bp-card">
                            <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg> Sobre este paquete</h2>
                            <div class="bp-desc">{!! $bundle->description !!}</div>
                        </div>
                    @endif

                    <div class="bp-card">
                        <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg> Cursos incluidos ({{ $courses->count() }})</h2>

                        @if ($courses->isEmpty())
                            <p class="bp-desc">Este paquete aún no tiene cursos asignados.</p>
                        @else
                            <div class="bp-courses">
                                @foreach ($courses as $course)
                                    @php $thumb = method_exists($course, 'getFirstMedia') ? $course->getFirstMedia('thumbnail') : null; @endphp
                                    <a class="bp-course" href="{{ route('courses.view', [$course->slack]) }}">
                                        <span class="ic">
                                            @if ($thumb)
                                                <img src="{{ $thumb->getFullUrl() }}" alt="{{ $course->title }}"
                                                 onerror="this.style.display='none';this.nextElementSibling.style.display='';">
                                <svg style="display:none;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9 12 4 2 9l10 5 10-5z"/><path d="M6 11v5c0 1 2.7 3 6 3s6-2 6-3v-5"/></svg>
                                            @else
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9 12 4 2 9l10 5 10-5z"/><path d="M6 11v5c0 1 2.7 3 6 3s6-2 6-3v-5"/></svg>
                                            @endif
                                        </span>
                                        <span class="tx">
                                            <span class="tt">{{ $course->title }}</span>
                                            <span class="mm">{{ $course->categorie->title ?? 'Curso' }}</span>
                                        </span>
                                        <span class="go"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Derecha: compra --}}
                @php
                    $bpThumb = null;
                    foreach ($courses as $bpc) {
                        $bpThumb = $bpc->getFirstMedia('thumbnail');
                        if ($bpThumb) break;
                    }
                @endphp
                <aside class="bp-buy">
                    @if ($bpThumb)
                        <div class="bp-buy-media">
                            <img src="{{ $bpThumb->getFullUrl() }}" alt="{{ $bundle->title }}" loading="lazy"
                                 onerror="this.src='{{ asset('/pages/images/courses/default.jpg') }}'">
                        </div>
                    @endif
                    <div class="head">
                        <div class="lbl">Precio del paquete</div>
                        <div class="price">$ {{ number_format($bundle->price, 0, ',', '.') }} <small>COP</small></div>
                    </div>
                    <div class="body">
                        <ul class="inc">
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Acceso a {{ $courses->count() }} {{ $courses->count() == 1 ? 'curso completo' : 'cursos completos' }}</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Certificado por cada curso</li>
                            <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> 100% virtual, a tu ritmo</li>
                        </ul>

                        @if ($alreadyOwned)
                            <div class="buy-actions">
                                <a href="{{ route('customers.courses') }}" class="buy-primary">
                                    <i class="fa-solid fa-play"></i> Ir a mis cursos
                                </a>
                                <span class="buy-owned-note">
                                    <i class="fa-solid fa-circle-check"></i> Ya adquiriste este paquete
                                </span>
                            </div>
                        @else
                            {{-- Mismo flujo AJAX que los cursos: el paquete se agrega al mismo carrito (drawer + badge) --}}
                            <form class="form-add-to-cart" action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="type" value="bundle">
                                <input type="hidden" name="slack" value="{{ $bundle->slack }}">
                                <input type="hidden" name="buy_now" id="bundleBuyNow" value="">
                                <button type="submit" class="btn-primary" id="bundleAdd"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.4"/><circle cx="19" cy="21" r="1.4"/><path d="M2.5 3h2l2.4 12.4a2 2 0 0 0 2 1.6h8.7a2 2 0 0 0 2-1.6L22 7H6"/></svg> Agregar al carrito</button>
                                <button type="submit" class="btn-outline" id="bundleBuy"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9z" fill="currentColor" stroke="none"/></svg> Comprar ahora</button>
                            </form>
                            <p class="note">Pago seguro · Acceso inmediato</p>
                        @endif
                    </div>
                </aside>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '#bundleBuy', function () { $('#bundleBuyNow').val('1'); });
    $(document).on('click', '#bundleAdd', function () { $('#bundleBuyNow').val(''); });
</script>
@endpush
