{{--
    Header de página compartido: título + breadcrumb (o subtítulo/descripción)
    + acciones opcionales a la derecha. Portado del componente mc-content-header
    de webadmin (modules/Core/resources/views/components/card.blade.php).

    Uso: va full-bleed, FUERA del container-fluid del contenido — por eso se
    incluye dentro de @section('page_header'), no de @section('content'):

    @section('page_header')
        @include('managers.includes.card', ['title' => '...'])
    @endsection

    @section('content')
        ...
    @endsection

    Props:
    - title (string, requerido)
    - subtitle (string, opcional) — reemplaza el breadcrumb por un subtítulo
    - description (string, opcional) — igual que subtitle, prioridad menor
    - breadcrumbs (array, opcional) — [['label' => '...', 'url' => '...'], ...].
      El último item siempre se pinta como "activo" (sin link). Sin este prop,
      se muestra Inicio > {{ $title }}.
    - actions (string, opcional) — HTML crudo con botones alineados a la derecha
--}}
<div class="mc-content-header">
    {{-- .container (no max-width fijo aparte): tiene que alinear exacto con
         el <div class="container"> de <main class="mc-content"> más abajo —
         mismos breakpoints/gutters del theme, no un ancho inventado. --}}
    <div class="mc-content-header-inner container">
        <div>
            <h1 class="mc-page-title">{{ $title }}</h1>
            @isset($subtitle)
                <p class="mc-page-subtitle">{{ $subtitle }}</p>
            @elseif(isset($description))
                <p class="mc-section-desc">{{ $description }}</p>
            @else
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        @if(isset($breadcrumbs) && is_array($breadcrumbs))
                            @foreach($breadcrumbs as $crumb)
                                @if($loop->last)
                                    <li class="breadcrumb-item active" aria-current="page">{{ $crumb['label'] }}</li>
                                @else
                                    <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="{{ $crumb['url'] ?? '#' }}">{{ $crumb['label'] }}</a></li>
                                @endif
                            @endforeach
                        @else
                            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="{{ route('manager.dashboard') }}">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                        @endif
                    </ol>
                </nav>
            @endisset
        </div>

        @isset($actions)
            <div class="mc-banner-actions">
                {!! $actions !!}
            </div>
        @endisset
    </div>
</div>
