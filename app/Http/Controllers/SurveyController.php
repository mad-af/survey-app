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
                'respondent:id,external_id,name,email,phone,gender,birth_year,organization,department,role_title,location,demographics,consent_at',
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
}