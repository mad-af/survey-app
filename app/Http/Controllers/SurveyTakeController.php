<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Response;
use App\Models\Answer;
use App\Models\ResponseScore;
use App\Models\ResultCategory;
use App\Models\Choice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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

    /**
     * Get survey data with sections and questions for API.
     */
    public function getSurveyData(Survey $survey): JsonResponse
    {
        try {
            // Load survey with all necessary relationships
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

            // Transform data for frontend
            $surveyData = [
                'id' => $survey->id,
                'title' => $survey->title,
                'description' => $survey->description,
                'status' => $survey->status,
                'is_anonymous' => $survey->is_anonymous,
                'sections' => $survey->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'survey_id' => $section->survey_id,
                        'title' => $section->title,
                        'description' => $section->description,
                        'order' => $section->order,
                        'questions' => $section->questions->map(function ($question) {
                            return [
                                'id' => $question->id,
                                'section_id' => $question->section_id,
                                'text' => $question->text,
                                'type' => $question->type->value,
                                'required' => $question->required,
                                'order' => $question->order,
                                'score_weight' => $question->score_weight,
                                'choices' => $question->choices->map(function ($choice) {
                                    return [
                                        'id' => $choice->id,
                                        'question_id' => $choice->question_id,
                                        'label' => $choice->label,
                                        'value' => $choice->value,
                                        'score' => $choice->score,
                                        'order' => $choice->order,
                                    ];
                                })
                            ];
                        })
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $surveyData,
                'message' => 'Survey data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve survey data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit survey response via API.
     */
    public function submitResponse(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'respondent_token' => 'nullable|string|max:255',
                'meta' => 'nullable|array',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.choice_id' => 'nullable|integer|exists:choices,id',
                'answers.*.value_text' => 'nullable|string',
                'answers.*.value_number' => 'nullable|numeric',
                'answers.*.value_json' => 'nullable|array',
                'is_partial' => 'nullable|boolean',
                'section_id' => 'nullable|integer|exists:survey_sections,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $isPartial = $request->boolean('is_partial', false);
            $respondentToken = $request->respondent_token;

            // Find existing response or create new one
            $response = null;
            if ($respondentToken) {
                $response = Response::where('survey_id', $survey->id)
                    ->where('respondent_token', $respondentToken)
                    ->first();
            }

            if (!$response) {
                // Create new response record
                $response = Response::create([
                    'survey_id' => $survey->id,
                    'respondent_token' => $respondentToken,
                    'started_at' => now(),
                    'submitted_at' => $isPartial ? null : now(),
                    'meta' => $request->meta ?? [],
                ]);
            } else {
                // Update existing response
                $response->update([
                    'submitted_at' => $isPartial ? $response->submitted_at : now(),
                    'meta' => array_merge($response->meta ?? [], $request->meta ?? []),
                ]);
            }

            // Process answers - update or create
            foreach ($request->answers as $answerData) {
                
                $existingAnswer = Answer::where('response_id', $response->id)
                    ->where('question_id', $answerData['question_id'])
                    ->first();

                $answerPayload = [
                    'response_id' => $response->id,
                    'question_id' => $answerData['question_id'],
                    'choice_id' => $answerData['choice_id'] ?? null,
                    'value_text' => $answerData['value_text'] ?? null,
                    'value_number' => $answerData['value_number'] ?? null,
                    'value_json' => $answerData['value_json'] ?? null,
                ];

                if ($existingAnswer) {
                    // Update existing answer
                    $existingAnswer->update($answerPayload);
                } else {
                    // Create new answer
                    Answer::create($answerPayload);
                }
            }

            // Calculate and save score for both complete and partial submissions
            // This ensures that every response has a score record, even if incomplete
            $this->calculateAndSaveScore($response, $survey);

            DB::commit();

            $message = $isPartial ? 'Section answers saved successfully' : 'Survey response submitted successfully';
            
            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => $message
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit survey response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey by code for public access.
     */
    public function getSurveyByCode(string $code): JsonResponse
    {
        try {
            $survey = Survey::where('code', $code)
                ->where('status', 'active')
                ->first();

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Survey not found or not active'
                ], 404);
            }

            return $this->getSurveyData($survey);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit survey response by code via API.
     */
    public function submitResponseByCode(Request $request, string $code): JsonResponse
    {
        try {
            $survey = Survey::where('code', $code)
                ->where('status', 'active')
                ->first();

            if (!$survey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Survey not found or not active'
                ], 404);
            }

            return $this->submitResponse($request, $survey);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit survey response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate and save response score
     */
    private function calculateAndSaveScore(Response $response, Survey $survey): void
    {
        // Load survey with all necessary relationships for scoring
        $survey->load([
            'sections.questions.choices',
            'resultCategories' => function ($query) {
                $query->orderBy('min_score');
            }
        ]);

        // Load response answers with relationships
        $response->load('answers.choice', 'answers.question.section');

        $totalScore = 0;
        $maxPossibleScore = 0;
        $sectionScores = [];

        // Calculate score for each section
        foreach ($survey->sections as $section) {
            $sectionScore = 0;
            $sectionMaxScore = 0;

            foreach ($section->questions as $question) {
                $questionWeight = $question->score_weight ?? 1;
                $questionMaxScore = 0;
                $questionScore = 0;

                // Find the answer for this question
                $answer = $response->answers->where('question_id', $question->id)->first();

                if ($answer && $answer->choice_id) {
                    // For choice-based questions, use choice score
                    $choice = $question->choices->where('id', $answer->choice_id)->first();
                    if ($choice) {
                        $questionScore = $choice->score * $questionWeight;
                    }
                } elseif ($answer && $answer->value_number !== null) {
                    // For numeric questions, use the numeric value
                    $questionScore = $answer->value_number * $questionWeight;
                }

                // Calculate max possible score for this question
                if ($question->choices->isNotEmpty()) {
                    $questionMaxScore = $question->choices->max('score') * $questionWeight;
                } else {
                    // For non-choice questions, assume max score is the weight itself
                    $questionMaxScore = $questionWeight;
                }

                $sectionScore += $questionScore;
                $sectionMaxScore += $questionMaxScore;
            }

            $sectionScores[] = [
                'section_id' => $section->id,
                'title' => $section->title,
                'score' => round($sectionScore, 2),
                'max_score' => round($sectionMaxScore, 2),
                'percentage' => $sectionMaxScore > 0 ? round(($sectionScore / $sectionMaxScore) * 100, 2) : 0
            ];

            $totalScore += $sectionScore;
            $maxPossibleScore += $sectionMaxScore;
        }

        // Calculate percentage
        $percentage = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;

        // Determine result category based on percentage
        $resultCategory = null;
        foreach ($survey->resultCategories as $category) {
            if ($percentage >= $category->min_score && $percentage <= $category->max_score) {
                $resultCategory = $category;
                break;
            }
        }

        // Create or update response score
        ResponseScore::updateOrCreate(
            ['response_id' => $response->id],
            [
                'result_category_id' => $resultCategory?->id,
                'total_score' => round($totalScore, 2),
                'max_possible_score' => round($maxPossibleScore, 2),
                'percentage' => round($percentage, 2),
                'section_scores' => $sectionScores,
            ]
        );
    }
}