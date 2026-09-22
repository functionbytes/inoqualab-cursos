@extends('layouts.managers')

@section('title', 'Configuración SEO')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Configuración SEO'])
@endsection

@section('content')


    <div class="row g-4 align-items-start">

        {{-- Columna izquierda: formularios --}}
        <div class="col-lg-8">

            {{-- Card 1: General --}}
            <div class="card mb-4">
                <form id="formSeoGeneral">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">General</h6>
                        <p class="text-muted mb-3">Configuración básica del SEO del sitio</p>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nombre del sitio</label>
                                <input type="text" class="form-control" name="seo_site_name"
                                       value="{{ $settings['seo_site_name'] ?? '' }}"
                                       placeholder="Mi Plataforma de Cursos">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Sufijo del title</label>
                                <input type="text" class="form-control" name="seo_title_suffix"
                                       value="{{ $settings['seo_title_suffix'] ?? '' }}"
                                       placeholder=" | Mi Sitio">
                                <div class="form-text">Se añade al final del título en todas las páginas. Ej: <code> | Mi Sitio</code></div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-semibold">Imagen OG por defecto</label>
                                <input type="url" class="form-control" name="seo_og_image_default"
                                       id="seo_og_image_default"
                                       value="{{ $settings['seo_og_image_default'] ?? '' }}"
                                       placeholder="https://ejemplo.com/og-default.jpg">
                                <div class="form-text">Se usa cuando una página no tiene imagen OG propia. Recomendado: 1200×630 px.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Handle Twitter / X</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" class="form-control" name="seo_twitter_site"
                                           value="{{ ltrim($settings['seo_twitter_site'] ?? '', '@') }}"
                                           placeholder="misitioweb">
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary w-100" data-form="formSeoGeneral"
                                data-url="{{ route('manager.settings.seo.update') }}"
                                id="btn-save-general">
                            Guardar configuración general
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card 2: Verificaciones de buscadores --}}
            <div class="card mb-4">
                <form id="formSeoVerifications">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">Verificaciones de buscadores</h6>
                        <p class="text-muted mb-3">Códigos de verificación para webmaster tools</p>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Google Search Console</label>
                                <input type="text" class="form-control" name="seo_google_verification"
                                       value="{{ $settings['seo_google_verification'] ?? '' }}"
                                       placeholder="google-site-verification=xxxxx">
                                <div class="form-text">Código de verificación meta tag de Google.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Bing Webmaster Tools</label>
                                <input type="text" class="form-control" name="seo_bing_verification"
                                       value="{{ $settings['seo_bing_verification'] ?? '' }}"
                                       placeholder="msvalidate.01 xxxxx">
                                <div class="form-text">Código de verificación meta tag de Bing.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Pinterest</label>
                                <input type="text" class="form-control" name="seo_pinterest_verification"
                                       value="{{ $settings['seo_pinterest_verification'] ?? '' }}"
                                       placeholder="p:domain_verify xxxxx">
                                <div class="form-text">Verificación de Pinterest.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Baidu Webmaster</label>
                                <input type="text" class="form-control" name="seo_baidu_verification"
                                       value="{{ $settings['seo_baidu_verification'] ?? '' }}"
                                       placeholder="baidu-site-verification xxxxx">
                                <div class="form-text">Verificación de Baidu.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Yandex Webmaster</label>
                                <input type="text" class="form-control" name="seo_yandex_verification"
                                       value="{{ $settings['seo_yandex_verification'] ?? '' }}"
                                       placeholder="yandex-verification xxxxx">
                                <div class="form-text">Verificación de Yandex.</div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <hr class="my-1">
                                <h6 class="fw-semibold mt-2 mb-3">IndexNow</h6>
                                <div class="row g-3">
                                    <div class="col-12 col-md-3">
                                        <div class="form-check form-switch pt-2">
                                            <input class="form-check-input" type="checkbox"
                                                   id="seo_indexnow_enabled" name="seo_indexnow_enabled"
                                                   @checked(($settings['seo_indexnow_enabled'] ?? '') === '1')>
                                            <label class="form-check-label fw-semibold" for="seo_indexnow_enabled">
                                                Activar IndexNow
                                            </label>
                                        </div>
                                        <div class="form-text">Notifica a Bing/Yandex al publicar contenido.</div>
                                    </div>
                                    <div class="col-12 col-md-9">
                                        <label class="form-label fw-semibold">Clave IndexNow</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-monospace"
                                                   id="seo_indexnow_key" name="seo_indexnow_key"
                                                   value="{{ $settings['seo_indexnow_key'] ?? '' }}"
                                                   placeholder="Genera una clave UUID única">
                                            <button type="button" class="btn btn-outline-secondary" id="btn-gen-uuid">
                                                Generar clave
                                            </button>
                                        </div>
                                        <div class="form-text">
                                            Debes alojar el archivo <code>&lt;clave&gt;.txt</code> en la raíz del dominio.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary w-100" data-form="formSeoVerifications"
                                data-url="{{ route('manager.settings.seo.update') }}"
                                id="btn-save-verifications">
                            Guardar verificaciones
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card 3: robots.txt --}}
            <div class="card mb-4">
                <form id="formRobots">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">robots.txt</h6>
                        <p class="text-muted mb-3">Controla qué rastreadores pueden indexar</p>
                        <textarea class="form-control font-monospace" name="robots_txt" rows="10"
                                  placeholder="User-agent: *&#10;Allow: /&#10;Disallow: /panel/">{{ $settings['robots_txt'] ?? '' }}</textarea>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Este contenido se publicará en <code>/robots.txt</code>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary w-100" data-form="formRobots"
                                data-url="{{ route('manager.settings.seo.robots') }}"
                                id="btn-save-robots">
                            Guardar robots.txt
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card 4: llms.txt --}}
            <div class="card">
                <form id="formLlms">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-1">llms.txt</h6>
                        <p class="text-muted mb-3">Instrucciones para modelos de lenguaje (LLMs)</p>
                        <textarea class="form-control font-monospace" name="llms_txt" rows="8"
                                  placeholder="# Mi Sitio&#10;&#10;Descripción breve del sitio para LLMs...">{{ $settings['llms_txt'] ?? '' }}</textarea>
                        <div class="form-text mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Este contenido se publicará en <code>/llms.txt</code>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-primary w-100" data-form="formLlms"
                                data-url="{{ route('manager.settings.seo.llms') }}"
                                id="btn-save-llms">
                            Guardar llms.txt
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- Columna derecha: sidebar informativo --}}
        <div class="col-lg-4">

            <div class="card mb-3">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">Sobre estos ajustes</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Cada tarjeta se guarda de forma independiente: los cambios en "General" no afectan a "Verificaciones" ni a los archivos de texto, y viceversa.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="mb-0 fw-bold">IndexNow y los archivos .txt</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-semibold mb-2">IndexNow</h6>
                    <p class="text-muted mb-3">Notifica automáticamente a Bing y Yandex cuando publicas o actualizas contenido, sin esperar a que lo rastreen.</p>

                    <hr class="my-3">

                    <h6 class="fw-semibold mb-2">robots.txt / llms.txt</h6>
                    <p class="text-muted mb-0">Se publican tal cual en la raíz del dominio. Ten cuidado al restringir rutas: un <code>Disallow: /</code> mal puesto puede desindexar todo el sitio.</p>
                </div>
            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/seo/index.js') }}"></script>
@endpush
