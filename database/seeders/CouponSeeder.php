<?php

namespace Database\Seeders;

use App\Models\Coupon\Coupon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            // 1. Porcentaje global — sin restricción de producto
            [
                'title' => 'Descuento 10% — todos los productos',
                'description' => 'Aplica el 10% sobre cualquier curso o paquete del carrito.',
                'code' => 'TEST10',
                'type' => 1,       // 1 = porcentaje
                'amount' => 10,
                'min_price' => 0,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 100,
                'usage' => 0,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 1,
            ],

            // 2. Porcentaje alto — para probar descuentos grandes
            [
                'title' => 'Descuento 50% — todos los productos',
                'description' => 'Mitad de precio en todo el carrito.',
                'code' => 'TEST50',
                'type' => 1,
                'amount' => 50,
                'min_price' => 0,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 100,
                'usage' => 0,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 1,
            ],

            // 3. Monto fijo — tarifa plana
            [
                'title' => 'Descuento fijo $10.000',
                'description' => 'Resta $10.000 al total del carrito.',
                'code' => 'FIJO10K',
                'type' => 0,       // 0 = monto fijo
                'amount' => 10000,
                'min_price' => 0,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 100,
                'usage' => 0,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 1,
            ],

            // 4. Porcentaje restringido a un curso específico (ID 1)
            [
                'title' => 'Descuento 20% — curso 1 únicamente',
                'description' => 'Solo aplica sobre el Módulo 1 (ID 1).',
                'code' => 'CURSO1-20',
                'type' => 1,
                'amount' => 20,
                'min_price' => 0,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 100,
                'usage' => 0,
                'course_ids' => '1',
                'bundle_ids' => '',
                'available' => 1,
            ],

            // 5. Porcentaje con precio mínimo
            [
                'title' => 'Descuento 15% — mínimo $50.000',
                'description' => 'Solo aplica si el carrito supera $50.000.',
                'code' => 'MIN50K',
                'type' => 1,
                'amount' => 15,
                'min_price' => 50000,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 100,
                'usage' => 0,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 1,
            ],

            // 6. Cupón vencido — para probar rechazo por fecha
            [
                'title' => 'Cupón vencido (expirado)',
                'description' => 'Fecha de fin en el pasado. Debe rechazarse.',
                'code' => 'VENCIDO',
                'type' => 1,
                'amount' => 25,
                'min_price' => 0,
                'start_date' => '2020-01-01',
                'end_date' => '2020-12-31',
                'limit' => 100,
                'usage' => 0,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 1,
            ],

            // 7. Cupón sin disponibilidad (desactivado)
            [
                'title' => 'Cupón inactivo',
                'description' => 'available = 0. Debe rechazarse aunque la fecha sea válida.',
                'code' => 'INACTIVO',
                'type' => 1,
                'amount' => 30,
                'min_price' => 0,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 100,
                'usage' => 0,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 0,
            ],

            // 8. Cupón con límite de uso agotado
            [
                'title' => 'Cupón límite agotado',
                'description' => 'usage >= limit. Debe rechazarse por uso máximo.',
                'code' => 'AGOTADO',
                'type' => 1,
                'amount' => 20,
                'min_price' => 0,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 5,
                'usage' => 5,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 1,
            ],

            // 9. 100% de descuento — borde superior
            [
                'title' => 'Descuento 100% — gratis',
                'description' => 'Borde superior: descuento total del carrito.',
                'code' => 'GRATIS100',
                'type' => 1,
                'amount' => 100,
                'min_price' => 0,
                'start_date' => '2025-01-01',
                'end_date' => '2030-12-31',
                'limit' => 10,
                'usage' => 0,
                'course_ids' => '',
                'bundle_ids' => '',
                'available' => 1,
            ],
        ];

        foreach ($coupons as $data) {
            Coupon::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['slack' => strtoupper(Str::random(6))])
            );
        }

        $this->command->info('CouponSeeder: '.count($coupons).' cupones de prueba insertados.');
    }
}
