<?php

namespace Database\Factories\Course;

use App\Models\Course\Course;
use App\Models\Course\CourseChapter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CourseChapter>
 */
class CourseChapterFactory extends Factory
{
    protected $model = CourseChapter::class;

    public function definition(): array
    {
        static $position = 0;
        $position++;

        return [
            'slack' => Str::random(10),
            'title' => fake()->sentence(3, false),
            'description' => fake()->optional()->sentence(),
            'position' => $position,
            'available' => 1,
            'course_id' => Course::factory(),
        ];
    }

    /** Reset the static position counter between tests. */
    public function atPosition(int $position): static
    {
        return $this->state(['position' => $position]);
    }
}
