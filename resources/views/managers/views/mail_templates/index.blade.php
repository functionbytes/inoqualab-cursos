@extends('layouts.managers')

@section('content')

    @include('managers.includes.card', ['title' => 'Plantillas de correo electrónico'])

    <div class="widget-content searchable-container list">

        <div class="card card-body">
            <p class="text-muted mb-0 small">
                Gestiona el asunto y el contenido HTML de los correos que envía la plataforma.
                Las variables disponibles se muestran en cada plantilla al editarla.
            </p>
        </div>

        <div class="card card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Clave</th>
                            <th>Asunto</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td class="align-middle fw-semibold">{{ $template->name }}</td>
                                <td class="align-middle">
                                    <code class="small">{{ $template->key }}</code>
                                </td>
                                <td class="align-middle text-muted small">{{ $template->subject }}</td>
                                <td class="align-middle text-end">
                                    <a href="{{ route('manager.mail_templates.preview', $template->id) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-secondary me-1"
                                       data-bs-toggle="tooltip" title="Vista previa">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('manager.mail_templates.edit', $template->id) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-pencil me-1"></i> Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No hay plantillas. Ejecuta el seeder: <code>php artisan db:seed --class=MailTemplateSeeder</code>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection
