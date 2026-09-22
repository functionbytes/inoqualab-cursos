<div class="card">
        {{-- Info del usuario --}}
        <div class="card-body border-bottom py-3">
            <div class="alert bg-light border mb-0 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0 emails-user-avatar">
                    <span class="fw-bold text-primary">{{ strtoupper(substr($user->firstname, 0, 1)) }}</span>
                </div>
                <div class="flex-grow-1">
                    <strong>{{ $user->firstname }} {{ $user->lastname }}</strong>
                    <span class="text-muted ms-2 small">{{ $user->email }}</span>
                </div>
                <div>
                    <span class="badge bg-primary-subtle text-primary">{{ $logs->total() }} correo(s) en total</span>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        @if($logs->count() > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Asunto</th>
                                <th>Estado</th>
                                <th>Destinatario</th>
                                <th>Fecha de envío</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="ps-4">
                                        <p class="mb-0 fw-semibold text-truncate emails-subject-col"
                                           title="{{ $log->subject }}">
                                            {{ $log->subject }}
                                        </p>
                                    </td>
                                    <td>
                                        @if($log->status === 'sent')
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="fas fa-check me-1"></i>Enviado
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i class="fas fa-times me-1"></i>Fallido
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="small text-muted">{{ $log->recipient_email }}</code>
                                    </td>
                                    <td>
                                        <p class="text-muted">
                                            {{ $log->sent_at ? $log->sent_at->format('d/m/Y H:i') : $log->created_at->format('d/m/Y H:i') }}
                                        </p>
                                        <br>
                                        <p class="text-muted">
                                            {{ $log->sent_at ? $log->sent_at->diffForHumans() : $log->created_at->diffForHumans() }}
                                        </p>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('manager.users.emails.show', $log->id) }}"
                                           class="btn btn-sm btn-outline-primary" title="Ver contenido del correo">
                                            Vista previa
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $logs,
                'itemLabel' => 'correos',
            ])
        @else
            <div class="card-body text-center py-5">
                <i class="fas fa-envelope-open-text fa-3x mb-3 text-muted opacity-50"></i>
                <h5 class="fw-bold mb-2">Sin correos registrados</h5>
                <p class="text-muted mb-0">
                    Aún no se han enviado correos a este usuario.<br>
                    <small>El historial se genera automáticamente a partir del próximo envío.</small>
                </p>
            </div>
        @endif
    </div>
