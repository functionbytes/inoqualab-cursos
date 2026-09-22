@extends('layouts.managers')

@section('page_header')
    @include('managers.includes.card', ['title' => 'Google Analytics'])
@endsection

@section('content')


    <div class="widget-content searchable-container list">

        <div class="row g-4 align-items-start">

            {{-- Columna principal --}}
            <div class="col-lg-8">

                {{-- Google Analytics GA4 --}}
                <form id="analyticsForm" data-update-url="{{ route('manager.settings.analytics.update') }}">
                    @csrf

                    <div class="card mb-4">

                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-1">Estado del servicio</h6>
                            <p class="text-muted mb-3">Habilita o deshabilita Google Analytics GA4 en el sitio.</p>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="googleAnalyticsEnable"
                                       name="google_analytics_enable" value="1"
                                       {{ $settings['google_analytics_enable'] ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="googleAnalyticsEnable">
                                    Habilitar Google Analytics GA4
                                </label>
                            </div>
                            <small class="text-muted d-block">Activa el seguimiento y el acceso al dashboard de Analytics.</small>
                        </div>

                        <div id="ga4Fields" class="{{ $settings['google_analytics_enable'] ? '' : 'd-none' }}">

                        <hr class="my-0">

                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-1">Identificadores</h6>
                            <p class="text-muted mb-3">Necesitas dos IDs distintos de Google Analytics.</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="propertyId" class="form-label fw-semibold">Property ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control font-monospace" id="propertyId"
                                           name="google_analytics_property_id"
                                           placeholder="123456789"
                                           value="{{ $settings['google_analytics_property_id'] }}">
                                    <small class="text-muted d-block mt-1">
                                        ID numérico (9–10 dígitos). GA4 → Admin → Configuración de la propiedad.
                                        Usado para la <strong>API de datos</strong> (dashboard).
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label for="measurementId" class="form-label fw-semibold">Measurement ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control font-monospace" id="measurementId"
                                           name="google_analytics_measurement_id"
                                           placeholder="G-XXXXXXXXXX"
                                           value="{{ $settings['google_analytics_measurement_id'] }}">
                                    <small class="text-muted d-block mt-1">
                                        Formato <code>G-XXXXXXX</code>. Usado para el <strong>script gtag.js</strong> del sitio web.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr class="my-0">

                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-1">Credenciales de servicio (JSON)</h6>
                            <p class="text-muted mb-3">
                                Archivo de cuenta de servicio de Google Cloud Console.
                                Necesario para que el servidor consulte los datos de tu propiedad GA4.
                            </p>

                            @if($credentialsInfo['configured'])
                                <div class="alert alert-success border-0 mb-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i>
                                    <strong>Credenciales configuradas</strong><br>
                                    <small>
                                        Proyecto: <code>{{ $credentialsInfo['project_id'] }}</code><br>
                                        Cuenta: <code>{{ $credentialsInfo['client_email'] }}</code>
                                    </small>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 mb-3 py-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Sin credenciales.</strong> El dashboard de Analytics no funcionará hasta configurarlas.
                                </div>
                            @endif

                            <label for="credentials" class="form-label fw-semibold">
                                {{ $credentialsInfo['configured'] ? 'Actualizar credenciales' : 'Pegar credenciales' }}
                            </label>
                            <textarea class="form-control font-monospace" id="credentials"
                                      name="google_analytics_credentials"
                                      rows="7"
                                      placeholder='{"type": "service_account", "project_id": "...", "private_key": "...", "client_email": "..."}'></textarea>
                            <small class="text-muted d-block mt-1 mb-3">
                                Deja vacío para conservar las credenciales actuales.
                                <a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank" class="ms-1">
                                    Google Cloud Console
                                </a>
                            </small>

                            <button type="button" class="btn btn-outline-primary" id="validateBtn">
                                Validar JSON
                            </button>
                        </div>

                        <hr class="my-0">

                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-1">Caché del dashboard</h6>
                            <p class="text-muted mb-3">Los datos del dashboard se almacenan en caché para no exceder los límites de la API.</p>

                            <div>
                                <label for="cacheLifetime" class="form-label fw-semibold">Tiempo de caché (minutos)</label>
                                <input type="number" class="form-control w-100" id="cacheLifetime"
                                       name="analytics_cache_lifetime"
                                       min="1" max="1440"
                                       value="{{ $settings['analytics_cache_lifetime'] }}">
                                <small class="text-muted d-block mt-1">Entre 1 y 1440 min (24 h). Recomendado: 60.</small>
                            </div>
                        </div>

                        </div>{{-- /#ga4Fields --}}

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100">
                                Guardar configuración GA4
                            </button>
                        </div>

                        <div class="card-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-outline-secondary w-100" id="clearCacheBtn"
                                    data-url="{{ route('manager.settings.analytics.clear-cache') }}">
                                Limpiar caché del dashboard
                            </button>
                        </div>

                    </div>
                </form>

                {{-- Pixels de seguimiento --}}
                <form id="pixelsForm" data-update-url="{{ route('manager.settings.analytics.update') }}">
                    @csrf

                    <div class="card">

                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-1">Pixels de seguimiento</h6>
                            <p class="text-muted mb-0">
                                Ingresa los IDs de cada plataforma. Deja vacío los que no uses.
                                Los scripts se inyectan automáticamente en el sitio público.
                            </p>
                        </div>

                        <hr class="my-0">

                        {{-- Meta Pixel --}}
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">Meta Pixel (Facebook / Instagram)</h6>
                            <input type="text" class="form-control font-monospace" name="meta_pixel_id"
                                   placeholder="123456789012345"
                                   value="{{ $settings['meta_pixel_id'] }}">
                            <small class="text-muted d-block mt-1">
                                Meta Ads Manager → Fuentes de datos → Pixels → ID numérico.
                            </small>
                        </div>

                        <hr class="my-0">

                        {{-- Microsoft Clarity --}}
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <h6 class="fw-bold mb-0">Microsoft Clarity</h6>
                                <span class="badge bg-success-subtle text-success">Gratis</span>
                            </div>
                            <input type="text" class="form-control font-monospace" name="microsoft_clarity_id"
                                   placeholder="abcd1efgh2"
                                   value="{{ $settings['microsoft_clarity_id'] }}">
                            <small class="text-muted d-block mt-1">
                                <a href="https://clarity.microsoft.com" target="_blank">clarity.microsoft.com</a>
                                → tu proyecto → Configuración → ID del proyecto.
                            </small>
                        </div>

                        <hr class="my-0">

                        {{-- TikTok Pixel --}}
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">TikTok Pixel</h6>
                            <input type="text" class="form-control font-monospace" name="tiktok_pixel_id"
                                   placeholder="CXXXXXXXXXXXXXXXXXXXXXXX"
                                   value="{{ $settings['tiktok_pixel_id'] }}">
                            <small class="text-muted d-block mt-1">
                                TikTok Ads Manager → Activos → Eventos → Pixel web → ID del pixel.
                            </small>
                        </div>

                        <hr class="my-0">

                        {{-- LinkedIn Insight Tag --}}
                        <div class="card-body">
                            <h6 class="fw-bold mb-2">LinkedIn Insight Tag</h6>
                            <input type="text" class="form-control font-monospace" name="linkedin_insight_tag_id"
                                   placeholder="1234567"
                                   value="{{ $settings['linkedin_insight_tag_id'] }}">
                            <small class="text-muted d-block mt-1">
                                LinkedIn Campaign Manager → Activos → Insight Tag → Partner ID numérico.
                            </small>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary w-100">
                                Guardar pixels
                            </button>
                        </div>

                    </div>
                </form>

            </div>

            {{-- Columna lateral --}}
            <div class="col-lg-4">

                {{-- Reportes programados --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">Reportes programados</h6>
                        <p class="text-muted mb-3">Configura el envío automático de reportes por email con frecuencia y formato personalizados.</p>
                        <a href="{{ route('manager.settings.analytics.schedules.index') }}" class="btn btn-primary w-100">
                            Gestionar reportes
                        </a>
                    </div>
                </div>

                {{-- Notificaciones --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-1">Notificaciones</h6>
                        <p class="text-muted mb-3">Configura los destinatarios que reciben alertas cuando un reporte se envía o falla.</p>
                        <a href="{{ route('manager.settings.analytics.notifications') }}" class="btn btn-outline-secondary w-100">
                            Configurar notificaciones
                        </a>
                    </div>
                </div>

                {{-- Cómo configurar GA4 --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">
                            Cómo configurar GA4
                        </h6>
                        <ol class="ps-3 mb-0 small">
                            <li>Ve a <a href="https://analytics.google.com" target="_blank">analytics.google.com</a></li>
                            <li>Admin → Configuración de la propiedad → copia el <strong>Property ID</strong></li>
                            <li>Admin → Streams de datos → Web → copia el <strong>Measurement ID</strong> (<code>G-XXXXX</code>)</li>
                            <li>Ve a <a href="https://console.cloud.google.com" target="_blank">Google Cloud Console</a></li>
                            <li>Crea o selecciona un proyecto</li>
                            <li>Habilita la API <em>Google Analytics Data API</em></li>
                            <li>Crea una <em>Cuenta de servicio</em> → descarga el JSON</li>
                            <li>En GA4, Admin → Gestión de acceso → añade el email de la cuenta de servicio con rol <em>Viewer</em></li>
                            <li>Pega el JSON en el campo de credenciales</li>
                        </ol>
                    </div>
                </div>

                {{-- Estado actual --}}
                <div class="card">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">
                            Estado actual
                        </h6>
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Seguimiento web (gtag.js)</span>
                                @if($settings['google_analytics_enable'] && $settings['google_analytics_measurement_id'])
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">API datos (dashboard)</span>
                                @if($credentialsInfo['configured'] && $settings['google_analytics_property_id'])
                                    <span class="badge bg-success-subtle text-success">Configurado</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Sin configurar</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Meta Pixel</span>
                                @if($settings['meta_pixel_id'])
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Microsoft Clarity</span>
                                @if($settings['microsoft_clarity_id'])
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">TikTok Pixel</span>
                                @if($settings['tiktok_pixel_id'])
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">LinkedIn Insight</span>
                                @if($settings['linkedin_insight_tag_id'])
                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Inactivo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/analytics/index.js') }}"></script>
@endpush
