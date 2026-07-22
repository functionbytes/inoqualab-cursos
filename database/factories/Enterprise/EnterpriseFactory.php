<?php

namespace Database\Factories\Enterprise;

use App\Models\Enterprise\Enterprise;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Enterprise>
 */
class EnterpriseFactory extends Factory
{
    protected $model = Enterprise::class;

    public function definition(): array
    {
        $title = fake()->company();

        return [
            'slack' => 'ent-'.Str::random(12),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->randomNumber(4),
            'address' => fake()->address(),
            'cellphone' => fake()->numerify('3#########'),
            'nit' => (string) fake()->numerify('#########-#'),
            'code' => strtoupper(Str::random(6)),
            'email' => fake()->unique()->companyEmail(),
            'leading' => fake()->name(),
            'supporting' => fake()->name(),
            'available' => 1,
            'mail_notification' => 0,
            'inscription_notification' => 0,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(['available' => 0]);
    }
}
