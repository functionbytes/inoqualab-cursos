{{-- Contenido de Mis certificados: alerta de vencimiento, filtro por estado,
     grupos por año y paginador. Vive en un partial aparte para poder
     re-renderizarlo por AJAX (ver el script en certificates/index.blade.php)
     sin recargar la página. $counts y $state (el filtro activo) ya vienen
     calculados desde el controller -- acá solo se arma cómo se ve cada
     tarjeta. --}}
@php
    // El estado por tarjeta (para el chip y para decidir si se ofrece
    // renovar) usa el mismo corte que ya aplicó el controller para filtrar
    // -- esto es solo presentación, no vuelve a decidir qué certificados
    // entran a la página.
    $items = $certificates->map(function ($certificate) {
        $end = $certificate->end_at ? \Carbon\Carbon::parse($certificate->end_at) : null;
        $daysLeft = $end
            ? (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false)
            : null;

        if ($daysLeft === null) {
            $itemState = 'none';
        } elseif ($daysLeft < 0) {
            $itemState = 'expired';
        } elseif ($daysLeft <= 30) {
            $itemState = 'soon';
        } else {
            $itemState = 'valid';
        }

        return (object) [
            'certificate' => $certificate,
            'end' => $end,
            'daysLeft' => $daysLeft,
            'state' => $itemState,
        ];
    });

    $labels = [
        'valid' => 'Vigente',
        'soon' => 'Por vencer',
        'expired' => 'Vencido',
        'none' => 'Sin fecha',
    ];

    // Agrupados por año de emisión (no por página): dentro de esta misma
    // página de resultados pueden convivir certificados de distintos años.
    $groups = $items->groupBy(function ($item) {
        return $item->certificate->start_at
            ? \Carbon\Carbon::parse($item->certificate->start_at)->year
            : 'Sin fecha';
    });
@endphp

@if($items->isEmpty())

    <div class="pnl-card pnl-head pnl-head-row">
        <div class="sub">Descarga y comparte tus constancias de capacitación</div>
        <form class="pnl-search" action="{{ route('customers.certificates') }}" method="GET" role="search">
            @include('customers.includes.icon', ['name' => 'search'])
            {{-- Hidden: sin esto, buscar desde una pestaña de estado filtrada
                 perdía ese filtro (un form GET descarta el query string del
                 "action"), igual que el hidden de $condition en Mis pedidos. --}}
            @if($state)<input type="hidden" name="state" value="{{ $state }}">@endif
            <input type="search" name="search" placeholder="Curso o Nº de certificado…" autocomplete="off"
                   value="{{ $searchKey ?? '' }}" aria-label="Buscar certificado">
        </form>
    </div>
    <div class="pnl-gap"></div>

    @if($counts['todos'] > 0)
        <div class="pnl-filter" id="ctFilter" role="group" aria-label="Filtrar certificados">
            @foreach(['todos' => 'Todos', 'valid' => 'Vigentes', 'soon' => 'Por vencer', 'expired' => 'Vencidos'] as $key => $label)
                <a href="{{ $key === 'todos' ? route('customers.certificates', array_filter(['search' => $searchKey])) : route('customers.certificates', array_filter(['state' => $key, 'search' => $searchKey])) }}"
                   class="{{ ($state ?: 'todos') === $key ? 'active' : '' }}">
                    {{ $label }}<span class="cnt">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>
        <div class="pnl-gap"></div>
    @endif

    @if($searchKey)
        <div class="od-searching">
            Resultados para <b>{{ $searchKey }}</b>
            <a href="{{ route('customers.certificates') }}">Quitar búsqueda</a>
        </div>
    @endif

    <div class="pnl-empty">
        <span class="ic">@include('customers.includes.icon', ['name' => 'award'])</span>
        @if($searchKey)
            <h3>Ningún certificado coincide con «{{ $searchKey }}»</h3>
            <p>Revisa el nombre del curso o el número, o quita la búsqueda para ver todo tu historial.</p>
            <a href="{{ route('customers.certificates') }}">Ver todos los certificados</a>
        @elseif($state)
            <h3>No tienes certificados en este estado</h3>
            <p>Prueba con otra pestaña para ver el resto de tus certificados.</p>
            <a href="{{ route('customers.certificates') }}">Ver todos los certificados</a>
        @else
            <h3>Aún no tienes certificados</h3>
            <p>El certificado se emite automáticamente cuando completas un curso y apruebas su examen final. Continúa tus cursos para obtener el primero.</p>
            <a href="{{ route('customers.courses') }}">Ver mis cursos</a>
        @endif
    </div>

