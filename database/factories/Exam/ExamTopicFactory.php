<?php

namespace Database\Factories\Exam;

use App\Models\Exam\ExamTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExamTopic>
 */
class ExamTopicFactory extends Factory
{
    protected $model = ExamTopic::class;

    public function definition(): array
    {
        $showAns = fake()->numberBetween(5, 20);

        return [
            'slack' => Str::random(10),
            'title' => fake()->sentence(4, false),
            'description' => fake()->optional()->sentence(),
            'per_q_mark' => fake()->numberBetween(1, $showAns),
            'timer' => fake()->optional()->numberBetween(10, 60),
            'available' => true,
            'show_ans' => $showAns,
            'quiz_again' => true,
            'due_days' => null,
            'type' => null,
            // course_id must be set via for() or state
        ];
    }

    /** Disable retries for this exam topic. */
    public function noRetry(): static
    {
        return $this->state(['quiz_again' => false]);
    }

    /** Set a specific passing threshold (per_q_mark out of show_ans). */
    public function withPassingScore(int $required, int $showAns): static
    {
        return $this->state(['per_q_mark' => $required, 'show_ans' => $showAns]);
    }
}
