<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Answer;
use App\Enums\ResponseStatus;
use Exception;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SurveyProcessController extends Controller
{
    /**
     * Entry point for survey access
     * 
     * @param Request $request
     * @return InertiaResponse
     */
    public function entry(Request $request): InertiaResponse
    {
        try {
            // Get available surveys for public access
            $surveys = Survey::where('status', 'active')
                        ->where('visibility', 'public')
                        ->where(function($query) {
                            $query->whereNull('starts_at')
                                  ->orWhere('starts_at', '<=', now());
                        })
                        ->where(function($query) {
                            $query->whereNull('ends_at')
                                  ->orWhere('ends_at', '>=', now());
                        })
                        ->select('id', 'code', 'title', 'description', 'starts_at', 'ends_at')
                        ->orderBy('created_at', 'desc')
                        ->get();

            return Inertia::render('Entry', [
                'publicSurveys' => $surveys
            ]);

        } catch (Exception $e) {
            Log::error('Failed to retrieve available surveys: ' . $e->getMessage());
            
            return Inertia::render('Entry', [
                'publicSurveys' => []
            ]);
        }
    }

    /**
     * Process survey initialization
     * 
     * @param Request $request
     * @param Survey $survey
     * @return JsonResponse
     */
    public function initializeSurvey(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'respondent_data' => 'required|array',
                'respondent_data.name' => 'required|string|max:255',
                'respondent_data.email' => 'nullable|email|max:255',
                'respondent_data.phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create response record with respondent data
            $response = Response::firstOrCreate(
                [
                    'survey_id' => $survey->id,
                    'respondent_token' => session()->getId()
                ],
                [
                    'current_step' => Response::STEP_RESPONDENT_DATA,
                    'status' => ResponseStatus::STARTED,
                    'started_at' => now(),
                    'meta' => $request->respondent_data
                ]
            );

            // Update response with respondent data if it already exists
            if (!$response->wasRecentlyCreated) {
                $response->update([
                    'meta' => array_merge($response->meta ?? [], $request->respondent_data),
                    'status' => ResponseStatus::IN_PROGRESS
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Survey initialized successfully',
                'data' => [
                    'response_id' => $response->id,
                    'current_step' => $response->current_step,
                    'survey' => $survey->load('sections.questions.choices')
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process section answers
     * 
     * @param Request $request
     * @param Survey $survey
     * @return JsonResponse
     */
    public function processSectionAnswers(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'response_id' => 'required|exists:responses,id',
                'section_id' => 'required|exists:survey_sections,id',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.choice_id' => 'nullable|exists:choices,id',
                'answers.*.value_text' => 'nullable|string',
                'answers.*.value_number' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $response = Response::findOrFail($request->response_id);
            $answers = $request->answers;

            foreach ($answers as $answerData) {
                Answer::updateOrCreate(
                    [
                        'response_id' => $response->id,
                        'question_id' => $answerData['question_id']
                    ],
                    [
                        'choice_id' => $answerData['choice_id'] ?? null,
                        'value_text' => $answerData['value_text'] ?? null,
                        'value_number' => $answerData['value_number'] ?? null,
                    ]
                );
            }

            // Move to next step (Questions step) and update status
            $response->setCurrentStep(Response::STEP_QUESTIONS);
            $response->markAsInProgress();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Section answers processed successfully',
                'data' => [
                    'current_step' => $response->current_step,
                    'step_name' => $response->step_name
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process section answers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize survey response
     * 
     * @param Request $request
     * @param Survey $survey
     * @return JsonResponse
     */
    public function finalizeSurvey(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'response_id' => 'required|exists:responses,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $response = Response::findOrFail($request->response_id);
            
            // Update response to final step and mark as completed
            $response->update([
                'current_step' => Response::STEP_RESULT
            ]);
            $response->markAsCompleted();

            // Calculate and save score
            $this->calculateAndSaveScore($response, $survey);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Survey finalized successfully',
                'data' => [
                    'response_id' => $response->id,
                    'status' => $response->status,
                    'submitted_at' => $response->submitted_at
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey progress
     * 
     * @param Request $request
     * @param Survey $survey
     * @return JsonResponse
     */
    public function getSurveyProgress(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'response_id' => 'required|exists:responses,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $response = Response::findOrFail($request->response_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_step' => $response->current_step,
                    'step_name' => $response->step_name,
                    'status' => $response->status,
                    'started_at' => $response->started_at,
                    'submitted_at' => $response->submitted_at
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get survey progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate survey answers
     * 
     * @param Request $request
     * @param Survey $survey
     * @return JsonResponse
     */
    public function validateAnswers(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.choice_id' => 'nullable|exists:choices,id',
                'answers.*.value_text' => 'nullable|string',
                'answers.*.value_number' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $answers = $request->answers;
            $validationErrors = [];

            foreach ($answers as $index => $answerData) {
                $question = \App\Models\Question::find($answerData['question_id']);
                
                if (!$question) {
                    $validationErrors["answers.{$index}.question_id"] = ['Question not found'];
                    continue;
                }

                // Validate based on question type
                switch ($question->type) {
                    case 'SINGLE_CHOICE':
                    case 'MULTIPLE_CHOICE':
                        if (empty($answerData['choice_id'])) {
                            $validationErrors["answers.{$index}.choice_id"] = ['Choice is required for this question type'];
                        }
                        break;
                    
                    case 'TEXT':
                        if (empty($answerData['value_text'])) {
                            $validationErrors["answers.{$index}.value_text"] = ['Text value is required for this question type'];
                        }
                        break;
                    
                    case 'NUMBER':
                        if (!isset($answerData['value_number'])) {
                            $validationErrors["answers.{$index}.value_number"] = ['Number value is required for this question type'];
                        }
                        break;
                }
            }

            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Answer validation failed',
                    'errors' => $validationErrors
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'All answers are valid'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate answers',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate and save survey score
     * 
     * @param Response $response
     * @param Survey $survey
     * @return void
     */
    private function calculateAndSaveScore(Response $response, Survey $survey): void
    {
        // Load survey with sections, questions, and choices
        $survey->load('sections.questions.choices');
        
        // Load response answers
        $response->load('answers.choice', 'answers.question.section');
        
        $totalScore = 0;
        $maxScore = 0;
        $sectionScores = [];
        
        foreach ($survey->sections as $section) {
            $sectionScore = 0;
            $sectionMaxScore = 0;
            
            foreach ($section->questions as $question) {
                $answer = $response->answers->where('question_id', $question->id)->first();
                
                if ($answer) {
                    switch ($question->type) {
                        case 'SINGLE_CHOICE':
                        case 'MULTIPLE_CHOICE':
                            if ($answer->choice) {
                                $sectionScore += $answer->choice->score ?? 0;
                            }
                            $sectionMaxScore += $question->choices->max('score') ?? 0;
                            break;
                        
                        case 'NUMBER':
                            $sectionScore += $answer->value_number ?? 0;
                            $sectionMaxScore += $question->max_value ?? 100;
                            break;
                    }
                }
            }
            
            $sectionScores[$section->id] = [
                'score' => $sectionScore,
                'max_score' => $sectionMaxScore,
                'percentage' => $sectionMaxScore > 0 ? ($sectionScore / $sectionMaxScore) * 100 : 0
            ];
            
            $totalScore += $sectionScore;
            $maxScore += $sectionMaxScore;
        }
        
        // Save response score
        \App\Models\ResponseScore::updateOrCreate(
            ['response_id' => $response->id],
            [
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'percentage' => $maxScore > 0 ? ($totalScore / $maxScore) * 100 : 0,
                'section_scores' => $sectionScores
            ]
        );
    }
}
