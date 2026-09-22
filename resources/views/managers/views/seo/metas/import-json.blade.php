@extends('layouts.managers')

@section('title', 'Importar backup JSON')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Importar backup JSON',
        'description' => 'Restaura metadatos SEO a partir de un archivo de backup',
    ])
@endsection

@section('content')


    <div class="row g-4">

        {{-- Form column --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <form action="{{ route('manager.seo.metas.import-json') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="card-body">

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="mb-4">
                            <label for="json_file" class="form-label fw-semibold">
                                Archivo JSON
                            </label>
                            <input
                                type="file"
                                name="json_file"
                                id="json_file"
                                class="form-control @error('json_file') is-invalid @enderror"
                                accept=".json"
                            >
                            @error('json_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Solo archivos .json generados por el exportador. Tamaño maximo: 20 MB.</div>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="skip_existing"
                                id="skip_existing"
                                value="1"
                                @checked(old('skip_existing', true))
                            >
                            <label class="form-check-label" for="skip_existing">
                                Omitir registros que ya existen
                            </label>
                            <div class="form-text">
                                Si esta marcado, los registros que ya tengan metadatos SEO no seran modificados.
                                Desmarca para sobreescribir con los datos del backup.
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Importar JSON
                        </button>
                        <a href="{{ route('manager.seo.metas.index') }}" class="btn btn-secondary w-100">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Help panel --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header p-4 border-bottom border-light">
                    <h6 class="mb-0 fw-bold">Sobre este formato</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Este importador acepta exclusivamente archivos generados por el exportador JSON del sistema.
                    </p>

                    <div class="alert alert-light border small p-3 mb-4">
                        <p class="fw-semibold mb-2">Estructura esperada</p>
                        <pre class="mb-0 small text-muted import-json-example">[
  {
    "seoable_type": "App\\Models\\Course",
    "seoable_id": 1,
    "title": "...",
    "description": "...",
    "robots": "index,follow",
    ...
  }
]</pre>
                    </div>

                    <div class="mb-3">
                        <p class="fw-semibold small mb-2">Campos reconocidos</p>
                        <div class="d-flex flex-column gap-1">
                            <div><code class="small">seoable_type</code> <span class="badge bg-danger-subtle text-danger ms-1">req</span></div>
                            <div><code class="small">seoable_id</code> <span class="badge bg-danger-subtle text-danger ms-1">req</span></div>
                            <div><code class="small">title</code></div>
                            <div><code class="small">description</code></div>
                            <div><code class="small">keywords</code></div>
                            <div><code class="small">canonical_url</code></div>
                            <div><code class="small">robots</code></div>
                            <div><code class="small">og_title</code></div>
                            <div><code class="small">og_description</code></div>
                            <div><code class="small">og_image</code></div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small">Exportar backup JSON</span>
                        <a href="{{ route('manager.seo.metas.export-json') }}" class="btn btn-sm btn-outline-secondary">
                            Descargar
                        </a>
                    </div>
                    <div class="form-text mt-1">
                        Genera un backup JSON de todos los metadatos SEO actuales.
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/seo/metas/import-json.css') }}">
@endpush
