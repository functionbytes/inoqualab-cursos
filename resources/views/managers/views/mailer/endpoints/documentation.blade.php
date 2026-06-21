@extends('layouts.managers')

@section('title', 'Documentación de endpoints')

@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Overview Section --}}
    <div class="card mb-3">
        <div class="card-header border-bottom bg-light">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-book me-2"></i>Guía general de API
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-6">
                    <h6 class="fw-bold text-primary mb-3">URL base</h6>
                    <div class="bg-black text-light p-3 rounded font-monospace small overflow-auto">
                        <code>{{ $appUrl }}/api/email-endpoints</code>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h6 class="fw-bold text-primary mb-3">Headers requeridos</h6>
                    <div class="bg-black text-light p-3 rounded font-monospace small">
                        <code>Content-Type: application/json<br>X-API-Token: &lt;tu-token-api&gt;</code>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <h6 class="fw-bold text-primary mb-2">Códigos de respuesta</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <small class="d-block mb-1"><span class="badge bg-success">200 OK</span> Solicitud exitosa</small>
                        <small class="d-block mb-1"><span class="badge bg-success">202 Accepted</span> Email encolado</small>
                    </div>
                    <div class="col-md-6">
                        <small class="d-block mb-1"><span class="badge bg-warning">401 Unauthorized</span> Token inválido</small>
                        <small class="d-block mb-1"><span class="badge bg-warning">422 Unprocessable</span> Variables faltantes</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($endpoints->isEmpty())
        {{-- Empty State --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                <h6 class="mb-2">No hay endpoints configurados</h6>
                <p class="text-muted mb-3">Crea tu primer endpoint para obtener documentación personalizada</p>
                <a href="{{ route('mailers.endpoints.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Crear endpoint
                </a>
            </div>
        </div>
    @else
        {{-- Endpoints Documentation --}}
        @foreach($endpoints as $endpoint)
            <div class="card mb-3">
                {{-- Header --}}
                <div class="card-header border-bottom" style="@if($endpoint->is_active) background: rgba(54,199,108,0.10); @else background: rgba(220,53,69,0.10); @endif">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-1 fw-bold">
                                <i class="fas @if($endpoint->is_active) fa-check-circle text-success @else fa-times-circle text-danger @endif me-2"></i>
                                {{ $endpoint->name }}
                            </h5>
                            <p class="mb-0 text-muted">{{ $endpoint->description ?? 'Sin descripción' }}</p>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2">
                                @if($endpoint->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                                <span class="badge bg-secondary">{{ ucfirst($endpoint->type) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        {{-- Left Column: Technical Details --}}
                        <div class="col-lg-6">
                            {{-- Endpoint URL --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="fas fa-link me-1"></i>URL del endpoint
                                </h6>
                                <div class="bg-black text-light p-3 rounded font-monospace small text-break">
                                    <code>POST {{ $appUrl }}/api/email-endpoints/{{ $endpoint->slug }}/send</code>
                                </div>
                            </div>

                            {{-- API Token --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="fas fa-key me-1"></i>Token API
                                </h6>
                                <div class="bg-black text-light p-3 rounded d-flex justify-content-between align-items-center font-monospace small text-break">
                                    <span class="text-warning">{{ substr($endpoint->api_token, 0, 20) }}...</span>
                                    <button class="btn btn-sm btn-outline-light btn-copy-token" data-token="{{ $endpoint->api_token }}" title="Copiar token completo">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Usar en header: <code>X-API-Token: &lt;token&gt;</code>
                                </small>
                            </div>

                            {{-- Template Associated --}}
                            @if($endpoint->template)
                                <div class="mb-3">
                                    <h6 class="fw-bold text-primary mb-2">
                                        <i class="fas fa-envelope me-1"></i>Template asociado
                                    </h6>
                                    <div class="alert alert-info mb-0">
                                        <p class="mb-1">
                                            <strong>{{ $endpoint->template->subject }}</strong>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Right Column: Variables --}}
                        <div class="col-lg-6">
                            {{-- Expected Variables --}}
                            @if($endpoint->expected_variables && count($endpoint->expected_variables) > 0)
                                <div class="mb-3">
                                    <h6 class="fw-bold text-primary mb-2">
                                        <i class="fas fa-list me-1"></i>Variables esperadas
                                    </h6>
                                    <div class="bg-light p-3 rounded border-start border-3 border-info">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($endpoint->expected_variables as $variable)
                                                <span class="badge bg-info text-white font-monospace px-2 py-1">
                                                    {{{ $variable }}}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Required Variables --}}
                            @if($endpoint->required_variables && count($endpoint->required_variables) > 0)
                                <div class="mb-3">
                                    <h6 class="fw-bold text-danger mb-2">
                                        <i class="fas fa-asterisk me-1"></i>Variables requeridas
                                    </h6>
                                    <div class="bg-light p-3 rounded border-start border-3 border-danger">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($endpoint->required_variables as $variable)
                                                <span class="badge bg-danger text-white font-monospace px-2 py-1">
                                                    {{{ $variable }}}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Estas variables son obligatorias en cada solicitud
                                    </small>
                                </div>
                            @endif

                            {{-- Stats --}}
                            <div class="mb-3">
                                <h6 class="fw-bold text-primary mb-2">
                                    <i class="fas fa-chart-bar me-1"></i>Estadísticas
                                </h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <small class="d-block text-muted">Total de requests</small>
                                        <strong>{{ $endpoint->requests_count ?? 0 }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="d-block text-muted">Último request</small>
                                        <strong class="small">
                                            @if($endpoint->last_request_at)
                                                {{ $endpoint->last_request_at->diffForHumans() }}
                                            @else
                                                Sin requests
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Code Examples --}}
                    <hr class="my-4">

                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fas fa-code me-1"></i>Ejemplos de uso
                    </h6>

                    <ul class="nav nav-pills mb-3" role="tablist" id="endpoint-{{ $endpoint->id }}-tabs">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="curl-{{ $endpoint->id }}-tab" data-bs-toggle="tab" data-bs-target="#curl-{{ $endpoint->id }}" type="button" role="tab">
                                <i class="fab fa-linux me-1"></i>cURL
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="js-{{ $endpoint->id }}-tab" data-bs-toggle="tab" data-bs-target="#js-{{ $endpoint->id }}" type="button" role="tab">
                                <i class="fab fa-js me-1"></i>JavaScript
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="php-{{ $endpoint->id }}-tab" data-bs-toggle="tab" data-bs-target="#php-{{ $endpoint->id }}" type="button" role="tab">
                                <i class="fab fa-php me-1"></i>PHP
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="python-{{ $endpoint->id }}-tab" data-bs-toggle="tab" data-bs-target="#python-{{ $endpoint->id }}" type="button" role="tab">
                                <i class="fab fa-python me-1"></i>Python
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="endpoint-{{ $endpoint->id }}-content">
                        {{-- cURL Example --}}
                        <div class="tab-pane fade show active" id="curl-{{ $endpoint->id }}" role="tabpanel">
                            <div class="bg-black text-light p-3 rounded copy-code-block font-monospace small overflow-auto">
                                <pre class="mb-0"><code>curl -X POST {{ $appUrl }}/api/email-endpoints/{{ $endpoint->slug }}/send \
  -H "Content-Type: application/json" \
  -H "X-API-Token: {{ substr($endpoint->api_token, 0, 20) }}..." \
  -d '{
    @if($endpoint->required_variables && count($endpoint->required_variables) > 0)
@php $vars = $endpoint->required_variables; @endphp
@foreach($vars as $var)
    "{{ $var }}": "valor_{{ strtolower($var) }}",
@endforeach
    @endif
    "other_variable": "other_value"
  }'</code></pre>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary mt-2 btn-copy-code" data-target="curl-{{ $endpoint->id }}">
                                <i class="fas fa-copy me-1"></i>Copiar
                            </button>
                        </div>

                        {{-- JavaScript Example --}}
                        <div class="tab-pane fade" id="js-{{ $endpoint->id }}" role="tabpanel">
                            <div class="bg-black text-light p-3 rounded copy-code-block font-monospace small overflow-auto">
                                <pre class="mb-0"><code>fetch('{{ $appUrl }}/api/email-endpoints/{{ $endpoint->slug }}/send', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-API-Token': '{{ substr($endpoint->api_token, 0, 20) }}...'
  },
  body: JSON.stringify({
    @if($endpoint->required_variables && count($endpoint->required_variables) > 0)
@foreach($endpoint->required_variables as $var)
    {{ $var }}: 'valor_{{ strtolower($var) }}',
@endforeach
    @endif
    other_variable: 'other_value'
  })
})
.then(response => response.json())
.then(data => console.log('Success:', data))
.catch(error => console.error('Error:', error));</code></pre>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary mt-2 btn-copy-code" data-target="js-{{ $endpoint->id }}">
                                <i class="fas fa-copy me-1"></i>Copiar
                            </button>
                        </div>

                        {{-- PHP Example --}}
                        <div class="tab-pane fade" id="php-{{ $endpoint->id }}" role="tabpanel">
                            <div class="bg-black text-light p-3 rounded copy-code-block font-monospace small overflow-auto">
                                <pre class="mb-0"><code>$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => '{{ $appUrl }}/api/email-endpoints/{{ $endpoint->slug }}/send',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
    'X-API-Token: {{ substr($endpoint->api_token, 0, 20) }}...'
  ],
  CURLOPT_POSTFIELDS => json_encode([
    @if($endpoint->required_variables && count($endpoint->required_variables) > 0)
@foreach($endpoint->required_variables as $var)
    '{{ $var }}' => 'valor_{{ strtolower($var) }}',
@endforeach
    @endif
    'other_variable' => 'other_value'
  ])
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

