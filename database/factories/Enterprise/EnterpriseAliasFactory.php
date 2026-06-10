<?php

namespace Database\Factories\Enterprise;

use App\Models\Enterprise\Enterprise;
use App\Models\Enterprise\EnterpriseAlias;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnterpriseAlias>
 */
class EnterpriseAliasFactory extends Factory
{
    protected $model = EnterpriseAlias::class;

    public function definition(): array
    {
        $aliasValue = fake()->company();

        return [
            'enterprise_id' => Enterprise::factory(),
            'alias_type' => fake()->randomElement(['code', 'name']),
            'alias_value' => $aliasValue,
            'normalized_value' => $this->normalize($aliasValue),
        ];
    }

    public function code(): static
    {
        $code = strtoupper(fake()->bothify('C##'));

        return $this->state([
            'alias_type' => 'code',
            'alias_value' => $code,
            'normalized_value' => strtolower($code),
        ]);
    }

    public function name(): static
    {
        $name = fake()->company();

        return $this->state([
            'alias_type' => 'name',
            'alias_value' => $name,
            'normalized_value' => $this->normalize($name),
        ]);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $value = preg_replace('/\s+/', ' ', trim($value));

        return $value;
    }
}
