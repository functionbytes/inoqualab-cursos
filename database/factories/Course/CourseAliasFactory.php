<?php

namespace Database\Factories\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseAlias>
 */
class CourseAliasFactory extends Factory
{
    protected $model = CourseAlias::class;

    public function definition(): array
    {
        $alias = strtoupper(fake()->words(fake()->numberBetween(2, 4), true));

        return [
            'course_id' => Course::factory(),
            'alias' => $alias,
            'normalized_alias' => strtolower(trim($alias)),
            'source' => 'mail',
        ];
    }

    public function manual(): static
    {
        return $this->state(['source' => 'manual']);
    }
}
