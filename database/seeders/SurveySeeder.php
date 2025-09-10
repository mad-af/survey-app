<?php

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveySection;
use App\Models\Question;
use App\Models\Choice;
use App\Models\ResultCategory;
use App\Models\ResultCategoryRule;
use App\Enums\QuestionType;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if not exists
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@survey.com'],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Create the main survey
        $survey = Survey::create([
            'owner_id' => $admin->id,
            'code' => 'CACINGAN001',
            'title' => 'Kuisioner Pencegahan Cacingan',
            'description' => 'Kuisioner untuk mengevaluasi pengetahuan, sikap, dan perilaku terkait pencegahan cacingan',
            'status' => 'active',
            'is_anonymous' => true,
            'visibility' => 'public',
            'starts_at' => now(),
            'ends_at' => now()->addMonths(6),
        ]);

        // Section 1: Pengetahuan
        $knowledgeSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'Pengetahuan',
            'description' => 'Bagian ini mengevaluasi pengetahuan Anda tentang cacingan',
            'order' => 1,
        ]);

        // Question 1: Apakah Adik tahu tentang sakit cacingan?
        $q1 = Question::create([
            'section_id' => $knowledgeSection->id,
            'text' => 'Apakah Adik tahu tentang sakit cacingan?',
            'type' => QuestionType::SINGLE_CHOICE,
            'required' => true,
            'order' => 1,
            'score_weight' => 1.00,
        ]);

        Choice::create(['question_id' => $q1->id, 'label' => 'Ya', 'value' => 'ya', 'score' => 0, 'order' => 1]);
        Choice::create(['question_id' => $q1->id, 'label' => 'Tidak', 'value' => 'tidak', 'score' => 1, 'order' => 2]);

        // Question 2: Tanda-tanda cacingan
        $signs = ['Sakit perut/mencret', 'Tidak nafsu makan', 'Pucat/lemas', 'Perut buncit'];
        foreach ($signs as $index => $sign) {
            $question = Question::create([
                'section_id' => $knowledgeSection->id,
                'text' => "Apakah {$sign} merupakan tanda-tanda cacingan?",
                'type' => QuestionType::SINGLE_CHOICE,
                'required' => true,
                'order' => $index + 2,
                'score_weight' => 1.00,
            ]);

            Choice::create(['question_id' => $question->id, 'label' => 'Ya', 'value' => 'ya', 'score' => 0, 'order' => 1]);
            // Different scoring for 'Pucat/lemas' and 'Perut buncit'
            $noScore = in_array($index, [2, 3]) ? 2 : 1;
            Choice::create(['question_id' => $question->id, 'label' => 'Tidak', 'value' => 'tidak', 'score' => $noScore, 'order' => 2]);
        }

        // Question 3: Penularan cacingan
        $transmissions = ['Bermain tanah', 'Tidak mencuci tangan', 'Makan dan minum sembarangan'];
        foreach ($transmissions as $index => $transmission) {
            $question = Question::create([
                'section_id' => $knowledgeSection->id,
                'text' => "Apakah {$transmission} dapat menyebabkan penularan cacingan?",
                'type' => QuestionType::SINGLE_CHOICE,
                'required' => true,
                'order' => $index + 6,
                'score_weight' => 1.00,
            ]);

            Choice::create(['question_id' => $question->id, 'label' => 'Ya', 'value' => 'ya', 'score' => 0, 'order' => 1]);
            Choice::create(['question_id' => $question->id, 'label' => 'Tidak', 'value' => 'tidak', 'score' => 1, 'order' => 2]);
        }

        // Section 2: Sikap
        $attitudeSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'Sikap',
            'description' => 'Bagian ini mengevaluasi sikap Anda terhadap pencegahan cacingan',
            'order' => 2,
        ]);

        $attitudes = [
            'Tidak bermain tanah',
            'Mencuci tangan',
            'Memotong dan membersihkan kuku',
            'Memakai alas kaki jika keluar rumah',
            'Minum obat cacing'
        ];

        foreach ($attitudes as $index => $attitude) {
            $question = Question::create([
                'section_id' => $attitudeSection->id,
                'text' => "Bagaimana sikap Anda terhadap: {$attitude}",
                'type' => QuestionType::SINGLE_CHOICE,
                'required' => true,
                'order' => $index + 1,
                'score_weight' => 1.00,
            ]);

            Choice::create(['question_id' => $question->id, 'label' => 'Setuju', 'value' => 'setuju', 'score' => 0, 'order' => 1]);
            Choice::create(['question_id' => $question->id, 'label' => 'Tidak Setuju', 'value' => 'tidak_setuju', 'score' => 1, 'order' => 2]);
        }

        // Section 3: Perilaku
        $behaviorSection = SurveySection::create([
            'survey_id' => $survey->id,
            'title' => 'Perilaku',
            'description' => 'Bagian ini mengevaluasi perilaku Anda terkait pencegahan cacingan',
            'order' => 3,
        ]);

        $behaviors = [
            'Apakah setiap mau makan dan setelah buang air besar adik mencuci tangan?',
            'Jika ya, dengan apa mencuci tangan?',
            'Setelah bermain tanah apakah mencuci tangan?',
            'Dengan apa mencuci tangan setelah bermain tanah?',
            'Apakah adik menggunakan alas kaki saat keluar rumah?',
            'Istirahat di sekolah apakah tetap memakai sepatu?',
            'Di mana buang air besar?',
            'Apakah kuku anak panjang? (observasi)',
            'Apakah kuku anak bersih? (observasi)'
        ];

        foreach ($behaviors as $index => $behavior) {
            $question = Question::create([
                'section_id' => $behaviorSection->id,
                'text' => $behavior,
                'type' => QuestionType::SINGLE_CHOICE,
                'required' => true,
                'order' => $index + 1,
                'score_weight' => 1.00,
            ]);

            // Different choices based on question type
            if (in_array($index, [1, 3])) { // Questions about washing hands method
                Choice::create(['question_id' => $question->id, 'label' => 'Air dan sabun', 'value' => 'air_sabun', 'score' => 0, 'order' => 1]);
                Choice::create(['question_id' => $question->id, 'label' => 'Air saja', 'value' => 'air_saja', 'score' => 1, 'order' => 2]);
            } elseif ($index === 6) { // Question about defecation place
                Choice::create(['question_id' => $question->id, 'label' => 'WC/Jamban', 'value' => 'wc_jamban', 'score' => 0, 'order' => 1]);
                Choice::create(['question_id' => $question->id, 'label' => 'Tanah', 'value' => 'tanah', 'score' => 1, 'order' => 2]);
                Choice::create(['question_id' => $question->id, 'label' => 'Sungai', 'value' => 'sungai', 'score' => 1, 'order' => 3]);
            } elseif ($index === 7) { // Question about long nails (reverse scoring)
                Choice::create(['question_id' => $question->id, 'label' => 'Ya', 'value' => 'ya', 'score' => 1, 'order' => 1]);
                Choice::create(['question_id' => $question->id, 'label' => 'Tidak', 'value' => 'tidak', 'score' => 0, 'order' => 2]);
            } elseif ($index === 8) { // Question about clean nails
                Choice::create(['question_id' => $question->id, 'label' => 'Ya', 'value' => 'ya', 'score' => 0, 'order' => 1]);
                Choice::create(['question_id' => $question->id, 'label' => 'Tidak', 'value' => 'tidak', 'score' => 1, 'order' => 2]);
            } else { // Standard yes/no questions
                Choice::create(['question_id' => $question->id, 'label' => 'Ya', 'value' => 'ya', 'score' => 0, 'order' => 1]);
                Choice::create(['question_id' => $question->id, 'label' => 'Tidak', 'value' => 'tidak', 'score' => 1, 'order' => 2]);
            }
        }

        // Create Result Categories for Survey
        $surveyResultCategory = ResultCategory::create([
            'owner_type' => 'survey',
            'owner_id' => $survey->id,
            'name' => 'Survey',
        ]);

        // Create Result Category Rules for Survey (reversed logic: <40% = Baik)
        ResultCategoryRule::create([
            'result_category_id' => $surveyResultCategory->id,
            'operation' => 'lt',
            'title' => 'Baik',
            'score' => 40.00,
            'color' => 'success', // DaisyUI class for good scores (low percentage)
        ]);

        ResultCategoryRule::create([
            'result_category_id' => $surveyResultCategory->id,
            'operation' => 'gt',
            'title' => 'Buruk',
            'score' => 70.00,
            'color' => 'error', // DaisyUI class for bad scores (high percentage)
        ]);

        ResultCategoryRule::create([
            'result_category_id' => $surveyResultCategory->id,
            'operation' => 'else',
            'title' => 'Sedang',
            'score' => 0.00, // Not used for 'else' operation
            'color' => 'warning', // DaisyUI class for medium scores
        ]);

        // Create Result Categories for all Survey Sections
        $sections = SurveySection::where('survey_id', $survey->id)->get();
        foreach ($sections as $section) {
            $sectionResultCategory = ResultCategory::create([
                'owner_type' => 'survey_section',
                'owner_id' => $section->id,
                'name' => 'Section ' . $section->order,
            ]);

            // Create Result Category Rules for Section (reversed logic: <40% = Baik)
            ResultCategoryRule::create([
                'result_category_id' => $sectionResultCategory->id,
                'operation' => 'lt',
                'title' => 'Baik',
                'score' => 40.00,
                'color' => 'success', // DaisyUI class for good scores (low percentage)
            ]);

            ResultCategoryRule::create([
                'result_category_id' => $sectionResultCategory->id,
                'operation' => 'gt',
                'title' => 'Buruk',
                'score' => 70.00,
                'color' => 'error', // DaisyUI class for bad scores (high percentage)
            ]);

            ResultCategoryRule::create([
                'result_category_id' => $sectionResultCategory->id,
                'operation' => 'else',
                'title' => 'Sedang',
                'score' => 0.00, // Not used for 'else' operation
                'color' => 'warning', // DaisyUI class for medium scores
            ]);
        }
    }
}