echo json_encode(json_decode($response), JSON_PRETTY_PRINT);</code></pre>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary mt-2 btn-copy-code" data-target="php-{{ $endpoint->id }}">
                                <i class="fas fa-copy me-1"></i>Copiar
                            </button>
                        </div>

                        {{-- Python Example --}}
                        <div class="tab-pane fade" id="python-{{ $endpoint->id }}" role="tabpanel">
                            <div class="bg-black text-light p-3 rounded copy-code-block font-monospace small overflow-auto">
                                <pre class="mb-0"><code>import requests
import json

url = '{{ $appUrl }}/api/email-endpoints/{{ $endpoint->slug }}/send'

headers = {
    'Content-Type': 'application/json',
    'X-API-Token': '{{ substr($endpoint->api_token, 0, 20) }}...'
}

payload = {
    @if($endpoint->required_variables && count($endpoint->required_variables) > 0)
@foreach($endpoint->required_variables as $var)
    '{{ $var }}': 'valor_{{ strtolower($var) }}',
@endforeach
    @endif
    'other_variable': 'other_value'
}

response = requests.post(url, json=payload, headers=headers)

print(f'Status Code: {response.status_code}')
print(json.dumps(response.json(), indent=2))</code></pre>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary mt-2 btn-copy-code" data-target="python-{{ $endpoint->id }}">
                                <i class="fas fa-copy me-1"></i>Copiar
                            </button>
                        </div>
                    </div>

                    {{-- Response Examples --}}
                    <hr class="my-4">

                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fas fa-exchange-alt me-1"></i>Respuestas
                    </h6>

                    <div class="row g-3">
                        <div class="col-lg-6">
                            <h6 class="small fw-bold text-success mb-2">
                                <i class="fas fa-check-circle me-1"></i>Respuesta exitosa (202)
                            </h6>
                            <div class="bg-black text-light p-3 rounded font-monospace overflow-auto small">
                                <pre class="mb-0"><code>{
  "success": true,
  "message": "Email queued for sending",
  "log_id": 12345,
  "endpoint": "{{ $endpoint->slug }}"
}</code></pre>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h6 class="small fw-bold text-danger mb-2">
                                <i class="fas fa-exclamation-circle me-1"></i>Respuesta de error (422)
                            </h6>
                            <div class="bg-black text-light p-3 rounded font-monospace overflow-auto small">
                                <pre class="mb-0"><code>{
  "success": false,
  "message": "Missing required variables",
  "missing_variables": [
    "customer_email"
  ]
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            Creado: {{ $endpoint->created_at->format('d/m/Y H:i') }}
                        </p>
                        <div class="d-flex gap-2">
                            <a href="{{ route('mailers.endpoints.edit', $endpoint) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Editar
                            </a>
                            <a href="{{ route('mailers.endpoints.logs', $endpoint) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-history me-1"></i>Ver logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-3">
        <a href="{{ route('mailers.endpoints.index') }}" class="btn btn-secondary">
            Volver
        </a>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {

    // Copy token to clipboard
    $(document).on('click', '.btn-copy-token', function() {
        const token = $(this).data('token');
        const $btn = $(this);

        navigator.clipboard.writeText(token).then(function() {
            const original = $btn.html();
            $btn.html('<i class="fas fa-check"></i>');
            setTimeout(function() { $btn.html(original); }, 2000);
            toastr.success('Token copiado al portapapeles', 'Copiado', { timeOut: 2000 });
        });
    });

    // Copy code blocks to clipboard
    $(document).on('click', '.btn-copy-code', function() {
        const target = $(this).data('target');
        const code = $('#' + target).find('code').text();
        const $btn = $(this);

        navigator.clipboard.writeText(code).then(function() {
            const original = $btn.html();
            $btn.html('<i class="fas fa-check me-1"></i>Copiado!');
            setTimeout(function() { $btn.html(original); }, 2000);
        });
    });
});
</script>
@endpush
