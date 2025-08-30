<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
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
            'email' => 'nullable|email|max:255|unique:respondents,email,' . ($request->respondent_id ?? 'NULL'),
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'role_title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'external_id' => 'nullable|string|max:100',
            'consent' => 'required|accepted',
            'consent_at' => 'nullable|date',
            'demographics' => 'nullable|array'
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Get survey and response from middleware
            $surveyModel = $request->survey;
            $surveyResponse = $request->survey_response;

            if (!$surveyResponse) {
                throw new \Exception('Survey response not found');
            }

            // Check if respondent already exists for this response
            $respondent = null;
            if ($surveyResponse->respondent_id) {
                $respondent = \App\Models\Respondent::find($surveyResponse->respondent_id);
            }
            
            // Prepare respondent data
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
                // Create new respondent with metadata
                $respondentData['meta'] = [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'registration_timestamp' => now()->toISOString(),
                    'survey_code' => $surveyModel->code
                ];
                $respondent = \App\Models\Respondent::create($respondentData);
            }

            // Update response with respondent_id and mark as started
            $surveyResponse->update([
                'respondent_id' => $respondent->id,
                'started_at' => $surveyResponse->started_at ?? now()
            ]);

            // Create or update survey_respondent relationship using helper method
            $surveyRespondent = \App\Models\SurveyRespondent::createOrUpdateRelationship(
                $surveyModel->id,
                $respondent->id,
                'in_progress'
            );

            // Verify respondent can take survey
            if (!$surveyRespondent->canTakeSurvey()) {
                throw new \Exception('Respondent is not eligible to take this survey');
            }

            \Illuminate\Support\Facades\DB::commit();

            return Inertia::location('/survey/' . $surveyModel->code . '/questions');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            \Illuminate\Support\Facades\Log::error('Failed to register respondent', [
                'survey_id' => $request->survey_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register respondent: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : 'Registration failed'
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

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Get survey and response from middleware
            $surveyModel = $request->survey;
            $surveyResponse = $request->survey_response;

            // If no existing response, create one
            if (!$surveyResponse) {
                throw new \Exception('Survey response not found');
            }

            $answers = $request->answers;
            $sectionId = $request->section_id;
            
            if (empty($answers)) {
                throw new \Exception('No answers provided');
            }

            // Prepare bulk data for answers
            $answersToUpdate = [];
            $answersToCreate = [];
            $existingAnswers = [];
            
            // Get existing answers for this response
            if (!empty($answers)) {
                $questionIds = array_column($answers, 'question_id');
                $existingAnswers = \App\Models\Answer::where('response_id', $surveyResponse->id)
                    ->whereIn('question_id', $questionIds)
                    ->get()
                    ->keyBy('question_id');
            }

            // Save answers using bulk operations
            foreach ($answers as $answerData) {
                $questionId = $answerData['question_id'];
                $question = \App\Models\Question::find($questionId);
                if (!$question || $question->section->survey_id !== $surveyModel->id) {
                    continue;
                }

                $answerPayload = [
                    'choice_id' => $answerData['choice_id'] ?? null,
                    'value_text' => $answerData['value_text'] ?? null,
                    'value_number' => $answerData['value_number'] ?? null,
                    'value_json' => $answerData['value_json'] ?? null,
                    'updated_at' => now()
                ];

                if (isset($existingAnswers[$questionId])) {
                    // Update existing answer
                    $answersToUpdate[$questionId] = $answerPayload;
                } else {
                    // Create new answer
                    $answerPayload['response_id'] = $surveyResponse->id;
                    $answerPayload['question_id'] = $questionId;
                    $answerPayload['created_at'] = now();
                    $answersToCreate[] = $answerPayload;
                }
            }

            // Bulk update existing answers
            foreach ($answersToUpdate as $questionId => $values) {
                $existingAnswers[$questionId]->update($values);
            }

            // Bulk create new answers
            if (!empty($answersToCreate)) {
                \App\Models\Answer::insert($answersToCreate);
            }

            // Update survey respondent progress if respondent exists
            if ($surveyResponse->respondent_id) {
                $surveyRespondent = \App\Models\SurveyRespondent::where('survey_id', $surveyModel->id)
                    ->where('respondent_id', $surveyResponse->respondent_id)
                    ->first();
                    
                if ($surveyRespondent) {
                    $surveyRespondent->markAsInProgress();
                    
                    // Update progress metadata
                    $meta = $surveyRespondent->meta ?? [];
                    $meta['last_section_saved'] = $sectionId;
                    $meta['last_saved_at'] = now()->toISOString();
                    $meta['answers_count'] = \App\Models\Answer::where('response_id', $surveyResponse->id)->count();
                    $surveyRespondent->update(['meta' => $meta]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->back()->with([
                'success' => 'Section answers saved successfully',
                'responseId' => $surveyResponse->id,
                'data' => [
                    'answers_updated' => count($answersToUpdate),
                    'answers_created' => count($answersToCreate),
                    'section_id' => $sectionId
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            \Illuminate\Support\Facades\Log::error('Failed to save section answers', [
                'survey_id' => $surveyModel->id ?? null,
                'response_id' => $surveyResponse->id ?? null,
                'section_id' => $sectionId ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
            'is_partial' => 'boolean',
            'meta' => 'nullable|array'
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Get survey and response from middleware
            $surveyModel = $request->survey;
            $surveyResponse = $request->survey_response;

            if (!$surveyResponse) {
                throw new \Exception('Survey response not found');
            }

            $answers = $request->input('answers', []);
            $isPartial = $request->input('is_partial', false);
            
            if (empty($answers)) {
                throw new \Exception('No answers provided');
            }

            // Prepare bulk data for answers
            $answersToUpdate = [];
            $answersToCreate = [];
            $existingAnswers = [];
            
            // Get existing answers for this response
            if (!empty($answers)) {
                $questionIds = array_column($answers, 'question_id');
                $existingAnswers = \App\Models\Answer::where('response_id', $surveyResponse->id)
                    ->whereIn('question_id', $questionIds)
                    ->get()
                    ->keyBy('question_id');
            }

            // Process answers with bulk operations
            foreach ($answers as $answerData) {
                $questionId = $answerData['question_id'];
                $question = \App\Models\Question::find($questionId);
                
                if (!$question || $question->section->survey_id !== $surveyModel->id) {
                    continue;
                }

                // Prepare answer data with proper validation
                $valueNumber = null;
                if (isset($answerData['value_number']) && is_numeric($answerData['value_number'])) {
                    $valueNumber = (float)$answerData['value_number'];
                }
                
                $valueJson = null;
                if (!empty($answerData['value_json'])) {
                    $valueJson = is_string($answerData['value_json']) ? 
                        $answerData['value_json'] : json_encode($answerData['value_json']);
                }
                
                $answerPayload = [
                    'choice_id' => $answerData['choice_id'] ?? null,
                    'value_text' => $answerData['value_text'] ?? null,
                    'value_number' => $valueNumber,
                    'value_json' => $valueJson,
                    'updated_at' => now()
                ];

                if (isset($existingAnswers[$questionId])) {
                    // Update existing answer
                    $answersToUpdate[$questionId] = $answerPayload;
                } else {
                    // Create new answer
                    $answerPayload['response_id'] = $surveyResponse->id;
                    $answerPayload['question_id'] = $questionId;
                    $answerPayload['created_at'] = now();
                    $answersToCreate[] = $answerPayload;
                }
            }

            // Bulk update existing answers
            foreach ($answersToUpdate as $questionId => $values) {
                $existingAnswers[$questionId]->update($values);
            }

            // Bulk create new answers
            if (!empty($answersToCreate)) {
                \App\Models\Answer::insert($answersToCreate);
            }

            // Update response status
            $responseUpdateData = [
                'submitted_at' => now(),
                'meta' => array_merge($surveyResponse->meta ?? [], $request->meta ?? [], [
                    'submission_timestamp' => now()->toISOString(),
                    'total_answers' => count($answers),
                    'is_complete' => !$isPartial
                ])
            ];

            $surveyResponse->update($responseUpdateData);

            // Update survey respondent status to completed if respondent exists
            if ($surveyResponse->respondent_id) {
                $surveyRespondent = \App\Models\SurveyRespondent::where('survey_id', $surveyModel->id)
                    ->where('respondent_id', $surveyResponse->respondent_id)
                    ->first();
                    
                if ($surveyRespondent) {
                    $surveyRespondent->markAsCompleted();
                    
                    // Update completion metadata
                    $meta = $surveyRespondent->meta ?? [];
                    $meta['completed_at'] = now()->toISOString();
                    $meta['final_answers_count'] = \App\Models\Answer::where('response_id', $surveyResponse->id)->count();
                    $meta['completion_duration'] = $surveyRespondent->started_at ? 
                        now()->diffInMinutes($surveyRespondent->started_at) : null;
                    $surveyRespondent->update(['meta' => $meta]);
                }
            }

            // Calculate and save score
            $this->calculateAndSaveScore($surveyResponse, $surveyModel, $answers);

            \Illuminate\Support\Facades\DB::commit();

            // Redirect to result page
            return redirect()->route('survey.result', ['survey' => $surveyModel->code])
                ->with('success', 'Survey submitted successfully');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            
            \Illuminate\Support\Facades\Log::error('Failed to submit survey response', [
                'survey_id' => $surveyModel->id ?? null,
                'response_id' => $surveyResponse->id ?? null,
                'respondent_id' => $surveyResponse->respondent_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors([
                'message' => 'Failed to submit survey: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate and save response score (consistent with SurveyTakeController)
     */
    private function calculateAndSaveScore($response, $survey, $answers = null): void
    {
        try {
            // Load survey with all necessary relationships for scoring
            $survey->load([
                'sections.questions.choices',
                'resultCategories' => function ($query) {
                    $query->orderBy('min_score');
                }
            ]);

            // Use provided answers or load from database
            if ($answers === null) {
                $response->load('answers.choice', 'answers.question.section');
                $responseAnswers = $response->answers;
            } else {
                // Convert answers array to collection for consistent interface
                $responseAnswers = collect($answers);
            }

            $totalScore = 0;
            $maxPossibleScore = 0;
            $sectionScores = [];

            // Calculate score for each section
            foreach ($survey->sections as $section) {
                $sectionScore = 0;
                $sectionMaxScore = 0;

                foreach ($section->questions as $index => $question) {
                    $questionWeight = $question->score_weight ?? 1;
                    $questionMaxScore = 0;
                    $questionScore = 0;

                    switch ($question->type) {
                        case QuestionType::SINGLE_CHOICE:
                            // Single Choice: Hanya satu pilihan
                            $answer = $responseAnswers->where('question_id', $question->id)->first();
                            
                            if ($answer) {
                                $choiceId = is_array($answer) ? ($answer['choice_id'] ?? null) : $answer->choice_id;
                                if ($choiceId) {
                                    $choice = $question->choices->where('id', $choiceId)->first();
                                    if ($choice) {
                                        $questionScore = $choice->score * $questionWeight;
                                    }
                                }
                            }
                            $questionMaxScore = $question->choices->max('score') * $questionWeight;
                            break;
                            
                        case QuestionType::MULTIPLE_CHOICE:
                            // Multiple Choice: Bisa beberapa pilihan
                            $answers = $responseAnswers->where('question_id', $question->id);
                            
                            foreach ($answers as $answer) {
                                $choiceId = is_array($answer) ? ($answer['choice_id'] ?? null) : $answer->choice_id;
                                if ($choiceId) {
                                    $choice = $question->choices->where('id', $choiceId)->first();
                                    if ($choice) {
                                        $questionScore += $choice->score * $questionWeight;
                                    }
                                }
                            }
                            
                            // Untuk multiple choice, max score bisa berupa:
                            // Option 1: Jumlah semua pilihan (jika semua boleh dipilih)
                            $questionMaxScore = $question->choices->sum('score') * $questionWeight;
                            
                            // Option 2: Pilihan dengan skor tertinggi (jika hanya pilihan terbaik yang dihitung)
                            // $questionMaxScore = $question->choices->max('score') * $questionWeight;
                            break;
                            
                        case QuestionType::NUMBER:
                            // Numeric questions
                            $answer = $responseAnswers->where('question_id', $question->id)->first();
                            if ($answer) {
                                $valueNumber = is_array($answer) ? ($answer['value_number'] ?? null) : $answer->value_number;
                                if ($valueNumber !== null) {
                                    $questionScore = $valueNumber * $questionWeight;
                                }
                            }
                            $questionMaxScore = $questionWeight; // Atau nilai maksimum yang diharapkan
                            break;
                            
                        default:
                            // Handle other question types (SHORT_TEXT, LONG_TEXT, DATE)
                            if ($question->choices->isNotEmpty()) {
                                $questionMaxScore = $question->choices->max('score') * $questionWeight;
                            } else {
                                $questionMaxScore = $questionWeight;
                            }
                            break;
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
            \App\Models\ResponseScore::updateOrCreate(
                ['response_id' => $response->id],
                [
                    'result_category_id' => $resultCategory?->id,
                    'total_score' => round($totalScore, 2),
                    'max_possible_score' => round($maxPossibleScore, 2),
                    'percentage' => round($percentage, 2),
                    'section_scores' => $sectionScores,
                ]
            );
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to calculate and save score', [
                'response_id' => $response->id,
                'survey_id' => $survey->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw the exception to prevent breaking the main flow
            // Just log the error and continue
        }
    }
}