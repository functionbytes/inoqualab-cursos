@extends('layouts.pages')

@section('title', 'Paquetes de cursos · INOQUALAB')
@section('active', 'bundles')

@push('css')
<link rel="stylesheet" href="{{ url('/pages/css/storefront.css') }}?v={{ @filemtime(public_path('pages/css/storefront.css')) ?: '1' }}">
@endpush

@section('content')

    {{-- ===== Band ===== --}}
    <div class="band">
        <div class="container">
            <div class="crumb">
                <a href="{{ route('index') }}" style="color:inherit">INICIO</a>
                <span class="sep">/</span>
                <span class="cur">PAQUETES</span>
            </div>
            <h1>Paquetes de cursos</h1>
            <p class="lede">Adquiere varios cursos agrupados a un mejor precio y certifícate en toda un área.</p>
        </div>
    </div>

    {{-- ===== Listado de paquetes ===== --}}
    <main class="pkglist">
        <div class="container">

            @if ($bundles->isEmpty())
                <p class="pkglist-count">Aún no hay paquetes disponibles.</p>
            @else
                <p class="pkglist-count">
                    Mostrando <b>{{ $bundles->count() }}</b> {{ $bundles->count() == 1 ? 'paquete' : 'paquetes' }}
                </p>

                <div class="pkglist-grid">
                    @foreach ($bundles as $bundle)
                        @php
                            // Thumbnail: primer curso del paquete con media 'thumbnail'
                            $bundleThumb = null;
                            $coursesSum = 0;
                            $allPriced = $bundle->courses->isNotEmpty();
                            foreach ($bundle->courses as $bc) {
                                if (! $bundleThumb) {
                                    $bundleThumb = $bc->getFirstMedia('thumbnail');
                                }
                                if ($bc->price && $bc->price > 0) {
                                    $coursesSum += (float) $bc->price;
                                } else {
                                    $allPriced = false;
                                }
                            }

                            // Ahorro real: solo si todos los cursos tienen precio y la suma supera el precio del paquete
                            $showSave = $allPriced && $coursesSum > (float) $bundle->price && $bundle->price > 0;
                            $offPct = $showSave ? (int) round(($coursesSum - $bundle->price) / $coursesSum * 100) : 0;
                        @endphp

                        <div class="plcard">
                            <div class="plcard-media">
                                @if ($bundleThumb)
                                    <img src="{{ $bundleThumb->getFullUrl() }}" alt="{{ $bundle->title }}" loading="lazy"
                                         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:1;"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='';">
                                    <i class="fas fa-box-open pkico" style="display:none;"></i>
                                @else
                                    <i class="fas fa-box-open pkico"></i>
                                @endif
                                <span class="plcard-badge">Paquete</span>
                                @if ($showSave)
                                    <span class="plcard-off">-{{ $offPct }}%</span>
                                @endif
                            </div>

                            <div class="plcard-body">
                                <div class="plcard-cat">
                                    {{ $bundle->courses_count }} {{ $bundle->courses_count == 1 ? 'curso' : 'cursos' }}
                                </div>
                                <div class="plcard-title">{{ $bundle->title }}</div>

                                <div class="plcard-meta">
                                    <span><i class="fas fa-circle-play"></i> {{ $bundle->courses_count }} {{ $bundle->courses_count == 1 ? 'curso' : 'cursos' }}</span>
                                    @if ($bundle->duration)
                                        <span><i class="far fa-clock"></i> {{ $bundle->duration }}h</span>
                                    @endif
                                    <span><i class="fas fa-award"></i> Certificado</span>
                                </div>

                                <div class="plcard-foot">
                                    <span class="plcard-price">
                                        <span class="now">$ {{ number_format($bundle->price, 0, ',', '.') }} <small>COP</small></span>
                                        @if ($showSave)
                                            <span class="was">$ {{ number_format($coursesSum, 0, ',', '.') }} COP</span>
                                        @endif
                                    </span>
                                    <a class="plcard-btn" href="{{ route('bundles.view', [$bundle->slug ?? $bundle->slack]) }}">
                                        Ver paquete <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </main>

@endsection
