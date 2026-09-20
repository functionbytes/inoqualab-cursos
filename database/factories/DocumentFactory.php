<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'slack' => Str::random(10),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'available' => 1,
        ];
    }

    /** Oculto: no aparece en el portal del cliente (scope available()). */
    public function hidden(): static
    {
        return $this->state(['available' => 0]);
    }
}
