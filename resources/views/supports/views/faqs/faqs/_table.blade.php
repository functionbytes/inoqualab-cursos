<div class="card">

    {{-- Stats --}}
    <div class="card-body border-bottom">
        <div class="row g-3">
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Total</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['total']) }}</h4>
                        <span class="text-muted">Preguntas configuradas</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Publicadas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['public']) }}</h4>
                        <span class="text-muted">Visibles</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="card bg-light-secondary h-100">
                    <div class="card-body">
                        <h6 class="card-title mb-2">Ocultas</h6>
                        <h4 class="mb-1 fw-bold">{{ number_format($stats['hidden']) }}</h4>
                        <span class="text-muted">No visibles</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + Filtros --}}
    <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($available ?? '') !== '') {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($available === '1' ? 'Publico' : 'Oculto'),
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('available')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('support.faqs') }}" id="searchForm">

            <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Available" value="" {{ ($available ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Available" value="1" {{ ($available ?? '') === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Publico</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_Available" value="0" {{ ($available ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Oculto</span>
                    </label>
                </div>
            </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por título...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
    </div>

    {{-- Tabla --}}
    <div class="card-body">
        @if($faqs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="col-checkbox">
                                <input type="checkbox" class="form-check-input" id="select-all">
                            </th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($faqs as $faq)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bulk-checkbox"
                                           value="{{ $faq->id }}">
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ Str::words($faq->title, 8, '...') }}</div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $faq->categorie->title ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    @if($faq->available == 1)
                                        <span class="badge bg-success-subtle text-success">Publicada</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Oculta</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="text-muted">{{ $faq->updated_at->format('d/m/Y') }}</span>
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
                                                   href="{{ route('support.faqs.edit', $faq->slack) }}">
                                                    Editar
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item confirm-delete" href="#"
                                                   data-href="{{ route('support.faqs.destroy', $faq->slack) }}">
                                                    Eliminar
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
                <div class="mb-3 text-muted opacity-50">{!! \App\Html\IconHelper::render('empty-question', 48) !!}</div>
                <h5 class="fw-bold mb-2">
                    @if($searchKey || ($available ?? '') !== '')
                        No se encontraron resultados
                    @else
                        No hay preguntas frecuentes
                    @endif
                </h5>
                <p class="text-muted mb-4">
                    @if($searchKey || ($available ?? '') !== '')
                        No hay preguntas que coincidan con los filtros aplicados.
                    @else
                        Crea la primera pregunta frecuente.
                    @endif
                </p>
                @if($searchKey || ($available ?? '') !== '')
                    <a href="{{ route('support.faqs') }}" class="btn btn-outline-secondary">
                        Ver todas
                    </a>
                @else
                    <a href="{{ route('support.faqs.create') }}" class="btn btn-primary">
                        Nueva pregunta
                    </a>
                @endif
            </div>
        @endif
    </div>

    @include('managers.includes.pagination-footer', [
        'paginator' => $faqs,
        'itemLabel' => 'preguntas',
    ])

</div>
