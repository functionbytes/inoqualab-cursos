@extends('layouts.managers')

@section('title', 'Automatizaciones de remarketing')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Automatizaciones de remarketing</h5>
                        <p class="small mb-0 text-muted">Correos de ciclo de vida programados y su actividad reciente.</p>
                    </div>
                    <div class="ms-auto">
                        <a href="{{ route('manager.newsletter.lists.index') }}" class="btn btn-outline-secondary">Listas</a>
                    </div>
                </div>
            </div>

            {{-- Tarjetas por automatización --}}
            <div class="card-body">
                <div class="row g-3">
                    @foreach($automations as $auto)
                        <div class="col-12 col-lg-4">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0">{{ $auto['label'] }}</h6>
                                        <span class="badge bg-light-secondary text-muted">{{ $auto['schedule'] }}</span>
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
            <div class="card-header border-top border-bottom bg-light">
                <h6 class="fw-bold mb-0">Actividad reciente</h6>
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
