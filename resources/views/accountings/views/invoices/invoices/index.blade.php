@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <a href="{{ route('accounting.invoices.report') }}" class="btn btn-primary btn-icon" title="Reporte" aria-label="Reporte">
        <i class="fas fa-chart-bar"></i>
    </a>
    <a href="{{ route('accounting.invoices.create') }}" class="btn btn-primary btn-icon" title="Nueva factura" aria-label="Nueva factura">{!! \App\Html\IconHelper::render('plus') !!}</a>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('accountings.includes.card', [
        'title' => 'Facturación',
        'actions' => $headerActions,
    ])
@endsection

@section('content')

  <div class="widget-content searchable-container list">

    <div class="card">

      {{-- Search + Filtros --}}
      <div class="card-body border-bottom">
        @php
            $filterChips = [];
            if (($condition ?? '') !== '' && ($condition ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Estado: ' . optional($conditions->firstWhere('id', $condition))->title,
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('condition')),
                ];
            }
            if (($method ?? '') !== '' && ($method ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Metodo: ' . optional($methods->firstWhere('id', $method))->title,
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('methods')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('accounting.invoices') }}" id="searchForm">

            <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">
            <input type="hidden" name="methods" id="filterMethods" value="{{ $method ?? '' }}">

            @php ob_start(); @endphp
            <div class="filter-popover-field">
                <div class="filter-popover-label">Estado</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_condition" value="" {{ ($condition ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($conditions as $item)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_condition" value="{{ $item->id }}" {{ (string) ($condition ?? '') === (string) $item->id ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $item->title }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="filter-popover-field">
                <div class="filter-popover-label">Metodo de pago</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_methods" value="" {{ ($method ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($methods as $item)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_methods" value="{{ $item->id }}" {{ (string) ($method ?? '') === (string) $item->id ? 'checked' : '' }}>
                            <span class="filter-popover-dot"></span>
                            <span>{{ $item->title }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @php $popoverBody = trim(ob_get_clean()); @endphp

            @include('managers.includes.filter-toolbar', [
                'searchName' => 'search',
                'searchValue' => $searchKey ?? '',
                'searchPlaceholder' => 'Buscar por referencia...',
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
              <th>Factura</th>
              <th>Empresa</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Metodo de pago</th>
              <th>Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>

            @foreach ($invoices as $invoice)
              <tr>
                <td>
                  <div class="fw-semibold">{{ $invoice->reference }}</div>
                </td>
                <td>{{ $invoice->distributor->title ?? 'N/D' }}</td>
                <td class="text-center">
                  <span class="badge {{ $invoice->condition->badge_class }}">{{ $invoice->condition->title }}</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary-subtle text-secondary">{{ $invoice->method->title }}</span>
                </td>
                <td>
                  <span class="text-muted">{{ $invoice->updated_at->format('d/m/Y') }}</span>
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
                        <a class="dropdown-item" href="{{ route('accounting.invoices.edit', $invoice->slack) }}">Editar</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('accounting.invoices.view', $invoice->slack) }}">General</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('accounting.invoices.details', $invoice->slack) }}">Detallado</a>
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
          'paginator' => $invoices,
          'itemLabel' => 'facturas',
      ])
    </div>
  </div>

@endsection

@push('scripts')
    <script src="{{ asset('accountings/js/views/invoices/invoices/index.js') }}"></script>
@endpush
