@extends('layouts.customers')

@section('title', 'Mis cursos')

@push('css')
<link rel="stylesheet" href="{{ asset('customers/css/aula.css') }}">
@endpush

@section('content')
<section class="pnl-section" id="sec-cursos">

    <div class="pnl-card pnl-head">
        <h2>Mis cursos</h2>
        <div class="sub">Gestiona y continúa tus capacitaciones.</div>
    </div>

    <div style="height:22px"></div>

    @if($courses->isEmpty())

        <div class="pnl-card">
            <div class="doc-empty">
                <i class="fa-solid fa-folder-open" style="font-size:30px;display:block;margin-bottom:12px;"></i>
                Aún no tienes cursos asignados. Cuando te inscribas en una capacitación aparecerá aquí.
            </div>
        </div>

    @else

        <div class="pnl-filter" id="cursosFilter" role="group" aria-label="Filtrar mis cursos">
            <button type="button" class="active" data-f="todos" aria-pressed="true">Todos</button>
            <button type="button" data-f="progress" aria-pressed="false">En progreso</button>
            <button type="button" data-f="pending" aria-pressed="false">Pendientes</button>
            <button type="button" data-f="done" aria-pressed="false">Completados</button>
            <button type="button" data-f="expired" aria-pressed="false">Vencidos</button>
        </div>

        <div class="pc-grid" id="cursosCards">
            @foreach($courses as $inscription)
                @php
                    $progress = min(100, max(0, (int) round($inscription->percent)));

                    if ($inscription->expire == 1) {
                        $status = 'expired';
                        $statusLabel = 'Expirado';
                    } elseif ($progress >= 100) {
                        $status = 'done';
                        $statusLabel = 'Completado';
                    } elseif ($progress > 0) {
                        $status = 'progress';
                        $statusLabel = 'En progreso';
                    } else {
                        $status = 'pending';
                        $statusLabel = 'Pendiente';
                    }

                    $course   = $inscription->course;
                    $category = $course?->categorie?->title ?? 'Curso';
                    $year     = optional($inscription->created_at)->format('Y') ?? date('Y');
                    $thumb    = $course?->getFirstMedia('thumbnail')?->getFullUrl() ?? '/pages/images/courses/default.jpg';

                    $contentUrl  = route('customers.courses.content', $inscription->slack);
                    $certificate = $status === 'done' ? $inscription->certificate : null;
                @endphp

                <div class="pc-card" data-status="{{ $status }}">
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

                        @if($status === 'done' && $certificate)
                            <a class="pc-btn ghost" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">
                                <i class="fa-solid fa-download"></i> Ver certificado
                            </a>
                        @elseif($status === 'expired')
                            <a class="pc-btn ghost" href="{{ route('checkout', ['course', $course->slack]) }}">
                                <i class="fa-solid fa-rotate"></i> Renovar acceso
                            </a>
                        @else
                            <a class="pc-btn" href="{{ $contentUrl }}">
                                <i class="fa-solid fa-play"></i> {{ $progress > 0 ? 'Continuar' : 'Comenzar curso' }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    @endif

</section>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#cursosFilter').on('click', 'button', function () {
            var $btn = $(this);
            $btn.siblings().removeClass('active').attr('aria-pressed', 'false');
            $btn.addClass('active').attr('aria-pressed', 'true');

            var f = $btn.data('f');
            $('#cursosCards .pc-card').each(function () {
                var match = (f === 'todos') || ($(this).data('status') === f);
                $(this).prop('hidden', !match);
            });
        });
    });
</script>
@endpush
