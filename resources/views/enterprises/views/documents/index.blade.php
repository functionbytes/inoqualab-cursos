@extends('layouts.managers')

@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Documentos'])
@endsection

@section('content')

    <div class="widget-content searchable-container list">

        <div class="card">

            {{-- Search --}}
            <div class="card-body border-bottom">
                <form method="GET" action="{{ route('enterprise.documents') }}" id="searchForm">
                    @include('managers.includes.filter-toolbar', [
                        'searchName' => 'search',
                        'searchValue' => $searchKey ?? '',
                        'searchPlaceholder' => 'Buscar por titulo...',
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
                            <th>Fecha</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($documents as $document)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ Str::upper(Str::lower($document->title)) }}</div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $document->updated_at->format('d/m/Y') }}</span>
                                </td>
                                <td class="text-center">
                                    @php $documentMedia = $document->getfirstMedia('files'); @endphp
                                    @if($documentMedia)
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-boundary="viewport">
                                                <i class="fas fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" target="_blank" href="{{ $documentMedia->getfullUrl() }}">Descargar</a>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted">No disponible</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

            @include('managers.includes.pagination-footer', [
                'paginator' => $documents,
                'itemLabel' => 'documentos',
            ])
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('enterprises/js/views/documents/index.js') }}"></script>
@endpush
