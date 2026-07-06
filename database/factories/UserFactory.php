<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slack' => (string) Str::uuid(),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'available' => 1,
            'verified' => 1,
            'validation' => 1,
            'terms' => 1,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * `role` no está en `$fillable` (evita mass assignment vía request), así
     * que se asigna por propiedad directa tras construir el modelo en vez de
     * ir en `definition()`.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            $user->role ??= 'customer';
        });
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'verified' => 0,
        ]);
    }

    /** Marca al usuario como no disponible (inactivo). */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => ['available' => 0]);
    }

    /** Asigna un rol concreto (columna `role`; el observer sincroniza Spatie). */
    public function role(string $role): static
    {
        return $this->afterMaking(function (User $user) use ($role) {
            $user->role = $role;
        });
    }

    public function manager(): static
    {
        return $this->role('manager');
    }

    public function customer(): static
    {
        return $this->role('customer');
    }

    public function support(): static
    {
        return $this->role('support');
    }

    public function distributor(): static
    {
        return $this->role('distributor');
    }

    public function enterprise(): static
    {
        return $this->role('enterprise');
    }

    public function accounting(): static
    {
        return $this->role('accounting');
    }
}
