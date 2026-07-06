<?php

namespace Database\Factories\Blog;

use App\Models\Blog\Blog;
use App\Models\Blog\BlogCategorie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4, false);

        return [
            'slack' => Str::random(10),
            'title' => Str::upper($title),
            'slug' => Str::slug($title).'-'.fake()->unique()->randomNumber(5),
            'description' => fake()->paragraph(),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'available' => 1,
            'date_at' => fake()->date(),
            'categorie_id' => BlogCategorie::factory(),
        ];
    }

    /** Unavailable / draft blog post. */
    public function unavailable(): static
    {
        return $this->state(['available' => 0]);
    }
}
