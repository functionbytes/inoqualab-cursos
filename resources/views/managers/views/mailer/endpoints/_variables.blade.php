{{--
    Variables esperadas + mapeo JSON -> plantilla, compartido por create/edit.
    La lógica vive en public/managers/js/views/mailer/endpoints/variables.js.

    variable_mappings se guarda como [ruta JSON => variable de plantilla],
    que es como lo lee SendEndpointEmailJob::mapVariables().
--}}
@php
    $endpointModel = $endpoint ?? null;
    $expectedVariables = array_values(array_filter((array) old('expected_variables', $endpointModel?->expected_variables ?? [])));
    $requiredVariables = (array) old('required_variables', $endpointModel?->required_variables ?? []);
    $variableMappings = (array) old('variable_mappings', $endpointModel?->variable_mappings ?? []);

    $templateVariables = [];
    foreach ($templates as $template) {
        $templateVariables[$template->id] = array_values(array_filter(array_column($template->getAvailableVariables(), 'name')));
    }
@endphp

<hr class="my-0">

{{-- Variables esperadas --}}
<div class="card-body">
    <div class="repeater-head">
        <div>
            <h6 class="fw-bold text-dark mb-1">Variables esperadas</h6>
            <p class="text-muted small mb-0">
                Campos que el sistema externo envía en el JSON, por ejemplo <code>customer_email</code>.
                Marca la casilla de las que deben venir siempre: si falta alguna, la petición se rechaza con un error 422.
            </p>
        </div>
        <button type="button" class="btn btn-icon btn-sm repeater-add" id="addExpectedVariable" title="Agregar variable" aria-label="Agregar variable">
            {!! \App\Html\IconHelper::render('plus') !!}
        </button>
    </div>

    <div id="expectedVariablesContainer" class="repeater-list">
        @foreach($expectedVariables as $variable)
            <div class="repeater-row expected-row">
                <input type="text" class="form-control expected-var-input" name="expected_variables[]"
                       value="{{ $variable }}" placeholder="Ej: customer_email" maxlength="100">
                <label class="repeater-required" title="Obligatoria">
                    <input type="checkbox" class="form-check-input expected-required" aria-label="Obligatoria" {{ in_array($variable, $requiredVariables, true) ? 'checked' : '' }}>
                </label>
                <button type="button" class="btn repeater-remove remove-variable" title="Quitar variable" aria-label="Quitar variable">
                    {!! \App\Html\IconHelper::render('trash') !!}
                </button>
            </div>
        @endforeach
    </div>
    <p class="repeater-empty text-muted small mb-0 @if(count($expectedVariables)) d-none @endif" id="expectedVariablesEmpty">
        Aún no hay variables. Usa el botón + para agregar la primera.
    </p>

    <template id="expectedVariableTemplate">
        <div class="repeater-row expected-row">
            <input type="text" class="form-control expected-var-input" name="expected_variables[]" placeholder="Ej: customer_email" maxlength="100">
            <label class="repeater-required" title="Obligatoria">
                <input type="checkbox" class="form-check-input expected-required" aria-label="Obligatoria">
            </label>
            <button type="button" class="btn repeater-remove remove-variable" title="Quitar variable" aria-label="Quitar variable">
                {!! \App\Html\IconHelper::render('trash') !!}
            </button>
        </div>
    </template>
</div>

<hr class="my-0">

{{-- Mapeo de variables --}}
<div class="card-body"
     id="mappingsSection"
     data-template-variables='@json($templateVariables)'>
    <div class="repeater-head">
        <div>
            <h6 class="fw-bold text-dark mb-1">Mapeo de variables (opcional)</h6>
            <p class="text-muted small mb-0">
                Conecta cada campo del JSON con la variable de la plantilla donde debe aparecer.
                Acepta rutas anidadas, como <code>user.email</code>. Si agregas al menos un mapeo, solo se
                usan los campos mapeados; sin mapeos, cada campo se envía con su nombre en mayúsculas.
            </p>
        </div>
        <button type="button" class="btn btn-icon btn-sm repeater-add" id="addMapping" title="Agregar mapeo" aria-label="Agregar mapeo">
            {!! \App\Html\IconHelper::render('plus') !!}
        </button>
    </div>

    <div class="repeater-columns @if(! count($variableMappings)) d-none @endif" id="mappingsColumns">
        <span>Campo del JSON</span>
        <span>Variable de la plantilla</span>
    </div>

    <div id="mappingsContainer" class="repeater-list">
        @foreach($variableMappings as $jsonPath => $templateVariable)
            <div class="repeater-row mapping-row">
                <input type="text" class="form-control mapping-json" value="{{ $jsonPath }}"
                       placeholder="Ej: user.email" list="expectedVariablesList" maxlength="150">
                <select class="form-select mapping-template" data-selected="{{ $templateVariable }}">
                    <option value="{{ $templateVariable }}" selected>{{ $templateVariable }}</option>
                </select>
                <button type="button" class="btn repeater-remove remove-mapping" title="Quitar mapeo" aria-label="Quitar mapeo">
                    {!! \App\Html\IconHelper::render('trash') !!}
                </button>
            </div>
        @endforeach
    </div>
    <p class="repeater-empty text-muted small mb-0 @if(count($variableMappings)) d-none @endif" id="mappingsEmpty">
        Sin mapeos. Usa el botón + para conectar un campo del JSON con una variable de la plantilla.
    </p>

    <datalist id="expectedVariablesList">
        @foreach($expectedVariables as $variable)
            <option value="{{ $variable }}"></option>
        @endforeach
    </datalist>

    <template id="mappingTemplate">
        <div class="repeater-row mapping-row">
            <input type="text" class="form-control mapping-json" placeholder="Ej: user.email" list="expectedVariablesList" maxlength="150">
            <select class="form-select mapping-template" data-selected=""></select>
            <button type="button" class="btn repeater-remove remove-mapping" title="Quitar mapeo" aria-label="Quitar mapeo">
                {!! \App\Html\IconHelper::render('trash') !!}
            </button>
        </div>
    </template>
</div>
