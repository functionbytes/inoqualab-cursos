{{--
    Partial AJAX: tabla + paginacion de miembros de una lista de newsletter.

    Se incluye normalmente desde members.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al paginar. Ver
    public/managers/js/ajax-table.js.
--}}
<div class="card">

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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-users', 48) !!}</div>
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

    @include('managers.includes.pagination-footer', [
        'paginator' => $members,
        'itemLabel' => 'miembros',
    ])

</div>
