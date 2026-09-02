@extends('layouts.customers')

@section('title', 'Mis cursos')

@section('context-title', 'Mis cursos')
@section('context-icon')@include('customers.includes.icon', ['name' => 'cap'])@endsection
@section('context-subtitle', 'Continúa donde lo dejaste o inscríbete a uno nuevo')
@section('context-stat-number', $courses->count())
@section('context-stat-label', Str::plural('curso', $courses->count()))

@push('css')
<link rel="stylesheet" href="{{ asset('customers/css/aula.css') }}">
@endpush

@php
    // Se calculan aquí los estados para poder contar por filtro en la cabecera:
    // antes los botones no decían cuántos cursos había detrás de cada uno.
    $items = $courses->map(function ($inscription) {
        $progress = min(100, max(0, (int) round($inscription->percent)));

        if ($inscription->expire == 1) {
            $status = 'expired';
        } elseif ($progress >= 100) {
            $status = 'done';
        } elseif ($progress > 0) {
            $status = 'progress';
        } else {
            $status = 'pending';
        }

        return (object) [
            'inscription' => $inscription,
            'progress' => $progress,
            'status' => $status,
        ];
    });

    $labels = [
        'expired' => 'Expirado',
        'done' => 'Completado',
        'progress' => 'En progreso',
        'pending' => 'Pendiente',
    ];

    $counts = [
        'todos' => $items->count(),
        'progress' => $items->where('status', 'progress')->count(),
        'pending' => $items->where('status', 'pending')->count(),
        'done' => $items->where('status', 'done')->count(),
        'expired' => $items->where('status', 'expired')->count(),
    ];
@endphp

