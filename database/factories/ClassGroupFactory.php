<?php

namespace Database\Factories;

use App\Enums\CbcLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'school_id' => 1,
            'level' => $this->faker->randomElement(CbcLevel::cases()),
        ];
    }
}
