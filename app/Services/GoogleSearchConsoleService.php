<?php

namespace App\Services;

use App\Models\Seo\SeoMeta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Search Console API client using OAuth 2.0.
 *
 * Setup:
 *  1. Create an OAuth 2.0 Web Client in Google Cloud Console.
 *  2. Add redirect URI: <app-url>/panel/seo/gsc/callback
 *  3. Set GSC_CLIENT_ID, GSC_CLIENT_SECRET in .env
 *  4. Visit /panel/seo/gsc to connect.
 *
 * The refresh token is stored in storage/app/seo-gsc.json.
 */
class GoogleSearchConsoleService
{
    private const OAUTH_AUTHORIZE = 'https://accounts.google.com/o/oauth2/v2/auth';

    private const OAUTH_TOKEN = 'https://oauth2.googleapis.com/token';

    private const API_BASE = 'https://searchconsole.googleapis.com/v1';

    private const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    private const TOKEN_FILE = 'seo-gsc.json';

    public function isConfigured(): bool
    {
        return $this->clientId() !== '' && $this->clientSecret() !== '';
    }

    public function isConnected(): bool
    {
        return $this->isConfigured() && $this->getToken('refresh_token') !== null;
    }

    public function authorizationUrl(string $state = ''): string
    {
        return self::OAUTH_AUTHORIZE.'?'.http_build_query([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function handleCallback(string $code): bool
    {
        $response = Http::asForm()->post(self::OAUTH_TOKEN, [
            'code' => $code,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'redirect_uri' => $this->redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful()) {
            Log::warning('GSC OAuth callback failed', ['body' => $response->body()]);

            return false;
        }

        $refreshToken = $response->json('refresh_token');

        if (! $refreshToken) {
            return false;
        }

        $this->saveToken('refresh_token', $refreshToken);

        return true;
    }

    public function disconnect(): void
    {
        $path = storage_path('app/'.self::TOKEN_FILE);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * @return array<int, array{page: string, clicks: int, impressions: int, ctr: float, position: float}>
     */
    public function fetchSearchAnalytics(int $days = 28): array
    {
        $token = $this->accessToken();
        if (! $token) {
            return [];
        }

        $property = $this->propertyUrl();
        if (! $property) {
            return [];
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->post(self::API_BASE.'/sites/'.urlencode($property).'/searchAnalytics/query', [
                'startDate' => now()->subDays($days)->toDateString(),
                'endDate' => now()->toDateString(),
                'dimensions' => ['page'],
                'rowLimit' => 1000,
            ]);

        if (! $response->successful()) {
            Log::warning('GSC searchAnalytics failed', ['body' => $response->body()]);

            return [];
        }

        return array_map(fn ($row) => [
            'page' => $row['keys'][0] ?? '',
            'clicks' => (int) ($row['clicks'] ?? 0),
            'impressions' => (int) ($row['impressions'] ?? 0),
            'ctr' => (float) ($row['ctr'] ?? 0),
            'position' => (float) ($row['position'] ?? 0),
        ], $response->json('rows') ?? []);
    }

    public function importIntoMetas(int $days = 28): int
    {
        $rows = $this->fetchSearchAnalytics($days);
        $updated = 0;

        foreach ($rows as $row) {
            if (! $row['page']) {
                continue;
            }

            $meta = SeoMeta::where('canonical_url', $row['page'])->first();
            if (! $meta) {
                continue;
            }

            $meta->forceFill([
                'gsc_clicks' => $row['clicks'],
                'gsc_impressions' => $row['impressions'],
                'gsc_position' => round($row['position'], 1),
                'gsc_updated_at' => now(),
            ])->saveQuietly();

            $updated++;
        }

        return $updated;
    }

    private function accessToken(): ?string
    {
        $refreshToken = $this->getToken('refresh_token');
        if (! $refreshToken) {
            return null;
        }

        $response = Http::asForm()->post(self::OAUTH_TOKEN, [
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId(),
            'client_secret' => $this->clientSecret(),
            'grant_type' => 'refresh_token',
        ]);

        return $response->successful() ? (string) $response->json('access_token') : null;
    }

    private function clientId(): string
    {
        return (string) config('services.gsc.client_id');
    }

    private function clientSecret(): string
    {
        return (string) config('services.gsc.client_secret');
    }

    private function redirectUri(): string
    {
        return route('manager.seo.gsc.callback');
    }

    private function propertyUrl(): string
    {
        return (string) (config('services.gsc.property_url') ?: config('app.url', ''));
    }

    private function getToken(string $key): ?string
    {
        $path = storage_path('app/'.self::TOKEN_FILE);
        if (! file_exists($path)) {
            return null;
        }

        $data = json_decode(file_get_contents($path), true);

        return $data[$key] ?? null;
    }

    private function saveToken(string $key, string $value): void
    {
        $path = storage_path('app/'.self::TOKEN_FILE);
        $data = file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
        $data[$key] = $value;
        file_put_contents($path, json_encode($data));
    }
}
