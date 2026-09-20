<?php

namespace Database\Factories\Users;

use App\Models\Course\Course;
use App\Models\User;
use App\Models\Users\Certificate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        return [
            'slack' => Str::random(10),
            'user_id' => User::factory()->customer(),
            'course_id' => Course::factory(),
            'inscription_id' => null,
            'exam_id' => null,
            'certifier_id' => null,
            'certification_id' => null,
            'start_at' => Carbon::today(),
            'end_at' => Carbon::today()->addYear(),
        ];
    }

    /** Vencido: end_at ya pasó. */
    public function expired(): static
    {
        return $this->state(fn () => [
            'start_at' => Carbon::today()->subYears(2),
            'end_at' => Carbon::today()->subYear(),
        ]);
    }

    /** Por vencer: dentro de los próximos 30 días (el mismo corte que usa el controller). */
    public function expiringSoon(): static
    {
        return $this->state(fn () => [
            'start_at' => Carbon::today()->subMonths(11),
            'end_at' => Carbon::today()->addDays(15),
        ]);
    }

    /** Sin fecha de vencimiento. */
    public function withoutExpiration(): static
    {
        return $this->state(['end_at' => null]);
    }
}
