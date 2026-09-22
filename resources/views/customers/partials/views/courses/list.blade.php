{{-- Contenido de Mis cursos: subtítulo, buscador, filtro por estado, rejilla
     y paginador. Vive en un partial aparte para poder re-renderizarlo por
     AJAX (ver el script en courses/index.blade.php) sin recargar la página.
     $counts, $state y $searchKey ya vienen calculados desde el controller.
     Todo dentro de .ct-table -- misma caja única que Certificados/Mis
     pedidos, en vez de piezas sueltas (buscador, filtro y rejilla) flotando
     por separado sobre el fondo gris. --}}
@php
    // El estado por tarjeta (para el badge y la nota) usa el mismo corte que
    // ya aplicó el controller para filtrar -- esto es solo presentación, no
    // vuelve a decidir qué cursos entran a la página.
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
@endphp

@if($items->isEmpty() && $counts['todos'] === 0)

    <div class="pnl-card pnl-head pnl-head-row">
        <div class="sub">Gestiona y continúa tus capacitaciones.</div>
    </div>
    <div class="pnl-gap"></div>

    <div class="pnl-empty">
        <span class="ic">@include('customers.includes.icon', ['name' => 'cap'])</span>
        <h3>Aún no tienes cursos asignados</h3>
        <p>Cuando te inscribas en una capacitación aparecerá aquí, con su progreso y su certificado.</p>
        <a href="{{ route('home') }}">Explorar el catálogo</a>
    </div>

@else

    <div class="ct-table">
        <div class="ct-head">
            <div class="sub">
                {{ $counts['todos'] }} {{ $counts['todos'] === 1 ? 'capacitación' : 'capacitaciones' }} ·
                {{ $counts['expired'] > 0 ? $counts['expired'].' con el acceso vencido' : 'accesos al día' }} ·
                {{ $avgProgress }}% de avance promedio
            </div>
            <form class="pnl-search" action="{{ route('customers.courses') }}">
                @include('customers.includes.icon', ['name' => 'search'])
                <input type="search" name="search" id="cursosBuscar" placeholder="Buscar por título…" autocomplete="off" value="{{ $searchKey }}">
                @if($state)<input type="hidden" name="state" value="{{ $state }}">@endif
            </form>
        </div>

        <div class="pnl-filter pnl-filter-flat" id="cursosFilter" role="group" aria-label="Filtrar mis cursos">
            @foreach(['todos' => 'Todos', 'progress' => 'En progreso', 'pending' => 'Pendientes', 'done' => 'Completados', 'expired' => 'Vencidos'] as $key => $label)
                <a href="{{ $key === 'todos' ? route('customers.courses') : route('customers.courses', ['state' => $key]) }}"
                   class="{{ ($state ?: 'todos') === $key ? 'active' : '' }}">
                    {{ $label }}<span class="cnt">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>

        <div class="ct-body-wrap">
            @if($items->isEmpty())

                <div class="pnl-empty compact">
                    <span class="ic">@include('customers.includes.icon', ['name' => 'search'])</span>
                    <h3>No hay cursos que coincidan</h3>
                    <p>Prueba con otro filtro o borra la búsqueda para ver todas tus capacitaciones.</p>
                    <a href="{{ route('customers.courses') }}">Ver todos los cursos</a>
                </div>

            @else

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

                        <div class="pc-card" data-status="{{ $status }}">
                            <div class="pc-media has-thumb-dim" data-var-thumb="{{ $thumb }}">
                                <span class="pc-cat">{{ $category }}</span>
                            </div>

                            <div class="pc-body">
                                <div class="pc-top">
                                    <span class="pc-year">{{ $year }}</span>
                                    <span class="pc-badge st-{{ $status }}">{{ $statusLabel }}</span>
                                </div>

                                <div class="pc-title">{{ $course?->title ?? 'Curso' }}</div>

                                <div class="pc-track" role="progressbar" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $progress }}% completado"><div class="pc-fill" data-var-p="{{ $progress }}"></div></div>

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
                                        Ver certificado
                                    </a>
                                @elseif($status === 'expired')
                                    <a class="pc-btn ghost" href="{{ route('checkout', ['course', $course->slack]) }}">
                                        Renovar acceso
                                    </a>
                                @else
                                    <a class="pc-btn" href="{{ $contentUrl }}">
                                        {{ $progress > 0 ? 'Continuar' : 'Comenzar curso' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

            @endif
        </div>

        @if($courses->hasPages())
            <div class="ct-foot">
                <span>Mostrando {{ $courses->firstItem() }}-{{ $courses->lastItem() }} de {{ $courses->total() }} resultados</span>
                <nav>{{ $courses->appends(request()->input())->links() }}</nav>
            </div>
        @endif
    </div>

@endif
