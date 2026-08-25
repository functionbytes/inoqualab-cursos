@php use Carbon\Carbon; @endphp

@extends('layouts.customers')

@section('title', 'Dashboard')

@push('css')
    <link rel="stylesheet" href="{{ url('customers/css/aula.css') }}">
@endpush

@php
    $statusOf = function ($insc) {
        if ((int) $insc->expire === 1) {
            return 'expired';
        }
        if ((float) $insc->percent >= 100) {
            return 'done';
        }
        if ((float) $insc->percent > 0) {
            return 'progress';
        }

        return 'pending';
    };

    $statusLabels = [
        'expired' => 'Vencido',
        'done' => 'Completado',
        'progress' => 'En curso',
        'pending' => 'Sin iniciar',
    ];

    $activos = $courses->filter(fn ($c) => (int) $c->expire !== 1);
    $expirados = $courses->filter(fn ($c) => (int) $c->expire === 1);
    $completados = $courses->filter(fn ($c) => $statusOf($c) === 'done');

    $promedio = $courses->count() > 0
        ? (int) round($courses->avg(fn ($c) => min(100, (float) $c->percent)))
        : 0;

    // Curso destacado: el más avanzado que siga accesible; si no hay ninguno
    // accesible, el primero de la lista para que el bloque no quede vacío.
    $hero = $courses
        ->filter(fn ($c) => (int) $c->expire !== 1 && (float) $c->percent < 100)
        ->sortByDesc('percent')
        ->first() ?: $courses->first();

    $heroPercent = $hero ? (int) round(min(100, (float) $hero->percent)) : 0;
    $heroExpired = $hero ? (int) $hero->expire === 1 : false;

    // La ruta se ordena por id: es el orden en que se compraron los módulos y
    // el orden en que se espera que se cursen.
    $ruta = $courses->sortBy('id')->values();

    // Iniciados = los que tienen algún avance registrado.
    $iniciados = $courses->filter(fn ($c) => (float) $c->percent > 0)->count();

    // Circunferencia del anillo de progreso (r=43 en el viewBox 0 0 100 100).
    $ring = 2 * M_PI * 43;
@endphp

@section('content')

