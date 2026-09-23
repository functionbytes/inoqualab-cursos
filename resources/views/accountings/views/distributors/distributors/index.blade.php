@extends('layouts.managers')

@section('page_header')
    @include('accountings.includes.card', ['title' => 'Distribuidores'])
@endsection

@section('content')

  <div class="widget-content searchable-container list">

    <div class="card">

      {{-- Search + Filtros --}}
      <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($available ?? '') !== '' && ($available ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Estado: ' . ($available == '1' ? 'Publico' : 'Oculto'),
                    'clear_url' => route('accounting.distributors', array_filter(['search' => $searchKey ?? ''])),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('accounting.distributors') }}" id="searchForm">

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
                'searchPlaceholder' => 'Buscar por nombre...',
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
              <th>Titulo</th>
              <th class="text-center">Estado</th>
              <th>Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>

            @foreach ($distributors as $distributor)
              <tr>
                <td>
                  <div class="fw-semibold">{{ Str::words(Str::upper(Str::lower($distributor->title)), 12, '...') }}</div>
                </td>
                <td class="text-center">
                  <span class="badge {{ $distributor->available == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                    {{ $distributor->available == 1 ? 'Publico' : 'Oculto' }}
                  </span>
                </td>
                <td>
                  <span class="text-muted">{{ $distributor->updated_at->format('d/m/Y') }}</span>
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
                        <a class="dropdown-item" href="{{ route('accounting.distributors.invoices', $distributor->slack) }}">Facturas</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('accounting.distributors.orders', $distributor->slack) }}">Ordenes</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('accounting.distributors.enterprises', $distributor->slack) }}">Empresas</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('accounting.distributors.view', $distributor->slack) }}">Visualizar</a>
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
          'paginator' => $distributors,
          'itemLabel' => 'distribuidores',
      ])
    </div>
  </div>

@endsection

@push('scripts')
    <script src="{{ asset('accountings/js/views/distributors/distributors/index.js') }}"></script>
@endpush
