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
     * Display survey responses page
     */
    public function showResponses(Survey $survey)
    {
        try {
            // Load survey with sections for section title mapping
            $survey->load('sections');
            
            // Get all responses with related data
            $responses = Response::with([
                'respondent:id,external_id,name,email,phone,gender,birth_year,organization,department,role_title,location_id,consent_at',
                'respondent.location:id,province_code,province_name,regency_code,regency_name,district_code,district_name,village_code,village_name,detailed_address,latitude,longitude',
                'score.resultCategory:id,name,description,color,min_score,max_score'
            ])
            ->where('survey_id', $survey->id)
            ->orderBy('created_at', 'desc')
            ->get();

            // Calculate statistics
            $totalResponses = $responses->count();
            $completedResponses = $responses->where('status', 'completed')->count();
            $inProgressResponses = $responses->where('status', 'in_progress')->count();
            $startedResponses = $responses->where('status', 'started')->count();
            $abandonedResponses = $responses->where('status', 'abandoned')->count();

            return Inertia::render('Dashboard/Survey/Response', [
                'survey' => $survey,
                'responses' => $responses,
                'statistics' => [
                    'total' => $totalResponses,
                    'completed' => $completedResponses,
                    'in_progress' => $inProgressResponses,
                    'started' => $startedResponses,
                    'abandoned' => $abandonedResponses,
                    'completion_rate' => $totalResponses > 0 ? round(($completedResponses / $totalResponses) * 100, 2) : 0
                ]
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Failed to load survey responses: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display dashboard with statistics
     */
    public function dashboard()
    {
        try {
            // Get statistics
            $totalSurveys = Survey::count();
            $activeSurveys = Survey::where('status', SurveyStatus::ACTIVE)->count();
            $draftSurveys = Survey::where('status', SurveyStatus::DRAFT)->count();
            $closedSurveys = Survey::where('status', SurveyStatus::CLOSED)->count();
            
            $totalResponses = Response::count();
            $completedResponses = Response::whereNotNull('submitted_at')->count();
            $totalRespondents = Response::distinct('respondent_id')->count('respondent_id');
            
            // Calculate new respondents this month
            $newRespondentsThisMonth = Response::whereMonth('created_at', Carbon::now()->month)
                                             ->whereYear('created_at', Carbon::now()->year)
                                             ->distinct('respondent_id')
                                             ->count('respondent_id');
            
            // Calculate completion rate
            $completionRate = $totalResponses > 0 ? round(($completedResponses / $totalResponses) * 100) : 0;
            
            // Get recent surveys
            $recentSurveys = Survey::with('owner:id,name')
                                  ->withCount('responses')
                                  ->select('id', 'owner_id', 'code', 'title', 'description', 'status', 'created_at')
                                  ->orderBy('created_at', 'desc')
                                  ->limit(4)
                                  ->get();
            
            // Get recent activities (sample data for now)
            $recentActivities = collect([
                [
                    'id' => 1,
                    'type' => 'survey_created',
                    'title' => 'Survey Baru Dibuat',
                    'description' => 'Survey baru telah dibuat',
                    'created_at' => Carbon::now()->subHours(2)->toISOString()
                ],
                [
                    'id' => 2,
                    'type' => 'response_received',
                    'title' => 'Respons Baru Diterima',
                    'description' => 'Respons baru diterima untuk survey',
                    'created_at' => Carbon::now()->subHours(4)->toISOString()
                ],
                [
                    'id' => 3,
                    'type' => 'user_registered',
                    'title' => 'Responden Baru',
                    'description' => 'Responden baru terdaftar',
                    'created_at' => Carbon::now()->subHours(6)->toISOString()
                ],
                [
                    'id' => 4,
                    'type' => 'survey_completed',
                    'title' => 'Survey Selesai',
                    'description' => 'Survey telah ditutup',
                    'created_at' => Carbon::now()->subDay()->toISOString()
                ]
            ]);
            
            $statistics = [
                'totalSurveys' => $totalSurveys,
                'activeSurveys' => $activeSurveys,
                'draftSurveys' => $draftSurveys,
                'closedSurveys' => $closedSurveys,
                'totalResponses' => $totalResponses,
                'completedResponses' => $completedResponses,
                'totalRespondents' => $totalRespondents,
                'newRespondentsThisMonth' => $newRespondentsThisMonth,
                'completionRate' => $completionRate
            ];
            
            return Inertia::render('Dashboard/Index', [
                'statistics' => $statistics,
                'recentSurveys' => $recentSurveys,
                'recentActivities' => $recentActivities
            ]);
            
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Failed to load dashboard: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get public surveys for welcome page
     */
    public function getPublicSurveys()
    {
        try {
            $publicSurveys = Survey::where('status', SurveyStatus::ACTIVE)
                                  ->where('visibility', SurveyVisibility::PUBLIC)
                                  ->where(function ($query) {
                                      $query->whereNull('starts_at')
                                            ->orWhere('starts_at', '<=', now());
                                  })
                                  ->where(function ($query) {
                                      $query->whereNull('ends_at')
                                            ->orWhere('ends_at', '>=', now());
                                  })
                                  ->withCount('responses')
                                  ->select('id', 'code', 'title', 'description', 'created_at')
                                  ->orderBy('created_at', 'desc')
                                  ->limit(6)
                                  ->get()
                                  ->map(function ($survey) {
                                      return [
                                          'id' => $survey->id,
                                          'code' => $survey->code,
                                          'title' => $survey->title,
                                          'description' => $survey->description,
                                          'estimated_duration' => '5-10',
                                          'responses_count' => $survey->responses_count
                                      ];
                                  });

            // Get total respondents count
            $totalRespondents = Response::distinct('respondent_id')->count('respondent_id');

            return [
                'publicSurveys' => $publicSurveys,
                'totalRespondents' => $totalRespondents
            ];
        } catch (\Exception $e) {
            return [
                'publicSurveys' => collect([]),
                'totalRespondents' => 0
            ];
        }
    }

    /**
     * Display welcome page with public surveys
     */
    public function welcome()
    {
        $data = $this->getPublicSurveys();
        
        return Inertia::render('Welcome', [
            'publicSurveys' => $data['publicSurveys'],
            'totalRespondents' => $data['totalRespondents']
        ]);
    }

}