@php use Carbon\Carbon; use Illuminate\Support\Str; @endphp

@extends('layouts.customers')

@section('title', 'Dashboard')

@push('css')
    <link rel="stylesheet" href="{{ url('customers/css/aula.css') }}?v={{ @filemtime(public_path('customers/css/aula.css')) ?: 1 }}">
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

    $certificatesCount = $courses->filter(fn ($c) => $c->certificate)->count();

    // El nombre viene en mayúsculas de BD (uso legal en certificados/facturas);
    // para un saludo se ve gritado, así que acá se normaliza solo para mostrar.
    $firstName = Str::of($user->firstname)->lower()->explode(' ')->first();
    $firstName = $firstName ? Str::ucfirst($firstName) : $user->firstname;
@endphp

@section('content')

<div class="pb-dash">

    {{-- ===== Cabecera: saludo -- la idea es que la portada empuje a
         "seguir" antes que a "buscar" (el buscador vivía acá, se quitó). ===== --}}
    <div class="pb-head">
        <div>
            <div class="eyebrow">{{ ucfirst(Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM')) }}</div>
            <h1>Hola, {{ $firstName }}</h1>
            <p class="pb-sub">
                @if($hero && ! $heroExpired && $heroPercent < 100)
                    Continúa donde lo dejaste y sigue sumando avances.
                @elseif($expirados->count() > 0)
                    Tienes {{ $expirados->count() }} {{ $expirados->count() === 1 ? 'curso por renovar' : 'cursos por renovar' }}.
                @else
                    Vas al día con tu plan de formación.
                @endif
            </p>
        </div>
    </div>

    {{-- ===== Chips de resumen: mismo dato que antes vivía en la franja
         inferior ("Resumen"), pero como contexto rápido junto al saludo en
         vez de una tarjeta aparte al final de la página. ===== --}}
    <div class="pb-chips">
        <span class="pb-chip"><b>{{ $activos->count() }}</b>{{ $activos->count() === 1 ? 'Curso activo' : 'Cursos activos' }}</span>
        <span class="pb-chip"><b>{{ $certificatesCount }}</b>{{ $certificatesCount === 1 ? 'Certificado' : 'Certificados' }}</span>
        @if($expirados->count() > 0)
            <span class="pb-chip is-warn"><b>{{ $expirados->count() }}</b>Por vencer</span>
        @endif
    </div>

    {{-- ===== Banda de próxima acción: fusiona el hero de "retomar" y la
         tarjeta "Próximo hito" de antes en un único bloque -- un solo lugar
         que le dice al alumno qué hacer ahora, no dos que repetían lo
         mismo. ===== --}}
    @if($hero && ! $heroExpired && $heroPercent < 100)
        <div class="pb-next">
            <span class="ic">@include('customers.includes.icon', ['name' => 'play'])</span>
            <div class="txt">
                <div class="lbl">{{ $heroPercent > 0 ? 'Continúa donde quedaste' : 'Empieza cuando quieras' }}</div>
                <b>{{ $hero->course?->title }}</b>
                <span>{{ $hero->course?->categorie?->title ?: 'Capacitación' }} · {{ $heroPercent }}% completado</span>
            </div>
            <a class="pb-cta" href="{{ route('customers.courses.content', $hero->slack) }}">
                {{ $heroPercent > 0 ? 'Reanudar' : 'Comenzar' }}
            </a>
        </div>
    @elseif($expirados->count() > 0)
        <div class="pb-next is-warn">
            <span class="ic">@include('customers.includes.icon', ['name' => 'refresh'])</span>
            <div class="txt">
                <div class="lbl">Tienes accesos vencidos</div>
                <b>{{ $expirados->count() }} {{ $expirados->count() === 1 ? 'curso con el acceso vencido' : 'cursos con el acceso vencido' }}</b>
                <span>Al renovar conservas el progreso registrado.</span>
            </div>
            <a class="pb-cta" href="{{ route('customers.courses') }}">Renovar accesos</a>
        </div>
    @elseif($hero)
        <div class="pb-next is-done">
            <span class="ic">@include('customers.includes.icon', ['name' => 'award'])</span>
            <div class="txt">
                <div class="lbl">Vas al día</div>
                <b>No tienes cursos pendientes</b>
                <span>Explora el catálogo para seguir formándote.</span>
            </div>
            <a class="pb-cta" href="{{ route('home') }}">Ver catálogo</a>
        </div>
    @endif

    {{-- ===== Todos tus cursos: lista compacta con barra de progreso en vez
         de tarjetas grandes -- el detalle vive en /cursos, acá solo se
         necesita ver de un vistazo en qué va cada uno. ===== --}}
    @if($ruta->count() > 0)
        <div class="pb-sec">
            <h3>Todos tus cursos</h3>
            <span class="note">
                {{ $iniciados }} {{ $iniciados === 1 ? 'iniciado' : 'iniciados' }}
                @if($expirados->count() > 0) · {{ $expirados->count() }} por renovar @endif
            </span>
        </div>

        <div class="pb-list">
            @foreach($ruta as $insc)
                @php
                    $st = $statusOf($insc);
                    $percent = (int) round(min(100, (float) $insc->percent));
                @endphp
                <div class="pb-row st-{{ $st }}">
                    <span class="ic">
                        @if($st === 'done')
                            @include('customers.includes.icon', ['name' => 'award'])
                        @elseif($st === 'expired')
                            @include('customers.includes.icon', ['name' => 'refresh'])
                        @else
                            @include('customers.includes.icon', ['name' => 'play'])
                        @endif
                    </span>
                    <div class="txt">
                        <b>{{ $insc->course?->title }}</b>
                        <span>{{ $statusLabels[$st] }}</span>
                    </div>

                    @if($st === 'expired')
                        <a class="act" href="{{ route('customers.courses') }}">Renovar</a>
                    @elseif($st === 'done')
                        @if($insc->certificate)
                            <a class="act" href="{{ route('customers.certificate.download', $insc->certificate->slack) }}">Certificado</a>
                        @else
                            <a class="act" href="{{ route('customers.courses.content', $insc->slack) }}">Repasar</a>
                        @endif
                    @else
                        <div class="bar"><i style="--p:{{ $percent }}%"></i></div>
                        <span class="pct">{{ $percent }}%</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>

@endsection
