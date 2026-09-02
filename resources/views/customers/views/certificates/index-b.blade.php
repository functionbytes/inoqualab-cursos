@extends('layouts.customers')

@section('title', 'Mis credenciales')

@section('context-title', 'Mis credenciales')
@section('context-icon')@include('customers.includes.icon', ['name' => 'award'])@endsection
@section('context-subtitle', 'Descarga los certificados de tus cursos aprobados')
@section('context-stat-number', $certificates->total())
@section('context-stat-label', Str::plural('certificado', $certificates->total()))

@php
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
    })->values();

    $labels = [
        'valid' => 'Vigente',
        'soon' => 'Por vencer',
        'expired' => 'Vencido',
        'none' => 'Sin fecha',
    ];

    // El primero se muestra en el panel de previsualización al abrir la página.
    $first = $items->first();
@endphp

@section('content')
<section class="pnl-section">

    <div class="cd-head">
        {{-- El título ya lo muestra la banda de contexto del header. --}}
        <div class="sub">Selecciona una credencial para previsualizarla y descargarla</div>
    </div>

    @if($items->isEmpty())

        <div class="pnl-empty">
            <span class="ic">@include('customers.includes.icon', ['name' => 'award'])</span>
            <h3>Aún no tienes credenciales</h3>
            <p>El certificado se emite automáticamente cuando completas un curso y apruebas su examen final.</p>
            <a href="{{ route('customers.courses') }}">Ver mis cursos</a>
        </div>

    @else

    <div class="cd-wrap">

        <div class="cd-list">
            <div class="cd-list-head">
                <span>{{ $items->count() }} {{ $items->count() === 1 ? 'credencial' : 'credenciales' }}</span>
                <span class="muted">Más reciente primero</span>
            </div>

            @foreach($items as $i => $item)
                @php
                    $certificate = $item->certificate;
                    $course = $certificate->course;
                @endphp
                <button type="button" class="cd-row st-{{ $item->state }} {{ $i === 0 ? 'is-active' : '' }}"
                        data-idx="{{ $i }}"
                        data-title="{{ optional($course)->title ?? 'Curso' }}"
                        data-code="{{ \Illuminate\Support\Str::upper($certificate->slack) }}"
                        data-issued="{{ $certificate->start_at ? \Carbon\Carbon::parse($certificate->start_at)->locale('es')->isoFormat('D MMM YYYY') : '—' }}"
                        data-expires="{{ $item->end ? $item->end->locale('es')->isoFormat('D MMM YYYY') : 'Sin caducidad' }}"
                        data-state="{{ $labels[$item->state] }}"
                        data-download="{{ route('customers.certificate.download', $certificate->slack) }}"
                        data-view="{{ route('customers.certificate.view', $certificate->slack) }}">
                    <span class="ic">@include('customers.includes.icon', ['name' => 'award'])</span>
                    <span class="txt">
                        <b>{{ optional($course)->title ?? 'Curso' }}</b>
                        <span>Certificado de curso · {{ \Illuminate\Support\Str::upper($certificate->slack) }}</span>
                    </span>
                    <span class="meta">
                        <b>{{ $labels[$item->state] }}</b>
                        <span>{{ $item->end ? 'hasta '.$item->end->locale('es')->isoFormat('MMM YYYY') : 'sin caducidad' }}</span>
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Previsualización: el diploma se ve sin salir de la página ni abrir
             el PDF, que es lo que obligaba a hacer la tabla anterior. --}}
        <aside class="cd-side">
            <div class="cd-preview">
                <div class="sheet">
                    <span class="mark">@include('customers.includes.icon', ['name' => 'cap'])</span>
                    <span class="kicker">Certificado de aprobación</span>
                    <span class="lead">Se certifica que</span>
                    <span class="who">{{ $user->firstname }} {{ $user->lastname }}</span>
                    <span class="lead">ha aprobado satisfactoriamente</span>
                    <span class="name" id="cdName">{{ optional(optional($first)->certificate->course)->title ?? 'Curso' }}</span>
                    <span class="rule"></span>
                    <span class="foot">
                        <span><i>Emitido</i><b id="cdIssued">—</b></span>
                        <span class="r"><i>Código</i><b id="cdCode">—</b></span>
                    </span>
                </div>
            </div>

            <div class="cd-buttons">
                <a class="solid" id="cdDownload" href="#" target="_blank">Descargar PDF</a>
                <a class="ghost" id="cdView" href="#" target="_blank">Ver</a>
            </div>

            <div class="cd-note">
                <div class="top">
                    @include('customers.includes.icon', ['name' => 'shield-check'])
                    <b>Verificable con su código</b>
                </div>
                <p>Tu empleador puede comprobar la validez del certificado con el código <b id="cdCode2">—</b> sin necesidad de que le envíes el archivo.</p>
            </div>
        </aside>

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

@push('scripts')
<script>
    $(function () {
        function pintar($row) {
            $('#cdName').text($row.data('title'));
            $('#cdIssued').text($row.data('issued'));
            $('#cdCode').text($row.data('code'));
            $('#cdCode2').text($row.data('code'));
            $('#cdDownload').attr('href', $row.data('download'));
            $('#cdView').attr('href', $row.data('view'));
        }

        $('.cd-row').on('click', function () {
            var $row = $(this);
            $('.cd-row').removeClass('is-active');
            $row.addClass('is-active');
            pintar($row);
        });

        // Estado inicial: la primera credencial de la lista.
        var $primera = $('.cd-row').first();
        if ($primera.length) { pintar($primera); }
    });
</script>
@endpush
