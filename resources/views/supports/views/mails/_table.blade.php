<div class="card">

    {{-- Stats / filtro por estado --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <a href="{{ route('support.mails.index') }}" class="text-decoration-none">
                    <div class="card h-100 bg-light-secondary">
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
                    <div class="card h-100 bg-light-secondary">
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
                    <div class="card h-100 bg-light-secondary">
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
                    <div class="card h-100 bg-light-secondary">
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
                    <div class="card h-100 bg-light-secondary">
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

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => request('search') ?? '',
                'searchPlaceholder' => 'Buscar por remitente o asunto...',
            ])
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-inbox', 48) !!}</div>
                <h5 class="fw-bold mb-2">Sin resultados</h5>
                <p class="text-muted mb-0">No hay correos que coincidan con los filtros aplicados.</p>
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $mails,
        'itemLabel' => 'correos',
    ])

</div>
