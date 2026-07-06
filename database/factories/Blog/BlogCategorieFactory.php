<?php

namespace Database\Factories\Blog;

use App\Models\Blog\BlogCategorie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogCategorie>
 */
class BlogCategorieFactory extends Factory
{
    protected $model = BlogCategorie::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(2, true);

        return [
            'slack' => Str::random(10),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(5),
            'available' => 1,
        ];
    }

    /** Unavailable / hidden category. */
    public function unavailable(): static
    {
        return $this->state(['available' => 0]);
    }
}
