<?php

namespace Tests\Unit;

use App\Services\WompiService;
use Tests\TestCase;

class WompiServiceTest extends TestCase
{
    /**
     * Sin el ajuste `wompi_sandbox` guardado, el servicio tiene que caer en
     * config('services.wompi.sandbox') — que por defecto es true.
     *
     * No lo hacía: el constructor decidía con `setting('wompi_sandbox') !== null`,
     * y setting() nunca devuelve null (su default es la cadena vacía). La rama
     * del fallback era código muerto y un entorno sin el ajuste evaluaba
     * `'' === 'true'` = false, es decir, hablaba con la API de PRODUCCIÓN de
     * Wompi creyendo estar en sandbox.
     */
    public function test_without_the_setting_it_falls_back_to_config_and_stays_in_sandbox(): void
    {
        $this->forgetSandboxSetting();
        config(['services.wompi.sandbox' => true]);

        $service = new WompiService;

        $this->assertTrue($service->isSandbox());
        $this->assertSame('https://sandbox.wompi.co/v1', $service->getBaseUrl());
    }

    public function test_without_the_setting_a_config_of_false_still_wins(): void
    {
        $this->forgetSandboxSetting();
        config(['services.wompi.sandbox' => false]);

        $this->assertSame('https://production.wompi.co/v1', (new WompiService)->getBaseUrl());
    }

    public function test_the_stored_setting_takes_precedence_over_config(): void
    {
        config(['services.wompi.sandbox' => false]);
        $this->overrideSandboxSetting('true');

        $this->assertSame(
            'https://sandbox.wompi.co/v1',
            (new WompiService)->getBaseUrl(),
            'El ajuste del panel manda sobre la config.'
        );

        config(['services.wompi.sandbox' => true]);
        $this->overrideSandboxSetting('false');

        $this->assertSame('https://production.wompi.co/v1', (new WompiService)->getBaseUrl());
    }

    /** Deja el ajuste como si nunca se hubiera guardado. */
    private function forgetSandboxSetting(): void
    {
        _settingsCache(null, true);
        _settingsCache(['wompi_sandbox' => '']);
    }

    private function overrideSandboxSetting(string $value): void
    {
        _settingsCache(null, true);
        _settingsCache(['wompi_sandbox' => $value]);
    }

    public function test_integrity_signature_is_deterministic_sha256(): void
    {
        $service = new WompiService;

        $a = $service->generateIntegritySignature('REF123', 100000);
        $b = $service->generateIntegritySignature('REF123', 100000);

        $this->assertSame($a, $b, 'La firma debe ser determinista para los mismos datos.');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $a, 'Debe ser un SHA-256 en hex.');

        $different = $service->generateIntegritySignature('REF124', 100000);
        $this->assertNotSame($a, $different, 'Referencias distintas deben producir firmas distintas.');
    }

    public function test_checkout_url_contains_required_params(): void
    {
        $service = new WompiService;

        $url = $service->checkoutUrl('REF123', 100000, [
            'email' => 'cliente@example.com',
            'firstname' => 'Ana',
            'lastname' => 'Pérez',
            'cellphone' => '3001234567',
        ], 'https://training.test/payments/response');

        $this->assertStringStartsWith('https://checkout.wompi.co/p/?', $url);
        $this->assertStringContainsString('currency=COP', $url);
        $this->assertStringContainsString('amount-in-cents=100000', $url);
        $this->assertStringContainsString('reference=REF123', $url);
        $this->assertStringContainsString('signature:integrity=', $url);
        $this->assertStringContainsString('redirect-url=', $url);
    }

    public function test_webhook_signature_fails_closed_without_secret(): void
    {
        $service = new WompiService;
        $this->setEventsSecret($service, '');

        $payload = $this->webhookPayload('checksum-irrelevante', 'whatever');

        $this->assertFalse(
            $service->verifyWebhookSignature($payload, 'whatever'),
            'Sin events secret la verificación debe fallar (fail-closed).'
        );
    }

    public function test_webhook_signature_validates_correct_checksum_and_rejects_tampered(): void
    {
        $secret = 'test_events_secret';
        $service = new WompiService;
        $this->setEventsSecret($service, $secret);

        $timestamp = 1610641025;
        $concat = 'T1'.'APPROVED'.'4900000'.$timestamp.$secret;
        $checksum = hash('sha256', $concat);

        $payload = $this->webhookPayload($checksum, $timestamp);

        $this->assertTrue($service->verifyWebhookSignature($payload, $checksum), 'Checksum correcto.');
        $this->assertTrue($service->verifyWebhookSignature($payload, ''), 'Checksum del body cuando no llega header.');
        $this->assertFalse($service->verifyWebhookSignature($payload, 'deadbeef'), 'Checksum manipulado.');
    }

    private function webhookPayload(string $checksum, int|string $timestamp): array
    {
        return [
            'timestamp' => $timestamp,
            'signature' => [
                'properties' => ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'],
                'checksum' => $checksum,
            ],
            'data' => [
                'transaction' => [
                    'id' => 'T1',
                    'status' => 'APPROVED',
                    'amount_in_cents' => 4900000,
                    'reference' => 'REF123',
                ],
            ],
        ];
    }

    private function setEventsSecret(WompiService $service, string $secret): void
    {
        $ref = new \ReflectionProperty($service, 'eventsSecret');
        $ref->setAccessible(true);
        $ref->setValue($service, $secret);
    }
}
