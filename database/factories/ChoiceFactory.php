<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Choice>
 */
class ChoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'label' => fake()->words(2, true),
            'value' => fake()->slug(2),
            'score' => fake()->randomFloat(2, 0, 5),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
