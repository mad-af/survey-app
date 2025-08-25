<?php

namespace Database\Factories;

use App\Models\Respondent;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Response>
 */
class ResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 month', 'now');
        $submittedAt = fake()->optional(0.8)->dateTimeBetween($startedAt, 'now');
        
        return [
            'survey_id' => Survey::factory(),
            'respondent_id' => fake()->optional(0.7)->randomElement([null, Respondent::factory()]),
            'respondent_token' => fake()->optional(0.3)->uuid(),
            'started_at' => $startedAt,
            'submitted_at' => $submittedAt,
            'meta' => [
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'completion_time_minutes' => fake()->numberBetween(5, 45),
            ],
        ];
    }
}
