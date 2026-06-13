<?php

namespace Database\Factories;

use App\Models\Course\Course;
use App\Models\Inscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Inscription>
 */
class InscriptionFactory extends Factory
{
    protected $model = Inscription::class;

    public function definition(): array
    {
        return [
            'slack' => Str::random(10),
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'order_id' => null,
            'percent' => 0,
            'enroll_start' => Carbon::now()->subDays(10)->toDateString(),
            'enroll_expire' => Carbon::now()->addMonths(3)->toDateString(),
            'enroll_culminated' => null,
            'culminated' => 0,
            'expire' => 0,
        ];
    }

    /** Inscription that has been fully completed. */
    public function culminated(): static
    {
        return $this->state([
            'culminated' => 1,
            'enroll_culminated' => Carbon::now()->toDateString(),
            'percent' => 100,
        ]);
    }

    /** Inscription that is expired. */
    public function expired(): static
    {
        return $this->state([
            'expire' => 1,
            'enroll_expire' => Carbon::now()->subDays(1)->toDateString(),
        ]);
    }
}
