<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class WompiService
{
    private string $publicKey;

    private string $integritySecret;

    private string $eventsSecret;

    private bool $sandbox;

    private string $baseUrl;

    public function __construct()
    {
        // El helper setting() NUNCA devuelve null: su firma es
        // setting($key, $default = '') y entrega ese default cuando la clave no
        // existe o su valor es vacío. La comprobación `!== null` se cumplía
        // siempre, así que el fallback a config() era código muerto y un
        // entorno sin el ajuste caía en `'' === 'true'` = false, es decir,
        // apuntaba a la API de PRODUCCIÓN de Wompi mientras
        // config('services.wompi.sandbox') decía true.
        $sandboxSetting = setting('wompi_sandbox');
        $this->sandbox = $sandboxSetting === ''
            ? (bool) config('services.wompi.sandbox', true)
            : $sandboxSetting === 'true';

        // Credenciales separadas por entorno (sandbox/producción) para poder
        // guardar ambos juegos de llaves a la vez y solo cambiar cuál está
        // activo con el toggle. Si aún no se migró a las claves con sufijo,
        // cae a las claves legacy sin sufijo (config unica de antes de este
        // cambio) para no romper instalaciones existentes.
        $envSuffix = $this->sandbox ? '_sandbox' : '_production';
        $this->publicKey = setting('wompi_public_key'.$envSuffix)
            ?: setting('wompi_public_key')
            ?: config('services.wompi.public_key', '');
        $this->integritySecret = setting('wompi_integrity_secret'.$envSuffix)
            ?: setting('wompi_integrity_secret')
            ?: config('services.wompi.integrity_secret', '');
        $this->eventsSecret = setting('wompi_events_secret'.$envSuffix)
            ?: setting('wompi_events_secret')
            ?: config('services.wompi.events_secret', '');

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
        $client = new Client(['timeout' => 15]);

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = $client->get("{$this->baseUrl}/transactions/{$transactionId}");
                $data = json_decode((string) $response->getBody(), true);

                return $data['data'] ?? null;
            } catch (ClientException $e) {
                // 4xx (p.ej. 404: transacción inexistente) es definitivo, no se reintenta.
                Log::warning('Wompi getTransaction 4xx', [
                    'id' => $transactionId,
                    'status' => $e->getResponse()?->getStatusCode(),
                ]);

                return null;
            } catch (\Throwable $e) {
                // Error de red / 5xx: fallo transitorio -> reintentar con backoff.
                Log::warning('Wompi getTransaction fallo transitorio', [
                    'id' => $transactionId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < 3) {
                    usleep(300000 * $attempt);
                }
            }
        }

        return null;
    }

    /**
     * Verifica la firma del webhook enviado por Wompi.
     * Concatenación: transactionId + status + amountInCents + currency + checksum + eventsSecret
     */
    /**
     * Valida la firma de un evento (webhook) de Wompi según su especificación:
     * se concatenan los valores de los campos listados en signature.properties
     * (en orden, extraídos de data), luego el timestamp y luego el events_secret;
     * se hace SHA256 y se compara con el checksum recibido (header X-Event-Checksum
     * o body signature.checksum). Ver https://docs.wompi.co/docs/colombia/eventos/
     */
    public function verifyWebhookSignature(array $payload, string $receivedChecksum = ''): bool
    {
        // Fail-closed: sin secret configurado no se puede verificar -> rechazar y avisar.
        if (empty($this->eventsSecret)) {
            Log::error('Wompi events secret no configurado: webhook rechazado (fail-closed). Configura wompi_events_secret.');

            return false;
        }

        $properties = $payload['signature']['properties'] ?? null;
        $checksum = $receivedChecksum !== '' ? $receivedChecksum : ($payload['signature']['checksum'] ?? '');
        $timestamp = $payload['timestamp'] ?? null;
        $data = $payload['data'] ?? [];

        if (! is_array($properties) || $properties === [] || $checksum === '' || $timestamp === null) {
            Log::warning('Webhook Wompi sin signature.properties/checksum/timestamp válidos.');

            return false;
        }

        // Valores de las propiedades indicadas (en orden) + timestamp + events_secret.
        $concatenated = '';
        foreach ($properties as $property) {
            $concatenated .= (string) data_get($data, $property, '');
        }
        $concatenated .= (string) $timestamp;
        $concatenated .= $this->eventsSecret;

        $computed = hash('sha256', $concatenated);

        return hash_equals(strtoupper($computed), strtoupper((string) $checksum));
    }
}
