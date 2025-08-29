<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SurveyTakeController extends Controller
{
    /**
     * Display the survey taking page
     */
    public function show(Survey $survey)
    {
        // Load survey with sections and questions
        $survey->load([
            'sections' => function ($query) {
                $query->orderBy('order');
            },
            'sections.questions' => function ($query) {
                $query->orderBy('order');
            },
            'sections.questions.choices' => function ($query) {
                $query->orderBy('order');
            }
        ]);

        // Check if survey is active
        if (!$survey->is_active) {
            return redirect()->route('surveys.index')
                ->with('error', 'Survey ini tidak aktif.');
        }

        // Check if survey has expired
        if ($survey->end_date && $survey->end_date->isPast()) {
            return redirect()->route('surveys.index')
                ->with('error', 'Survey ini sudah berakhir.');
        }

        return Inertia::render('Survey/Take', [
            'survey' => $survey
        ]);
    }

    /**
     * Store survey response
     */
    public function store(Request $request, Survey $survey)
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required',
            'external_id' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        // Create survey response
        $response = $survey->responses()->create([
            'external_id' => $request->external_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'submitted_at' => now(),
        ]);

        // Store answers
        foreach ($request->answers as $questionId => $answerValue) {
            $question = $survey->sections()
                ->with('questions')
                ->get()
                ->pluck('questions')
                ->flatten()
                ->where('id', $questionId)
                ->first();

            if ($question) {
                $response->answers()->create([
                    'question_id' => $questionId,
                    'answer_text' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                ]);
            }
        }

        return redirect()->route('surveys.thank-you', $survey)
            ->with('success', 'Terima kasih! Jawaban Anda telah berhasil disimpan.');
    }

    /**
     * Show thank you page
     */
    public function thankYou(Survey $survey)
    {
        return Inertia::render('Survey/ThankYou', [
            'survey' => $survey
        ]);
    }
}