<?php

namespace Database\Seeders;

use App\Models\Course\CourseAlias;
use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseAlias;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mapeos conocidos para la interceptación de correos → órdenes.
 *
 * Siembra el agrupador y los alias de empresa/curso derivados del correo
 * de San Diego / rednacional. Idempotente: se puede correr múltiples veces.
 *
 *   php artisan db:seed --class=Database\\Seeders\\IncomingMailMappingSeeder
 */
class IncomingMailMappingSeeder extends Seeder
{
    public function run(): void
    {
        $normalize = fn (string $v): string => trim(preg_replace('/\s+/', ' ', strtolower(Str::ascii($v))));

        // 1) Agrupador C00 → DOMIORIENTE S.A.S. (id 9)
        $enterprise = Enterprise::find(9);

        if ($enterprise === null) {
            $this->command?->warn('Empresa id=9 (DOMIORIENTE) no existe; se omite el mapeo.');

            return;
        }

        if (blank($enterprise->code)) {
            $enterprise->code = 'C00';
            $enterprise->save();
            $this->command?->info("Agrupador C00 asignado a {$enterprise->title}.");
        } else {
            $this->command?->info("Empresa {$enterprise->title} ya tiene code={$enterprise->code}; no se modifica.");
        }

        // 2) Alias de empresa (código + razón social) como respaldo del matcher
        EnterpriseAlias::firstOrCreate(
            [
                'enterprise_id' => $enterprise->id,
                'alias_type' => EnterpriseAlias::TYPE_CODE,
                'normalized_value' => $normalize('C00'),
            ],
            ['alias_value' => 'C00'],
        );

        EnterpriseAlias::firstOrCreate(
            [
                'enterprise_id' => $enterprise->id,
                'alias_type' => EnterpriseAlias::TYPE_NAME,
                'normalized_value' => $normalize('DOMIORIENTE S.A.S.'),
            ],
            ['alias_value' => 'DOMIORIENTE S.A.S.'],
        );

        // 3) Alias de curso: "BPM VIRTUAL" → curso id 23 (programa completo)
        CourseAlias::firstOrCreate(
            ['normalized_alias' => $normalize('BPM VIRTUAL')],
            [
                'course_id' => 23,
                'alias' => 'BPM VIRTUAL',
                'source' => 'seeder',
            ],
        );

        $this->command?->info('Alias de empresa y curso sembrados.');
    }
}
