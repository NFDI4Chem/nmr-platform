<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\StectrumType;

class StectrumTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StectrumType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'neuclei' => $this->faker->word(),
            'dimentionality' => $this->faker->randomElement(["1D","2D"]),
            'name' => $this->faker->name(),
        ];
    }
}
