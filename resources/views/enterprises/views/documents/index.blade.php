@extends('layouts.managers')

@section('page_header')
    @include('enterprises.includes.card', ['title' => 'Documentos'])
@endsection

@section('content')

    <div class="widget-content searchable-container list">
        
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
                        <th scope="col">Titulo</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    
                   
                    @foreach ($documents as $key => $document)
                        <tr class="search-items">
                            <td>
                                <span class="usr-email-addr" data-email="{{ $document->title }}">{{ Str::upper( Str::lower($document->title) )  }}</span>
                            </td>
                            <td>
                                <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($document->updated_at)) }}">{{ date('Y-m-d', strtotime($document->updated_at)) }}</span>
                            </td>
                            <td class="text-left">
                                @if($document && $document->getfirstMedia('files'))
                                    <a target="_blank" href="{{ $document->getfirstMedia('files')->getfullUrl() }}" class="btn mb-1 waves-effect waves-light btn-sm btn-primary">
                                        Descargar
                                    </a>
                                @else
                                    <span class="text-muted">No disponible</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
            <div class="result-body ">
                <span>Mostrar {{ $documents->firstItem() }}-{{ $documents->lastItem() }} de {{ $documents->total() }} resultados</span>
                <nav>
                    {{ $documents->appends(request()->input())->links() }}
                </nav>
            </div>
        </div>
    </div>
@endsection



