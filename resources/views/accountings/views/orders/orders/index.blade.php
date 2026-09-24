@extends('layouts.managers')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones" aria-label="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('accounting.orders.resumen') }}">
                Resumen
            </a>
            <a class="dropdown-item" href="{{ route('accounting.orders.report') }}">
                Reporte
            </a>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('accountings.includes.card', [
        'title' => 'Ordenes',
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
            if (($type ?? '') !== '' && ($type ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Tipo: ' . optional($types->firstWhere('id', $type))->title,
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('type')),
                ];
            }
            if (($method ?? '') !== '' && ($method ?? null) !== null) {
                $filterChips[] = [
                    'label' => 'Metodo: ' . optional($methods->firstWhere('id', $method))->title,
                    'clear_url' => url()->current() . '?' . http_build_query(request()->except('methods')),
                ];
            }
        @endphp
        <form method="GET" action="{{ route('accounting.orders') }}" id="searchForm">

            <input type="hidden" name="condition" id="filterCondition" value="{{ $condition ?? '' }}">
            <input type="hidden" name="type" id="filterType" value="{{ $type ?? '' }}">
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
                <div class="filter-popover-label">Tipo</div>
                <div class="filter-popover-options">
                    <label class="filter-popover-option">
                        <input type="radio" data-filter-name="popover_type" value="" {{ ($type ?? '') === '' ? 'checked' : '' }}>
                        <span class="filter-popover-dot"></span>
                        <span>Todos</span>
                    </label>
                    @foreach($types as $item)
                        <label class="filter-popover-option">
                            <input type="radio" data-filter-name="popover_type" value="{{ $item->id }}" {{ (string) ($type ?? '') === (string) $item->id ? 'checked' : '' }}>
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
                'searchPlaceholder' => 'Buscar por orden, cliente o identificacion...',
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
              <th>Numero</th>
              <th>Cliente</th>
              <th class="text-center">Estado pago</th>
              <th class="text-center">Tipo pago</th>
              <th>Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>

            @foreach ($orders as $order)
              <tr>
                <td>{{ $order->reference }}</td>
                <td>
                  {{ Str::upper(($order->user->firstname ?? 'N/D') . ' ' . ($order->user->lastname ?? '')) }}
                </td>
                <td class="text-center">
                  <span class="badge {{ $order->condition->badge_class }}">{{ $order->condition->title }}</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary-subtle text-secondary">{{ $order->type->title }}</span>
                </td>
                <td>
                  <span class="text-muted">{{ $order->created_at->format('d/m/Y') }}</span>
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
                        <a class="dropdown-item" href="{{ route('accounting.orders.view', $order->slack) }}">Visualizar</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="{{ route('accounting.orders.edit', $order->slack) }}">Editar</a>
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
          'paginator' => $orders,
          'itemLabel' => 'ordenes',
      ])
    </div>
  </div>

@endsection

@push('scripts')
    <script src="{{ asset('accountings/js/views/orders/orders/index.js') }}"></script>
@endpush
