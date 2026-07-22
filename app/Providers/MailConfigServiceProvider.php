<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // En local y testing usamos siempre la config del .env/.env.testing
        // (Mailpit, mailer 'array', etc.) — sobreescribir aquí con credenciales
        // SMTP reales de BD rompería Mail::fake() y cualquier test que dispare
        // un envío de correo (Mail::html() raw no pasa por una Mailable
        // fake-eable de la misma forma, así que igual intentaría la conexión).
        if ($this->app->environment(['local', 'testing'])) {
            return;
        }

        try {
            DB::connection()->getPdo();

            if (! DB::getSchemaBuilder()->hasTable('settings')) {
                return;
            }

            $rows = DB::table('settings')
                ->whereIn('key', [
                    'mail_driver', 'mail_host', 'mail_port',
                    'mail_from_address', 'mail_from_name',
                    'mail_encryption', 'mail_username', 'mail_password',
                ])
                ->pluck('value', 'key');

            $driver = $rows->get('mail_driver', 'smtp');

            // 'mailjet' como driver legacy se mapea a smtp usando su servidor SMTP
            $transport = match ($driver) {
                'mailjet' => 'smtp',
                default => $driver,
            };

            Config::set('mail.default', 'db_mailer');
            Config::set('mail.mailers.db_mailer', [
                'transport' => $transport,
                'host' => $rows->get('mail_host', 'smtp.mailgun.org'),
                'port' => (int) $rows->get('mail_port', 587),
                'encryption' => $rows->get('mail_encryption', 'tls') ?: 'tls',
                'username' => $rows->get('mail_username'),
                'password' => $rows->get('mail_password'),
                'timeout' => null,
            ]);
            Config::set('mail.from', [
                'address' => $rows->get('mail_from_address', config('mail.from.address')),
                'name' => $rows->get('mail_from_name', config('mail.from.name')),
            ]);

        } catch (\Exception) {
            // No hay BD disponible — se usa la config del .env
        }
    }

    public function boot(): void {}
}
