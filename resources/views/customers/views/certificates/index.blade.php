@extends('layouts.customers')

@section('title', 'Mis certificados')

@php
    // El estado se calcula una vez por certificado: la vista lo necesita para el
    // filtro de la cabecera, para el color de la tarjeta y para decidir si se
    // ofrece renovar.
    $items = $certificates->map(function ($certificate) {
        $end = $certificate->end_at ? \Carbon\Carbon::parse($certificate->end_at) : null;
        $daysLeft = $end
            ? (int) \Carbon\Carbon::now()->startOfDay()->diffInDays($end->copy()->startOfDay(), false)
            : null;

        if ($daysLeft === null) {
            $state = 'none';
        } elseif ($daysLeft < 0) {
            $state = 'expired';
        } elseif ($daysLeft <= 30) {
            $state = 'soon';
        } else {
            $state = 'valid';
        }

        return (object) [
            'certificate' => $certificate,
            'end' => $end,
            'daysLeft' => $daysLeft,
            'state' => $state,
        ];
    });

    $labels = [
        'valid' => 'Vigente',
        'soon' => 'Por vencer',
        'expired' => 'Vencido',
        'none' => 'Sin fecha',
    ];

    $counts = [
        'valid' => $items->where('state', 'valid')->count(),
        'soon' => $items->where('state', 'soon')->count(),
        'expired' => $items->where('state', 'expired')->count(),
    ];
@endphp

@section('content')
<section class="pnl-section">

    <div class="pnl-card pnl-head pnl-head-row">
        <div>
            <h2>Mis certificados</h2>
            <div class="sub">
                @if($certificates->total() > 0)
                    {{ $certificates->total() }} {{ $certificates->total() === 1 ? 'certificado emitido' : 'certificados emitidos' }}
                @else
                    Descarga y comparte tus constancias de capacitación
                @endif
            </div>
        </div>
        @if($counts['soon'] + $counts['expired'] > 0)
            <div class="ct-alert">
                @include('customers.includes.icon', ['name' => 'warning'])
                <span>
                    @if($counts['expired'] > 0)
                        {{ $counts['expired'] }} {{ $counts['expired'] === 1 ? 'vencido' : 'vencidos' }}
                    @endif
                    @if($counts['expired'] > 0 && $counts['soon'] > 0) · @endif
                    @if($counts['soon'] > 0)
                        {{ $counts['soon'] }} por vencer
                    @endif
                </span>
            </div>
        @endif
    </div>

    <div class="pnl-gap"></div>

    @if($items->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'award'])</span>
            <h3>Aún no tienes certificados</h3>
            <p>El certificado se emite automáticamente cuando completas un curso y apruebas su examen final. Continúa tus cursos para obtener el primero.</p>
            <a href="{{ route('customers.courses') }}">Ver mis cursos</a>
        </div>

    @else

        <div class="ct-grid">
            @foreach($items as $item)
                @php
                    $certificate = $item->certificate;
                    $course = $certificate->course;
                    $state = $item->state;
                    $courseSlack = optional($course)->slack;
                    $needsRenewal = in_array($state, ['soon', 'expired'], true);
                @endphp

                <article class="ct-card st-{{ $state }}">
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
                                {{ $labels[$state] }}
                                @if($state === 'soon') · {{ $item->daysLeft }} días @endif
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
                                @include('customers.includes.icon', ['name' => 'download']) Descargar
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

        @if($certificates->total() > 0)
            <div class="result-body">
                <span>Mostrando {{ $certificates->firstItem() }}-{{ $certificates->lastItem() }} de {{ $certificates->total() }} resultados</span>
                <nav>{{ $certificates->appends(request()->input())->links() }}</nav>
            </div>
        @endif

    @endif

</section>
@endsection
