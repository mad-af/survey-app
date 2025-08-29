<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use App\Models\Response;
use App\Enums\SurveyStatus;
use App\Enums\SurveyVisibility;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Inertia\Inertia;

class SurveyController extends Controller
{
    /**
     * Display a listing of the surveys.
     */
    public function index(): JsonResponse
    {
        try {
            $surveys = Survey::with('owner:id,name')
                        ->select('id', 'owner_id', 'code', 'title', 'description', 'status', 'visibility', 'starts_at', 'ends_at', 'created_at', 'updated_at')
                        ->orderBy('created_at', 'desc')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $surveys,
                'message' => 'Surveys retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve surveys',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created survey in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:draft,active,closed',
                'visibility' => 'required|string|in:private,link,public',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $survey = Survey::create([
                'owner_id' => Auth::id() ?? 1,
                'code' => Str::upper(Str::random(8)),
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'visibility' => $request->visibility,
                'starts_at' => $request->start_date ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null,
                'ends_at' => $request->end_date ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null,
            ]);

            // Load the owner relationship
            $survey->load('owner:id,name');

            return response()->json([
                'success' => true,
                'data' => $survey,
                'message' => 'Survey created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified survey.
     */
    public function show(Survey $survey): JsonResponse
    {
        try {
            $survey->load('owner:id,name');
            
            return response()->json([
                'success' => true,
                'data' => $survey,
                'message' => 'Survey retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified survey in storage.
     */
    public function update(Request $request, Survey $survey): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|string|in:draft,active,closed',
                'visibility' => 'required|string|in:private,link,public',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'visibility' => $request->visibility,
                'starts_at' => $request->start_date ? date('Y-m-d H:i:s', strtotime($request->start_date)) : null,
                'ends_at' => $request->end_date ? date('Y-m-d H:i:s', strtotime($request->end_date)) : null,
            ];

            $survey->update($updateData);
            $survey->load('owner:id,name');

            return response()->json([
                'success' => true,
                'data' => $survey,
                'message' => 'Survey updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified survey from storage.
     */
    public function destroy(Survey $survey): JsonResponse
    {
        try {
            // Check if user has permission to delete this survey
            if (Auth::id() !== $survey->owner_id && !Auth::user()->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this survey'
                ], 403);
            }

            $survey->delete();

            return response()->json([
                'success' => true,
                'message' => 'Survey deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete survey',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey statistics.
     */
    public function statistics(Survey $survey): JsonResponse
    {
        try {
            // Load necessary relationships
            $survey->load(['sections.questions', 'responses']);
            
            // Calculate statistics
            $totalSections = $survey->sections->count();
            $totalQuestions = $survey->sections->sum(function ($section) {
                return $section->questions->count();
            });
            $totalResponses = $survey->responses->count();
            
            // Calculate completion rate
            $completedResponses = $survey->responses->whereNotNull('submitted_at')->count();
            $completionRate = $totalResponses > 0 ? round(($completedResponses / $totalResponses) * 100) . '%' : '0%';
            
            // Calculate average completion time
            $completedResponsesWithTime = $survey->responses
                ->whereNotNull('submitted_at')
                ->whereNotNull('started_at');
            
            $averageTime = '0m';
            if ($completedResponsesWithTime->count() > 0) {
                $totalMinutes = $completedResponsesWithTime->sum(function ($response) {
                    $start = Carbon::parse($response->started_at);
                    $end = Carbon::parse($response->submitted_at);
                    return $start->diffInMinutes($end);
                });
                $avgMinutes = round($totalMinutes / $completedResponsesWithTime->count());
                $averageTime = $avgMinutes . 'm';
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'totalSections' => $totalSections,
                    'totalQuestions' => $totalQuestions,
                    'totalResponses' => $totalResponses,
                    'completionRate' => $completionRate,
                    'averageTime' => $averageTime
                ],
                'message' => 'Survey statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve survey statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show entry page with public surveys
     */
    public function showEntry()
    {
        // Get public surveys that are active
        $publicSurveys = Survey::where('visibility', SurveyVisibility::PUBLIC)
            ->where('status', SurveyStatus::ACTIVE)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->select('id', 'code', 'title', 'description', 'starts_at', 'ends_at')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return Inertia::render('Entry', [
            'publicSurveys' => $publicSurveys
        ]);
    }

    /**
     * Handle survey entry with survey code
     */
    public function enter(Request $request)
    {
        $request->validate([
            'survey_code' => 'required|string'
        ]);

        // Find survey by code
        $survey = Survey::where('code', $request->survey_code)
            ->where('status', 'active')
            ->first();

        if (!$survey) {
            return back()->withErrors([
                'survey_code' => 'Kode survey tidak valid atau survey tidak aktif.'
            ]);
        }

        // Check if survey is within date range
        $now = now();
        if ($survey->starts_at && $now->lt($survey->starts_at)) {
            return back()->withErrors([
                'survey_code' => 'Survey belum dimulai.'
            ]);
        }

        if ($survey->ends_at && $now->gt($survey->ends_at)) {
            return back()->withErrors([
                'survey_code' => 'Survey sudah berakhir.'
            ]);
        }

        // Generate unique respondent token
        $respondentToken = $this->generateRespondentToken($survey->id);

        // Create Response entry
        $response = Response::create([
            'survey_id' => $survey->id,
            'respondent_id' => null, // Will be filled after respondent registration
            'respondent_token' => $respondentToken,
            'started_at' => now(),
            'meta' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'survey_code' => $request->survey_code
            ]
        ]);

        // Store token in session for middleware access
        session([
            'survey_token' => $respondentToken,
            'survey_id' => $survey->id,
            'survey_code' => $survey->code,
            'response_id' => $response->id
        ]);

        // Use Inertia redirect instead of JSON response
        return redirect()->intended('/survey/' . $survey->code . '/respondent-data');
    }

    /**
     * Show respondent data form
     */
    public function showRespondentData(Request $request, $survey)
    {
        // Find survey by code
        $surveyModel = Survey::where('code', $survey)->first();
        
        if (!$surveyModel) {
            abort(404, 'Survey not found');
        }
        
        // Get existing respondent data if available
        $existingRespondent = null;
        $surveyResponse = $request->survey_response;

        if ($surveyResponse && $surveyResponse->respondent_id) {
            $existingRespondent = \App\Models\Respondent::find($surveyResponse->respondent_id);
        }
        
        return Inertia::render('Survey/RespondentData', [
            'survey' => $surveyModel,
            'surveyCode' => $survey,
            'existingRespondent' => $existingRespondent
        ]);
    }

    /**
     * Logout from survey and clear session
     */
    public function logout(Request $request)
    {
        // Clear all survey-related session data
        session()->forget([
            'survey_token',
            'survey_id', 
            'survey_code',
            'response_id'
        ]);

        // Clear the entire session to be safe
        session()->flush();

        return response()->json([
            'success' => true,
            'message' => 'Survey session cleared successfully'
        ]);
    }

    /**
     * Show survey questions
     */
    public function showQuestions(Request $request, $survey)
    {
        // Find survey by code
        $surveyModel = Survey::where('code', $survey)->first();
        
        if (!$surveyModel) {
            abort(404, 'Survey not found');
        }
        
        // Load survey with sections, questions, and choices
        $surveyModel->load([
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
        
        // Get existing response data if available
        $existingResponse = null;
        $surveyResponse = $request->survey_response;

        if ($surveyResponse) {
            // Load answers for the existing response
            $surveyResponse->load('answers');
            // Format answers as object with question_id as key
            $formattedAnswers = [];
            foreach ($surveyResponse->answers as $answer) {
                $formattedAnswers[$answer->question_id] = [
                    'question_id' => $answer->question_id,
                    'choice_id' => $answer->choice_id,
                    'answer_value' => $answer->answer_value ?? $answer->value_text ?? $answer->value_number,
                    'value_text' => $answer->value_text,
                    'value_number' => $answer->value_number,
                    'value_json' => $answer->value_json,
                ];
            }
            
            $existingResponse = [
                'id' => $surveyResponse->id,
                'answers' => $formattedAnswers,
                'status' => $surveyResponse->status
            ];
        }
        
        return Inertia::render('Survey/Questions', [
            'surveyCode' => $survey,
            'surveyData' => $surveyModel,
            'existingResponse' => $existingResponse
        ]);
    }

    /**
     * Show survey result page
     */
    public function showResult(Request $request, $survey)
    {
        
        // Get survey data from middleware
        $surveyModel = $request->survey;
        $surveyResponse = $request->survey_response;
        
        // Load survey with sections
        $surveyModel->load([
            'sections' => function ($query) {
                $query->orderBy('order');
            }
        ]);
        
        // Load response with score and result category
        $surveyResponse->load(['score.resultCategory']);
        
        // Get response score
        $responseScore = $surveyResponse->score;

        if (!$responseScore) {
            abort(404, 'Survey result not found');
        }
        
        // Prepare survey result data
        $surveyResult = [
            'survey' => [
                'title' => $surveyModel->title,
                'description' => $surveyModel->description,
                'code' => $surveyModel->code
            ],
            'score' => [
                'total_score' => $responseScore->total_score,
                'max_possible_score' => $responseScore->max_possible_score,
                'percentage' => $responseScore->percentage,
                'category' => $responseScore->resultCategory ? [
                    'name' => $responseScore->resultCategory->name,
                    'description' => $responseScore->resultCategory->description,
                    'color' => $responseScore->resultCategory->color
                ] : null
            ],
            'sections' => []
        ];
        
        // Process section scores
        if ($responseScore->section_scores && is_array($responseScore->section_scores)) {
            foreach ($surveyModel->sections as $section) {
                $sectionScore = collect($responseScore->section_scores)
                    ->firstWhere('section_id', $section->id);
                
                if ($sectionScore) {
                    $surveyResult['sections'][] = [
                        'id' => $section->id,
                        'title' => $section->title,
                        'description' => $section->description,
                        'score' => $sectionScore['score'] ?? 0,
                        'max_score' => $sectionScore['max_score'] ?? 0,
                        'percentage' => $sectionScore['percentage'] ?? 0
                    ];
                }
            }
        }
        
        return Inertia::render('Survey/Result', [
            'surveyCode' => $survey,
            'surveyResult' => $surveyResult
        ]);
    }

    /**
     * Handle respondent registration
     */
    public function registerRespondent(Request $request, $survey)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'birth_year' => 'required|integer|min:1900|max:' . date('Y'),
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role_title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'external_id' => 'nullable|string|max:100',
            'consent' => 'nullable|boolean',
            'consent_at' => 'nullable|date',
            'demographics' => 'nullable|array'
        ]);

        try {
            // Get survey token and response from middleware
            $surveyToken = $request->survey_token;
            $surveyId = $request->survey_id;
            $responseId = $request->response_id;
            $surveyModel = $request->survey;
            $surveyResponse = $request->survey_response;

            // Check if respondent already exists for this response
            $respondent = null;
            if ($surveyResponse->respondent_id) {
                $respondent = \App\Models\Respondent::find($surveyResponse->respondent_id);
            }
            
            $respondentData = [
                'external_id' => $request->external_id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'birth_year' => $request->birth_year,
                'organization' => $request->organization,
                'department' => $request->department,
                'role_title' => $request->role_title,
                'location' => $request->location,
                'demographics' => $request->demographics ?? [],
                'consent' => $request->consent,
                'consent_at' => $request->consent_at ? \Carbon\Carbon::parse($request->consent_at) : now(),
            ];
            
            if ($respondent) {
                // Update existing respondent
                $respondent->update($respondentData);
            } else {
                // Create new respondent
                $respondentData['meta'] = [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'registration_timestamp' => now()->toISOString()
                ];
                $respondent = \App\Models\Respondent::create($respondentData);
            }

            // Update response with respondent_id
            $surveyResponse->update([
                'respondent_id' => $respondent->id
            ]);

            // Create or update survey_respondent relationship
            $surveyRespondent = \App\Models\SurveyRespondent::where('survey_id', $surveyId)
                ->where('respondent_id', $respondent->id)
                ->first();
                
            if (!$surveyRespondent) {
                \App\Models\SurveyRespondent::create([
                    'survey_id' => $surveyId,
                    'respondent_id' => $respondent->id,
                    'invited_at' => now(),
                    'started_at' => now(),
                    'status' => 'in_progress'
                ]);
            }

            return Inertia::location('/survey/' . $surveyModel->code . '/questions');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register respondent',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Show survey responses and respondents
     */
    public function showResponses($surveyId)
    {
        try {
            $survey = Survey::with([
                'responses' => function($query) {
                    $query->with([
                        'respondent:id,name,email,phone,gender,birth_year,organization,department,role_title,location,created_at',
                        'score:id,response_id,total_score,max_possible_score,percentage,section_scores',
                        'score.resultCategory:id,name,description,color'
                    ])->orderBy('created_at', 'desc');
                },
                'sections:id,survey_id,title,description'
            ])->findOrFail($surveyId);

            // Format responses data
            $formattedResponses = $survey->responses->map(function($response) {
                return [
                    'id' => $response->id,
                    'status' => $response->status,
                    'submitted_at' => $response->submitted_at,
                    'created_at' => $response->created_at,
                    'respondent' => $response->respondent ? [
                        'id' => $response->respondent->id,
                        'name' => $response->respondent->name,
                        'email' => $response->respondent->email,
                        'phone' => $response->respondent->phone,
                        'gender' => $response->respondent->gender,
                        'birth_year' => $response->respondent->birth_year,
                        'organization' => $response->respondent->organization,
                        'department' => $response->respondent->department,
                        'role_title' => $response->respondent->role_title,
                        'location' => $response->respondent->location,
                        'registered_at' => $response->respondent->created_at
                    ] : null,
                    'score' => $response->score ? [
                        'total_score' => $response->score->total_score,
                        'max_possible_score' => $response->score->max_possible_score,
                        'percentage' => $response->score->percentage,
                        'category' => $response->score->resultCategory ? [
                            'name' => $response->score->resultCategory->name,
                            'description' => $response->score->resultCategory->description,
                            'color' => $response->score->resultCategory->color
                        ] : null,
                        'section_scores' => $response->score->section_scores
                    ] : null
                ];
            });

            return Inertia::render('Dashboard/Survey/Response', [
                'survey' => [
                    'id' => $survey->id,
                    'code' => $survey->code,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'status' => $survey->status,
                    'created_at' => $survey->created_at,
                    'sections' => $survey->sections
                ],
                'responses' => $formattedResponses,
                'totalResponses' => $survey->responses->count(),
                'completedResponses' => $survey->responses->where('status', 'completed')->count(),
                'inProgressResponses' => $survey->responses->where('status', 'in_progress')->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve survey responses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique respondent token
     */
    private function generateRespondentToken($surveyId)
    {
        return Str::random(32) . '_' . time() . '_' . $surveyId;
    }

    /**
     * Save section answers (partial submission)
     */
    public function saveSectionAnswers(Request $request, $survey)
    {
        $request->validate([
            'answers' => 'required|array',
            'section_id' => 'required|integer',
            'is_partial' => 'required|boolean',
            'meta' => 'nullable|array'
        ]);

        try {
            // Get survey and response from middleware
            $surveyResponse = $request->survey_response;

            // If no existing response, create one
            if (!$surveyResponse) {
                dd("Kesalahan");
            }

            // Save answers
            foreach ($request->answers as $answerData) {
                $existingAnswer = \App\Models\Answer::where('response_id', $surveyResponse->id)
                    ->where('question_id', $answerData['question_id'])
                    ->first();

                $answerPayload = [
                    'response_id' => $surveyResponse->id,
                    'question_id' => $answerData['question_id'],
                    'choice_id' => $answerData['choice_id'] ?? null,
                    'answer_value' => $answerData['answer_value'] ?? null,
                    'value_text' => $answerData['value_text'] ?? null,
                    'value_number' => $answerData['value_number'] ?? null,
                    'value_json' => $answerData['value_json'] ?? null,
                ];

                if ($existingAnswer) {
                    $existingAnswer->update($answerPayload);
                } else {
                    \App\Models\Answer::create($answerPayload);
                }
            }

            return redirect()->back()->with([
                'success' => 'Section answers saved successfully',
                'responseId' => $surveyResponse->id
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'message' => 'Failed to save section answers: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Submit final survey response
     */
    public function submitSurveyResponse(Request $request, $survey)
    {
        $request->validate([
            'answers' => 'required|array',
            'is_partial' => 'required|boolean',
            'meta' => 'nullable|array'
        ]);

        try {
            // Get survey and response from middleware
            $surveyModel = $request->survey;
            $surveyResponse = $request->survey_response;

            // If no existing response, create one
            if (!$surveyResponse) {
                $surveyResponse = \App\Models\Response::create([
                    'survey_id' => $surveyModel->id,
                    'respondent_id' => $request->respondent_id,
                    'respondent_token' => $request->respondent_token,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'meta' => $request->meta
                ]);
            } else {
                // Update existing response to completed
                $surveyResponse->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'meta' => array_merge($surveyResponse->meta ?? [], $request->meta ?? [])
                ]);
            }

            // Save all answers
            foreach ($request->answers as $answerData) {
                $existingAnswer = \App\Models\Answer::where('response_id', $surveyResponse->id)
                    ->where('question_id', $answerData['question_id'])
                    ->first();

                $answerPayload = [
                    'response_id' => $surveyResponse->id,
                    'question_id' => $answerData['question_id'],
                    'choice_id' => $answerData['choice_id'] ?? null,
                    'answer_value' => $answerData['answer_value'] ?? null,
                    'value_text' => $answerData['value_text'] ?? null,
                    'value_number' => $answerData['value_number'] ?? null,
                    'value_json' => $answerData['value_json'] ?? null,
                ];

                if ($existingAnswer) {
                    $existingAnswer->update($answerPayload);
                } else {
                    \App\Models\Answer::create($answerPayload);
                }
            }

            // Calculate score (reuse logic from SurveyTakeController)
            $this->calculateAndSaveScore($surveyResponse, $surveyModel);

            // Redirect to result page
            return redirect()->route('survey.result', ['survey' => $surveyModel->code]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'message' => 'Failed to submit survey: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate and save response score (copied from SurveyTakeController)
     */
    private function calculateAndSaveScore($response, $survey): void
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
                }

                // Calculate max possible score for this question
                $maxChoiceScore = $question->choices->max('score') ?? 0;
                $questionMaxScore = $maxChoiceScore * $questionWeight;

                $sectionScore += $questionScore;
                $sectionMaxScore += $questionMaxScore;
            }

            $sectionScores[$section->id] = [
                'score' => $sectionScore,
                'max_score' => $sectionMaxScore,
                'percentage' => $sectionMaxScore > 0 ? ($sectionScore / $sectionMaxScore) * 100 : 0
            ];

            $totalScore += $sectionScore;
            $maxPossibleScore += $sectionMaxScore;
        }

        // Calculate percentage
        $percentage = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;

        // Determine result category
        $resultCategory = null;
        if ($survey->resultCategories->isNotEmpty()) {
            foreach ($survey->resultCategories as $category) {
                if ($percentage >= $category->min_score && 
                    ($category->max_score === null || $percentage <= $category->max_score)) {
                    $resultCategory = $category;
                    break;
                }
            }
        }

        // Save or update response score
        \App\Models\ResponseScore::updateOrCreate(
            ['response_id' => $response->id],
            [
                'total_score' => $totalScore,
                'max_possible_score' => $maxPossibleScore,
                'percentage' => round($percentage, 2),
                'section_scores' => $sectionScores,
                'result_category_id' => $resultCategory?->id,
                'calculated_at' => now()
            ]
        );
    }
}