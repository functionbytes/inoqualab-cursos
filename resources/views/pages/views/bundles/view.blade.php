@extends('layouts.pages')

@section('title', $bundle->title)

@section('active', 'bundles')

@push('css')
<link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
@endpush

@section('content')

@php
    $coursesCount = $courses->count();

    // Imagen del paquete: primer thumbnail disponible entre los cursos incluidos.
    $bundleMedia = null;
    foreach ($courses as $bc) {
        $bundleMedia = $bc->getFirstMedia('thumbnail');
        if ($bundleMedia) {
            break;
        }
    }
@endphp

{{-- ===== Hero (navy) ===== --}}
<section class="course-hero">
    <div class="container">
        <div class="crumb">
            <a href="{{ route('index') }}">INICIO</a> <span class="sep">/</span>
            <a href="{{ route('bundles') }}">PAQUETES</a> <span class="sep">/</span>
            <span class="cur">DETALLE</span>
        </div>
        <h1>{{ $bundle->title }}</h1>
        @if ($bundle->description)
            <p class="lede">{{ \Illuminate\Support\Str::limit(strip_tags($bundle->description), 200) }}</p>
        @endif
        <div class="ch-meta">
            <span><i class="fa-solid fa-circle-play"></i> {{ $coursesCount }} {{ $coursesCount == 1 ? 'curso' : 'cursos' }}</span>
            <span><i class="fa-solid fa-circle-check"></i> Certificado al finalizar</span>
        </div>
    </div>
</section>

{{-- ===== Cuerpo ===== --}}
<section class="course-body">
    <div class="container">
        <div class="course-grid">

            {{-- Columna izquierda --}}
            <div class="course-content">

                {{-- Sobre este paquete --}}
                <div class="pkg-card">
                    <h2>Sobre este paquete</h2>
                    @if ($bundle->description)
                        <p>{{ strip_tags($bundle->description) }}</p>
                    @else
                        <p>Adquiere todos los cursos de este paquete en un solo pago, con acceso virtual a tu ritmo y certificado por cada curso completado.</p>
                    @endif
                    <div class="pkg-about-grid">
                        <div class="pkg-hl">
                            <span class="hi"><i class="fa-solid fa-circle-play"></i></span>
                            <span class="ht">
                                <b>{{ $coursesCount }} {{ $coursesCount == 1 ? 'curso' : 'cursos' }}</b>
                                <span>Ruta completa</span>
                            </span>
                        </div>
                        <div class="pkg-hl">
                            <span class="hi"><i class="fa-solid fa-award"></i></span>
                            <span class="ht">
                                <b>{{ $coursesCount }} {{ $coursesCount == 1 ? 'certificado' : 'certificados' }}</b>
                                <span>Uno por curso</span>
                            </span>
                        </div>
                        <div class="pkg-hl">
                            <span class="hi"><i class="fa-solid fa-shield-halved"></i></span>
                            <span class="ht">
                                <b>De por vida</b>
                                <span>Acceso virtual</span>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Cursos incluidos --}}
                <div class="pkg-card" style="margin-top:24px">
                    <div class="pkg-head">
                        <h2>Cursos incluidos ({{ $coursesCount }})</h2>
                    </div>

                    @if ($courses->isEmpty())
                        <p>Este paquete aún no tiene cursos asignados.</p>
                    @else
                        <div class="pkg-path">
                            @foreach ($courses as $course)
                                @php
                                    $classesCount = $course->lessons_count ?? $course->lessons()->count();
                                @endphp
                                <div class="pkg-step">
                                    <a class="pkg-steplink" href="{{ route('courses.view', [$course->slack]) }}">
                                        <span class="sl-info">
                                            <span class="sl-name">{{ $course->title }}</span>
                                            <span class="sl-meta">
                                                <span><i class="fa-solid fa-circle-play"></i> {{ $classesCount }} {{ $classesCount == 1 ? 'clase' : 'clases' }}</span>
                                                <span>{{ $course->categorie->title ?? 'Curso' }}</span>
                                            </span>
                                        </span>
                                        <span class="sl-go"><i class="fa-solid fa-chevron-right"></i></span>
                                    </a>
                                    <span class="node">{{ $loop->iteration }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Columna derecha: compra --}}
            <div class="buy-card">
                <div class="buy-media">
                    @if ($bundleMedia)
                        <img src="{{ $bundleMedia->getFullUrl() }}" alt="{{ $bundle->title }}" loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="ph" style="display:none;"><i class="fa-solid fa-box-open"></i><span>Imagen del paquete</span></div>
                    @else
                        <div class="ph"><i class="fa-solid fa-box-open"></i><span>Imagen del paquete</span></div>
                    @endif
                </div>

                <div class="buy-priceband">
                    <div class="lbl">Precio del paquete</div>
                    <div class="amt">$ {{ number_format($bundle->price, 0, ',', '.') }}<span class="cur">COP</span></div>
                </div>

                <div class="buy-pad">
                    <ul class="buy-incl">
                        <li><i class="fa-solid fa-check"></i> Acceso a {{ $coursesCount }} {{ $coursesCount == 1 ? 'curso completo' : 'cursos completos' }}</li>
                        <li><i class="fa-solid fa-check"></i> {{ $coursesCount }} {{ $coursesCount == 1 ? 'certificado' : 'certificados' }}</li>
                        <li><i class="fa-solid fa-check"></i> Acceso de por vida</li>
                    </ul>

                    @if ($alreadyOwned)
                        <div class="buy-actions">
                            <a class="buy-primary" href="{{ route('customers.courses') }}">
                                <i class="fa-solid fa-circle-check"></i> Ya tienes este paquete
                            </a>
                        </div>
                        <p style="text-align:center;font-size:12.5px;font-weight:600;margin:16px 0 0">Ve a tus cursos para continuar aprendiendo</p>
                    @else
                        <form class="form-add-to-cart" action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" value="bundle">
                            <input type="hidden" name="slack" value="{{ $bundle->slack }}">
                            <input type="hidden" name="qty" value="1">
                            <input type="hidden" name="buy_now" id="bundleBuyNow" value="">
                            <div class="buy-actions">
                                <button type="submit" class="buy-primary" id="btnBundleBuyNow">Compra ahora</button>
                                <button type="submit" class="buy-outline" id="btnBundleAddCart">Agregar al carrito</button>
                            </div>
                        </form>
                        <p style="text-align:center;font-size:12.5px;font-weight:600;margin:16px 0 0">Pago seguro · Acceso inmediato</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

@push('scripts')
<script>
    $(document).ready(function () {
        // Diferenciar "Compra ahora" vs "Agregar al carrito" (el handler del carrito vive en layouts.pages)
        $(document).on('click', '#btnBundleBuyNow', function () { $('#bundleBuyNow').val('1'); });
        $(document).on('click', '#btnBundleAddCart', function () { $('#bundleBuyNow').val(''); });
    });
</script>
@endpush

@endsection
