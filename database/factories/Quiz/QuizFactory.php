<?php

namespace Database\Factories\Quiz;

use App\Models\Course\Course;
use App\Models\Course\CourseLesson;
use App\Models\Inscription;
use App\Models\Quiz\Quiz;
use App\Models\Quiz\QuizTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => CourseLesson::factory(),
            'course_id' => Course::factory(),
            'topic_id' => QuizTopic::factory(),
            'inscription_id' => Inscription::factory(),
            'correct' => 0,
            'wrong' => 0,
            'score' => 0,
        ];
    }

    /** Quiz with a passing score already computed. */
    public function passed(int $total = 10, int $correct = 8): static
    {
        $wrong = $total - $correct;
        $score = $total === $correct ? 100 : round(100 - ($wrong / $total * 100), 2);

        return $this->state(['correct' => $correct, 'wrong' => $wrong, 'score' => $score]);
    }

    /** Quiz with a failing score. */
    public function failed(int $total = 10, int $correct = 3): static
    {
        $wrong = $total - $correct;
        $score = round(100 - ($wrong / $total * 100), 2);

        return $this->state(['correct' => $correct, 'wrong' => $wrong, 'score' => $score]);
    }
}
