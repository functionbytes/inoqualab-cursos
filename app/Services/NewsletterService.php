<?php

namespace App\Services;

use App\Mail\Newsletter\DoubleOptinMail;
use App\Mail\Newsletter\NewSubscriberAdminMail;
use App\Mail\Newsletter\SubscribedMail;
use App\Mail\Newsletter\UnsubscribedMail;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterService
{
    public function __construct(
        private readonly NewsletterMailjetService $mailjet
    ) {}

    public function subscribe(string $email, ?string $name, string $ip): Newsletter
    {
        $doubleOptin = setting('newsletter_double_optin') == 1;

        $subscriber = Newsletter::query()->firstOrCreate(
            ['email' => $email],
            [
                'slack' => Str::uuid(),
                'name' => $name,
                'source' => 'form',
                'is_active' => ! $doubleOptin,
                'ip_address' => $ip,
                'subscribed_at' => $doubleOptin ? null : now(),
            ]
        );

        if ($doubleOptin) {
            // Already active (previously confirmed) → nothing to do
            if ($subscriber->is_active) {
                return $subscriber;
            }

            // Pending or previously unsubscribed → send/resend confirmation
            $subscriber->update(['confirmation_token' => Str::random(64)]);
            Mail::queue(new DoubleOptinMail($subscriber->fresh()));
        } else {
            $wasReactivated = false;

            if (! $subscriber->wasRecentlyCreated && ! $subscriber->is_active) {
                $subscriber->subscribe();
                $wasReactivated = true;
            }

            if ($subscriber->wasRecentlyCreated || $wasReactivated) {
                Mail::queue(new SubscribedMail($subscriber));

                if (setting('newsletter_email_notifications') == 1) {
                    Mail::queue(new NewSubscriberAdminMail($subscriber));
                }

                $this->mailjet->addContact($email, $name);
            }
        }

        return $subscriber;
    }

    public function confirmSubscription(string $token): ?Newsletter
    {
        $subscriber = Newsletter::query()
            ->where('confirmation_token', $token)
            ->where('is_active', false)
            ->whereNotNull('confirmation_token')
            ->first();

        if (! $subscriber) {
            return null;
        }

        // Token expira a los 7 días desde que se generó (refleja updated_at)
        if ($subscriber->updated_at->lt(now()->subDays(7))) {
            $subscriber->update(['confirmation_token' => null]);

            return null;
        }

        $subscriber->update([
            'is_active' => true,
            'subscribed_at' => now(),
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);

        Mail::queue(new SubscribedMail($subscriber->fresh()));

        if (setting('newsletter_email_notifications') == 1) {
            Mail::queue(new NewSubscriberAdminMail($subscriber));
        }

        $this->mailjet->addContact($subscriber->email, $subscriber->name);

        return $subscriber->fresh();
    }

    public function resendConfirmation(Newsletter $subscriber): bool
    {
        if ($subscriber->is_active || ! $subscriber->confirmation_token) {
            return false;
        }

        $subscriber->update(['confirmation_token' => Str::random(64)]);
        Mail::queue(new DoubleOptinMail($subscriber->fresh()));

        return true;
    }

    public function addManual(string $email, ?string $name): array
    {
        $subscriber = Newsletter::query()->firstOrCreate(
            ['email' => $email],
            [
                'slack' => Str::uuid(),
                'name' => $name,
                'source' => 'manual',
                'is_active' => true,
                'subscribed_at' => now(),
            ]
        );

        $wasReactivated = false;
        if (! $subscriber->wasRecentlyCreated && ! $subscriber->is_active) {
            $subscriber->subscribe();
            $wasReactivated = true;
        }

        if ($subscriber->wasRecentlyCreated || $wasReactivated) {
            $this->mailjet->addContact($email, $name);
        }

        $message = ($subscriber->wasRecentlyCreated || $wasReactivated)
            ? 'Suscriptor añadido correctamente.'
            : 'El correo ya está registrado como suscriptor activo.';

        return compact('message');
    }

    public function importCsv(UploadedFile $file): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = 0;

        $handle = fopen($file->getRealPath(), 'r');

        // Strip UTF-8 BOM
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $firstRow = true;
        // escape: '\\' preserva el comportamiento histórico y silencia la
        // deprecación de PHP 8.4 (el $escape por defecto dejó de ser implícito).
        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            // Fallback: semicolon delimiter
            if (count($row) === 1 && str_contains($row[0], ';')) {
                $row = str_getcsv($row[0], ';', '"', '\\');
            }

            $email = trim($row[0] ?? '');
            $name = trim($row[1] ?? '') ?: null;

            // Skip header if first cell is not a valid email
            if ($firstRow) {
                $firstRow = false;
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    continue;
                }
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors++;

                continue;
            }

            try {
                $subscriber = Newsletter::query()->firstOrCreate(
                    ['email' => $email],
                    [
                        'slack' => Str::uuid(),
                        'name' => $name,
                        'source' => 'import',
                        'is_active' => true,
                        'subscribed_at' => now(),
                    ]
                );

                if ($subscriber->wasRecentlyCreated) {
                    $this->mailjet->addContact($email, $name);
                    $imported++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable) {
                $errors++;
            }
        }

        fclose($handle);

        $parts = ["{$imported} importados", "{$skipped} ya existían"];
        if ($errors) {
            $parts[] = "{$errors} con error";
        }
        $message = 'Importación completada: '.implode(', ', $parts).'.';

        return compact('imported', 'skipped', 'errors', 'message');
    }

    public function bulkDelete(array $ids): int
    {
        // Capturar los emails antes de borrar para poder limpiar Mailjet después.
        $emails = Newsletter::query()->whereIn('id', $ids)->pluck('email');

        $deleted = Newsletter::query()->whereIn('id', $ids)->delete();

        // Efecto externo fuera de la escritura: deja sincronizado Mailjet.
        foreach ($emails as $email) {
            $this->mailjet->removeContact($email);
        }

        return $deleted;
    }

    public function bulkUnsubscribe(array $ids): int
    {
        $toUnsubscribe = Newsletter::query()
            ->whereIn('id', $ids)
            ->subscribed()
            ->get(['id', 'email', 'is_active']);

        // Escrituras de BD atómicas; los efectos externos (no reversibles) van después.
        DB::transaction(function () use ($toUnsubscribe) {
            foreach ($toUnsubscribe as $newsletter) {
                $newsletter->unsubscribe();
            }
        });

        foreach ($toUnsubscribe as $newsletter) {
            Mail::queue(new UnsubscribedMail($newsletter->email));
            $this->mailjet->removeContact($newsletter->email);
        }

        return $toUnsubscribe->count();
    }

    public function bulkResubscribe(array $ids): int
    {
        $subscribers = Newsletter::query()
            ->whereIn('id', $ids)
            ->unsubscribed()
            ->get(['id', 'email', 'name']);

        DB::transaction(function () use ($subscribers) {
            foreach ($subscribers as $newsletter) {
                $newsletter->subscribe();
            }
        });

        foreach ($subscribers as $newsletter) {
            $this->mailjet->addContact($newsletter->email, $newsletter->name);
        }

        return $subscribers->count();
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $search = $request->query('search');
        $source = $request->query('source');
        $status = $request->query('status');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="newsletter_'.now()->format('Y-m-d').'.csv"',
        ];

        return response()->stream(function () use ($search, $source, $status) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Email', 'Nombre', 'Estado', 'Origen', 'IP', 'Suscrito el', 'Confirmado el', 'Dado de baja el', 'Creado'], ',', '"', '\\');

            Newsletter::query()
                ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                }))
                ->when($source !== null && $source !== '', fn ($q) => $q->where('source', $source))
                ->when($status === 'active', fn ($q) => $q->subscribed())
                ->when($status === 'inactive', fn ($q) => $q->unsubscribed())
                ->orderBy('id')
                ->each(function (Newsletter $n) use ($handle) {
                    fputcsv($handle, [
                        $n->id,
                        $n->email,
                        $n->name ?? '',
                        $n->is_active ? 'Activo' : 'Dado de baja',
                        match ($n->source) {
                            'registration' => 'Registro',
                            'manual' => 'Manual',
                            'import' => 'Importación',
                            default => 'Formulario',
                        },
                        $n->ip_address ?? '',
                        $n->subscribed_at?->format('Y-m-d H:i:s') ?? '',
                        $n->confirmed_at?->format('Y-m-d H:i:s') ?? '',
                        $n->unsubscribed_at?->format('Y-m-d H:i:s') ?? '',
                        $n->created_at?->format('Y-m-d H:i:s') ?? '',
                    ], ',', '"', '\\');
                });

            fclose($handle);
        }, 200, $headers);
    }
}
