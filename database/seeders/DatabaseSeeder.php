<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Choice;
use App\Models\Question;
use App\Models\Respondent;
use App\Models\Response;
use App\Models\ResponseScore;
use App\Models\ResultCategory;
use App\Models\Survey;
use App\Models\SurveyRespondent;
use App\Models\SurveySection;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create surveyor users
        $surveyors = User::factory()->surveyor()->count(3)->create();

        // Create respondents
        $respondents = Respondent::factory()->count(20)->create();

        // Create surveys with sections, questions, and choices
        $surveys = Survey::factory()->count(5)->create([
            'owner_id' => $admin->id,
        ]);

        foreach ($surveys as $survey) {
            // Create sections for each survey
            $sections = SurveySection::factory()->count(3)->create([
                'survey_id' => $survey->id,
            ]);

            // Create result categories for each survey
            ResultCategory::factory()->count(4)->create([
                'survey_id' => $survey->id,
            ]);

            foreach ($sections as $section) {
                // Create questions for each section
                $questions = Question::factory()->count(5)->create([
                    'section_id' => $section->id,
                ]);

                foreach ($questions as $question) {
                    // Create choices for multiple choice questions
                    if (in_array($question->type, ['multiple_choice', 'single_choice'])) {
                        Choice::factory()->count(4)->create([
                            'question_id' => $question->id,
                        ]);
                    }
                }
            }

            // Enroll respondents to surveys
            $enrolledRespondents = $respondents->random(10);
            foreach ($enrolledRespondents as $respondent) {
                SurveyRespondent::factory()->create([
                    'survey_id' => $survey->id,
                    'respondent_id' => $respondent->id,
                ]);
            }

            // Create responses and answers
            $completedRespondents = $enrolledRespondents->random(6);
            foreach ($completedRespondents as $respondent) {
                $response = Response::factory()->create([
                    'survey_id' => $survey->id,
                    'respondent_id' => $respondent->id,
                ]);

                // Create answers for each question
                foreach ($survey->sections as $section) {
                    foreach ($section->questions as $question) {
                        Answer::factory()->create([
                            'response_id' => $response->id,
                            'question_id' => $question->id,
                            'choice_id' => $question->choices->isNotEmpty() ? $question->choices->random()->id : null,
                        ]);
                    }
                }

                // Create response score
                ResponseScore::factory()->create([
                    'response_id' => $response->id,
                    'result_category_id' => $survey->resultCategories->random()->id,
                ]);
            }
        }
    }
}
