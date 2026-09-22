@extends('layouts.managers')

@section('page_header')
    @include('distributors.includes.card', ['title' => 'Empresas'])
@endsection

@section('content')

  <div class="widget-content searchable-container list"
       data-bulk-url="{{ route('distributor.enterprises.bulk-action') }}"
       data-bulk-entity-label="empresa(s)">

    <div class="card card-body">
      <div class="row">
        <div class="col-md-12 col-xl-12">
          <form class="position-relative form-search" action="{{ Request::fullUrl() }}" method="GET">
            <div class="row justify-content-between g-2 ">
              <div class="col-auto flex-grow-1">
                <div class="tt-search-box">
                  <div class="input-group">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i class="fas fa-magnifying-glass"></i></span>
                    <input class="form-control rounded-start w-100 ps-5" type="text" id="search" name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}" @endisset>
                  </div>
                </div>
              </div>
              <div class="col-auto">
                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                  <i class="fa-duotone fa-magnifying-glass"></i>
                </button>
              </div>
              <div class="col-auto">
                <a href=" {{ route('distributor.enterprises.create') }}" class="btn btn-primary">
                  <i class="fa-duotone fa-plus"></i>
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="card card-body">
      <div class="table-responsive">
        <table class="table search-table align-middle text-nowrap">
          <thead class="header-item">
          <tr>
            <th scope="col" class="col-checkbox"><input type="checkbox" class="form-check-input" id="select-all"></th>
            <th scope="col">Titulo</th>
            <th scope="col">Estado</th>
            <th scope="col">Fecha</th>
            <th scope="col">Acciones</th>
          </tr>
          </thead>
          <tbody>

          @foreach ($enterprises as $key => $enterprise)

            <tr class="search-items">

              <td>
                <input type="checkbox" class="form-check-input bulk-checkbox" value="{{ $enterprise->id }}">
              </td>
              <td>
                <span class="usr-email-addr" data-email="{{ Str::lower($enterprise->title)  }}">{{ Str::words( Str::upper(Str::lower($enterprise->title)), 12, '...')  }}</span>
              </td>

              <td>
                 <span class="badge {{ $enterprise->available == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                   {{ $enterprise->available == 1 ? 'Publico' : 'Oculto' }}
                 </span>
              </td>
              <td>
                <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($enterprise->updated_at)) }}">{{ date('Y-m-d', strtotime($enterprise->updated_at)) }}</span>
              </td>
              <td class="text-left">
                <div class="dropdown dropstart">
                  <a href="#" class="text-muted" id="dropdownMenuButton-{{ $loop->index }}" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-ellipsis-vertical fs-5"></i>
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-{{ $loop->index }}">
                    <li>
                      <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('distributor.enterprises.navegation', $enterprise->slack) }}">Dashboard</a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('distributor.enterprises.edit', $enterprise->slack) }}">Editar</a>
                    </li>

                  </ul>
                </div>
              </td>
            </tr>
          @endforeach

          </tbody>
        </table>
      </div>
      <div class="result-body ">
        <span>Mostrar {{ $enterprises->firstItem() }}-{{ $enterprises->lastItem() }} de {{ $enterprises->total() }} resultados</span>
        <nav>
          {{ $enterprises->appends(request()->input())->links() }}
        </nav>
      </div>
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


