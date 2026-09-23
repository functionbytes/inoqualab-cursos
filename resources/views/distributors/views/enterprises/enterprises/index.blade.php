@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('distributor.enterprises.create') }}" class="btn btn-primary btn-icon" title="Nueva empresa" aria-label="Nueva empresa">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('distributors.includes.card', [
        'title' => 'Empresas',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

  <div class="widget-content searchable-container list"
       data-bulk-url="{{ route('distributor.enterprises.bulk-action') }}"
       data-bulk-entity-label="empresa(s)">

    <div class="card">

      {{-- Search + Filtros --}}
      <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($available ?? '') !== '' && ($available ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($available == '1' ? 'Publico' : 'Oculto'),
                    'clear_url' => route('distributor.enterprises', array_filter(['search' => $searchKey ?? ''])),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('distributor.enterprises') }}" id="searchForm">

            <input type="hidden" name="available" id="filterAvailable" value="{{ $available ?? '' }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_available" value="" {{ ($available ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_available" value="1" {{ ($available ?? '') === '1' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Publico</span>
                    </label>
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_available" value="0" {{ ($available ?? '') === '0' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Oculto</span>
                    </label>
                </div>
            </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por nombre, NIT o correo...',
                'popoverBody' => $popoverBody,
                'filterChips' => $filterChips,
            ])
        </form>
      </div>

      {{-- Tabla --}}
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle text-nowrap mb-0">
            <thead class="table-light">
            <tr>
              <th class="col-checkbox"><input type="checkbox" class="form-check-input" id="select-all"></th>
              <th>Titulo</th>
              <th class="text-center">Estado</th>
              <th>Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>

            @foreach ($enterprises as $enterprise)

              <tr>

                <td>
                  <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $enterprise->id }}">
                </td>
                <td>
                  <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($enterprise->title)), 12, '...') }}</div>
                </td>

                <td class="text-center">
                   <span class="badge {{ $enterprise->available == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                     {{ $enterprise->available == 1 ? 'Publico' : 'Oculto' }}
                   </span>
                </td>
                <td>
                  <span class="text-muted">{{ $enterprise->updated_at->format('d/m/Y') }}</span>
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
                        <a class="dropdown-item" href="{{ route('distributor.enterprises.navegation', $enterprise->slack) }}">Dashboard</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('distributor.enterprises.edit', $enterprise->slack) }}">Editar</a>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            @endforeach

            </tbody>
          </table>
        </div>
      </div>

      @include('managers.includes.pagination-footer', [
          'paginator' => $enterprises,
          'itemLabel' => 'empresas',
      ])
    </div>
  </div>

  @include('managers.includes.bulk-toolbar-modal', [
      'bulkEntityLabel' => 'empresa(s)',
      'bulkActions' => [
          ['value' => 'publish', 'label' => 'Publicar'],
          ['value' => 'hide', 'label' => 'Ocultar'],
          ['value' => 'delete', 'label' => 'Eliminar'],
      ],
  ])

@endsection

@push('css')
    <link rel="stylesheet" href="{{ asset('distributors/css/tables.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('distributors/js/enterprises/enterprises/index.js') }}"></script>
@endpush


