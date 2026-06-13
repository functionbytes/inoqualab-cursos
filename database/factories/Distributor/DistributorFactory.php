<?php

namespace Database\Factories\Distributor;

use App\Models\Distributor\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Distributor>
 */
class DistributorFactory extends Factory
{
    protected $model = Distributor::class;

    public function definition(): array
    {
        $title = fake()->company();

        return [
            'slack' => 'dist-'.Str::random(12),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->randomNumber(4),
            'address' => fake()->address(),
            'cellphone' => fake()->numerify('3#########'),
            'nit' => (string) fake()->numerify('#########-#'),
            'email' => fake()->unique()->companyEmail(),
            'leading' => fake()->name(),
            'supporting' => fake()->name(),
            'available' => 1,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(['available' => 0]);
    }
}
