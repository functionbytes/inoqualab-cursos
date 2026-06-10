@extends('layouts.pages')

@section('title', 'Paquetes')

@push('css')
    <link rel="stylesheet" href="{{ url('/pages/css/cursos.css') }}">
@endpush

@section('content')

<div class="cursos-page">

    {{-- ===== Band ===== --}}
    <div class="band">
        <div class="cx-container">
            <div class="crumb"><a href="{{ route('index') }}" style="color:inherit">INICIO</a> <span class="sep">/</span> <span class="cur">PAQUETES</span></div>
            <h1>Paquetes de cursos</h1>
            <p class="lede">Adquiere varios cursos agrupados a un mejor precio y certifícate en toda un área.</p>
        </div>
    </div>

    {{-- ===== Catálogo ===== --}}
    <main class="catalog">
        <div class="cx-container">

            @if ($bundles->isEmpty())
                <div class="cat-empty">
                    <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg></div>
                    <p>Aún no hay paquetes disponibles.</p>
                </div>
            @else
                <div class="cat-count" style="margin-bottom:18px;">Mostrando <b>{{ $bundles->count() }}</b> {{ $bundles->count() == 1 ? 'paquete' : 'paquetes' }}</div>

                <div class="cat-grid">
                    @foreach ($bundles as $bundle)
                        @php
                            $bundleThumb = null;
                            foreach ($bundle->courses as $bc) {
                                $bundleThumb = $bc->getFirstMedia('thumbnail');
                                if ($bundleThumb) break;
                            }
                        @endphp
                        <a class="ccard" href="{{ route('bundles.view', [$bundle->slug ?? $bundle->slack]) }}"
                           data-title="{{ \Illuminate\Support\Str::lower($bundle->title) }}">
                            <div class="ccard-media">
                                @if ($bundleThumb)
                                    <img src="{{ $bundleThumb->getFullUrl() }}" alt="{{ $bundle->title }}" loading="lazy"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='';">
                                    <div class="ph" style="display:none;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg></div>
                                @else
                                    <div class="ph"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg></div>
                                @endif
                                <span class="ccard-badge premium">Paquete</span>
                            </div>
                            <div class="ccard-body">
                                <div class="ccard-cat">{{ $bundle->courses_count }} {{ $bundle->courses_count == 1 ? 'curso' : 'cursos' }}@if($bundle->duration) · {{ $bundle->duration }}h @endif</div>
                                <div class="ccard-title">{{ $bundle->title }}</div>
                                <div class="ccard-meta">
                                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z" fill="currentColor" stroke="none"/></svg> {{ $bundle->courses_count }} {{ $bundle->courses_count == 1 ? 'curso' : 'cursos' }}</span>
                                    @if($bundle->duration)<span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg> {{ $bundle->duration }}h</span>@endif
                                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9 12 4 2 9l10 5 10-5z"/><path d="M6 11v5c0 1 2.7 3 6 3s6-2 6-3v-5"/></svg> Certificado</span>
                                </div>
                                <div class="ccard-foot">
                                    <span class="ccard-price">$ {{ number_format($bundle->price, 0, ',', '.') }} <small>COP</small></span>
                                    <span class="ccard-cta">Ver paquete <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </main>

</div>
@endsection
