@extends('layouts.managers')

@push('css')
<link rel="stylesheet" href="{{ asset('managers/css/views/settings/modules/setting.css') }}">
@endpush

@section('content')

<div class="row g-4 align-items-start">

    {{-- Columna principal --}}
    <div class="col-lg-8">
        <form id="formModules"
              data-update-url="{{ route('manager.settings.modules.update') }}"
              data-module-keys='@php $__jsonInline1 = ["module_coupons","module_bundles","module_incoming_mail","module_invoices","module_departments","module_documents","module_contacts","module_newsletter","module_reviews","module_certifications","module_certifiers","module_enterprises","module_distributors","module_faqs","module_instructions","module_seo","module_analytics"]; @endphp@json($__jsonInline1)'>
            @csrf

            {{-- Contenido --}}
            <div class="card mb-4">
                <div class="card-header border-bottom p-3">
                    <h5 class="mb-0 fw-bold">Contenido</h5>
                    <p class="text-muted mb-0">Módulos de gestión de contenido y operaciones</p>
                </div>

                @foreach([
                    ['module_coupons',       'Cupones',            'Gestión de cupones de descuento para cursos y paquetes.'],
                    ['module_bundles',       'Paquetes',           'Agrupación de cursos en paquetes con precio combinado.'],
                    ['module_invoices',      'Facturas',           'Generación y gestión de facturas para órdenes.'],
                    ['module_departments',   'Departamentos',      'Organización de usuarios y contenido por departamentos.'],
                    ['module_documents',     'Documentos',         'Repositorio de archivos y documentos descargables.'],
                    ['module_contacts',      'Contáctenos',        'Formulario de contacto y gestión de mensajes recibidos.'],
                    ['module_incoming_mail', 'Correos entrantes',  'Bandeja de correos entrantes y creación de órdenes desde email.'],
                    ['module_newsletter',    'Newsletter',         'Suscriptores, campañas y envío masivo de boletines.'],
                    ['module_reviews',       'Reseñas',            'Sistema de valoraciones y comentarios de cursos.'],
                ] as [$key, $label, $desc])
                <div class="card-body @if(!$loop->last) border-bottom @endif">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $label }}</h6>
                            <p class="text-muted mb-0">{{ $desc }}</p>
                        </div>
                        <div class="form-check form-switch flex-shrink-0 mt-1">
                            <input class="form-check-input" type="checkbox"
                                   name="{{ $key }}" id="{{ $key }}" value="1"
                                   @if(setting($key) !== 0) checked @endif>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Plataforma --}}
            <div class="card mb-4">
                <div class="card-header border-bottom p-3">
                    <h5 class="mb-0 fw-bold">Plataforma</h5>
                    <p class="text-muted mb-0">Módulos de gestión de entidades de la plataforma</p>
                </div>

                @foreach([
                    ['module_certifications', 'Certificados',    'Emisión y gestión de certificados para estudiantes que completen cursos.'],
                    ['module_certifiers',     'Capacitadores',   'Registro y gestión de instructores y capacitadores externos.'],
                    ['module_enterprises',    'Empresas',        'Gestión de empresas clientes y sus usuarios asociados.'],
                    ['module_distributors',   'Distribuidores',  'Red de distribuidores con acceso diferenciado a contenido.'],
                ] as [$key, $label, $desc])
                <div class="card-body @if(!$loop->last) border-bottom @endif">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $label }}</h6>
                            <p class="text-muted mb-0">{{ $desc }}</p>
                        </div>
                        <div class="form-check form-switch flex-shrink-0 mt-1">
                            <input class="form-check-input" type="checkbox"
                                   name="{{ $key }}" id="{{ $key }}" value="1"
                                   @if(setting($key) !== 0) checked @endif>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Configuración / utilidades --}}
            <div class="card mb-4">
                <div class="card-header border-bottom p-3">
                    <h5 class="mb-0 fw-bold">Utilidades</h5>
                    <p class="text-muted mb-0">Módulos de soporte y configuración avanzada</p>
                </div>

                @foreach([
                    ['module_faqs',         'Preguntas frecuentes', 'Sección de preguntas y respuestas accesible desde el sitio público.'],
                    ['module_instructions', 'Instrucciones',        'Guías y manuales de uso organizados por categorías.'],
                    ['module_seo',          'SEO',                  'Herramientas de optimización para motores de búsqueda.'],
                    ['module_analytics',    'Analytics',            'Panel de estadísticas, métricas de sesiones y reportes de ventas.'],
                ] as [$key, $label, $desc])
                <div class="card-body @if(!$loop->last) border-bottom @endif">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $label }}</h6>
                            <p class="text-muted mb-0">{{ $desc }}</p>
                        </div>
                        <div class="form-check form-switch flex-shrink-0 mt-1">
                            <input class="form-check-input" type="checkbox"
                                   name="{{ $key }}" id="{{ $key }}" value="1"
                                   @if(setting($key) !== 0) checked @endif>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" id="btnSave" class="btn btn-primary w-100">
                Guardar cambios
            </button>

        </form>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-bold mb-2">¿Qué hace esta configuración?</h6>
                <p class="text-muted">Activa o desactiva la visibilidad de módulos en el panel de administración. Los módulos desactivados dejan de aparecer en el menú lateral.</p>
                <hr>
                <h6 class="fw-bold mb-2">Notas importantes</h6>
                <ul class="text-muted ps-3 mb-0 modules-notes-list">
                    <li class="mb-1">Desactivar un módulo <strong>no elimina</strong> los datos existentes.</li>
                    <li class="mb-1">Puedes reactivarlo en cualquier momento y los datos estarán intactos.</li>
                    <li>Los módulos de <strong>Cursos</strong> y <strong>Usuarios</strong> son obligatorios y no se pueden desactivar.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/views/settings/modules/setting.js') }}"></script>
@endpush
