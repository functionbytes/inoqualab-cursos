<?php

namespace Database\Factories\Mail;

use App\Models\Mail\IncomingMail;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<IncomingMail>
 */
class IncomingMailFactory extends Factory
{
    protected $model = IncomingMail::class;

    public function definition(): array
    {
        return [
            'slack' => Str::random(6),
            'message_id' => '<'.fake()->uuid().'@mail.example.com>',
            'from' => fake()->companyEmail(),
            'subject' => fake()->sentence(6),
            'received_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'raw_body' => fake()->paragraphs(3, true),
            'parsed_payload' => null,
            'status' => 'pending_review',
            'confidence_score' => null,
            'matched_enterprise_id' => null,
            'order_id' => null,
            'error_log' => null,
            'processed_at' => null,
        ];
    }

    public function pendingReview(): static
    {
        return $this->state([
            'status' => 'pending_review',
            'confidence_score' => null,
            'processed_at' => null,
        ]);
    }

    public function processed(): static
    {
        return $this->state([
            'status' => 'processed',
            'confidence_score' => fake()->numberBetween(60, 100),
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => 'failed',
            'confidence_score' => fake()->numberBetween(0, 40),
            'error_log' => fake()->sentence(),
            'processed_at' => now(),
        ]);
    }

    public function ignored(): static
    {
        return $this->state([
            'status' => 'ignored',
            'processed_at' => now(),
        ]);
    }
}
