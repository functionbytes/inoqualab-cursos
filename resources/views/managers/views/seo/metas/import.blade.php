@extends('layouts.managers')

@section('title', 'Importar metas SEO desde CSV')

@section('page_header')
    @include('managers.includes.card', [
        'title' => 'Importar desde CSV',
        'description' => 'Carga un archivo CSV para importar o actualizar metadatos SEO',
    ])
@endsection

@section('content')


    <div class="row g-4">

        {{-- Form column --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <form action="{{ route('manager.seo.metas.import') }}" method="POST" enctype="multipart/form-data">
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
                            <label for="csv_file" class="form-label fw-semibold">
                                Archivo CSV
                            </label>
                            <input
                                type="file"
                                name="csv_file"
                                id="csv_file"
                                class="form-control @error('csv_file') is-invalid @enderror"
                                accept=".csv"
                            >
                            @error('csv_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Solo archivos .csv. Tamaño maximo: 10 MB.</div>
                        </div>

                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="update_existing"
                                id="update_existing"
                                value="1"
                                @checked(old('update_existing'))
                            >
                            <label class="form-check-label" for="update_existing">
                                Actualizar registros existentes
                            </label>
                            <div class="form-text">
                                Si esta marcado, los registros que ya existan seran actualizados con los datos del CSV.
                                Si no, se omitiran.
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Importar CSV
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
                    <h6 class="mb-0 fw-bold">Formato del CSV</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        La primera fila debe ser el encabezado con los nombres de columna.
                    </p>

                    <div class="mb-3">
                        <p class="fw-semibold small mb-2">Columnas requeridas</p>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-danger-subtle text-danger mt-1">req</span>
                                <div>
                                    <code class="small">seoable_type</code>
                                    <div class="form-text">Clase del modelo (ej: <code>App\Models\Course</code>)</div>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-danger-subtle text-danger mt-1">req</span>
                                <div>
                                    <code class="small">seoable_id</code>
                                    <div class="form-text">ID del registro al que pertenece</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="fw-semibold small mb-2">Columnas opcionales</p>
                        <div class="d-flex flex-column gap-1">
                            <div><code class="small">title</code> — Titulo SEO</div>
                            <div><code class="small">description</code> — Meta description</div>
                            <div><code class="small">keywords</code> — Palabras clave</div>
                            <div><code class="small">canonical_url</code> — URL canonica</div>
                            <div><code class="small">robots</code> — Directiva robots</div>
                            <div><code class="small">og_title</code> — Open Graph title</div>
                            <div><code class="small">og_description</code> — Open Graph description</div>
                            <div><code class="small">og_image</code> — Open Graph image URL</div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small">Exportar CSV actual</span>
                        <a href="{{ route('manager.seo.metas.export') }}" class="btn btn-sm btn-outline-secondary">
                            Descargar
                        </a>
                    </div>
                    <div class="form-text mt-1">
                        Descarga el CSV actual como plantilla para editar y volver a importar.
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
