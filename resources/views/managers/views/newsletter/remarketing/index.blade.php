@extends('layouts.managers')

@section('title', 'Automatizaciones de remarketing')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Automatizaciones de remarketing',
        'description' => 'Correos de ciclo de vida programados y su actividad reciente.',
    ])
@endsection

@section('content')

    @php
        $automationIcons = [
            'course_completed' => 'remarketing-crosssell',
            'certificate_expiring' => 'remarketing-certificate',
            'course_access_expiring' => 'remarketing-access',
        ];
    @endphp

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Tarjetas por automatización --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    @foreach($automations as $auto)
                        <div class="col-12 col-lg-4">
                            <div class="card h-100 border remarketing-auto-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="remarketing-auto-icon">
                                            {!! \App\Html\IconHelper::render($automationIcons[$auto['list_trigger']] ?? 'dot', 26) !!}
                                        </div>
                                        <div class="flex-fill">
                                            <h6 class="fw-bold mb-1">{{ $auto['label'] }}</h6>
                                            <span class="badge bg-light-secondary text-muted">{{ $auto['schedule'] }}</span>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-3">{{ $auto['description'] }}</p>

                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">En su lista</span>
                                        <span class="fw-semibold">{{ number_format($auto['list_size']) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Correos enviados (total)</span>
                                        <span class="fw-semibold">{{ number_format($auto['total_sent']) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="text-muted">Corridas</span>
                                        <span class="fw-semibold">{{ number_format($auto['total_runs']) }}</span>
                                    </div>

                                    @if($auto['last_run'])
                                        <div class="border-top pt-2">
                                            <div class="small text-muted">Última corrida</div>
                                            <div class="small">
                                                {{ $auto['last_run']->created_at->format('d/m/Y H:i') }}
                                                &middot; cohorte {{ optional($auto['last_run']->cohort_date)->format('d/m/Y') ?? '—' }}
                                            </div>
                                            <div class="small">
                                                <span class="badge bg-info-subtle text-info">{{ $auto['last_run']->found }} encontrados</span>
                                                <span class="badge bg-success-subtle text-success">{{ $auto['last_run']->sent }} enviados</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="border-top pt-2">
                                            <span class="small text-muted">Sin corridas registradas todavía.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Historial reciente --}}
            <div class="card-body border-bottom">
                <h6 class="fw-bold mb-1">Actividad reciente</h6>
                <p class="text-muted mb-0">Últimas corridas de los comandos programados</p>
            </div>
            <div class="card-body">
                @if($recentRuns->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Automatización</th>
                                    <th class="text-center">Cohorte</th>
                                    <th class="text-center">Encontrados</th>
                                    <th class="text-center">Enviados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRuns as $run)
                                    <tr>
                                        <td class="text-muted">{{ $run->created_at->format('d/m/Y H:i') }}</td>
                                        <td><code>{{ $run->command }}</code></td>
                                        <td class="text-center">{{ optional($run->cohort_date)->format('d/m/Y') ?? '—' }}</td>
                                        <td class="text-center">{{ number_format($run->found) }}</td>
                                        <td class="text-center fw-semibold">{{ number_format($run->sent) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-robot fa-2x mb-2 text-muted opacity-50"></i>
                        <p class="text-muted mb-0">Aún no hay corridas registradas. Aparecerán aquí cuando el scheduler ejecute los comandos.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/newsletter/remarketing/index.css') }}">
@endpush
