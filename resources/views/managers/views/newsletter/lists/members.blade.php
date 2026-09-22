@extends('layouts.managers')

@section('title', 'Suscriptores de la lista')

@section('page_header')
    @php ob_start(); @endphp
<a href="{{ route('manager.newsletter.lists.index') }}" class="btn btn-outline-secondary">
                            Volver a listas
                        </a>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-member-modal">
                            + Agregar suscriptor
                        </button>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => $list->name,
        'description' => number_format($members->total()) . ' suscriptor(es)'
            . ($list->trigger !== 'manual' ? ' · lista dinámica (se actualiza sola por eventos)' : ''),
        'actions' => $headerActions,
    ])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div id="ajax-table-root">
            @include('managers.views.newsletter.lists._members', ['list' => $list, 'members' => $members])
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
