@extends('layouts.managers')

@section('title', 'Plantillas de correo')

@section('content')


    <div class="widget-content searchable-container list"
         data-flash-success="{{ session('success') }}"
         data-flash-error="{{ session('error') }}">

        <div class="card">

            {{-- Header --}}
            <div class="card-header p-4 border-bottom border-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold">Plantillas de correo electrónico</h5>
                        <p class="mb-0 text-muted">Gestiona el asunto y el contenido HTML de los correos que envía la plataforma</p>
                    </div>
                </div>
            </div>

            {{-- Tabla --}}
            <div class="card-body">
                @if($templates->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Clave</th>
                                    <th>Asunto</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                    <tr>
                                        <td class="fw-semibold">{{ $template->name }}</td>
                                        <td><code>{{ $template->key }}</code></td>
                                        <td class="text-muted">{{ $template->subject }}</td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-sm btn-link text-muted p-0 border-0"
                                                        data-bs-toggle="dropdown"
                                                        data-bs-boundary="viewport">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.mail_templates.preview', $template->id) }}"
                                                           target="_blank">
                                                            Vista previa
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item"
                                                           href="{{ route('manager.mail_templates.edit', $template->id) }}">
                                                            Editar
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
                        <i class="fas fa-envelope-open-text fa-3x mb-3 text-muted opacity-50"></i>
                        <h5 class="fw-bold mb-2">No hay plantillas configuradas</h5>
                        <p class="text-muted mb-0">
                            Ejecuta el seeder para cargar las plantillas:<br>
                            <code>php artisan db:seed --class=MailTemplateSeeder</code>
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>

@endsection

@push('scripts')
<script src="{{ asset('managers/js/flash-toastr.js') }}"></script>
@endpush
