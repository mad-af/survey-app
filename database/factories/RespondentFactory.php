<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Respondent>
 */
class RespondentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->uuid(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female', 'other', 'prefer_not_to_say']),
            'birth_year' => fake()->numberBetween(1950, 2005),
            'organization' => fake()->company(),
            'department' => fake()->randomElement(['HR', 'IT', 'Marketing', 'Sales', 'Finance', 'Operations']),
            'role_title' => fake()->jobTitle(),
            'location' => fake()->city() . ', ' . fake()->country(),
            'demographics' => [
                'education' => fake()->randomElement(['high_school', 'bachelor', 'master', 'phd']),
                'experience_years' => fake()->numberBetween(0, 30),
                'industry' => fake()->randomElement(['technology', 'healthcare', 'finance', 'education', 'retail']),
            ],
            'consent_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
