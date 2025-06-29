<?php

namespace Database\Factories;

use App\Models\Score;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Score>
 */
class ScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sbd' => $this->faker->unique()->numerify('########'),
            'toan' => $this->faker->randomFloat(2, 0, 10),
            'ngu_van' => $this->faker->randomFloat(2, 0, 10),
            'ngoai_ngu' => $this->faker->randomFloat(2, 0, 10),
            'vat_li' => $this->faker->randomFloat(2, 0, 10),
            'hoa_hoc' => $this->faker->randomFloat(2, 0, 10),
            'sinh_hoc' => $this->faker->randomFloat(2, 0, 10),
            'lich_su' => $this->faker->randomFloat(2, 0, 10),
            'dia_li' => $this->faker->randomFloat(2, 0, 10),
            'gdcd' => $this->faker->randomFloat(2, 0, 10),
        ];
    }

    /**
     * Create a high-scoring student for Group A testing.
     */
    public function highScoreGroupA(): static
    {
        return $this->state(fn (array $attributes) => [
            'toan' => $this->faker->randomFloat(2, 8.0, 10.0),
            'vat_li' => $this->faker->randomFloat(2, 8.0, 10.0),
            'hoa_hoc' => $this->faker->randomFloat(2, 8.0, 10.0),
        ]);
    }

    /**
     * Create a specific SBD for testing.
     */
    public function withSbd(string $sbd): static
    {
        return $this->state(fn (array $attributes) => [
            'sbd' => $sbd,
        ]);
    }
}
