$(document).ready(function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ allowClear: false, width: '100%' });
    }

    if (typeof MailerEndpointEdit !== 'undefined') {
        MailerEndpointEdit.init();
    }

    // Slug preview
    $('#slug').on('input', function () {
        $('#slugPreview').text($(this).val() || 'slug');
    });

    // Add expected variable
    $('#addExpectedVariable').on('click', function () {
        const html = `<div class="input-group mb-2">
            <span class="input-group-text bg-light"><i class="fas fa-cube text-primary"></i></span>
            <input type="text" class="form-control expected-var-input" name="expected_variables[]" placeholder="Ej: customer_email">
            <button type="button" class="btn btn-outline-danger remove-variable"><i class="fas fa-times"></i></button>
        </div>`;
        $('#expectedVariablesContainer').append(html);
        updateRequiredVariables();
    });

    // Remove expected variable
    $(document).on('click', '.remove-variable', function () {
        $(this).closest('.input-group').remove();
        updateRequiredVariables();
    });

    // Update required variables checkboxes
    function updateRequiredVariables() {
        const vars = [];
        $('.expected-var-input').each(function () {
            const v = $(this).val().trim();
            if (v) vars.push(v);
        });

        if (vars.length === 0) {
            $('#requiredVariablesContainer').html('<p class="text-muted"><i class="fas fa-info-circle me-1"></i>Primero agrega variables esperadas arriba</p>');
            return;
        }

        let html = '';
        vars.forEach(function (v, i) {
            html += `<div class="form-check form-check-inline mb-2">
                <input class="form-check-input" type="checkbox" name="required_variables[]" id="req_${i}" value="${v}">
                <label class="form-check-label" for="req_${i}">
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2">${v}</span>
                </label>
            </div>`;
        });
        $('#requiredVariablesContainer').html(html);
    }

    $(document).on('input', '.expected-var-input', updateRequiredVariables);

    // Add mapping
    $('#addMapping').on('click', function () {
        const html = `<div class="row g-2 mb-2 mapping-row">
            <div class="col-5"><input type="text" class="form-control form-control-sm mapping-template" placeholder="email"></div>
            <div class="col-5"><input type="text" class="form-control form-control-sm mapping-json" placeholder="user.email"></div>
            <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger remove-mapping w-100"><i class="fas fa-times"></i></button></div>
        </div>`;
        $('#mappingsContainer').append(html);
    });

    $(document).on('click', '.remove-mapping', function () {
        $(this).closest('.mapping-row').remove();
    });

    // Serialize mappings before submit
    $('#formCreate').on('submit', function () {
        $('.mapping-row').each(function () {
            const tplVar = $(this).find('.mapping-template').val().trim();
            const jsonPath = $(this).find('.mapping-json').val().trim();
            if (tplVar && jsonPath) {
                $(this).append(`<input type="hidden" name="variable_mappings[${tplVar}]" value="${jsonPath}">`);
            }
        });
    });
});
