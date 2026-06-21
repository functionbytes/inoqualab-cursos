@extends('layouts.managers')

@section('title', 'Hreflang — SEO metas')

@section('content')


    <div class="widget-content">

        {{-- Stats --}}
        <div class="row g-3 mb-3">

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center bg-primary-subtle flex-shrink-0"
                             style="width:44px;height:44px;">
                            <i class="fas fa-language text-primary fs-5"></i>
                        </div>
                        <div>
                            <h4 class="fw-semibold mb-0">{{ $metas->count() }}</h4>
                            <p class="text-muted small mb-0">Metas con locale</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center bg-warning-subtle flex-shrink-0"
                             style="width:44px;height:44px;">
                            <i class="fas fa-circle-question text-warning fs-5"></i>
                        </div>
                        <div>
                            <h4 class="fw-semibold mb-0">{{ $withoutLocale }}</h4>
                            <p class="text-muted small mb-0">Sin locale configurado</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-2 d-flex align-items-center justify-content-center {{ count($conflicts) > 0 ? 'bg-danger-subtle' : 'bg-success-subtle' }} flex-shrink-0"
                             style="width:44px;height:44px;">
                            <i class="fas fa-{{ count($conflicts) > 0 ? 'triangle-exclamation text-danger' : 'check-circle text-success' }} fs-5"></i>
                        </div>
                        <div>
                            <h4 class="fw-semibold mb-0">{{ count($conflicts) }}</h4>
                            <p class="text-muted small mb-0">Conflictos detectados</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Alerta de conflictos --}}
        @if(count($conflicts) > 0)
            <div class="alert alert-warning d-flex gap-2 mb-3" role="alert">
                <i class="fas fa-triangle-exclamation flex-shrink-0 mt-1"></i>
                <div>
                    <h6 class="fw-semibold mb-1">Conflictos hreflang detectados</h6>
                    <p class="mb-2 small">Las siguientes rutas tienen múltiples metas con el mismo locale. Esto puede confundir a los motores de búsqueda.</p>
                    <ul class="mb-0 small">
                        @foreach($conflicts as $route)
                            <li class="font-monospace">{{ $route }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Listado agrupado por locale --}}
        <div class="card">

            <div class="card-header p-4 border-bottom border-light d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold">Metas agrupadas por locale</h5>
                    <p class="mb-0 text-muted small">Etiquetas hreflang configuradas en los metadatos SEO</p>
                </div>
                <a href="{{ route('manager.seo.metas.index') }}" class="btn btn-outline-secondary btn-sm">
                    Volver a metas
                </a>
            </div>

            <div class="card-body">

                @if($grouped->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-language fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay metas con locale configurado</h5>
                        <p class="text-muted mb-4">Configura el campo locale en los metadatos SEO para gestionar hreflang.</p>
                        <a href="{{ route('manager.seo.metas.index') }}" class="btn btn-primary">
                            Ir a metas SEO
                        </a>
                    </div>
                @else
                    <div class="accordion" id="hreflangAccordion">

                        @foreach($grouped as $locale => $items)
                            @php $accordionId = 'locale-' . Str::slug($locale); @endphp

                            <div class="accordion-item border rounded mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-semibold {{ $loop->first ? '' : 'collapsed' }}"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $accordionId }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                            aria-controls="{{ $accordionId }}">
                                        <span class="badge bg-primary me-2">{{ strtoupper($locale) }}</span>
                                        {{ $locale }}
                                        <span class="badge bg-light text-dark border ms-2">{{ $items->count() }} metas</span>
                                    </button>
                                </h2>

                                <div id="{{ $accordionId }}"
                                     class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                     data-bs-parent="#hreflangAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="6%">ID</th>
                                                        <th>Title</th>
                                                        <th>URL canónica</th>
                                                        <th width="10%" class="text-center">Accion</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($items as $meta)
                                                        <tr>
                                                            <td>
                                                                <span class="text-muted small">#{{ $meta->id }}</span>
                                                            </td>
                                                            <td>
                                                                <span class="small fw-semibold">
                                                                    {{ Str::limit($meta->title, 60) ?: '—' }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($meta->canonical_url)
                                                                    <span class="small text-muted font-monospace">
                                                                        {{ Str::limit($meta->canonical_url, 55) }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted small">—</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="{{ route('manager.seo.metas.edit', $meta->id) }}"
                                                                   class="btn btn-sm btn-light">
                                                                    Editar
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach

                    </div>
                @endif

            </div>

        </div>

    </div>

@endsection
