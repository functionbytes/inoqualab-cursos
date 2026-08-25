@extends('layouts.customers')

@section('title', 'Mis cursos')

@push('css')
<link rel="stylesheet" href="{{ asset('customers/css/aula.css') }}">
@endpush

@php
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
    })->sortBy(fn ($i) => $i->inscription->id)->values();

    $labels = [
        'expired' => 'Vencido',
        'done' => 'Completado',
        'progress' => 'En progreso',
        'pending' => 'Sin iniciar',
    ];

    $counts = [
        'todos' => $items->count(),
        'progress' => $items->where('status', 'progress')->count(),
        'done' => $items->where('status', 'done')->count(),
        'expired' => $items->where('status', 'expired')->count(),
    ];

    $conAcceso = $items->where('status', '!=', 'expired')->count();

    $promedio = $items->count() > 0
        ? (int) round($items->avg('progress'))
        : 0;
@endphp

@section('content')
<section class="pnl-section" id="sec-cursos">

    @if($courses->isEmpty())

        <div class="pnl-card pnl-head">
            <h2>Mi expediente formativo</h2>
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

    <div class="cx-wrap">

        <div class="cx-main">
            <div class="cx-head">
                <div>
                    <h2>Mi expediente formativo</h2>
                    <div class="sub">
                        {{ $counts['todos'] }} {{ $counts['todos'] === 1 ? 'capacitación' : 'capacitaciones' }} ·
                        {{ $conAcceso }} con acceso vigente
                    </div>
                </div>
                <div class="cx-seg" id="cursosFilter" role="group" aria-label="Filtrar mis cursos">
                    @foreach(['todos' => 'Todos', 'progress' => 'En progreso', 'done' => 'Completados', 'expired' => 'Vencidos'] as $key => $label)
                        <button type="button" class="{{ $key === 'todos' ? 'active' : '' }}" data-f="{{ $key }}"
                                aria-pressed="{{ $key === 'todos' ? 'true' : 'false' }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="cx-cols">
                <span>Capacitación</span>
                <span>Progreso</span>
                <span>Estado</span>
                <span></span>
            </div>

            <div class="cx-list" id="cursosCards">
                @foreach($items as $i => $item)
                    @php
                        $inscription = $item->inscription;
                        $progress = $item->progress;
                        $status = $item->status;

                        $course   = $inscription->course;
                        $category = $course?->categorie?->title ?? 'Curso';
                        $certificate = $status === 'done' ? $inscription->certificate : null;
                    @endphp

                    <div class="cx-row st-{{ $status }}" data-status="{{ $status }}">
                        <div class="cx-course">
                            <span class="num">{{ $i + 1 }}</span>
                            <div class="txt">
                                <b>{{ $course?->title ?? 'Curso' }}</b>
                                <span>
                                    {{ $category }}
                                    @if($status === 'expired')
                                        · acceso vencido{{ $inscription->finished ? ' el '.\Carbon\Carbon::parse($inscription->finished)->locale('es')->isoFormat('D MMM YYYY') : '' }}
                                    @elseif($status === 'done')
                                        · completado
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="cx-prog">
                            <div class="bar"><i style="--p:{{ $progress }}%"></i></div>
                            <div class="sub">{{ $progress }}% completado</div>
                        </div>

                        <div><span class="cx-chip">{{ $labels[$status] }}</span></div>

                        <div class="cx-act">
                            @if($status === 'done' && $certificate)
                                <a class="ghost" href="{{ route('customers.certificate.download', $certificate->slack) }}" target="_blank">Certificado</a>
                            @elseif($status === 'expired')
                                <a class="ghost" href="{{ route('checkout', ['course', $course->slack]) }}">Renovar</a>
                            @else
                                <a class="solid" href="{{ route('customers.courses.content', $inscription->slack) }}">{{ $progress > 0 ? 'Continuar' : 'Comenzar' }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach

                <div class="cx-none" id="cursosVacio" hidden>
                    No hay cursos en este estado. Cambia de pestaña para ver el resto de tu expediente.
                </div>
            </div>
        </div>

        {{-- Panel lateral: estado global y la acción que resuelve el problema
             más frecuente (accesos vencidos), sin tener que recorrer la lista. --}}
        <aside class="cx-side">
            <div class="cx-panel">
                <div class="eyebrow">Estado global</div>
                <div class="big">{{ $promedio }}<span>%</span></div>
                <div class="bar"><i style="--p:{{ $promedio }}%"></i></div>
                <div class="figs">
                    <div><b>{{ $conAcceso }}</b><span>Con acceso</span></div>
                    <div><b>{{ $counts['done'] }}</b><span>Completados</span></div>
                    <div><b class="warn">{{ $counts['expired'] }}</b><span>Vencidos</span></div>
                </div>
            </div>

            @if($counts['expired'] > 0)
                <div class="cx-warn">
                    <div class="top">
                        <span class="ic">@include('customers.includes.icon', ['name' => 'warning'])</span>
                        <h4>{{ $counts['expired'] }} {{ $counts['expired'] === 1 ? 'acceso vencido' : 'accesos vencidos' }}</h4>
                    </div>
                    <p>Al renovar conservas el progreso que ya tenías registrado en cada curso.</p>
                    <button type="button" class="cx-filter-expired">Ver los vencidos</button>
                </div>
            @endif

            <div class="cx-links">
                <h4>Atajos</h4>
                <a href="{{ route('customers.certificates') }}">
                    @include('customers.includes.icon', ['name' => 'award']) Mis certificados
                </a>
                <a href="{{ route('customers.orders') }}">
                    @include('customers.includes.icon', ['name' => 'receipt']) Pedidos y facturas
                </a>
                <a href="{{ route('home') }}">
                    @include('customers.includes.icon', ['name' => 'cap']) Explorar catálogo
                </a>
            </div>
        </aside>

    </div>

    @endif

</section>
@endsection

@push('scripts')
<script>
    $(function () {
        function aplicar(f) {
            var visibles = 0;

            $('#cursosCards .cx-row').each(function () {
                var ver = (f === 'todos') || ($(this).data('status') === f);
                $(this).prop('hidden', !ver);
                if (ver) { visibles++; }
            });

            $('#cursosVacio').prop('hidden', visibles !== 0);
        }

        $('#cursosFilter').on('click', 'button', function () {
            var $btn = $(this);
            $btn.siblings().removeClass('active').attr('aria-pressed', 'false');
            $btn.addClass('active').attr('aria-pressed', 'true');
            aplicar($btn.data('f'));
        });

        // El aviso del panel lateral filtra la lista en vez de llevar a otra página.
        $('.cx-filter-expired').on('click', function () {
            $('#cursosFilter button[data-f="expired"]').trigger('click');
        });
    });
</script>
@endpush
