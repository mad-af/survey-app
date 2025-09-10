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
use Barryvdh\DomPDF\Facade\Pdf;

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
            // Load survey with sections, questions, and choices for complete structure
            $survey->load([
                'sections' => function($query) {
                    $query->orderBy('order');
                },
                'sections.questions' => function($query) {
                    $query->orderBy('order');
                },
                'sections.questions.choices' => function($query) {
                    $query->orderBy('order');
                }
            ]);
            
            // Get all responses with related data (answers will be loaded on demand)
            $responses = Response::with([
                'respondent:id,external_id,name,email,phone,gender,birth_year,organization,department,role_title,location_id,consent_at',
                'respondent.location:id,province_code,province_name,regency_code,regency_name,district_code,district_name,village_code,village_name,detailed_address,latitude,longitude',
                'score.resultCategory:id,name,color,min_score,max_score'
            ])
            ->where('survey_id', $survey->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
            // Load answers only for responses that need them (completed responses)
            $responseIds = $responses->where('status', 'completed')->pluck('id');
            if ($responseIds->isNotEmpty()) {
                $responses->load([
                    'answers' => function($query) use ($responseIds) {
                        $query->whereIn('response_id', $responseIds)
                              ->select('id', 'response_id', 'question_id', 'choice_id', 'value_text', 'value_number', 'value_json');
                    }
                ]);
            }

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
     * Get response details with answers for a specific response
     */
    public function getResponseDetails(Survey $survey, Response $response)
    {
        try {
            // Verify response belongs to survey
            if ($response->survey_id !== $survey->id) {
                return response()->json(['error' => 'Response not found'], 404);
            }

            // Load response with answers
            $response->load([
                'answers:id,response_id,question_id,choice_id,value_text,value_number,value_json',
                'respondent:id,external_id,name,email,phone,gender,birth_year,organization,department,role_title,location_id,consent_at',
                'respondent.location:id,province_code,province_name,regency_code,regency_name,district_code,district_name,village_code,village_name,detailed_address,latitude,longitude',
                'score.resultCategory:id,name,color,min_score,max_score'
            ]);

            return response()->json([
                'response' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load response details: ' . $e->getMessage()], 500);
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
            
            // Get total responses count
            $totalResponses = Response::count();

            return [
                'publicSurveys' => $publicSurveys,
                'totalRespondents' => $totalRespondents,
                'totalResponses' => $totalResponses
            ];
        } catch (\Exception $e) {
            return [
                'publicSurveys' => collect([]),
                'totalRespondents' => 0,
                'totalResponses' => 0
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
            'totalRespondents' => $data['totalRespondents'],
            'totalResponses' => $data['totalResponses']
        ]);
    }

    /**
     * Export survey responses to CSV or PDF
     */
    public function exportResponses(Survey $survey, Request $request)
    {
        try {
            // Get export type and format from request
            $exportType = $request->get('type', 'all');
            $format = $request->get('format', 'excel'); // excel or pdf
            $statusFilter = $request->get('status_filter');
            $searchQuery = $request->get('search_query');
            
            // Build query for responses
            $query = Response::with([
                'respondent:id,external_id,name,email,phone,gender,birth_year,organization,department,role_title,location_id,consent_at',
                'respondent.location:id,province_code,province_name,regency_code,regency_name,district_code,district_name,village_code,village_name,detailed_address,latitude,longitude',
                'score.resultCategory:id,name,color,min_score,max_score'
            ])->where('survey_id', $survey->id);
            
            // Apply filters based on export type
            switch ($exportType) {
                case 'completed':
                    $query->where('status', 'completed');
                    break;
                case 'filtered':
                    if ($statusFilter) {
                        $query->where('status', $statusFilter);
                    }
                    if ($searchQuery) {
                        $query->whereHas('respondent', function ($q) use ($searchQuery) {
                            $q->where('name', 'like', '%' . $searchQuery . '%')
                              ->orWhere('email', 'like', '%' . $searchQuery . '%')
                              ->orWhere('organization', 'like', '%' . $searchQuery . '%')
                              ->orWhere('department', 'like', '%' . $searchQuery . '%');
                        })->orWhere('respondent_token', 'like', '%' . $searchQuery . '%');
                    }
                    break;
                default: // 'all'
                    // No additional filters
                    break;
            }
            
            $responses = $query->orderBy('created_at', 'desc')->get();
            
            if ($responses->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data to export'
                ], 400);
            }
            
            // Generate content based on format
            if ($format === 'pdf') {
                return $this->generatePDFExport($survey, $responses, $exportType);
            } else {
                // Default to CSV/Excel format
                $csvContent = $this->generateCSVContent($responses);
                $filename = 'survey-responses-' . $exportType . '-' . date('Y-m-d') . '.csv';
                
                return response($csvContent)
                    ->header('Content-Type', 'text/csv')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
            }
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export responses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate CSV content from responses data
     */
    private function generateCSVContent($responses)
    {
        // Define CSV headers
        $headers = [
            'Response ID',
            'Survey ID',
            'Respondent Token',
            'Status',
            'Current Step',
            'Started At',
            'Submitted At',
            'Respondent Name',
            'Respondent Email',
            'Respondent Phone',
            'Respondent Gender',
            'Respondent Birth Year',
            'Respondent Organization',
            'Respondent Department',
            'Respondent Role Title',
            'Respondent External ID',
            'Province',
            'Regency',
            'District',
            'Village',
            'Detailed Address',
            'Latitude',
            'Longitude',
            'Total Score',
            'Max Possible Score',
            'Score Percentage',
            'Result Category',
            'Duration (minutes)'
        ];
        
        // Generate CSV rows
        $rows = [];
        foreach ($responses as $response) {
            $respondent = $response->respondent;
            $location = $respondent ? $respondent->location : null;
            $score = $response->score;
            $resultCategory = $score ? $score->resultCategory : null;
            
            // Calculate duration
            $duration = '';
            if ($response->started_at && $response->submitted_at) {
                $start = Carbon::parse($response->started_at);
                $end = Carbon::parse($response->submitted_at);
                $duration = $start->diffInMinutes($end);
            }
            
            $rows[] = [
                $response->id ?? '',
                $response->survey_id ?? '',
                $response->respondent_token ?? '',
                $this->getStatusLabel($response->status) ?? '',
                $this->getStepLabel($response->current_step) ?? '',
                $response->started_at ? $response->started_at->format('Y-m-d H:i:s') : '',
                $response->submitted_at ? $response->submitted_at->format('Y-m-d H:i:s') : '',
                $respondent->name ?? '',
                $respondent->email ?? '',
                $respondent->phone ?? '',
                $this->getGenderLabel($respondent->gender ?? null) ?? '',
                $respondent->birth_year ?? '',
                $respondent->organization ?? '',
                $respondent->department ?? '',
                $respondent->role_title ?? '',
                $respondent->external_id ?? '',
                $location->province_name ?? '',
                $location->regency_name ?? '',
                $location->district_name ?? '',
                $location->village_name ?? '',
                $location->detailed_address ?? '',
                $location->latitude ?? '',
                $location->longitude ?? '',
                $score->total_score ?? '',
                $score->max_possible_score ?? '',
                $score->percentage ?? '',
                $resultCategory->name ?? '',
                $duration
            ];
        }
        
        // Combine headers and rows
        $csvArray = array_merge([$headers], $rows);
        
        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csvArray as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }
    
    /**
     * Generate PDF export from responses data
     */
    private function generatePDFExport($survey, $responses, $exportType)
    {
        // Create HTML content for PDF
        $html = $this->generatePDFHTML($survey, $responses, $exportType);
        
        $filename = 'survey-responses-' . $exportType . '-' . date('Y-m-d') . '.pdf';
        
        // Generate PDF using dompdf
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($filename);
    }
    
    /**
     * Generate HTML content for PDF export
     */
    private function generatePDFHTML($survey, $responses, $exportType)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Survey Responses Export</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .survey-info { margin-bottom: 15px; padding: 10px; background-color: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; font-size: 8px; }
        th { background-color: #e0e0e0; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Survey Responses Export</h1>
        <h2>' . htmlspecialchars($survey->title) . '</h2>
    </div>
    
    <div class="survey-info">
        <p><strong>Survey Code:</strong> ' . htmlspecialchars($survey->code) . '</p>
        <p><strong>Export Type:</strong> ' . ucfirst($exportType) . '</p>
        <p><strong>Export Date:</strong> ' . date('Y-m-d H:i:s') . '</p>
        <p><strong>Total Responses:</strong> ' . count($responses) . '</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Status</th>
                <th>Name</th>
                <th>Email</th>
                <th>Organization</th>
                <th>Department</th>
                <th>Province</th>
                <th>Regency</th>
                <th>Started</th>
                <th>Submitted</th>
                <th>Score</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>';
        
        foreach ($responses as $response) {
            $respondent = $response->respondent;
            $location = $respondent ? $respondent->location : null;
            $score = $response->score;
            $resultCategory = $score ? $score->resultCategory : null;
            
            $html .= '<tr>
                <td>' . htmlspecialchars($response->id ?? '') . '</td>
                <td>' . htmlspecialchars($this->getStatusLabel($response->status)) . '</td>
                <td>' . htmlspecialchars($respondent->name ?? '') . '</td>
                <td>' . htmlspecialchars($respondent->email ?? '') . '</td>
                <td>' . htmlspecialchars($respondent->organization ?? '') . '</td>
                <td>' . htmlspecialchars($respondent->department ?? '') . '</td>
                <td>' . htmlspecialchars($location->province_name ?? '') . '</td>
                <td>' . htmlspecialchars($location->regency_name ?? '') . '</td>
                <td>' . ($response->started_at ? $response->started_at->format('Y-m-d H:i') : '') . '</td>
                <td>' . ($response->submitted_at ? $response->submitted_at->format('Y-m-d H:i') : '') . '</td>
                <td>' . htmlspecialchars($score->total_score ?? '') . '</td>
                <td>' . htmlspecialchars($resultCategory->name ?? '') . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
    </table>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Helper method to get status label
     */
    private function getStatusLabel($status)
    {
        switch ($status) {
            case 'completed':
                return 'Completed';
            case 'in_progress':
                return 'In Progress';
            case 'started':
                return 'Started';
            case 'abandoned':
                return 'Abandoned';
            default:
                return 'Unknown';
        }
    }
    
    /**
     * Helper method to get step label
     */
    private function getStepLabel($step)
    {
        switch ($step) {
            case 'personal_info':
                return 'Personal Info';
            case 'survey_questions':
                return 'Survey Questions';
            case 'completed':
                return 'Completed';
            default:
                return ucfirst(str_replace('_', ' ', $step));
        }
    }
    
    /**
     * Helper method to get gender label
     */
    private function getGenderLabel($gender)
    {
        switch ($gender) {
            case 'male':
                return 'Male';
            case 'female':
                return 'Female';
            case 'other':
                return 'Other';
            default:
                return '';
        }
    }

}