@else

    {{-- Subtítulo, alerta de vencimiento, filtro por estado, grupos por año
         y el pie con el paginador viven en la misma caja -- antes eran
         piezas sueltas con espacio entre ellas. --}}
    <div class="ct-table">
        <div class="ct-head">
            <div class="sub">Descarga los certificados de tus cursos aprobados</div>
            <form class="pnl-search" action="{{ route('customers.certificates') }}" method="GET" role="search">
                @include('customers.includes.icon', ['name' => 'search'])
                @if($state)<input type="hidden" name="state" value="{{ $state }}">@endif
                <input type="search" name="search" placeholder="Curso o Nº de certificado…" autocomplete="off"
                       value="{{ $searchKey ?? '' }}" aria-label="Buscar certificado">
            </form>
        </div>

        <div class="pnl-filter pnl-filter-flat" id="ctFilter" role="group" aria-label="Filtrar certificados">
            @foreach(['todos' => 'Todos', 'valid' => 'Vigentes', 'soon' => 'Por vencer', 'expired' => 'Vencidos'] as $key => $label)
                <a href="{{ $key === 'todos' ? route('customers.certificates', array_filter(['search' => $searchKey])) : route('customers.certificates', array_filter(['state' => $key, 'search' => $searchKey])) }}"
                   class="{{ ($state ?: 'todos') === $key ? 'active' : '' }}">
                    {{ $label }}<span class="cnt">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>

        @if($searchKey)
            <div class="od-searching od-searching-inline">
                Resultados para <b>{{ $searchKey }}</b>
                <a href="{{ route('customers.certificates') }}">Quitar búsqueda</a>
            </div>
        @endif

        <div class="ct-body-wrap">
            @foreach($groups as $year => $yearItems)
                <div class="ct-year">
                    <b>{{ $year }}</b>
                    <span>{{ $yearItems->count() }} {{ Str::plural('certificado', $yearItems->count()) }}</span>
                    <div class="line"></div>
                </div>

                <div class="ct-grid">
                    @foreach($yearItems as $item)
                        @php
                            $certificate = $item->certificate;
                            $course = $certificate->course;
                            $itemState = $item->state;
                            $courseSlack = optional($course)->slack;
                            $needsRenewal = in_array($itemState, ['soon', 'expired'], true);
                        @endphp

                        <article class="ct-card st-{{ $itemState }}">
                            {{-- Miniatura del diploma: reconocer el certificado de un
                                 vistazo es más rápido que leer una fila de tabla. --}}
                            <div class="ct-thumb">
                                <div class="sheet">
                                    <span class="mark">@include('customers.includes.icon', ['name' => 'cap'])</span>
                                    <span class="kicker">Certificado de aprobación</span>
                                    <span class="name">{{ \Illuminate\Support\Str::limit(optional($course)->title ?? 'Curso', 46) }}</span>
                                    <span class="rule"></span>
                                    <span class="who">{{ $user->firstname }} {{ $user->lastname }}</span>
                                </div>
                            </div>

                            <div class="ct-body">
                                <div class="ct-top">
                                    <span class="chip">
                                        {{ $labels[$itemState] }}
                                        @if($itemState === 'soon') · {{ $item->daysLeft }} días @endif
                                    </span>
                                    <span class="code">Nº {{ \Illuminate\Support\Str::upper($certificate->slack) }}</span>
                                </div>

                                <h3>{{ optional($course)->title ?? 'Curso' }}</h3>

                                <div class="ct-dates">
                                    <div>
                                        <span>Emitido</span>
                                        <b>{{ $certificate->start_at ? \Carbon\Carbon::parse($certificate->start_at)->locale('es')->isoFormat('D MMM YYYY') : '—' }}</b>
                                    </div>
                                    <div>
                                        <span>Vence</span>
                                        <b>{{ $item->end ? $item->end->locale('es')->isoFormat('D MMM YYYY') : 'Sin caducidad' }}</b>
                                    </div>
                                </div>

                                <div class="ct-actions">
                                    <a class="solid" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                                        Descargar
                                    </a>
                                    <a class="ghost" href="{{ route('customers.certificate.view', $certificate->slack) }}" target="_blank" aria-label="Ver certificado">
                                        @include('customers.includes.icon', ['name' => 'search'])
                                    </a>
                                    @if($needsRenewal && $courseSlack)
                                        <a class="ghost" href="{{ route('checkout', ['course', $courseSlack]) }}" aria-label="Renovar certificado">
                                            @include('customers.includes.icon', ['name' => 'refresh'])
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="ct-foot">
            <span>Mostrando {{ $certificates->firstItem() }}-{{ $certificates->lastItem() }} de {{ $certificates->total() }} resultados</span>
            <nav>{{ $certificates->appends(request()->input())->links() }}</nav>
        </div>
    </div>

@endif
