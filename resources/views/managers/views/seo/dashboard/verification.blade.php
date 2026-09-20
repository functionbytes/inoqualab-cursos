@extends('layouts.managers')

@section('title', 'Verificación de motores de búsqueda')

@section('content')

    <div class="widget-content searchable-container list">

        <div class="row g-4 align-items-start">

            {{-- Columna izquierda --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('manager.seo.verification.update') }}" id="verificationForm">
                    @csrf
                    @method('PUT')

                    <div class="card">
                        <div class="card-header p-3 bg-white border-bottom">
                            <h5 class="mb-0 fw-bold">Códigos de verificación</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Ingresa los códigos de verificación proporcionados por cada motor de búsqueda. Se insertarán automáticamente como meta tags en el <code>&lt;head&gt;</code> de tu sitio.</p>

                            <div class="mb-4">
                                <label for="seo_google_verification" class="form-label fw-semibold">
                                    Google Search Console
                                </label>
                                <input type="text" class="form-control font-monospace" id="seo_google_verification"
                                       name="seo_google_verification"
                                       value="{{ $settings['seo_google_verification'] }}"
                                       placeholder="abc123def456ghi789">
                                <small class="text-muted d-block mt-1">
                                    Meta tag: <code>&lt;meta name="google-site-verification" content="..."&gt;</code>
                                </small>
                            </div>

                            <div class="mb-4">
                                <label for="seo_bing_verification" class="form-label fw-semibold">
                                    Bing Webmaster Tools
                                </label>
                                <input type="text" class="form-control font-monospace" id="seo_bing_verification"
                                       name="seo_bing_verification"
                                       value="{{ $settings['seo_bing_verification'] }}"
                                       placeholder="ABCDEF1234567890ABCDEF1234567890">
                                <small class="text-muted d-block mt-1">
                                    Meta tag: <code>&lt;meta name="msvalidate.01" content="..."&gt;</code>
                                </small>
                            </div>

                            <div class="mb-4">
                                <label for="seo_pinterest_verification" class="form-label fw-semibold">
                                    Pinterest
                                </label>
                                <input type="text" class="form-control font-monospace" id="seo_pinterest_verification"
                                       name="seo_pinterest_verification"
                                       value="{{ $settings['seo_pinterest_verification'] }}"
                                       placeholder="abcdef1234567890">
                                <small class="text-muted d-block mt-1">
                                    Meta tag: <code>&lt;meta name="p:domain_verify" content="..."&gt;</code>
                                </small>
                            </div>

                            <div class="mb-4">
                                <label for="seo_baidu_verification" class="form-label fw-semibold">
                                    Baidu Webmaster Tools
                                </label>
                                <input type="text" class="form-control font-monospace" id="seo_baidu_verification"
                                       name="seo_baidu_verification"
                                       value="{{ $settings['seo_baidu_verification'] }}"
                                       placeholder="code-abcdefgh">
                                <small class="text-muted d-block mt-1">
                                    Meta tag: <code>&lt;meta name="baidu-site-verification" content="..."&gt;</code>
                                </small>
                            </div>

                            <div class="mb-0">
                                <label for="seo_yandex_verification" class="form-label fw-semibold">
                                    Yandex Webmaster
                                </label>
                                <input type="text" class="form-control font-monospace" id="seo_yandex_verification"
                                       name="seo_yandex_verification"
                                       value="{{ $settings['seo_yandex_verification'] }}"
                                       placeholder="1234567890abcdef">
                                <small class="text-muted d-block mt-1">
                                    Meta tag: <code>&lt;meta name="yandex-verification" content="..."&gt;</code>
                                </small>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100">
                                Guardar configuración
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            {{-- Columna derecha --}}
            <div class="col-lg-4">

                <div class="card mb-3">
                    <div class="card-header border-bottom">
                        <h6 class="mb-0 fw-bold">Estado actual</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">Motores de búsqueda con código configurado.</p>

                        <div class="d-flex flex-column gap-2">
                            @php
                                $engines = [
                                    ['key' => 'seo_google_verification', 'name' => 'Google'],
                                    ['key' => 'seo_bing_verification', 'name' => 'Bing'],
                                    ['key' => 'seo_pinterest_verification', 'name' => 'Pinterest'],
                                    ['key' => 'seo_baidu_verification', 'name' => 'Baidu'],
                                    ['key' => 'seo_yandex_verification', 'name' => 'Yandex'],
                                ];
                            @endphp
                            @foreach($engines as $engine)
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom">
                                    <span class="small fw-semibold">{{ $engine['name'] }}</span>
                                    @if(!empty($settings[$engine['key']]))
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="fas fa-check me-1"></i>Configurado
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Sin configurar</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header border-bottom">
                        <h6 class="mb-0 fw-bold">Cómo obtener los códigos</h6>
                    </div>
                    <div class="card-body">

                        <h6 class="fw-semibold mb-2">Google Search Console</h6>
                        <ol class="text-muted ps-3 mb-3">
                            <li class="mb-1">Ve a Google Search Console</li>
                            <li class="mb-1">Agrega tu propiedad (URL del sitio)</li>
                            <li class="mb-1">Selecciona verificación por <strong>Etiqueta HTML</strong></li>
                            <li>Copia solo el valor del atributo <code>content</code></li>
                        </ol>

                        <hr class="my-3">

                        <h6 class="fw-semibold mb-2">Bing Webmaster Tools</h6>
                        <ol class="text-muted ps-3 mb-3">
                            <li class="mb-1">Ve a Bing Webmaster Tools</li>
                            <li class="mb-1">Agrega tu sitio web</li>
                            <li>Selecciona <strong>Etiqueta meta</strong> y copia el valor de <code>content</code></li>
                        </ol>

                        <hr class="my-3">

                        <h6 class="fw-semibold mb-2">Pinterest</h6>
                        <ol class="text-muted ps-3 mb-0">
                            <li class="mb-1">Ve a Pinterest Settings → Claim</li>
                            <li class="mb-1">En "Reclamar", selecciona tu sitio web</li>
                            <li>Copia el código de verificación de la etiqueta HTML</li>
                        </ol>

                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/seo/dashboard/verification.js') }}"></script>
@endpush
