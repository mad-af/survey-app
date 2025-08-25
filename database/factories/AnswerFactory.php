<?php

namespace Database\Factories;

use App\Models\Choice;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'response_id' => Response::factory(),
            'question_id' => Question::factory(),
            'choice_id' => fake()->optional(0.6)->randomElement([null, Choice::factory()]),
            'value_text' => fake()->optional(0.4)->sentence(),
            'value_number' => fake()->optional(0.3)->randomFloat(2, 1, 10),
            'value_json' => fake()->optional(0.2)->passthrough([
                'selected' => ['option1', 'option2'],
                'rating' => fake()->numberBetween(1, 5),
                'custom_data' => fake()->words(3)
            ]),
        ];
    }
}
