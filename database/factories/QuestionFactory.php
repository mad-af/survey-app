<?php

namespace Database\Factories;

use App\Models\SurveySection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section_id' => SurveySection::factory(),
            'text' => fake()->sentence() . '?',
            'type' => fake()->randomElement(['short_text', 'long_text', 'single_choice', 'multiple_choice', 'number', 'date']),
            'required' => fake()->boolean(70), // 70% chance of being required
            'order' => fake()->numberBetween(1, 20),
            'score_weight' => fake()->randomFloat(2, 0, 10),
        ];
    }
}
