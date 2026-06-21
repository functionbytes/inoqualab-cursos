@extends('layouts.managers')

@section('title', 'Importar desde .htaccess')

@section('content')


    <div class="row g-4">

        {{-- Form column --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <form action="{{ route('manager.seo.redirects.htaccess-import') }}" method="POST">
                    @csrf
                    <div class="card-header p-4 border-bottom border-light">
                        <h5 class="mb-1 fw-bold">Importar desde .htaccess</h5>
                        <p class="mb-0 text-muted small">Pega el contenido de tu archivo .htaccess para importar redirects</p>
                    </div>
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

                        <div class="mb-4">
                            <label for="htaccess_content" class="form-label fw-semibold">
                                Contenido del .htaccess
                            </label>
                            <textarea
                                name="htaccess_content"
                                id="htaccess_content"
                                class="form-control font-monospace @error('htaccess_content') is-invalid @enderror"
                                rows="8"
                                placeholder="Redirect 301 /ruta-antigua /ruta-nueva
Redirect permanent /otra-ruta https://ejemplo.com/destino
RedirectPermanent /vieja /nueva"
                            >{{ old('htaccess_content') }}</textarea>
                            @error('htaccess_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Pega solo las lineas de redirect. Las demas lineas seran ignoradas.</div>
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
                                Omitir redirects que ya existen
                            </label>
                            <div class="form-text">Si esta marcado, se omitiran los origenes que ya esten registrados.</div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            Importar
                        </button>
                        <a href="{{ route('manager.seo.redirects.index') }}" class="btn btn-secondary w-100">
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
                    <h6 class="mb-0 fw-bold">Sintaxis soportada</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        El importador reconoce las siguientes directivas de Apache:
                    </p>

                    <div class="mb-4">
                        <p class="fw-semibold small mb-1">Redirect</p>
                        <code class="d-block small bg-light p-2 rounded">Redirect 301 /origen /destino</code>
                        <code class="d-block small bg-light p-2 rounded mt-1">Redirect permanent /origen /destino</code>
                        <code class="d-block small bg-light p-2 rounded mt-1">Redirect temp /origen /destino</code>
                    </div>

                    <div class="mb-4">
                        <p class="fw-semibold small mb-1">RedirectPermanent</p>
                        <code class="d-block small bg-light p-2 rounded">RedirectPermanent /origen /destino</code>
                        <div class="form-text mt-1">Equivale a un redirect 301.</div>
                    </div>

                    <div class="mb-4">
                        <p class="fw-semibold small mb-1">RewriteRule</p>
                        <code class="d-block small bg-light p-2 rounded">RewriteRule ^origen$ /destino [R=301,L]</code>
                        <div class="form-text mt-1">Solo se importan reglas con flag R= (redirect). Las demas se ignoran.</div>
                    </div>

                    <hr>

                    <div class="alert alert-light border small mb-0 p-3">
                        <p class="fw-semibold mb-1">Notas importantes</p>
                        <ul class="mb-0 ps-3">
                            <li>Las lineas con <code>#</code> (comentarios) se ignoran</li>
                            <li>Las URLs relativas se mantienen tal cual</li>
                            <li>Las URLs absolutas se importan completas</li>
                            <li>El codigo 302 se usa si no se especifica (temporal)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
