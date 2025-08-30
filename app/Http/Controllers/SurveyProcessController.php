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
     * Enter survey with survey code
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enter(Request $request)
    {
        // Validate survey code
        $validator = Validator::make($request->all(), [
            'survey_code' => 'required|string|max:255'
        ], [
            'survey_code.required' => 'Kode survey wajib diisi.',
            'survey_code.string' => 'Kode survey harus berupa teks.',
            'survey_code.max' => 'Kode survey maksimal 255 karakter.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // Find survey by code
            $survey = Survey::where('code', $request->survey_code)
                          ->where('status', 'active')
                          ->first();

            if (!$survey) {
                return redirect()->back()->withErrors([
                    'survey_code' => 'Kode survey tidak ditemukan atau survey tidak aktif.'
                ])->withInput();
            }

            // Check survey date range
            $now = now();
            if ($survey->starts_at && $now->lt($survey->starts_at)) {
                return redirect()->back()->withErrors([
                    'survey_code' => 'Survey belum dimulai.'
                ])->withInput();
            }

            if ($survey->ends_at && $now->gt($survey->ends_at)) {
                return redirect()->back()->withErrors([
                    'survey_code' => 'Survey sudah berakhir.'
                ])->withInput();
            }
            
            // Generate unique token for this response
            $token = bin2hex(random_bytes(32));

            // Create new response record
            $response = Response::create([
                'survey_id' => $survey->id,
                'respondent_id' => null, // Will be filled later in respondent step
                'respondent_token' => $token,
                'started_at' => now(),
                'current_step' => Response::STEP_RESPONDENT_DATA,
                'status' => ResponseStatus::STARTED,
                'meta' => []
            ]);

            // Store survey session data
            session([
                'survey_token' => $token,
                'survey_id' => $survey->id,
                'survey_code' => $survey->code,
                'response_id' => $response->id,
                'current_step' => $response->current_step,
            ]);

            // Redirect to respondent data collection page
            return redirect('/survey/respondent-data');

        } catch (Exception $e) {
            Log::error('Failed to enter survey: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'survey_code' => 'Terjadi kesalahan saat memproses kode survey. Silakan coba lagi.'
            ])->withInput();
        }
     }

    /**
     * Show respondent data form page
     * 
     * @param Request $request
     * @return InertiaResponse|\Illuminate\Http\RedirectResponse
     */
    public function showRespondentData(Request $request)
    {
        try {
            // Get survey and response from middleware
            $survey = $request->survey;
            $response = $request->response;
            $surveyCode = $request->survey_code;

            // Get existing respondent data if available
            $existingRespondent = null;
            if ($response->respondent_id) {
                $existingRespondent = $response->respondent;
            }

            return Inertia::render('Survey/RespondentData', [
                'survey' => [
                    'id' => $survey->id,
                    'code' => $survey->code,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'is_anonymous' => $survey->is_anonymous
                ],
                'surveyCode' => $surveyCode,
                'existingRespondent' => $existingRespondent ? [
                    'id' => $existingRespondent->id,
                    'external_id' => $existingRespondent->external_id,
                    'name' => $existingRespondent->name,
                    'email' => $existingRespondent->email,
                    'phone' => $existingRespondent->phone,
                    'gender' => $existingRespondent->gender,
                    'birth_year' => $existingRespondent->birth_year,
                    'organization' => $existingRespondent->organization,
                    'department' => $existingRespondent->department,
                    'role_title' => $existingRespondent->role_title,
                    'location' => $existingRespondent->location,
                    'demographics' => $existingRespondent->demographics,
                    'consent' => $existingRespondent->consent,
                    'consent_at' => $existingRespondent->consent_at
                ] : null
            ]);

        } catch (Exception $e) {
            dd($e);
            Log::error('Failed to show respondent data page: ' . $e->getMessage());
            
            return redirect('/entry')->withErrors([
                'survey_code' => 'Terjadi kesalahan saat memuat halaman. Silakan coba lagi.'
            ]);
        }
    }
}
