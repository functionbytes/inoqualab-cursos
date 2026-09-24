@extends('layouts.managers')

@section('title', 'Contenido sin SEO')

@section('page_header')
    @php ob_start(); @endphp
    <div class="btn-group">
        <button type="button" class="btn btn-icon btn-actions-icon dropdown-toggle arrow-none"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Acciones">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <button type="button" id="btn-generate-all" class="dropdown-item"
                    data-generate-url="{{ route('manager.seo.orphans.generate') }}"
                    data-bulk-url="{{ route('manager.seo.orphans.bulk-generate') }}"
                    {{ $total === 0 ? 'disabled' : '' }}>
                Generar todo
            </button>
        </div>
    </div>
    @php $headerActions = trim(ob_get_clean()) ?: null; @endphp
    @include('managers.includes.card', [
        'title' => 'Contenido sin SEO',
        'description' => 'Contenido publicado que no tiene metadatos SEO configurados',
        'actions' => $headerActions,
    ])
@endsection

@section('content')


    @php
        $total = array_sum($counts);
        $typeConfig = [
            'Course'      => ['label' => 'Cursos',         'subtitle' => 'Sin metadatos'],
            'Blog'        => ['label' => 'Blogs',          'subtitle' => 'Sin metadatos'],
            'Bundle'      => ['label' => 'Bundles',        'subtitle' => 'Sin metadatos'],
            'Instruction' => ['label' => 'Instrucciones',  'subtitle' => 'Sin metadatos'],
            'Certifier'   => ['label' => 'Certificadores', 'subtitle' => 'Sin metadatos'],
        ];
        $badgeConfig = [
            'Course'      => 'bg-primary-subtle text-primary',
            'Blog'        => 'bg-success-subtle text-success',
            'Bundle'      => 'bg-warning-subtle text-warning',
            'Instruction' => 'bg-info-subtle text-info',
            'Certifier'   => 'bg-secondary-subtle text-secondary',
        ];
    @endphp

    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}" data-flash-error="{{ session('error') }}">

        <div class="card">

            {{-- Header --}}
            

            {{-- Stats --}}
            <div class="card-body border-bottom">
                <div class="row g-3">
                    <div class="col-6 col-lg-4">
                        <div class="card bg-light-secondary h-100">
                            <div class="card-body">
                                <h6 class="card-title mb-2 text-nowrap">Total sin SEO</h6>
                                <h4 class="mb-1 fw-bold" id="stat-total">{{ $total }}</h4>
                                <span class="text-muted">Sin metadatos</span>
                            </div>
                        </div>
                    </div>
                    @foreach($typeConfig as $typeKey => $cfg)
                        <div class="col-6 col-lg-4">
                            <div class="card bg-light-secondary h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2 text-nowrap">{{ $cfg['label'] }}</h6>
                                    <h4 class="mb-1 fw-bold" id="stat-{{ $typeKey }}">{{ $counts[$typeKey] ?? 0 }}</h4>
                                    <span class="text-muted">{{ $cfg['subtitle'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bulk toolbar --}}
            <div id="bulk-toolbar" class="d-none px-4 py-2 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <span class="small text-muted"><span id="bulk-count">0</span> seleccionados</span>
                    <button type="button" id="btn-bulk-generate" class="btn btn-sm btn-primary">
                        Generar seleccionados
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="card-body">
                @if($total > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap" id="orphans-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" class="form-check-input" id="select-all">
                                    </th>
                                    <th>Tipo</th>
                                    <th>Título</th>
                                    <th>URL</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orphans as $item)
                                    @php
                                        $badge = $badgeConfig[$item['type']] ?? 'bg-secondary-subtle text-secondary';
                                        $label = $typeConfig[$item['type']]['label'] ?? ucfirst($item['type']);
                                    @endphp
                                    <tr id="row-{{ $item['type'] }}-{{ $item['id'] }}">
                                        <td>
                                            <input type="checkbox"
                                                   class="form-check-input bulk-checkbox"
                                                   value="{{ $item['id'] }}"
                                                   data-model-class="{{ $item['model_class'] }}"
                                                   data-model-id="{{ $item['id'] }}"
                                                   data-type="{{ $item['type'] }}">
                                        </td>
                                        <td>
                                            <span class="badge {{ $badge }} rounded-pill">{{ $label }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $item['title'] }}</div>
                                        </td>
                                        <td>
                                            @if(!empty($item['url']))
                                                <a href="{{ $item['url'] }}" target="_blank"
                                                   class="text-muted text-decoration-none"
                                                   title="{{ $item['url'] }}">
                                                    {{ $item['url'] }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
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
                                                        <a class="dropdown-item generate-btn" href="#"
                                                           data-model-class="{{ $item['model_class'] }}"
                                                           data-model-id="{{ $item['id'] }}"
                                                           data-type="{{ $item['type'] }}">
                                                            Generar metas
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
                        <div class="mb-3 text-success opacity-75">{!! \App\Html\IconHelper::render('empty-check', 48) !!}</div>
                        <h5 class="fw-bold mb-2">Todo el contenido tiene SEO configurado</h5>
                        <p class="text-muted">No hay contenido sin metadatos SEO</p>
                    </div>
                @endif
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
<script src="{{ asset('managers/js/views/seo/orphans/index.js') }}"></script>
@endpush
