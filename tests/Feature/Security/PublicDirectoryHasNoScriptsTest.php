<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Guardarraíl: en public/ no debe haber PHP ejecutable salvo el front controller.
 *
 * El .htaccess de Laravel reescribe a index.php SOLO cuando el fichero pedido no
 * existe (`RewriteCond %{REQUEST_FILENAME} !-f`). Cualquier .php real dentro de
 * public/ queda por tanto accesible y se ejecuta directamente, saltándose el
 * kernel entero: sin sesión, sin middleware, sin autenticación.
 *
 * Se añadió tras encontrar en la rama tres scripts de depuración versionados
 * (test_mem.php, debug_server.php y un opcache_clear_temp.php que permitía a
 * cualquiera vaciar el OPcache del servidor) más dos samples de CKEditor que
 * reflejaban el POST recibido.
 */
class PublicDirectoryHasNoScriptsTest extends TestCase
{
    /**
     * Ficheros PHP admitidos en public/, con el motivo.
     *
     * Los index.php de pages/files/* son centinelas contra el listado de
     * directorio; no ejecutan nada.
     */
    private const ALLOWED = [
        'index.php',
        'pages/files/index.php',
        'pages/files/audio/index.php',
        'pages/files/pdf/index.php',
        'pages/files/zip/index.php',
    ];

    public function test_public_contains_no_unexpected_php_files(): void
    {
        $found = [];

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(public_path(), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
                continue;
            }

            $relative = str_replace(public_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

            if (! in_array($relative, self::ALLOWED, true)) {
                $found[] = $relative;
            }
        }

        sort($found);

        $this->assertSame([], $found, sprintf(
            "Hay PHP ejecutable en public/ que no está en la lista permitida:\n  %s\n\n".
            'Todo .php ahí se sirve directamente sin pasar por Laravel. Bórralo, muévelo fuera '.
            'del docroot, o añádelo a self::ALLOWED si de verdad tiene que estar.',
            implode("\n  ", $found)
        ));
    }
}
