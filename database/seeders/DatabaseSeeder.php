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
        // Create Users
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@survey.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $surveyor = User::create([
            'name' => 'John Surveyor',
            'email' => 'surveyor@survey.com',
            'password' => bcrypt('password'),
            'role' => 'surveyor',
        ]);

        // Create Surveys
        $survey1 = Survey::create([
            'owner_id' => $admin->id,
            'code' => 'SURVEY001',
            'title' => 'Customer Satisfaction Survey',
            'description' => 'Survey untuk mengukur kepuasan pelanggan terhadap layanan kami',
            'status' => 'active',
            'is_anonymous' => true,
            'visibility' => 'public',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
        ]);

        $survey2 = Survey::create([
            'owner_id' => $surveyor->id,
            'code' => 'SURVEY002',
            'title' => 'Employee Engagement Survey',
            'description' => 'Survey untuk mengukur tingkat keterlibatan karyawan',
            'status' => 'draft',
            'is_anonymous' => false,
            'visibility' => 'private',
            'starts_at' => now()->addDays(7),
            'ends_at' => now()->addDays(37),
        ]);

        // Create Survey Sections for Survey 1
        $section1 = SurveySection::create([
            'survey_id' => $survey1->id,
            'title' => 'Informasi Umum',
            'description' => 'Bagian untuk informasi umum responden',
            'order' => 1,
        ]);

        $section2 = SurveySection::create([
            'survey_id' => $survey1->id,
            'title' => 'Kepuasan Layanan',
            'description' => 'Bagian untuk menilai kepuasan terhadap layanan',
            'order' => 2,
        ]);

        // Create Survey Sections for Survey 2
        $section3 = SurveySection::create([
            'survey_id' => $survey2->id,
            'title' => 'Profil Karyawan',
            'description' => 'Informasi dasar karyawan',
            'order' => 1,
        ]);

        // Create Questions for Section 1 (Informasi Umum)
        $question1 = Question::create([
            'section_id' => $section1->id,
            'text' => 'Nama lengkap Anda',
            'type' => 'short_text',
            'required' => true,
            'order' => 1,
            'score_weight' => 0.00,
        ]);

        $question2 = Question::create([
            'section_id' => $section1->id,
            'text' => 'Usia Anda',
            'type' => 'single_choice',
            'required' => true,
            'order' => 2,
            'score_weight' => 0.00,
        ]);

        // Create Choices for Question 2 (Usia)
        Choice::create([
            'question_id' => $question2->id,
            'label' => '18-25 tahun',
            'value' => '18-25',
            'score' => 0.00,
            'order' => 1,
        ]);

        Choice::create([
            'question_id' => $question2->id,
            'label' => '26-35 tahun',
            'value' => '26-35',
            'score' => 0.00,
            'order' => 2,
        ]);

        Choice::create([
            'question_id' => $question2->id,
            'label' => '36-45 tahun',
            'value' => '36-45',
            'score' => 0.00,
            'order' => 3,
        ]);

        Choice::create([
            'question_id' => $question2->id,
            'label' => '46+ tahun',
            'value' => '46+',
            'score' => 0.00,
            'order' => 4,
        ]);

        // Create Questions for Section 2 (Kepuasan Layanan)
        $question3 = Question::create([
            'section_id' => $section2->id,
            'text' => 'Seberapa puas Anda dengan layanan kami secara keseluruhan?',
            'type' => 'single_choice',
            'required' => true,
            'order' => 1,
            'score_weight' => 1.00,
        ]);

        // Create Choices for Question 3 (Kepuasan)
        Choice::create([
            'question_id' => $question3->id,
            'label' => 'Sangat Tidak Puas',
            'value' => '1',
            'score' => 1.00,
            'order' => 1,
        ]);

        Choice::create([
            'question_id' => $question3->id,
            'label' => 'Tidak Puas',
            'value' => '2',
            'score' => 2.00,
            'order' => 2,
        ]);

        Choice::create([
            'question_id' => $question3->id,
            'label' => 'Netral',
            'value' => '3',
            'score' => 3.00,
            'order' => 3,
        ]);

        Choice::create([
            'question_id' => $question3->id,
            'label' => 'Puas',
            'value' => '4',
            'score' => 4.00,
            'order' => 4,
        ]);

        Choice::create([
            'question_id' => $question3->id,
            'label' => 'Sangat Puas',
            'value' => '5',
            'score' => 5.00,
            'order' => 5,
        ]);

        $question4 = Question::create([
            'section_id' => $section2->id,
            'text' => 'Saran atau masukan untuk perbaikan layanan',
            'type' => 'long_text',
            'required' => false,
            'order' => 2,
            'score_weight' => 0.00,
        ]);

        // Create Questions for Section 3 (Profil Karyawan)
        $question5 = Question::create([
            'section_id' => $section3->id,
            'text' => 'Departemen tempat Anda bekerja',
            'type' => 'multiple_choice',
            'required' => true,
            'order' => 1,
            'score_weight' => 0.00,
        ]);

        // Create Choices for Question 5 (Departemen)
        Choice::create([
            'question_id' => $question5->id,
            'label' => 'Human Resources',
            'value' => 'hr',
            'score' => 0.00,
            'order' => 1,
        ]);

        Choice::create([
            'question_id' => $question5->id,
            'label' => 'Information Technology',
            'value' => 'it',
            'score' => 0.00,
            'order' => 2,
        ]);

        Choice::create([
            'question_id' => $question5->id,
            'label' => 'Marketing',
            'value' => 'marketing',
            'score' => 0.00,
            'order' => 3,
        ]);

        Choice::create([
            'question_id' => $question5->id,
            'label' => 'Finance',
            'value' => 'finance',
            'score' => 0.00,
            'order' => 4,
        ]);

        // Create Result Categories
        $resultCategory1 = ResultCategory::create([
            'survey_id' => $survey1->id,
            'name' => 'Sangat Puas',
            'description' => 'Tingkat kepuasan sangat tinggi',
            'min_score' => 80,
            'max_score' => 100,
            'color' => '#22c55e',
        ]);

        $resultCategory2 = ResultCategory::create([
            'survey_id' => $survey1->id,
            'name' => 'Puas',
            'description' => 'Tingkat kepuasan baik',
            'min_score' => 60,
            'max_score' => 79,
            'color' => '#3b82f6',
        ]);

        $resultCategory3 = ResultCategory::create([
            'survey_id' => $survey1->id,
            'name' => 'Kurang Puas',
            'description' => 'Tingkat kepuasan rendah',
            'min_score' => 0,
            'max_score' => 59,
            'color' => '#ef4444',
        ]);

        // Create Respondents
        $respondents = [];
        for ($i = 1; $i <= 5; $i++) {
            $respondents[] = Respondent::create([
                'name' => 'Respondent ' . $i,
                'email' => 'respondent' . $i . '@example.com',
                'phone' => '08123456789' . $i,
                'gender' => fake()->randomElement(['male', 'female']),
                'birth_year' => fake()->numberBetween(1960, 2005),
                'organization' => 'PT Example',
                'department' => fake()->randomElement(['IT', 'HR', 'Marketing', 'Finance']),
            ]);
        }

        // Create Responses with ResponseScores
        foreach ($respondents as $index => $respondent) {
            $response = Response::create([
                'survey_id' => $survey1->id,
                'respondent_id' => $respondent->id,
                'respondent_token' => 'token_' . $respondent->id . '_' . time() . '_' . $index,
                'started_at' => now()->subHours(2),
                'submitted_at' => now()->subHour(),
            ]);

            // Create answers for this response
            Answer::create([
                'response_id' => $response->id,
                'question_id' => $question1->id,
                'value_text' => $respondent->name,
            ]);

            Answer::create([
                'response_id' => $response->id,
                'question_id' => $question2->id,
                'choice_id' => Choice::where('question_id', $question2->id)->inRandomOrder()->first()->id,
            ]);

            $satisfactionChoice = Choice::where('question_id', $question3->id)->inRandomOrder()->first();
            Answer::create([
                'response_id' => $response->id,
                'question_id' => $question3->id,
                'choice_id' => $satisfactionChoice->id,
            ]);

            Answer::create([
                'response_id' => $response->id,
                'question_id' => $question4->id,
                'value_text' => 'Saran untuk perbaikan layanan dari ' . $respondent->name,
            ]);

            // Create ResponseScore based on satisfaction answer
            $totalScore = $satisfactionChoice->score * 20; // Scale to 100
            $maxPossibleScore = 100;
            $percentage = ($totalScore / $maxPossibleScore) * 100;
            
            // Determine result category based on score
            $resultCategory = null;
            if ($percentage >= 80) {
                $resultCategory = $resultCategory1;
            } elseif ($percentage >= 60) {
                $resultCategory = $resultCategory2;
            } else {
                $resultCategory = $resultCategory3;
            }

            ResponseScore::create([
                'response_id' => $response->id,
                'result_category_id' => $resultCategory->id,
                'total_score' => $totalScore,
                'max_possible_score' => $maxPossibleScore,
                'percentage' => $percentage,
                'section_scores' => [
                    'section_1' => 0, // No scoring for info section
                    'section_2' => $totalScore, // All score from satisfaction section
                ],
            ]);

            // Create SurveyRespondent relationship
            SurveyRespondent::create([
                'survey_id' => $survey1->id,
                'respondent_id' => $respondent->id,
                'status' => 'completed',
                'invited_at' => now()->subDays(3),
                'started_at' => now()->subHours(2),
                'completed_at' => now()->subHour(),
            ]);
        }
    }
}
