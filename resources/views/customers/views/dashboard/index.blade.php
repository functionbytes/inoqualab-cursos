@php use Carbon\Carbon; @endphp

@extends('layouts.customers')

@section('title', 'Dashboard')

@push('css')
    <link rel="stylesheet" href="{{ url('customers/css/aula.css') }}?v={{ @filemtime(public_path('customers/css/aula.css')) ?: 1 }}">
@endpush

@php
    /**
     * Deriva el estado de una inscripción.
     * expired | done | progress | pending
     */
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
        'expired' => 'Expirado',
        'done' => 'Completado',
        'progress' => 'En progreso',
        'pending' => 'Pendiente',
    ];

    $defaultThumb = asset('/pages/images/courses/default.jpg');

    // Cursos activos = inscripciones no expiradas
    $activos = $courses->filter(fn ($c) => (int) $c->expire !== 1);

    // Cursos con acceso vencido
    $expirados = $courses->filter(fn ($c) => (int) $c->expire === 1);

    // Progreso promedio (sobre todas las inscripciones)
    $promedio = $courses->count() > 0
        ? (int) round($courses->avg(fn ($c) => min(100, (float) $c->percent)))
        : 0;

    // Certificados disponibles
    $certificados = $courses->filter(fn ($c) => $c->certificate)->count();

    // Completados
    $completados = $courses->filter(fn ($c) => $statusOf($c) === 'done')->count();

    // Cursos en progreso (para la grilla "En progreso")
    $enProgreso = $courses->filter(fn ($c) => $statusOf($c) === 'progress')
        ->sortByDesc('percent');

    // Curso del hero: mayor percent < 100 y no expirado; si no hay, el primero
    $hero = $courses
        ->filter(fn ($c) => (int) $c->expire !== 1 && (float) $c->percent < 100)
        ->sortByDesc('percent')
        ->first();

    if (! $hero) {
        $hero = $courses->first();
    }

    // La ruta lateral muestra las primeras inscripciones en el orden en que se
    // compraron: es el orden en que el alumno debe cursarlas.
    $ruta = $courses->sortBy('id')->take(5);
@endphp

