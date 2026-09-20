(function () {
    const form = document.getElementById('verificationForm');

    form?.addEventListener('submit', function (e) {
        e.preventDefault();
        const $btn = $(this).find('[type=submit]');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');

        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            body: new FormData(this)
        })
        .then(r => r.json())
        .then(data => {
            if (data.status) {
                toastr.success(data.message, 'Guardado');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                toastr.error(data.message || 'Error al guardar', 'Error');
                $btn.prop('disabled', false).html('Guardar configuración');
            }
        })
        .catch(() => {
            toastr.error('Error al guardar configuración', 'Error');
            $btn.prop('disabled', false).html('Guardar configuración');
        });
    });
})();
