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
use App\Models\Respondent;
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
            Log::error('Failed to show respondent data page: ' . $e->getMessage());
            
            return redirect('/entry')->withErrors([
                'survey_code' => 'Terjadi kesalahan saat memuat halaman. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Store respondent data and update response
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function respondentData(Request $request)
    {
        try {
            // Get survey and response from middleware
            $response = $request->response;

            // Validate request data
            $validator = Validator::make($request->all(), [
                'external_id' => 'nullable|string|max:255',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'gender' => 'nullable|in:male,female,other',
                'birth_year' => 'nullable|integer|min:1900|max:' . date('Y'),
                'organization' => 'nullable|string|max:255',
                'department' => 'nullable|string|max:255',
                'role_title' => 'nullable|string|max:255',
                'location' => 'nullable|string|max:255',
                'demographics' => 'nullable|array',
                'consent' => 'required|boolean|accepted',
                'consent_at' => 'nullable|date'
            ], [
                'name.required' => 'Nama wajib diisi.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal 255 karakter.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 255 karakter.',
                'phone.max' => 'Nomor telepon maksimal 20 karakter.',
                'gender.in' => 'Jenis kelamin harus salah satu dari: male, female, other.',
                'birth_year.integer' => 'Tahun lahir harus berupa angka.',
                'birth_year.min' => 'Tahun lahir minimal 1900.',
                'birth_year.max' => 'Tahun lahir tidak boleh lebih dari tahun sekarang.',
                'consent.required' => 'Persetujuan wajib diberikan.',
                'consent.accepted' => 'Anda harus menyetujui untuk melanjutkan.'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Check if respondent already exists for this response
            if (!empty($response->respondent_id)) {
                // Update existing respondent
                $respondent = $response->respondent;
                $respondent->update([
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
                    'demographics' => $request->demographics,
                    'consent_at' => $request->consent_at ? now() : null,
                ]);
            } else {
                // Create new respondent
                $respondent = Respondent::create([
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
                    'demographics' => $request->demographics,
                    'consent_at' => $request->consent ? now() : null,
                ]);

                // Link respondent to response
                $response->update([
                    'respondent_id' => $respondent->id
                ]);
            }

            // Update response to next step
            $response->update([
                'current_step' => Response::STEP_QUESTIONS,
                'status' => ResponseStatus::IN_PROGRESS
            ]);

            // Update session
            session([
                'current_step' => Response::STEP_QUESTIONS
            ]);

            // Redirect to questions page
            return redirect('/survey/questions');

        } catch (Exception $e) {
            dd($e);
            Log::error('Failed to save respondent data: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'general' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.'
            ])->withInput();
        }
    }

    /**
     * Show survey questions page
     * 
     * @param Request $request
     * @return InertiaResponse|\Illuminate\Http\RedirectResponse
     */
    public function showQuestions(Request $request)
    {
        try {
            // Get survey data from session
            $surveyId = session('survey_id');
            $surveyCode = session('survey_code');
            $responseId = session('response_id');


            // Get survey with sections and questions
            $survey = Survey::with([
                'sections' => function($query) {
                    $query->orderBy('order');
                },
                'sections.questions' => function($query) {
                    $query->orderBy('order');
                },
                'sections.questions.choices' => function($query) {
                    $query->orderBy('order');
                }
            ])->where('id', $surveyId)
              ->where('code', $surveyCode)
              ->first();

            if (!$survey) {
                return redirect('/entry')->withErrors([
                    'general' => 'Survey tidak ditemukan atau tidak aktif.'
                ]);
            }

            // Get existing response with answers
            $response = Response::with([
                'answers.question',
                'answers.choice'
            ])->find($responseId);

            // Format existing answers for frontend
            $existingAnswers = [];
            if ($response && $response->answers) {
                foreach ($response->answers as $answer) {
                    $existingAnswers[$answer->question_id] = [
                        'choice_id' => $answer->choice_id,
                        'value_text' => $answer->value_text,
                        'value_number' => $answer->value_number,
                        'value_json' => $answer->value_json
                    ];
                }
            }

            $existingResponse = null;
            if ($response) {
                $existingResponse = [
                    'id' => $response->id,
                    'answers' => $existingAnswers
                ];
            }

            return Inertia::render('Survey/Questions', [
                'surveyCode' => $surveyCode,
                'surveyData' => $survey,
                'existingResponse' => $existingResponse
            ]);

        } catch (Exception $e) {
            Log::error('Failed to load survey questions: ' . $e->getMessage());
            
            return redirect('/entry')->withErrors([
                'general' => 'Terjadi kesalahan saat memuat pertanyaan survey.'
            ]);
        }
    }
}
