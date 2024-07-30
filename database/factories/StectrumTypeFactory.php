<?php

namespace Database\Factories;

use App\Models\SpectrumType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpectrumTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SpectrumType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'neuclei' => $this->faker->word(),
            'dimentionality' => $this->faker->randomElement(['1D', '2D']),
            'name' => $this->faker->name(),
        ];
    }
}
