<?php

namespace Database\Factories\Exam;

use App\Models\Course\Course;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamTopic;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'topic_id' => ExamTopic::factory(),
            'inscription_id' => Inscription::factory(),
            'correct' => 0,
            'wrong' => 0,
            'score' => 0,
        ];
    }

    /** Exam with a passing score. */
    public function passed(int $total = 10, int $correct = 8): static
    {
        $wrong = $total - $correct;
        $score = $total === $correct ? 100 : round(100 - ($wrong / $total * 100), 2);

        return $this->state(['correct' => $correct, 'wrong' => $wrong, 'score' => $score]);
    }

    /** Exam with a failing score. */
    public function failed(int $total = 10, int $correct = 3): static
    {
        $wrong = $total - $correct;
        $score = round(100 - ($wrong / $total * 100), 2);

        return $this->state(['correct' => $correct, 'wrong' => $wrong, 'score' => $score]);
    }
}
