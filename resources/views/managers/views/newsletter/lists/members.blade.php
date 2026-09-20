@extends('layouts.managers')

@section('title', 'Suscriptores de la lista')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $list->name }}</h5>
                        <p class="small mb-0 text-muted">
                            {{ number_format($members->total()) }} suscriptor(es)
                            @if($list->trigger !== 'manual')
                                · lista dinámica (se actualiza sola por eventos)
                            @endif
                        </p>
                    </div>
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('manager.newsletter.lists.index') }}" class="btn btn-outline-secondary">
                            Volver a listas
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-member-modal">
                            + Agregar suscriptor
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($members->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Email</th>
                                    <th>Nombre</th>
                                    <th>Motivo de alta</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($members as $member)
                                    <tr id="member-row-{{ $member->id }}">
                                        <td class="fw-semibold">{{ $member->email }}</td>
                                        <td>{{ $member->name ?: '—' }}</td>
                                        <td class="small text-muted">{{ $member->pivot->added_reason ?: '—' }}</td>
                                        <td class="text-center">
                                            @if($member->is_active)
                                                <span class="badge bg-success-subtle text-success">Suscrito</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">Baja</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 border-0 btn-remove-member"
                                                    data-url="{{ route('manager.newsletter.lists.members.remove', [$list->id, $member->id]) }}">
                                                Quitar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">Sin suscriptores</h5>
                        <p class="text-muted mb-0">
                            @if($list->trigger !== 'manual')
                                Esta lista se llenará sola cuando ocurra su evento (curso completado, certificado o acceso por vencer).
                            @else
                                Aún no hay suscriptores en esta lista.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            @if($members->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $members->links() }}
                </div>
            @endif

        </div>
    </div>

    {{-- Modal: agregar suscriptor --}}
    <div class="modal fade" id="add-member-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar suscriptor a "{{ $list->name }}"</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="memberEmail" placeholder="correo@ejemplo.com">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Nombre</label>
                        <input type="text" class="form-control" id="memberName" placeholder="Opcional">
                    </div>
                </div>
                <div class="modal-footer flex-column">
                    <button type="button" id="btnAddMember" class="btn btn-primary w-100 mb-2"
                            data-add-url="{{ route('manager.newsletter.lists.members.add', $list->id) }}">Agregar</button>
                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/newsletter/lists/members.js') }}"></script>
@endpush
