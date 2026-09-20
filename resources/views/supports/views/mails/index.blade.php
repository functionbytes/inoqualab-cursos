@extends('layouts.managers')

@section('content')

    <div class="widget-content searchable-container list" id="mails-list"
         data-bulk-url="{{ route('support.mails.bulk-discard') }}"
         data-discard-url="{{ route('support.mails.discard', ':slack') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div>
                    <h5 class="mb-1 fw-bold">Correos entrantes</h5>
                    <p class="mb-0 text-muted">Correos interceptados por IMAP en espera de convertirse en órdenes</p>
                </div>
            </div>

            {{-- Stats / filtro por estado --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <a href="{{ route('support.mails.index') }}" class="text-decoration-none">
                            <div class="card h-100 {{ is_null($status) ? 'border border-primary' : '' }} bg-light-secondary">
                                <div class="card-body">
                                    <h6 class="card-title mb-2 text-dark">Total</h6>
                                    <h4 class="mb-1 fw-bold text-dark">{{ number_format($counts->sum()) }}</h4>
                                    <span class="text-muted">Todos los correos</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md">
                        <a href="{{ route('support.mails.index', ['status' => 'pending_review']) }}" class="text-decoration-none">
                            <div class="card h-100 {{ $status === 'pending_review' ? 'border border-primary' : '' }} bg-light-secondary">
                                <div class="card-body">
                                    <h6 class="card-title mb-2 text-dark">Pendientes</h6>
                                    <h4 class="mb-1 fw-bold text-dark">{{ number_format($counts->get('pending_review', 0)) }}</h4>
                                    <span class="text-muted">Por revisar</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md">
                        <a href="{{ route('support.mails.index', ['status' => 'processed']) }}" class="text-decoration-none">
                            <div class="card h-100 {{ $status === 'processed' ? 'border border-primary' : '' }} bg-light-secondary">
                                <div class="card-body">
                                    <h6 class="card-title mb-2 text-dark">Procesados</h6>
                                    <h4 class="mb-1 fw-bold text-dark">{{ number_format($counts->get('processed', 0)) }}</h4>
                                    <span class="text-muted">Convertidos en orden</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md">
                        <a href="{{ route('support.mails.index', ['status' => 'failed']) }}" class="text-decoration-none">
                            <div class="card h-100 {{ $status === 'failed' ? 'border border-primary' : '' }} bg-light-secondary">
                                <div class="card-body">
                                    <h6 class="card-title mb-2 text-dark">Fallidos</h6>
                                    <h4 class="mb-1 fw-bold text-dark">{{ number_format($counts->get('failed', 0)) }}</h4>
                                    <span class="text-muted">Con error de proceso</span>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-6 col-md">
                        <a href="{{ route('support.mails.index', ['status' => 'ignored']) }}" class="text-decoration-none">
                            <div class="card h-100 {{ $status === 'ignored' ? 'border border-primary' : '' }} bg-light-secondary">
                                <div class="card-body">
                                    <h6 class="card-title mb-2 text-dark">Ignorados</h6>
                                    <h4 class="mb-1 fw-bold text-dark">{{ number_format($counts->get('ignored', 0)) }}</h4>
                                    <span class="text-muted">Descartados</span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Buscador --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('support.mails.index') }}" id="searchForm">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-fill">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="search" name="search" class="form-control border-start-0 ps-0"
                                       placeholder="Buscar por remitente o asunto..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary flex-shrink-0">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($mails->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="col-checkbox">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Remitente</th>
                                    <th>Asunto</th>
                                    <th>Empresa</th>
                                    <th class="text-center">Confianza</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mails as $mail)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $mail->id }}">
                                        </td>
                                        <td>
                                            <span>{{ $mail->from }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ Str::limit($mail->subject, 60) }}</span>
                                        </td>
                                        <td>
                                            @if($mail->enterprise)
                                                <span>{{ $mail->enterprise->title }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(!is_null($mail->confidence_score))
                                                @php
                                                    $score = $mail->confidence_score;
                                                    $badgeClass = $score >= 90 ? 'bg-success-subtle text-success' : ($score >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ $score }}%</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $badgeMap = [
                                                    'pending_review' => ['class' => 'bg-warning-subtle text-warning', 'label' => 'Pendiente'],
                                                    'processed'      => ['class' => 'bg-success-subtle text-success', 'label' => 'Procesado'],
                                                    'failed'         => ['class' => 'bg-danger-subtle text-danger',   'label' => 'Fallido'],
                                                    'ignored'        => ['class' => 'bg-secondary-subtle text-secondary', 'label' => 'Ignorado'],
                                                ];
                                                $badge = $badgeMap[$mail->status] ?? ['class' => 'bg-secondary-subtle text-secondary', 'label' => $mail->status];
                                            @endphp
                                            <span class="badge {{ $badge['class'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($mail->received_at)->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('support.mails.show', $mail->slack) }}">Revisar</a>
                                                    </li>
                                                    @if($mail->status === 'processed' && $mail->order)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('support.users.orders.view', $mail->order->slack) }}">Ver orden</a>
                                                        </li>
                                                    @endif
                                                    @if($mail->status !== 'ignored')
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item discard-btn" href="#" data-slack="{{ $mail->slack }}">Descartar</a>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-envelope-open-text fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">Sin resultados</h5>
                        <p class="text-muted mb-0">No hay correos que coincidan con los filtros aplicados.</p>
                    </div>
                @endif
            </div>

            @if($mails->hasPages())
                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        Mostrando {{ $mails->firstItem() }}–{{ $mails->lastItem() }} de {{ $mails->total() }} correos
                    </span>
                    {{ $mails->appends(request()->input())->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Modal descartar --}}
    <div id="discard-modal" class="modal fade">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Descartar correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="display-4 text-warning"><i class="fa-duotone fa-triangle-exclamation"></i></div>
                    <h4 class="my-0">¿Descartar este correo?</h4>
                    <p>El correo será marcado como ignorado y no generará una orden.</p>
                    <div class="row justify-content-center mt-3">
                        <div class="col-sm-12 col-md-6">
                            <button type="button" id="discard-confirm-btn" class="btn btn-danger w-100 mb-2">Confirmar</button>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('managers.includes.bulk-toolbar-modal', [
        'bulkEntityLabel' => 'correo(s)',
        'bulkActions' => [
            ['value' => 'discard', 'label' => 'Descartar'],
        ],
    ])

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('supports/css/views/mails/index.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('supports/js/views/mails/index.js') }}"></script>
@endpush
