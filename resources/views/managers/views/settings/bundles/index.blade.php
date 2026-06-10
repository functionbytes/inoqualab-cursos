@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Paquetes'])

    <div class="widget-content searchable-container list">
        
        <div class="card card-body">
            <div class="row">
                <div class="col-md-12 col-xl-12">
                    <form class="position-relative form-search" action="{{ Request::fullUrl() }}" method="GET">
                        <div class="row justify-content-between g-2 ">
                            <div class="col-auto flex-grow-1">
                                <div class="tt-search-box">
                                    <div class="input-group">
                                        <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i data-feather="search"></i></span>
                                        <input class="form-control rounded-start w-100" type="text" id="search" name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}" @endisset>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="input-group">
                                    <select class="form-select select2" name="available" data-minimum-results-for-search="Infinity">
                                        <option value="">Seleccionar estado</option>
                                        <option value="1" @isset($available) @if ($available==1) selected @endif @endisset>  Publico</option>
                                        <option value="0" @isset($available) @if ($available==0) selected  @endif @endisset>  Oculto</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Buscar">
                                    <i class="fa-duotone fa-magnifying-glass"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <a href=" {{ route('manager.bundles.create') }}" class="btn btn-primary">
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
                        <th>Titulo</th>
                        <th>Precio</th>
                        <th>Cursos</th>
                        <th>Estado</th>
                        <th>Vence</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                   
                    @foreach ($bundles as $key => $bundle)
                        <tr class="search-items">

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @php $thumb = $bundle->getFirstMedia('thumbnail'); @endphp
                                    @if ($thumb)
                                        <img src="{{ $thumb->getFullUrl() }}" alt="{{ $bundle->title }}"
                                             style="width:38px;height:38px;object-fit:cover;border-radius:6px;flex-shrink:0;">
                                    @else
                                        <div style="width:38px;height:38px;border-radius:6px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="fas fa-box-open text-muted" style="font-size:14px;"></i>
                                        </div>
                                    @endif
                                    <span class="usr-email-addr" data-email="{{ $bundle->title }}">
                                        {{ Str::words(Str::upper(Str::lower($bundle->title)), 12, '...') }}
                                    </span>
                                </div>
                            </td>

                            <td>${{ number_format($bundle->price, 0, ',', '.') }}</td>
                            <td>{{ $bundle->courses_count ?? $bundle->courses->count() }}</td>

                            <td>
                                <span class="badge {{ $bundle->available == 1 ? 'bg-light-primary text-primary' : 'bg-light-secondary text-secondary' }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">
                                    {{ $bundle->available == 1 ? 'Publico' : 'Oculto' }}
                                </span>
                            </td>
                            <td>
                                @if ($bundle->expire_at)
                                    @php $expired = \Carbon\Carbon::parse($bundle->expire_at)->isPast(); @endphp
                                    <span class="{{ $expired ? 'text-danger fw-semibold' : '' }}">
                                        {{ \Carbon\Carbon::parse($bundle->expire_at)->format('d/m/Y') }}
                                        @if ($expired) <small>(vencido)</small> @endif
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-left">
                                <div class="dropdown dropstart">
                                    <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-vertical"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3"
                                               href="{{ route('bundles.view', [$bundle->slug ?? $bundle->slack]) }}"
                                               target="_blank">Ver en sitio</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3" href="{{ route('manager.bundles.edit', $bundle->slack) }}">Editar</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3 btn-toggle-available"
                                               data-slack="{{ $bundle->slack }}"
                                               data-available="{{ $bundle->available }}">
                                                {{ $bundle->available ? 'Ocultar' : 'Publicar' }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3 confirm-delete" data-href="{{ route('manager.bundles.destroy', $bundle->slack) }}">Eliminar</a>
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
                <span>Mostrar {{ $bundles->firstItem() }}-{{ $bundles->lastItem() }} de {{ $bundles->total() }} resultados</span>
                <nav>
                    {{ $bundles->appends(request()->input())->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).on('click', '.btn-toggle-available', function () {
    var $btn = $(this);
    var slack = $btn.data('slack');

    $.ajax({
        url: "{{ route('manager.bundles.toggle') }}",
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: { slack: slack },
        success: function (res) {
            if (res.success) {
                toastr.success(res.message, 'Listo', { positionClass: 'toast-bottom-right', progressBar: true, closeButton: true });
                setTimeout(function () { location.reload(); }, 1200);
            }
        }
    });
});
</script>
@endpush
