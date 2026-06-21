<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsletterMailjetService
{
    private const BASE = 'https://api.mailjet.com/v3/REST';

    public function isEnabled(): bool
    {
        return setting('newsletter_mailjet_enabled') == 1
            && filled(setting('newsletter_mailjet_api_key'))
            && filled(setting('newsletter_mailjet_api_secret'))
            && filled(setting('newsletter_mailjet_list_id'));
    }

    public function addContact(string $email, ?string $name): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            [$key, $secret] = $this->credentials();
            $listId = (int) setting('newsletter_mailjet_list_id');

            // Crear contacto — ignorar 400 si ya existe
            Http::withBasicAuth($key, $secret)
                ->post(self::BASE.'/contact', [
                    'Email' => $email,
                    'Name' => $name ?? '',
                ]);

            // Suscribir a la lista
            $res = Http::withBasicAuth($key, $secret)
                ->post(self::BASE."/contact/{$email}/managecontactslists", [
                    'ContactsLists' => [
                        ['Action' => 'addnoforce', 'ListID' => $listId],
                    ],
                ]);

            if ($res->failed()) {
                Log::warning('Mailjet addContact list failed', [
                    'email' => $email,
                    'status' => $res->status(),
                    'body' => $res->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Mailjet addContact failed', ['email' => $email, 'error' => $e->getMessage()]);
        }
    }

    public function removeContact(string $email): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            [$key, $secret] = $this->credentials();
            $listId = (int) setting('newsletter_mailjet_list_id');

            $res = Http::withBasicAuth($key, $secret)
                ->post(self::BASE."/contact/{$email}/managecontactslists", [
                    'ContactsLists' => [
                        ['Action' => 'unsub', 'ListID' => $listId],
                    ],
                ]);

            if ($res->failed()) {
                Log::warning('Mailjet removeContact failed', [
                    'email' => $email,
                    'status' => $res->status(),
                    'body' => $res->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Mailjet removeContact failed', ['email' => $email, 'error' => $e->getMessage()]);
        }
    }

    private function credentials(): array
    {
        return [
            setting('newsletter_mailjet_api_key'),
            setting('newsletter_mailjet_api_secret'),
        ];
    }
}
