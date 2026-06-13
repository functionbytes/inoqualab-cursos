<?php

namespace Database\Factories\Quiz;

use App\Models\Quiz\QuizTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QuizTopic>
 */
class QuizTopicFactory extends Factory
{
    protected $model = QuizTopic::class;

    public function definition(): array
    {
        $showAns = fake()->numberBetween(5, 20);

        return [
            'slack' => Str::random(10),
            'title' => fake()->sentence(4, false),
            'description' => fake()->optional()->sentence(),
            'timer' => fake()->optional()->numberBetween(10, 60),
            'per_q_mark' => fake()->numberBetween(1, $showAns),
            'available' => true,
            'show_ans' => $showAns,
            'quiz_again' => true,
            'due_days' => null,
            'type' => null,
            // lesson_id and course_id must be set via for() or state
        ];
    }

    /** Disable retries for this quiz topic. */
    public function noRetry(): static
    {
        return $this->state(['quiz_again' => false]);
    }

    /** Set a passing threshold: n out of show_ans questions required. */
    public function withPassingScore(int $required): static
    {
        return $this->state(function (array $attributes) use ($required) {
            return ['per_q_mark' => $required];
        });
    }
}
