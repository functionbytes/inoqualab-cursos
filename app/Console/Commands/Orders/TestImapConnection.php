<?php

namespace App\Console\Commands\Orders;

use Illuminate\Console\Command;
use Webklex\IMAP\Facades\Client;

class TestImapConnection extends Command
{
    protected $signature = 'mail:test-imap';

    protected $description = 'Prueba la conexión IMAP al buzón de órdenes entrantes y muestra el conteo de correos';

    public function handle(): int
    {
        $account = config('incoming_mail.imap_account');
        $folderName = config('incoming_mail.folder');

        $this->info("Cuenta IMAP: {$account} | Carpeta: {$folderName}");

        if (blank(config("imap.accounts.{$account}.host"))) {
            $this->error('IMAP_HOST está vacío en .env. Configura las credenciales primero.');

            return self::FAILURE;
        }

        try {
            $client = Client::account($account);
            $client->connect();
            $this->info('✔ Conexión IMAP establecida.');

            $folder = $client->getFolder($folderName);

            if ($folder === null) {
                $this->error("✘ No se encontró la carpeta '{$folderName}'.");

                return self::FAILURE;
            }

            $total = $folder->messages()->all()->count();
            $unseen = $folder->messages()->unseen()->count();

            $this->table(
                ['Carpeta', 'Total', 'No leídos'],
                [[$folderName, $total, $unseen]],
            );

            $this->info('Todo OK. El comando mail:fetch-orders ya puede leer este buzón.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('✘ Falló la conexión IMAP: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
