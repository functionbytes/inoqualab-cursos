<?php

namespace Database\Factories\Order;

use App\Enums\OrderCondition;
use App\Models\Order\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 *
 * Los ids de type_id/method_id/condition_id dependen de que
 * database\seeders\CatalogsSeeder haya corrido antes (siembra order_type/
 * order_method/order_condition con ids fijos -- ver ese seeder para el
 * porqué de los ids fijos, ya que el código los referencia por número).
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'slack' => Str::random(10),
            'number' => (string) fake()->unique()->numberBetween(100000, 999999),
            'reference' => 'ORD-'.Str::random(8),
            'user_id' => User::factory()->customer(),
            'type_id' => 1,
            'method_id' => 2,
            'condition_id' => OrderCondition::Pagada->value,
            'total_discount_amount' => 0,
            'total_before_discount' => 50000,
            'total_after_discount' => 50000,
            'total_tax_amount' => 0,
            'total_order_amount' => 50000,
        ];
    }

    public function generada(): static
    {
        return $this->state(['condition_id' => OrderCondition::Generada->value]);
    }

    public function pendiente(): static
    {
        return $this->state(['condition_id' => OrderCondition::Pendiente->value]);
    }

    public function rechazada(): static
    {
        return $this->state(['condition_id' => OrderCondition::Rechazada->value]);
    }

    public function pagada(): static
    {
        return $this->state(['condition_id' => OrderCondition::Pagada->value]);
    }
}
