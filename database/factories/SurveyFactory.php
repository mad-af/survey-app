<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Survey>
 */
class SurveyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);
        return [
            'owner_id' => User::factory(),
            'code' => Str::upper(Str::random(8)),
            'title' => $title,
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'active', 'closed']),
            'is_anonymous' => fake()->boolean(),
            'visibility' => fake()->randomElement(['private', 'link', 'public']),
            'starts_at' => fake()->dateTimeBetween('now', '+1 week'),
            'ends_at' => fake()->dateTimeBetween('+1 week', '+3 months'),
        ];
    }
}