<div class="pb-dash">

    {{-- ===== Cabecera ===== --}}
    <div class="pb-head">
        <div>
            <div class="eyebrow">{{ ucfirst(Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM')) }}</div>
            <h1>Tu sesión de hoy</h1>
        </div>
        <a class="pb-search" href="{{ route('customers.courses') }}">
            @include('customers.includes.icon', ['name' => 'search'])
            <span>Buscar en mis cursos…</span>
        </a>
    </div>

    @if($hero)
        {{-- ===== Bloque dominante: retomar ===== --}}
        <div class="pb-hero">
            <div class="pb-hero-body">
                <div class="eyebrow">
                    {{ $hero->course?->categorie?->title ?: 'Capacitación' }}
                    @if($heroExpired) · acceso vencido @endif
                </div>
                <h2>{{ $hero->course?->title }}</h2>
                <div class="pb-hero-meta">
                    <span>
                        @include('customers.includes.icon', ['name' => 'clock'])
                        {{ $statusLabels[$statusOf($hero)] }}
                    </span>
                    @if($hero->certificate)
                        <span>
                            @include('customers.includes.icon', ['name' => 'award'])
                            Certificado emitido
                        </span>
                    @endif
                </div>
                <div class="pb-hero-actions">
                    @if($heroExpired)
                        <a class="go" href="{{ route('customers.courses') }}">
                            @include('customers.includes.icon', ['name' => 'refresh'])
                            Renovar acceso
                        </a>
                    @else
                        <a class="go" href="{{ route('customers.courses.content', $hero->slack) }}">
                            @include('customers.includes.icon', ['name' => 'play'])
                            {{ $heroPercent > 0 ? 'Reanudar' : 'Comenzar' }}
                        </a>
                    @endif
                    <a class="ghost" href="{{ route('customers.courses') }}">Ver todos mis cursos</a>
                </div>
            </div>
            <div class="pb-ring">
                <svg viewBox="0 0 100 100" aria-hidden="true">
                    <circle cx="50" cy="50" r="43" class="track"/>
                    <circle cx="50" cy="50" r="43" class="fill"
                            stroke-dasharray="{{ round($ring, 1) }}"
                            stroke-dashoffset="{{ round($ring * (1 - $heroPercent / 100), 1) }}"/>
                </svg>
                <div class="val">
                    <b>{{ $heroPercent }}%</b>
                    <span>del curso</span>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Ruta formativa ===== --}}
    @if($ruta->count() > 0)
        <div class="pb-sec">
            <h3>Tu ruta formativa</h3>
            <span class="note">
                {{ $iniciados }} {{ $iniciados === 1 ? 'iniciado' : 'iniciados' }}
                @if($expirados->count() > 0) · {{ $expirados->count() }} por renovar @endif
            </span>
        </div>

        <div class="pb-route">
            @foreach($ruta as $i => $insc)
                @php
                    $st = $statusOf($insc);
                    $percent = (int) round(min(100, (float) $insc->percent));
                    $activa = $hero && $insc->id === $hero->id;
                @endphp
                <div class="pb-step st-{{ $st }} @if($activa) is-active @endif">
                    <div class="top">
                        <span class="num">{{ $i + 1 }}</span>
                        <span class="chip">{{ $statusLabels[$st] }}</span>
                    </div>
                    <div class="title">{{ $insc->course?->title }}</div>

                    @if($st === 'expired')
                        <a class="act" href="{{ route('customers.courses') }}">Renovar acceso</a>
                    @elseif($st === 'done')
                        @if($insc->certificate)
                            <a class="act" href="{{ route('customers.certificate.download', $insc->certificate->slack) }}">Certificado</a>
                        @else
                            <a class="act" href="{{ route('customers.courses.content', $insc->slack) }}">Repasar</a>
                        @endif
                    @else
                        <div class="bar"><i style="--p:{{ $percent }}%"></i></div>
                        <div class="sub">{{ $percent }}% completado</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- ===== Franja inferior ===== --}}
    <div class="pb-cols">
        <div class="pb-card">
            <h4>Próximo hito</h4>
            @if($hero && ! $heroExpired && $heroPercent < 100)
                <p>Termina «{{ \Illuminate\Support\Str::limit($hero->course?->title, 60) }}» para desbloquear su examen final y el certificado.</p>
                <div class="pb-goal">
                    <span class="ic">@include('customers.includes.icon', ['name' => 'award'])</span>
                    <div class="grow">
                        <div class="bar"><i style="--p:{{ $heroPercent }}%"></i></div>
                        <div class="sub">{{ $heroPercent }}% del curso completado</div>
                    </div>
                </div>
            @elseif($expirados->count() > 0)
                <p>Tienes {{ $expirados->count() }} {{ $expirados->count() === 1 ? 'curso con el acceso vencido' : 'cursos con el acceso vencido' }}. Al renovar conservas el progreso registrado.</p>
                <a class="pb-cta" href="{{ route('customers.courses') }}">Renovar accesos</a>
            @else
                <p>No tienes cursos pendientes. Explora el catálogo para seguir formándote.</p>
                <a class="pb-cta" href="{{ route('home') }}">Ver catálogo</a>
            @endif
        </div>

        <div class="pb-card">
            <div class="pb-card-head">
                <h4>Resumen</h4>
                <a href="{{ route('customers.courses') }}">Ver cursos</a>
            </div>
            <div class="pb-figs">
                <div><b>{{ $courses->count() }}</b><span>Inscripciones</span></div>
                <div><b>{{ $activos->count() }}</b><span>Con acceso</span></div>
                <div><b>{{ $completados->count() }}</b><span>Completados</span></div>
                <div><b>{{ $promedio }}%</b><span>Progreso medio</span></div>
            </div>
        </div>
    </div>

</div>

@endsection
