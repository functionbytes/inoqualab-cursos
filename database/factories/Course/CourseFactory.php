<?php

namespace Database\Factories\Course;

use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = fake()->sentence(4, false);
        $slug = Str::slug($title).'-'.fake()->randomNumber(4);
        $slack = Str::random(10);

        return [
            'slack' => $slack,
            'categorie_id' => fn () => \DB::table('course_categories')->value('id')
                ?? \DB::table('course_categories')->insertGetId([
                    'slack' => Str::random(10),
                    'title' => 'General',
                    'slug' => 'general',
                ]),
            'title' => $title,
            'slug' => $slug,
            'price' => fake()->randomFloat(2, 10000, 200000),
            'discount' => 0,
            'payment' => 1,
            'promotion' => 0,
            'available' => 1,
            'featured' => 0,
            'level' => 'beginner',
            'rating' => 0,
            'type' => 0,
            'exam' => 0,
            'certificate' => 0,
        ];
    }

    /** Course with a promotional discount. */
    public function onSale(): static
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? 100000;

            return [
                'promotion' => 1,
                'discount' => $price * 0.8,
            ];
        });
    }

    /** Free course (payment = 0). */
    public function free(): static
    {
        return $this->state([
            'price' => 0,
            'payment' => 0,
            'promotion' => 0,
        ]);
    }

    /** Unavailable / draft course. */
    public function unavailable(): static
    {
        return $this->state(['available' => 0]);
    }
}
