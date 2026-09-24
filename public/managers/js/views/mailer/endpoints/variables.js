// Variables esperadas + mapeo JSON -> plantilla del endpoint (create/edit).
// Markup: resources/views/managers/views/mailer/endpoints/_variables.blade.php
$(function () {
    var $form = $('#expectedVariablesContainer').closest('form');
    if (!$form.length) { return; }

    var $expectedList = $('#expectedVariablesContainer');
    var $mappingList = $('#mappingsContainer');
    var templateVariables = $('#mappingsSection').data('templateVariables') || {};

    function cloneTemplate(id) {
        return $($('#' + id).html().trim());
    }

    function toggleEmpty() {
        var hasExpected = $expectedList.children().length > 0;
        var hasMappings = $mappingList.children().length > 0;
        $('#expectedVariablesEmpty').toggleClass('d-none', hasExpected);
        $('#mappingsEmpty').toggleClass('d-none', hasMappings);
        $('#mappingsColumns').toggleClass('d-none', !hasMappings);
    }

    // ── Variables esperadas ────────────────────────────────────────────────
    function refreshExpectedDatalist() {
        var $datalist = $('#expectedVariablesList').empty();
        $expectedList.find('.expected-var-input').each(function () {
            var value = $(this).val().trim();
            if (value) { $datalist.append($('<option>').attr('value', value)); }
        });
    }

    $('#addExpectedVariable').on('click', function () {
        var $row = cloneTemplate('expectedVariableTemplate');
        $expectedList.append($row);
        toggleEmpty();
        $row.find('.expected-var-input').trigger('focus');
    });

    $(document).on('click', '.remove-variable', function () {
        $(this).closest('.expected-row').remove();
        refreshExpectedDatalist();
        toggleEmpty();
    });

    $(document).on('input', '.expected-var-input', refreshExpectedDatalist);

    // ── Mapeo: el select de variable depende de la plantilla elegida ────────
    function currentTemplateVariables() {
        return templateVariables[$('#mailer_template_id').val()] || [];
    }

    function fillMappingSelect($select) {
        var selected = $select.val() || $select.attr('data-selected') || '';
        var variables = currentTemplateVariables().slice();

        // Un mapeo guardado cuya variable ya no está en la plantilla se conserva
        // como opción para no perderlo al guardar sin querer.
        if (selected && variables.indexOf(selected) === -1) { variables.unshift(selected); }

        if ($select.hasClass('select2-hidden-accessible')) { $select.select2('destroy'); }
        $select.empty().append($('<option>').attr('value', ''));
        variables.forEach(function (name) {
            $select.append($('<option>').attr('value', name).text(name));
        });
        $select.val(selected);

        $select.select2({
            width: '100%',
            placeholder: variables.length ? 'Selecciona una variable' : 'Primero elige una plantilla',
            language: { noResults: function () { return 'La plantilla no tiene esa variable'; } },
        });
    }

    $mappingList.find('.mapping-template').each(function () { fillMappingSelect($(this)); });

    $('#mailer_template_id').on('change', function () {
        $mappingList.find('.mapping-template').each(function () { fillMappingSelect($(this)); });
    });

    $('#addMapping').on('click', function () {
        var $row = cloneTemplate('mappingTemplate');
        $mappingList.append($row);
        fillMappingSelect($row.find('.mapping-template'));
        toggleEmpty();
        $row.find('.mapping-json').trigger('focus');
    });

    $(document).on('click', '.remove-mapping', function () {
        var $row = $(this).closest('.mapping-row');
        $row.find('.mapping-template.select2-hidden-accessible').select2('destroy');
        $row.remove();
        toggleEmpty();
    });

    // ── Serializar antes de enviar ─────────────────────────────────────────
    // Obligatorias: required_variables[] con el nombre de cada fila marcada.
    // Mapeos: variable_mappings[ruta JSON] = variable de plantilla.
    $form.on('submit', function () {
        $form.find('.js-serialized').remove();

        $expectedList.find('.expected-row').each(function () {
            var name = $(this).find('.expected-var-input').val().trim();
            if (name && $(this).find('.expected-required').is(':checked')) {
                $form.append($('<input type="hidden" class="js-serialized" name="required_variables[]">').val(name));
            }
        });

        $mappingList.find('.mapping-row').each(function () {
            var jsonPath = $(this).find('.mapping-json').val().trim();
            var templateVar = $(this).find('.mapping-template').val();
            if (jsonPath && templateVar) {
                $form.append($('<input type="hidden" class="js-serialized">')
                    .attr('name', 'variable_mappings[' + jsonPath + ']')
                    .val(templateVar));
            }
        });
    });

    toggleEmpty();
});