@section('content')

    {{-- ===== Saludo ===== --}}
    <div class="pnl-greet">
        <div>
            <h1>Hola, {{ $user->firstname }}</h1>
            <div class="date">{{ ucfirst(Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')) }}</div>
        </div>
        @if($activos->count() > 0)
            <div class="pnl-streak">
                <span class="ic">@include('customers.includes.icon', ['name' => 'cap'])</span>
                <div><b>{{ $activos->count() }}</b> {{ $activos->count() === 1 ? 'curso activo' : 'cursos activos' }} · ¡sigue aprendiendo!</div>
            </div>
        @endif
    </div>

    @if($expirados->count() > 0)
        {{-- El aviso lleva la acción a la derecha: antes era un enlace subrayado
             perdido dentro del texto y casi nadie lo veía. --}}
        <div class="pnl-alert">
            <span class="ic">@include('customers.includes.icon', ['name' => 'warning'])</span>
            <div class="txt">
                <b>{{ $expirados->count() }}</b>
                {{ $expirados->count() === 1 ? 'curso con el acceso vencido' : 'cursos con el acceso vencido' }}.
                Renuévalos para retomar donde lo dejaste y obtener el certificado.
            </div>
            <a class="cta" href="{{ route('customers.courses') }}">Ver y renovar</a>
        </div>
    @endif

    @if($hero)
        @php
            $heroStatus = $statusOf($hero);
            $heroPercent = $heroStatus === 'done' ? 100 : (int) round(min(100, (float) $hero->percent));
            $heroThumb = $hero->course?->getFirstMedia('thumbnail')?->getFullUrl() ?: $defaultThumb;
            $heroCat = $hero->course?->categorie?->title;
            $heroExpired = $heroStatus === 'expired';
            $heroUrl = $heroExpired ? route('customers.courses') : route('customers.courses.content', $hero->slack);
        @endphp

        {{-- ===== Resume hero ===== --}}
        <div class="resume">
            <div class="resume-media has-thumb" data-var-thumb="{{ $heroThumb }}" data-default-thumb="{{ $defaultThumb }}">
                <a class="resume-play" href="{{ $heroUrl }}" aria-label="{{ $heroExpired ? 'Renovar acceso' : 'Reanudar curso' }}">
                    @include('customers.includes.icon', ['name' => $heroExpired ? 'refresh' : 'play'])
                </a>
                @if($heroCat)
                    <span class="resume-pill">{{ $heroCat }}</span>
                @endif
            </div>
            <div class="resume-body">
                <div class="resume-eyebrow">
                    @if($heroExpired)
                        Renueva para continuar
                    @else
                        {{ $heroPercent > 0 ? 'Continúa donde lo dejaste' : 'Comienza tu próximo curso' }}
                    @endif
                </div>
                <h2>{{ $hero->course?->title }}</h2>
                <div class="resume-next">
                    @include('customers.includes.icon', ['name' => 'play-circle'])
                    {{ $statusLabels[$heroStatus] }}
                </div>
                <div class="resume-prog">
                    <div class="track"><i data-var-p="{{ $heroPercent }}"></i></div>
                    <span class="pct">{{ $heroPercent }}% completado</span>
                </div>
                <div class="resume-actions">
                    <a class="go" href="{{ $heroUrl }}">
                        @if($heroExpired)
                            Renovar acceso
                        @else
                            {{ $heroPercent > 0 ? 'Reanudar curso' : 'Comenzar curso' }}
                        @endif
                    </a>
                    @if($hero->certificate)
                        <a class="ghost" href="{{ route('customers.certificate.download', $hero->certificate->slack) }}">
                            Certificado
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ===== Stats ===== --}}
    <div class="pnl-stats">
        <div class="stat-card">
            <div class="ic">@include('customers.includes.icon', ['name' => 'cap'])</div>
            <div>
                <div class="n">{{ $activos->count() }}</div>
                <div class="l">Cursos activos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="ic">@include('customers.includes.icon', ['name' => 'chart'])</div>
            <div>
                <div class="n">{{ $promedio }}%</div>
                <div class="l">Progreso promedio</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="ic">@include('customers.includes.icon', ['name' => 'award'])</div>
            <div>
                <div class="n">{{ $certificados }}</div>
                <div class="l">Certificados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="ic">@include('customers.includes.icon', ['name' => 'check'])</div>
            <div>
                <div class="n">{{ $completados }}</div>
                <div class="l">Cursos completados</div>
            </div>
        </div>
    </div>

    {{-- ===== En progreso + rail ===== --}}
    <div class="inicio-cols">
        <div class="inicio-main">
            <div class="pnl-sec-title">
                <h3>En progreso</h3>
                <a href="{{ route('customers.courses') }}">Ver todos</a>
            </div>

            @if($enProgreso->count() > 0)
                <div class="pc-grid">
                    @foreach($enProgreso as $insc)
                        @php
                            $st = $statusOf($insc);
                            $percent = (int) round(min(100, (float) $insc->percent));
                            $thumb = $insc->course?->getFirstMedia('thumbnail')?->getFullUrl() ?: $defaultThumb;
                            $cat = $insc->course?->categorie?->title;
                            $url = route('customers.courses.content', $insc->slack);
                        @endphp
                        <div class="pc-card" data-status="{{ $st }}">
                            <div class="pc-media has-thumb" data-var-thumb="{{ $thumb }}" data-default-thumb="{{ $defaultThumb }}">
                                @if($cat)
                                    <span class="pc-cat">{{ $cat }}</span>
                                @endif
                            </div>
                            <div class="pc-body">
                                <div class="pc-top">
                                    <span class="pc-year">{{ optional($insc->created_at)->format('Y') }}</span>
                                    <span class="pc-badge st-{{ $st }}">{{ $statusLabels[$st] }}</span>
                                </div>
                                <div class="pc-title">{{ $insc->course?->title }}</div>
                                <div class="pc-track"><div class="pc-fill" data-var-p="{{ $percent }}"></div></div>
                                <div class="pc-foot">
                                    <div class="col">
                                        <div class="k">Progreso</div>
                                        <div class="v">{{ $percent }}%</div>
                                    </div>
                                    <div class="col r">
                                        <div class="k">Estado</div>
                                        <div class="v">{{ $statusLabels[$st] }}</div>
                                    </div>
                                </div>
                                <a class="pc-btn" href="{{ $url }}">
                                    {{ $percent > 0 ? 'Continuar' : 'Empezar curso' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- El vacío explica qué hacer y ofrece la salida, en vez de ser
                     una tarjeta con una frase. --}}
                <div class="pnl-empty">
                    <span class="ic">@include('customers.includes.icon', ['name' => 'cap'])</span>
                    @if($expirados->count() > 0)
                        <h3>No tienes cursos en progreso</h3>
                        <p>
                            {{ $expirados->count() === 1 ? 'Tu curso tiene' : 'Tus '.$expirados->count().' cursos tienen' }}
                            el acceso vencido. Al renovarlo conservas el progreso que ya llevabas.
                        </p>
                        <a href="{{ route('customers.courses') }}">Ver y renovar accesos</a>
                    @else
                        <h3>Aún no tienes cursos en progreso</h3>
                        <p>Cuando empieces un curso aparecerá aquí para que continúes donde lo dejaste.</p>
                        <a href="{{ route('home') }}">Explorar el catálogo</a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Rail lateral --}}
        <div class="inicio-rail">
            @if($ruta->count() > 0)
                {{-- Ruta formativa: da contexto de dónde está el alumno dentro
                     del conjunto de cursos que compró. --}}
                <div class="goal-card">
                    <h4>Tu ruta formativa</h4>
                    <div class="pnl-route">
                        @foreach($ruta as $i => $insc)
                            @php
                                $st = $statusOf($insc);
                                $percent = (int) round(min(100, (float) $insc->percent));
                            @endphp
                            <div class="step st-{{ $st }}">
                                <div class="rail">
                                    <span class="dot">
                                        @if($st === 'done')
                                            @include('customers.includes.icon', ['name' => 'check'])
                                        @endif
                                    </span>
                                    @if(! $loop->last)<span class="line"></span>@endif
                                </div>
                                <div class="info">
                                    <b>{{ \Illuminate\Support\Str::limit($insc->course?->title, 42) }}</b>
                                    <span>
                                        @if($st === 'expired')
                                            Acceso vencido
                                        @elseif($st === 'done')
                                            Completado
                                        @elseif($st === 'progress')
                                            En progreso · {{ $percent }}%
                                        @else
                                            Sin iniciar
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="dl-card">
                <h4>Tus certificados</h4>
                @if($certificados > 0)
                    @foreach($courses->filter(fn ($c) => $c->certificate) as $insc)
                        <div class="deadline">
                            <div class="d"><b>@include('customers.includes.icon', ['name' => 'award'])</b></div>
                            <div class="info">
                                <b>{{ $insc->course?->title }}</b>
                                <a class="ok text-decoration-none" href="{{ route('customers.certificate.download', $insc->certificate->slack) }}">
                                    Descargar certificado
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="dl-card-empty">
                        Al completar un curso y aprobar su examen, el certificado aparece aquí para descargar.
                    </p>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('customers/js/views/dashboard/index.js') }}"></script>
@endpush