@section('content')
<section class="pnl-section" id="sec-cursos">

    <div class="pnl-card pnl-head pnl-head-row">
        <div>
            {{-- El título ya lo muestra la banda de contexto del header; aquí
                 solo queda el detalle dinámico (conteo, accesos vencidos). --}}
            <div class="sub">
                @if($counts['todos'] > 0)
                    {{ $counts['todos'] }} {{ $counts['todos'] === 1 ? 'capacitación' : 'capacitaciones' }} ·
                    {{ $counts['expired'] > 0 ? $counts['expired'].' con el acceso vencido' : 'accesos al día' }}
                @else
                    Gestiona y continúa tus capacitaciones.
                @endif
            </div>
        </div>
        @if($counts['todos'] > 0)
            <label class="pnl-search" for="cursosBuscar">
                @include('customers.includes.icon', ['name' => 'search'])
                <input type="search" id="cursosBuscar" placeholder="Buscar por título…" autocomplete="off">
            </label>
        @endif
    </div>

    <div class="pnl-gap"></div>

    @if($courses->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'cap'])</span>
            <h3>Aún no tienes cursos asignados</h3>
            <p>Cuando te inscribas en una capacitación aparecerá aquí, con su progreso y su certificado.</p>
            <a href="{{ route('home') }}">Explorar el catálogo</a>
        </div>

    @else

        <div class="pnl-filter" id="cursosFilter" role="group" aria-label="Filtrar mis cursos">
            @foreach(['todos' => 'Todos', 'progress' => 'En progreso', 'pending' => 'Pendientes', 'done' => 'Completados', 'expired' => 'Vencidos'] as $key => $label)
                <button type="button" class="{{ $key === 'todos' ? 'active' : '' }}" data-f="{{ $key }}"
                        aria-pressed="{{ $key === 'todos' ? 'true' : 'false' }}">
                    {{ $label }}<span class="cnt">{{ $counts[$key] }}</span>
                </button>
            @endforeach
        </div>

        <div class="pc-grid" id="cursosCards">
            @foreach($items as $item)
                @php
                    $inscription = $item->inscription;
                    $progress = $item->progress;
                    $status = $item->status;
                    $statusLabel = $labels[$status];

                    $course   = $inscription->course;
                    $category = $course?->categorie?->title ?? 'Curso';
                    $year     = optional($inscription->created_at)->format('Y') ?? date('Y');
                    $thumb    = $course?->getFirstMedia('thumbnail')?->getFullUrl() ?? '/pages/images/courses/default.jpg';

                    $contentUrl  = route('customers.courses.content', $inscription->slack);
                    $certificate = $status === 'done' ? $inscription->certificate : null;
                @endphp

                <div class="pc-card" data-status="{{ $status }}" data-title="{{ \Illuminate\Support\Str::lower($course?->title ?? '') }}">
                    <div class="pc-media"
                         style="background-image:linear-gradient(150deg,rgba(13,27,42,.72),rgba(13,27,42,.55)),url('{{ $thumb }}');background-size:cover;background-position:center;">
                        <span class="pc-cat">{{ $category }}</span>
                    </div>

                    <div class="pc-body">
                        <div class="pc-top">
                            <span class="pc-year">{{ $year }}</span>
                            <span class="pc-badge st-{{ $status }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="pc-title">{{ $course?->title ?? 'Curso' }}</div>

                        <div class="pc-track" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $progress }}% completado"><div class="pc-fill" style="--p:{{ $progress }}%"></div></div>

                        <div class="pc-foot">
                            <div class="col">
                                <div class="k">Progreso</div>
                                <div class="v">{{ $progress }}%</div>
                            </div>
                            <div class="col r">
                                <div class="k">Estado</div>
                                <div class="v">{{ $statusLabel }}</div>
                            </div>
                        </div>

                        {{-- Una línea que explica el estado: el badge por sí solo
                             no dice qué pasó ni qué se puede hacer. --}}
                        <div class="pc-note">
                            @if($status === 'expired')
                                El acceso venció{{ $inscription->finished ? ' el '.\Carbon\Carbon::parse($inscription->finished)->locale('es')->isoFormat('D MMM YYYY') : '' }}. Al renovarlo conservas tu progreso.
                            @elseif($status === 'done')
                                {{ $certificate ? 'Certificado disponible para descargar.' : 'Curso completado. El certificado se emite al aprobar el examen.' }}
                            @elseif($status === 'progress')
                                Te queda el {{ 100 - $progress }}% para completarlo.
                            @else
                                Aún no has empezado este curso.
                            @endif
                        </div>

                        @if($status === 'done' && $certificate)
                            <a class="pc-btn ghost" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                                @include('customers.includes.icon', ['name' => 'download']) Ver certificado
                            </a>
                        @elseif($status === 'expired')
                            <a class="pc-btn ghost" href="{{ route('checkout', ['course', $course->slack]) }}">
                                @include('customers.includes.icon', ['name' => 'refresh']) Renovar acceso
                            </a>
                        @else
                            <a class="pc-btn" href="{{ $contentUrl }}">
                                @include('customers.includes.icon', ['name' => 'play']) {{ $progress > 0 ? 'Continuar' : 'Comenzar curso' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Se muestra cuando el filtro o la búsqueda no dejan ninguna tarjeta:
             antes la rejilla simplemente quedaba en blanco. --}}
        <div class="pnl-empty" id="cursosVacio" hidden>
            <span class="ic">@include('customers.includes.icon', ['name' => 'search'])</span>
            <h3>No hay cursos que coincidan</h3>
            <p>Prueba con otro filtro o borra la búsqueda para ver todas tus capacitaciones.</p>
        </div>

    @endif

</section>
@endsection

@push('scripts')
<script>
    $(function () {
        var filtro = 'todos';
        var texto = '';

        function aplicar() {
            var visibles = 0;

            $('#cursosCards .pc-card').each(function () {
                var $c = $(this);
                var coincideEstado = (filtro === 'todos') || ($c.data('status') === filtro);
                var coincideTexto = texto === '' || String($c.data('title')).indexOf(texto) !== -1;
                var ver = coincideEstado && coincideTexto;

                $c.prop('hidden', !ver);
                if (ver) { visibles++; }
            });

            $('#cursosVacio').prop('hidden', visibles !== 0);
        }

        $('#cursosFilter').on('click', 'button', function () {
            var $btn = $(this);
            $btn.siblings().removeClass('active').attr('aria-pressed', 'false');
            $btn.addClass('active').attr('aria-pressed', 'true');
            filtro = $btn.data('f');
            aplicar();
        });

        $('#cursosBuscar').on('input', function () {
            texto = $(this).val().toLowerCase().trim();
            aplicar();
        });
    });
</script>
@endpush
