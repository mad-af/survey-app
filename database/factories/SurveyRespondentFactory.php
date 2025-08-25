<?php

namespace Database\Factories;

use App\Models\Respondent;
use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyRespondent>
 */
class SurveyRespondentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $invitedAt = fake()->dateTimeBetween('-2 months', 'now');
        $startedAt = fake()->optional(0.7)->dateTimeBetween($invitedAt, 'now');
        $completedAt = $startedAt ? fake()->optional(0.6)->dateTimeBetween($startedAt, 'now') : null;
        
        return [
            'survey_id' => Survey::factory(),
            'respondent_id' => Respondent::factory(),
            'status' => fake()->randomElement(['invited', 'started', 'completed', 'expired']),
            'invited_at' => $invitedAt,
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'expires_at' => fake()->dateTimeBetween('now', '+3 months'),
        ];
    }
}
