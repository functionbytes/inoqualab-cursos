<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Catálogos base: condiciones, métodos y tipos.
 *
 * Existían en producción pero ningún seeder los creaba, así que una base
 * recién migrada arrancaba sin ellos y el sistema no funcionaba: el checkout
 * no puede crear una orden sin `order_condition`/`order_method`/`order_type`,
 * `invoices:generate` aborta sin `invoice_condition`/`invoice_method`, y las
 * lecciones necesitan `course_types` para saber si son vídeo, PDF o quiz.
 *
 * Los ids van fijos porque hay código que los referencia por número —
 * LessonsController mapea 2=audio, 3=imagen, 4=zip, 5=pdf, y el checkout
 * compara contra Condition::Pagada->value.
 *
 * Idempotente: insertOrIgnore permite relanzarlo sin duplicar ni pisar datos.
 */
class CatalogsSeeder extends Seeder
{
    public function run(): void
    {
        $this->sembrar('order_condition', [
            1 => 'Generada', 2 => 'Pendiente', 3 => 'Rechazada', 4 => 'Pagada',
        ], [4 => 'payment']);

        $this->sembrar('order_method', [
            1 => 'Efectivo', 2 => 'Tarjeta', 3 => 'Credito', 4 => 'PSE', 5 => 'Nequi',
        ], [1 => 'cash', 2 => 'card', 3 => 'credit']);

        $this->sembrar('order_type', [
            1 => 'Online', 2 => 'Servicio',
        ], [2 => 'services']);

        $this->sembrar('invoice_condition', [
            1 => 'Generada', 2 => 'Pendiente', 3 => 'Rechazada', 4 => 'Pagada',
        ]);

        $this->sembrar('invoice_method', [
            1 => 'Efectivo', 2 => 'Tarjeta', 3 => 'Credito',
        ], [1 => 'cash', 2 => 'card', 3 => 'credit']);

        $this->sembrar('course_types', [
            1 => 'VIDEO', 2 => 'AUDIO', 3 => 'IMAGEN', 4 => 'ZIP',
            5 => 'PDF', 6 => 'QUIZ', 7 => 'TEXTO',
        ], [
            1 => 'video', 2 => 'audio', 3 => 'image', 4 => 'zip',
            5 => 'pdf', 6 => 'quiz', 7 => 'text',
        ]);
    }

    /**
     * @param  array<int, string>  $filas  id => título
     * @param  array<int, string>  $slugs  id => slug, cuando no se deriva del título
     */
    private function sembrar(string $tabla, array $filas, array $slugs = []): void
    {
        $columnas = DB::getSchemaBuilder()->getColumnListing($tabla);

        foreach ($filas as $id => $titulo) {
            $registro = ['id' => $id, 'title' => $titulo];

            if (in_array('slug', $columnas, true)) {
                $registro['slug'] = $slugs[$id] ?? Str::slug($titulo);
            }
            if (in_array('slack', $columnas, true)) {
                $registro['slack'] = Str::random(6);
            }
            foreach (['created_at', 'updated_at'] as $fecha) {
                if (in_array($fecha, $columnas, true)) {
                    $registro[$fecha] = now();
                }
            }

            DB::table($tabla)->insertOrIgnore($registro);
        }
    }
}
