<?php

namespace Database\Factories;

use App\Models\Response;
use App\Models\ResultCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResponseScore>
 */
class ResponseScoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $maxPossibleScore = fake()->randomFloat(2, 50, 100);
        $totalScore = fake()->randomFloat(2, 0, $maxPossibleScore);
        $percentage = ($totalScore / $maxPossibleScore) * 100;
        
        return [
            'response_id' => Response::factory(),
            'result_category_id' => fake()->optional(0.8)->randomElement([null, ResultCategory::factory()]),
            'total_score' => $totalScore,
            'max_possible_score' => $maxPossibleScore,
            'percentage' => $percentage,
            'section_scores' => [
                'section_1' => fake()->randomFloat(2, 0, 25),
                'section_2' => fake()->randomFloat(2, 0, 25),
                'section_3' => fake()->randomFloat(2, 0, 25),
            ],
        ];
    }
}
