<div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2">Total</h6>
                                <h4 class="mb-1 fw-bold">{{ number_format($total) }}</h4>
                                <span class="text-muted">Registros de auditoría</span>
                            </div>
                        </div>
                    </div>
                    @foreach($models as $key => $label)
                        <div class="col-6 col-md">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">{{ $label }}</h6>
                                    <h4 class="mb-1 fw-bold">{{ number_format($counts[$key] ?? 0) }}</h4>
                                    <span class="text-muted">Sobre {{ strtolower($label) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Filtro por modelo --}}
            <div class="card-body border-bottom">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="fw-semibold text-muted me-1">Filtrar por modelo:</span>
                    @foreach($models as $key => $label)
                        <a href="{{ Request::fullUrlWithQuery(['model' => $key]) }}"
                           class="btn btn-sm {{ $model == $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $label }}
                            @if(isset($counts[$key]))
                                <span class="badge bg-white text-dark ms-1">{{ $counts[$key] }}</span>
                            @endif
                        </a>
                    @endforeach
                    @if($model)
                        <a href="{{ Request::fullUrlWithQuery(['model' => null]) }}" class="btn btn-sm btn-light">
                            Todos
                        </a>
                    @endif
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descripción</th>
                                    <th class="text-center">Modelo</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activities as $activity)
                                    <tr>
                                        <td>
                                            <span>{{ $activity->description }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary">
                                                {{ $activity->subject_type ? class_basename($activity->subject_type) : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">{{ $activity->created_at->format('d/m/Y H:i') }}</span>
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
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.enterprises.activitys.view', $activity->id) }}">
                                                            Ver detalle
                                                        </a>
                                                    </li>
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
                        <i class="fas fa-clock-rotate-left fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">Sin actividades registradas</h5>
                        <p class="text-muted mb-0">No hay eventos de auditoría para este usuario{{ $model ? ' con el filtro aplicado' : '' }}.</p>
                    </div>
                @endif
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $activities,
                'itemLabel' => 'registros',
            ])

        </div>
