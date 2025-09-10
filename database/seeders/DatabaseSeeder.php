<?php

namespace Database\Seeders;

use App\Models\Choice;
use App\Models\Question;
use App\Models\ResultCategory;
use App\Models\ResultCategoryRule;
use App\Models\Survey;
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
        // Run SurveySeeder first
        $this->call(SurveySeeder::class);
        
        // Create Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@survey.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $surveyor = User::firstOrCreate(
            ['email' => 'surveyor@survey.com'],
            [
                'name' => 'John Surveyor',
                'password' => bcrypt('password'),
                'role' => 'surveyor',
            ]
        );

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

        // Create Result Categories for all Surveys
        $surveys = Survey::all();
        foreach ($surveys as $survey) {
            if ($survey->id == 1) {
                continue;
            }

            $surveyResultCategory = ResultCategory::create([
                'owner_type' => 'survey',
                'owner_id' => $survey->id,
                'name' => 'Survey',
            ]);

            // Create Result Category Rules for Survey
            ResultCategoryRule::create([
                'result_category_id' => $surveyResultCategory->id,
                'operation' => 'lt',
                'title' => 'Skor Rendah',
                'score' => 50.00,
                'color' => 'error', // DaisyUI class for low scores
            ]);

            ResultCategoryRule::create([
                'result_category_id' => $surveyResultCategory->id,
                'operation' => 'gt',
                'title' => 'Skor Tinggi',
                'score' => 80.00,
                'color' => 'success', // DaisyUI class for high scores
            ]);

            ResultCategoryRule::create([
                'result_category_id' => $surveyResultCategory->id,
                'operation' => 'else',
                'title' => 'Skor Sedang',
                'score' => 0.00, // Not used for 'else' operation
                'color' => 'info', // DaisyUI class for medium scores
            ]);
        }

        // Create Result Categories for all Survey Sections
        $sections = SurveySection::all();
        foreach ($sections as $section) {
            if ($section->survey_id == 1) {
                continue;
            }

            $sectionResultCategory = ResultCategory::create([
                'owner_type' => 'survey_section',
                'owner_id' => $section->id,
                'name' => 'Section ' . $section->order,
            ]);

            // Create Result Category Rules for Section
            ResultCategoryRule::create([
                'result_category_id' => $sectionResultCategory->id,
                'operation' => 'lt',
                'title' => 'Skor Rendah',
                'score' => 40.00,
                'color' => 'error', // DaisyUI class for low scores
            ]);

            ResultCategoryRule::create([
                'result_category_id' => $sectionResultCategory->id,
                'operation' => 'gt',
                'title' => 'Skor Tinggi',
                'score' => 75.00,
                'color' => 'success', // DaisyUI class for high scores
            ]);

            ResultCategoryRule::create([
                'result_category_id' => $sectionResultCategory->id,
                'operation' => 'else',
                'title' => 'Skor Sedang',
                'score' => 0.00, // Not used for 'else' operation
                'color' => 'info', // DaisyUI class for medium scores
            ]);
        }

    }
}
