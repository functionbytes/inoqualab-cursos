<form>
    <script
        type="text/javascript"
        src="https://checkout.wompi.co/widget.js"
        data-render="button"
        data-public-key="{{ $wompi->public }}"
        data-currency="{{ $wompi->currency }}"
        data-amount-in-cents="{{ $wompi->amount }}"
        data-reference="{{ $wompi->reference }}"
        data-expiration-time="{{ $wompi->expiration }}"
        data-redirect-url="{{ $wompi->redirect }}"
        data-signature:integrity="{{ $wompi->signature }}"
        data-customer-data:email="{{ $wompi->email }}"
        data-customer-data:full-name="{{ $wompi->firstname }} {{ $wompi->lastname }}"
        data-customer-data:phone-number="{{ $wompi->cellphone }}"
        data-customer-data:phone-number-prefix="+57"
    ></script>
    <button class="waybox-button d-none" type="submit">Paga con <strong>Wompi</strong></button>
</form>

@if(setting('wompi_sandbox') === 'true')
<div class="wompi-sandbox-box mt-3 p-3">
    <p class="wompi-sandbox-note mb-2"><strong>🧪 Sandbox:</strong> Simula el resultado del pago sin usar Wompi real.</p>
    <div class="wompi-sandbox-actions d-flex">
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'APPROVED']) }}"
           class="btn btn-sm wompi-sim-btn wompi-sim-btn--approved">
            ✓ Simular APROBADO
        </a>
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'DECLINED']) }}"
           class="btn btn-sm wompi-sim-btn wompi-sim-btn--declined">
            ✗ Simular DECLINADO
        </a>
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'PENDING']) }}"
           class="btn btn-sm wompi-sim-btn wompi-sim-btn--pending">
            ⏳ Simular PENDIENTE
        </a>
        <a href="{{ route('checkout.simulate', [$wompi->reference, 'ERROR']) }}"
           class="btn btn-sm wompi-sim-btn wompi-sim-btn--error">
            ⚠ Simular ERROR
        </a>
    </div>
</div>
@endif

@push('css')
    <link rel="stylesheet" href="{{ asset('pages/css/partials/sections/payments/wompi.css') }}">
@endpush
