<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResultCategory>
 */
class ResultCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $minScore = fake()->randomFloat(2, 0, 50);
        $maxScore = fake()->randomFloat(2, $minScore + 10, 100);
        
        return [
            'survey_id' => Survey::factory(),
            'name' => fake()->randomElement([
                'Beginner', 'Intermediate', 'Advanced', 'Expert',
                'Low Risk', 'Medium Risk', 'High Risk',
                'Poor', 'Fair', 'Good', 'Excellent'
            ]),
            'description' => fake()->sentence(),
            'min_score' => $minScore,
            'max_score' => $maxScore,
            'color' => fake()->hexColor(),
        ];
    }
}
