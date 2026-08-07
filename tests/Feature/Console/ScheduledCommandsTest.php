<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Los 15 comandos del scheduler nunca se habían ejecutado en un test.
 *
 * Importa ahora más que nunca: el cron del servidor lleva tiempo sin correr
 * (la poda de activity_log no se ejecutó en 19 meses), así que cuando se
 * active, los 15 arrancarán de golpe sobre datos reales. Si alguno estaba roto
 * desde hace meses, se descubriría en producción y con efectos secundarios:
 * varios borran registros, generan facturas o envían correos.
 *
 * Aquí se ejecutan contra una base limpia, con Mail/Notification/Queue
 * simulados. No se comprueba la lógica de negocio —cada comando tiene (o
 * debería tener) sus propios tests— sino que arrancan y terminan sin reventar.
 */
class ScheduledCommandsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tal y como están declarados en routes/console.php.
     *
     * `mail:fetch-orders` queda fuera: abre una conexión IMAP real y en el
     * entorno de tests INCOMING_MAIL_ENABLED viene a false, así que probarlo
     * aquí no diría nada útil.
     */
    public static function scheduledCommands(): array
    {
        return [
            'facturas mensuales' => ['invoices:generate'],
            'purga de notificaciones' => ['notification:autodelete'],
            'purga de clientes inactivos' => ['customers:inactive_delete'],
            'reconciliar pagos pendientes' => ['orders:reconcile-pending'],
            'recordatorio de carrito' => ['orders:remind-abandoned --hours=12'],
            'limpiar órdenes abandonadas' => ['orders:cleanup-abandoned'],
            'reparar matrículas' => ['orders:repair-enrollments'],
            'aviso certificados 30d' => ['certificates:notify-expiring --days=30'],
            'aviso certificados 7d' => ['certificates:notify-expiring --days=7'],
            'cross-sell tras completar' => ['courses:notify-completed --days=1'],
            'aviso de acceso por vencer' => ['courses:notify-expiring-access --days=7'],
            'reprocesar correos fallidos' => ['mails:reparse-failed'],
            'informes de analítica' => ['analytics:dispatch-schedules'],
            'poda del audit trail' => ['activitylog:clean'],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Nada debe salir del proceso durante el barrido.
        Mail::fake();
        Notification::fake();
        Queue::fake();

        // Catálogos que varios comandos dan por hechos. Existen en producción
        // pero NO hay seeder que los cree, así que una base recién migrada
        // (o la de tests) se queda sin ellos.
        DB::table('invoice_condition')->insertOrIgnore([
            ['id' => 1, 'slack' => 'invc01', 'title' => 'Generada', 'slug' => 'generada'],
        ]);
        DB::table('invoice_method')->insertOrIgnore([
            ['id' => 3, 'slack' => 'invm03', 'title' => 'Credito', 'slug' => 'credit'],
        ]);
    }

    #[DataProvider('scheduledCommands')]
    public function test_scheduled_command_runs_without_crashing(string $command): void
    {
        // activitylog:clean pide confirmación cuando APP_ENV parece producción.
        $args = str_contains($command, 'activitylog:clean') ? ' --force' : '';

        $exit = $this->artisan($command.$args)->run();

        $this->assertSame(
            0,
            $exit,
            "`php artisan {$command}` terminó con código {$exit}. ".
            'Cuando se active el cron, este comando fallará en producción.'
        );
    }

    public function test_every_scheduled_command_is_covered_by_this_test(): void
    {
        // Guardarraíl: si alguien añade un comando al scheduler y no lo mete
        // aquí, este test lo detecta en vez de dejarlo sin probar.
        $schedule = app(Schedule::class);

        $programados = collect($schedule->events())
            ->map(fn ($e) => $e->command)
            ->filter()
            // $e->command llega como "'/ruta/php' 'artisan' 'orders:reconcile-pending'".
            // Interesa el primer token tras 'artisan'.
            ->map(function ($c) {
                preg_match("/artisan'?\s+'?([a-z0-9:_-]+)/i", $c, $m);

                return $m[1] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $cubiertos = collect(self::scheduledCommands())
            ->map(fn ($c) => explode(' ', $c[0])[0])
            ->push('mail:fetch-orders')   // excluido a propósito: abre IMAP real
            ->unique();

        $sinCubrir = $programados->diff($cubiertos)->values();

        $this->assertEmpty(
            $sinCubrir->all(),
            'Hay comandos en el scheduler sin cubrir aquí: '.$sinCubrir->implode(', ')
        );
    }

    /**
     * Regresión: invoices:generate corría mensualmente sin withoutOverlapping().
     * Relanzarlo a mano mientras el cron también corría podía generar facturas
     * duplicadas por distribuidor (ver también lockForUpdate() en Invoices::handle()).
     */
    public function test_invoices_generate_has_overlap_protection(): void
    {
        $schedule = app(Schedule::class);

        $event = collect($schedule->events())->first(
            fn ($e) => str_contains($e->command, 'invoices:generate')
        );

        $this->assertNotNull($event, 'invoices:generate ya no está en el scheduler.');

        $reflection = new \ReflectionProperty($event, 'withoutOverlapping');
        $reflection->setAccessible(true);

        $this->assertTrue(
            $reflection->getValue($event),
            'invoices:generate debe tener withoutOverlapping() -- sin él, dos corridas solapadas duplican facturas.'
        );
    }
}
