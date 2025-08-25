<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveySection>
 */
class SurveySectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'title' => fake()->sentence(2),
            'description' => fake()->optional()->paragraph(),
            'order' => fake()->numberBetween(1, 10),
        ];
    }
}
