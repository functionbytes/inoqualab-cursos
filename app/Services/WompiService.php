<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;

class WompiService
{
    private string $publicKey;

    private string $integritySecret;

    private string $eventsSecret;

    private bool $sandbox;

    private string $baseUrl;

    public function __construct()
    {
        $this->publicKey = setting('wompi_public_key') ?: config('services.wompi.public_key', '');
        $this->integritySecret = setting('wompi_integrity_secret') ?: config('services.wompi.integrity_secret', '');
        $this->eventsSecret = setting('wompi_events_secret') ?: config('services.wompi.events_secret', '');
        $sandboxSetting = setting('wompi_sandbox');
        $this->sandbox = $sandboxSetting !== null
            ? $sandboxSetting === 'true'
            : (bool) config('services.wompi.sandbox', true);
        $this->baseUrl = $this->sandbox
            ? 'https://sandbox.wompi.co/v1'
            : 'https://production.wompi.co/v1';
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Firma de integridad de Wompi.
     * Cadena: <reference><amountInCents><currency>[<expirationTime>]<integritySecret>  (SHA256).
     * Si se envía expiration-time al checkout/widget, DEBE incluirse aquí o la firma será inválida.
     */
    public function generateIntegritySignature(string $reference, int $amountInCents, string $currency = 'COP', ?string $expirationTime = null): string
    {
        $payload = $reference.$amountInCents.$currency;

        if ($expirationTime) {
            $payload .= $expirationTime;
        }

        $payload .= $this->integritySecret;

        return hash('sha256', $payload);
    }

    /**
     * Tiempo de expiración estándar para una transacción (ISO8601, +1 día).
     */
    public function defaultExpiration(): string
    {
        return Carbon::now('America/Bogota')->addDay()->toIso8601String();
    }

    /**
     * Datos para renderizar el WIDGET embebido de Wompi (opción A).
     * La firma incluye la expiración para coincidir con data-expiration-time.
     */
    public function buildWidgetData(string $reference, int $amountInCents, array $customer, string $redirectUrl): array
    {
        $expiration = $this->defaultExpiration();

        return [
            'public_key' => $this->publicKey,
            'currency' => 'COP',
            'amount_in_cents' => $amountInCents,
            'reference' => $reference,
            'signature' => $this->generateIntegritySignature($reference, $amountInCents, 'COP', $expiration),
            'expiration_time' => $expiration,
            'redirect_url' => $redirectUrl,
            'customer' => $customer,
        ];
    }

    /**
     * URL del WEB CHECKOUT alojado de Wompi (opción B, redirección).
     * La firma incluye la expiración para coincidir con el parámetro expiration-time.
     */
    public function checkoutUrl(string $reference, int $amountInCents, array $customer, string $redirectUrl): string
    {
        $expiration = $this->defaultExpiration();

        $params = [
            'public-key' => $this->publicKey,
            'currency' => 'COP',
            'amount-in-cents' => (string) $amountInCents,
            'reference' => $reference,
            'signature:integrity' => $this->generateIntegritySignature($reference, $amountInCents, 'COP', $expiration),
            'redirect-url' => $redirectUrl,
            'expiration-time' => $expiration,
            'customer-data:email' => $customer['email'] ?? '',
            'customer-data:full-name' => trim(($customer['firstname'] ?? '').' '.($customer['lastname'] ?? '')),
            'customer-data:phone-number' => $customer['cellphone'] ?? '',
        ];

        // Mantenemos las claves literales (incluido ":") y codificamos solo los valores.
        $query = collect($params)
            ->map(fn ($value, $key) => $key.'='.rawurlencode($value))
            ->implode('&');

        return 'https://checkout.wompi.co/p/?'.$query;
    }

    /**
     * Consulta una transacción en la API de Wompi por su ID.
     */
    public function getTransaction(string $transactionId): ?array
    {
        try {
            $client = new Client(['timeout' => 15]);
            $response = $client->get("{$this->baseUrl}/transactions/{$transactionId}");
            $data = json_decode((string) $response->getBody(), true);

            return $data['data'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Verifica la firma del webhook enviado por Wompi.
     * Concatenación: transactionId + status + amountInCents + currency + checksum + eventsSecret
     */
    public function verifyWebhookSignature(array $payload, string $receivedSignature): bool
    {
        if (empty($this->eventsSecret)) {
            return true;
        }

        $transaction = $payload['data']['transaction'] ?? [];
        $concatenated = implode('', [
            $transaction['id'] ?? '',
            $transaction['status'] ?? '',
            $transaction['amount_in_cents'] ?? '',
            $transaction['currency'] ?? '',
            $payload['signature']['checksum'] ?? '',
        ]);

        $expected = hash('sha256', $concatenated.$this->eventsSecret);

        return hash_equals($expected, $receivedSignature);
    }
}
