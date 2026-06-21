@php use Carbon\Carbon; @endphp

@extends('layouts.customers')

@section('title', 'Dashboard')

@push('css')
    <link rel="stylesheet" href="{{ url('customers/css/aula.css') }}">
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
                <span class="ic"><i class="fa-solid fa-graduation-cap"></i></span>
                <div><b>{{ $activos->count() }}</b> {{ $activos->count() === 1 ? 'curso activo' : 'cursos activos' }} · ¡sigue aprendiendo!</div>
            </div>
        @endif
    </div>

    @if($hero)
        @php
            $heroStatus = $statusOf($hero);
            $heroPercent = $heroStatus === 'done' ? 100 : (int) round(min(100, (float) $hero->percent));
            $heroThumb = $hero->course?->getFirstMedia('thumbnail')?->getFullUrl() ?: $defaultThumb;
            $heroCat = $hero->course?->categorie?->title;
            $heroUrl = route('customers.courses.content', $hero->slack);
        @endphp

        {{-- ===== Resume hero ===== --}}
        <div class="resume">
            <div class="resume-media" style="background-image:url('{{ $heroThumb }}');background-size:cover;background-position:center;">
                <a class="resume-play" href="{{ $heroUrl }}" aria-label="Reanudar curso">
                    <i class="fa-solid fa-play"></i>
                </a>
                @if($heroCat)
                    <span class="resume-pill">{{ $heroCat }}</span>
                @endif
            </div>
            <div class="resume-body">
                <div class="resume-eyebrow">
                    {{ $heroPercent > 0 ? 'Continúa donde lo dejaste' : 'Comienza tu próximo curso' }}
                </div>
                <h2>{{ $hero->course?->title }}</h2>
                <div class="resume-next">
                    <i class="fa-regular fa-circle-play"></i>
                    {{ $statusLabels[$heroStatus] }}
                </div>
                <div class="resume-prog">
                    <div class="track"><i style="--p:{{ $heroPercent }}%"></i></div>
                    <span class="pct">{{ $heroPercent }}% completado</span>
                </div>
                <div class="resume-actions">
                    <a class="go" href="{{ $heroUrl }}">
                        <i class="fa-solid fa-play"></i>
                        {{ $heroPercent > 0 ? 'Reanudar curso' : 'Comenzar curso' }}
                    </a>
                    @if($hero->certificate)
                        <a class="ghost" href="{{ route('customers.certificate.download', $hero->certificate->slack) }}">
                            <i class="fa-solid fa-download"></i>
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
            <div class="ic"><i class="fa-solid fa-book-open"></i></div>
            <div>
                <div class="n">{{ $activos->count() }}</div>
                <div class="l">Cursos activos</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="ic"><i class="fa-solid fa-chart-line"></i></div>
            <div>
                <div class="n">{{ $promedio }}%</div>
                <div class="l">Progreso promedio</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="ic"><i class="fa-solid fa-award"></i></div>
            <div>
                <div class="n">{{ $certificados }}</div>
                <div class="l">Certificados</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="ic"><i class="fa-solid fa-circle-check"></i></div>
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
                            <div class="pc-media" style="background-image:url('{{ $thumb }}');background-size:cover;background-position:center;">
                                @if($cat)
                                    <span class="pc-cat">{{ $cat }}</span>
                                @endif
                            </div>
                            <div class="pc-body">
                                <div class="pc-top">
                                    <span class="pc-year">{{ optional($insc->created_at)->format('Y') }}</span>
                                    <span class="pc-badge {{ $st }}">{{ $statusLabels[$st] }}</span>
                                </div>
                                <div class="pc-title">{{ $insc->course?->title }}</div>
                                <div class="pc-track"><div class="pc-fill" style="--p:{{ $percent }}%"></div></div>
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
                                    <i class="fa-solid fa-play"></i>
                                    {{ $percent > 0 ? 'Continuar' : 'Empezar curso' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="pnl-card">
                    <div class="pnl-head">
                        <h2>Aún no tienes cursos en progreso</h2>
                        <div class="sub">Cuando empieces un curso aparecerá aquí para que continúes donde lo dejaste.</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Rail lateral: honesto y discreto, sin números inventados --}}
        <div class="inicio-rail">
            <div class="goal-card">
                <h4>Tu aprendizaje</h4>
                <p style="font-size:13px;color:var(--muted);line-height:1.55;margin:0;">
                    @if($activos->count() > 0)
                        Tienes {{ $activos->count() }} {{ $activos->count() === 1 ? 'curso activo' : 'cursos activos' }}
                        con un progreso promedio del {{ $promedio }}%. ¡Sigue avanzando!
                    @else
                        Cuando te inscribas a un curso, aquí verás tu avance y tus metas de estudio.
                    @endif
                </p>
            </div>

            <div class="dl-card">
                <h4>Tus certificados</h4>
                @if($certificados > 0)
                    @foreach($courses->filter(fn ($c) => $c->certificate) as $insc)
                        <div class="deadline">
                            <div class="d"><b><i class="fa-solid fa-award"></i></b></div>
                            <div class="info">
                                <b>{{ $insc->course?->title }}</b>
                                <a class="ok" href="{{ route('customers.certificate.download', $insc->certificate->slack) }}"
                                   style="text-decoration:none;">
                                    <i class="fa-solid fa-download"></i> Descargar certificado
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p style="font-size:13px;color:var(--muted);line-height:1.55;margin:0;">
                        Al completar un curso, tu certificado estará disponible aquí para descargar.
                    </p>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            // Fallback de imagen para los fondos (resume-media y pc-media) si la URL falla.
            $('.resume-media, .pc-media').each(function () {
                var $el = $(this);
                var bg = $el.css('background-image');
                var match = bg && bg.match(/url\(["']?([^"')]+)["']?\)/);
                if (!match) { return; }
                var img = new Image();
                img.onerror = function () {
                    $el.css('background-image', "url('{{ asset('/pages/images/courses/default.jpg') }}')");
                };
                img.src = match[1];
            });
        });
    </script>
@endpush
