<?php

namespace App\Http\Controllers;

use App\Enums\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Survey;
use App\Models\Response;
use App\Models\Answer;
use App\Models\Respondent;
use App\Models\Choice;
use App\Models\ResponseScore;
use App\Models\ResultCategory;
use App\Models\Location;
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
    public function submitRespondentData(Request $request)
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
                // Location fields
                'province_code' => 'required|string',
                'province_name' => 'required|string|max:255',
                'regency_code' => 'required|string',
                'regency_name' => 'required|string|max:255',
                'district_code' => 'required|string',
                'district_name' => 'required|string|max:255',
                'village_code' => 'required|string',
                'village_name' => 'required|string|max:255',
                'detailed_address' => 'nullable|string|max:500',
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
                'province_code.required' => 'Provinsi wajib dipilih.',
                'province_name.required' => 'Nama provinsi wajib diisi.',
                'regency_code.required' => 'Kabupaten/Kota wajib dipilih.',
                'regency_name.required' => 'Nama kabupaten/kota wajib diisi.',
                'district_code.required' => 'Kecamatan wajib dipilih.',
                'district_name.required' => 'Nama kecamatan wajib diisi.',
                'village_code.required' => 'Kelurahan/Desa wajib dipilih.',
                'village_name.required' => 'Nama kelurahan/desa wajib diisi.',
                'consent.required' => 'Persetujuan wajib diberikan.',
                'consent.accepted' => 'Anda harus menyetujui untuk melanjutkan.'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Handle location data - extract coordinates from demographics if available
            $latitude = null;
            $longitude = null;
            if ($request->demographics && isset($request->demographics['geolocation'])) {
                $latitude = $request->demographics['geolocation']['latitude'] ?? null;
                $longitude = $request->demographics['geolocation']['longitude'] ?? null;
            }

            // Create or update location record
            $location = Location::create([
                'province_code' => $request->province_code,
                'province_name' => $request->province_name,
                'regency_code' => $request->regency_code,
                'regency_name' => $request->regency_name,
                'district_code' => $request->district_code,
                'district_name' => $request->district_name,
                'village_code' => $request->village_code,
                'village_name' => $request->village_name,
                'detailed_address' => $request->detailed_address,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);

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
                    'location_id' => $location->id,
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
                    'location_id' => $location->id,
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

    /**
     * Submit survey questions answers (Partial Save)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitQuestionPartials(Request $request)
    {

        try {
            // Get survey data from session
            $surveyId = session('survey_id');
            $responseId = session('response_id');

            // Validate request data
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.choice_id' => 'nullable|integer|exists:choices,id',
                'answers.*.value_text' => 'nullable|string|max:65535',
                'answers.*.value_number' => 'nullable|numeric',
                'answers.*.value_json' => 'nullable|string',
                'section_id' => 'nullable|integer|exists:survey_sections,id',
                'meta' => 'nullable|array'
            ], [
                'answers.required' => 'Data jawaban harus diisi.',
                'answers.array' => 'Format data jawaban tidak valid.',
                'answers.*.question_id.required' => 'ID pertanyaan harus diisi.',
                'answers.*.question_id.exists' => 'Pertanyaan tidak ditemukan.',
                'answers.*.choice_id.exists' => 'Pilihan jawaban tidak ditemukan.',
                'answers.*.value_text.max' => 'Teks jawaban terlalu panjang.',
                'answers.*.value_number.numeric' => 'Nilai harus berupa angka.',
                'section_id.exists' => 'Bagian survey tidak ditemukan.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get response record
            $response = Response::find($responseId);
            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Response survey tidak ditemukan.'
                ], 404);
            }

            // Verify survey ownership
            if ($response->survey_id != $surveyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Response tidak sesuai dengan survey.'
                ], 403);
            }

            DB::beginTransaction();

            // Process and save answers
            $answersData = $request->input('answers', []);
            $meta = $request->input('meta', []);

            foreach ($answersData as $answerData) {
                // Check if answer already exists
                $existingAnswer = Answer::where('response_id', $responseId)
                                       ->where('question_id', $answerData['question_id'])
                                       ->first();

                $answerPayload = [
                    'response_id' => $responseId,
                    'question_id' => $answerData['question_id'],
                    'choice_id' => $answerData['choice_id'] ?? null,
                    'value_text' => $answerData['value_text'] ?? null,
                    'value_number' => $answerData['value_number'] ?? null,
                    'value_json' => $answerData['value_json'] ?? null,
                    'updated_at' => now()
                ];

                if ($existingAnswer) {
                    // Update existing answer
                    $existingAnswer->update($answerPayload);
                } else {
                    // Create new answer
                    $answerPayload['created_at'] = now();
                    Answer::create($answerPayload);
                }
            }

            // Update response status and metadata for partial save
            $responseUpdateData = [
                'status' => ResponseStatus::IN_PROGRESS,
                'updated_at' => now()
            ];

            // Update meta information
            if (!empty($meta)) {
                $existingMeta = $response->meta ? json_decode($response->meta, true) : [];
                $updatedMeta = array_merge($existingMeta, $meta);
                $responseUpdateData['meta'] = json_encode($updatedMeta);
            }

            $response->update($responseUpdateData);

            DB::commit();

            // Return partial save response
            return response()->json([
                'success' => true,
                'message' => 'Jawaban berhasil disimpan.',
                'responseId' => $responseId,
                'is_partial' => true
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to save partial survey answers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan jawaban. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Submit survey questions answers (Final Submission)
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function submitQuestions(Request $request)
    {
        try {
            // Get survey data from session
            $surveyId = session('survey_id');
            $responseId = session('response_id');

            // Validate request data
            $validator = Validator::make($request->all(), [
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.choice_id' => 'nullable|integer|exists:choices,id',
                'answers.*.value_text' => 'nullable|string|max:65535',
                'answers.*.value_number' => 'nullable|numeric',
                'answers.*.value_json' => 'nullable|string',
                'section_id' => 'nullable|integer|exists:survey_sections,id',
                'meta' => 'nullable|array'
            ], [
                'answers.required' => 'Data jawaban harus diisi.',
                'answers.array' => 'Format data jawaban tidak valid.',
                'answers.*.question_id.required' => 'ID pertanyaan harus diisi.',
                'answers.*.question_id.exists' => 'Pertanyaan tidak ditemukan.',
                'answers.*.choice_id.exists' => 'Pilihan jawaban tidak ditemukan.',
                'answers.*.value_text.max' => 'Teks jawaban terlalu panjang.',
                'answers.*.value_number.numeric' => 'Nilai harus berupa angka.',
                'section_id.exists' => 'Bagian survey tidak ditemukan.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get response record
            $response = Response::find($responseId);
            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Response survey tidak ditemukan.'
                ], 404);
            }

            // Verify survey ownership
            if ($response->survey_id != $surveyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Response tidak sesuai dengan survey.'
                ], 403);
            }

            DB::beginTransaction();

            // Process and save answers
            $answersData = $request->input('answers', []);
            $meta = $request->input('meta', []);

            foreach ($answersData as $answerData) {
                // Check if answer already exists
                $existingAnswer = Answer::where('response_id', $responseId)
                                       ->where('question_id', $answerData['question_id'])
                                       ->first();

                $answerPayload = [
                    'response_id' => $responseId,
                    'question_id' => $answerData['question_id'],
                    'choice_id' => $answerData['choice_id'] ?? null,
                    'value_text' => $answerData['value_text'] ?? null,
                    'value_number' => $answerData['value_number'] ?? null,
                    'value_json' => $answerData['value_json'] ?? null,
                    'updated_at' => now()
                ];

                if ($existingAnswer) {
                    // Update existing answer
                    $existingAnswer->update($answerPayload);
                } else {
                    // Create new answer
                    $answerPayload['created_at'] = now();
                    Answer::create($answerPayload);
                }
            }

            // Update response status and metadata for final submission
            $responseUpdateData = [
                'status' => ResponseStatus::COMPLETED,
                'submitted_at' => now(),
                'current_step' => Response::STEP_RESULT,
                'updated_at' => now()
            ];

            // Update meta information
            if (!empty($meta)) {
                $existingMeta = $response->meta ? json_decode($response->meta, true) : [];
                $updatedMeta = array_merge($existingMeta, $meta);
                $responseUpdateData['meta'] = json_encode($updatedMeta);
            }

            $response->update($responseUpdateData);
            
            // Calculate and save score for completed response
            $this->calculateScore($response);

            // Update session
            session([
                'current_step' => Response::STEP_RESULT
            ]);

            DB::commit();

            // Clear survey session data
            session()->forget([
                'survey_token',
                'survey_id', 
                'survey_code',
                'response_id',
                'current_step'
            ]);

            // Final submission - redirect to result page
            return redirect("/survey/result?token={$response->respondent_token}");

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit survey answers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan jawaban. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Calculate score for a response based on question types
     */
    private function calculateScore(Response $response): void
    {
        try {
            $totalScore = 0;
            $maxPossibleScore = 0;
            $sectionScores = [];
            
            // Get all answers for this response with related question, choice, and section data
            $answers = Answer::where('response_id', $response->id)
                ->with(['question.section', 'question.choices', 'choice'])
                ->get();

            // Group answers by section for section-wise scoring
            $answersBySection = $answers->groupBy('question.section_id');

            foreach ($answersBySection as $sectionId => $sectionAnswers) {
                $sectionScore = 0;
                $sectionMaxScore = 0;

                foreach ($sectionAnswers as $answer) {
                    $question = $answer->question;
                    $questionType = $question->type;
                    $scoreWeight = $question->score_weight ?? 1;
                    $questionScore = 0;
                    $questionMaxScore = 0;

                    // Calculate score based on question type
                    switch ($questionType) {
                        case QuestionType::SINGLE_CHOICE:
                            if ($answer->choice) {
                                $questionScore = ($answer->choice->score ?? 0) * $scoreWeight;
                            }
                            // Max score is the highest choice score for this question
                            $questionMaxScore = ($question->choices->max('score') ?? 0) * $scoreWeight;
                            break;

                        case QuestionType::MULTIPLE_CHOICE:
                            if ($answer->value_json && is_array($answer->value_json)) {
                                foreach ($answer->value_json as $choiceId) {
                                    $choice = Choice::find($choiceId);
                                    if ($choice) {
                                        $questionScore += ($choice->score ?? 0) * $scoreWeight;
                                    }
                                }
                            }
                            // Max score is sum of all choice scores for this question
                            $questionMaxScore = ($question->choices->sum('score') ?? 0) * $scoreWeight;
                            break;

                        case QuestionType::NUMBER:
                            $questionScore = ($answer->value_number ?? 0) * $scoreWeight;
                            // For number type, assume max score is 100 or define based on your requirements
                            $questionMaxScore = 100 * $scoreWeight;
                            break;

                        case QuestionType::SHORT_TEXT:
                        case QuestionType::LONG_TEXT:
                            // For text types, implement custom scoring logic if needed
                            $questionScore = 0;
                            $questionMaxScore = 0; // No scoring for text questions by default
                            break;

                        case QuestionType::DATE:
                            // For date type, implement age-based or date-related scoring if needed
                            $questionScore = 0;
                            $questionMaxScore = 0; // No scoring for date questions by default
                            break;

                        default:
                            $questionScore = 0;
                            $questionMaxScore = 0;
                            break;
                    }

                    $sectionScore += $questionScore;
                    $sectionMaxScore += $questionMaxScore;
                }

                $sectionScores[] = [
                    'section_id' => $sectionId,
                    'score' => $sectionScore,
                    'max_score' => $sectionMaxScore,
                    'percentage' => $sectionMaxScore > 0 ? ($sectionScore / $sectionMaxScore) * 100 : 0
                ];

                $totalScore += $sectionScore;
                $maxPossibleScore += $sectionMaxScore;
            }

            // Calculate overall percentage
            $percentage = $maxPossibleScore > 0 ? ($totalScore / $maxPossibleScore) * 100 : 0;

            // Determine result category based on total score
            $resultCategory = ResultCategory::where('survey_id', $response->survey_id)
                ->where('min_score', '<=', $totalScore)
                ->where('max_score', '>=', $totalScore)
                ->first();

            // Save or update the response score with complete data
            ResponseScore::updateOrCreate(
                ['response_id' => $response->id],
                [
                    'result_category_id' => $resultCategory?->id,
                    'total_score' => round($totalScore, 2),
                    'max_possible_score' => round($maxPossibleScore, 2),
                    'percentage' => round($percentage, 2),
                    'section_scores' => $sectionScores,
                    'updated_at' => now()
                ]
            );

            Log::info("Score calculated for response {$response->id}: {$totalScore}/{$maxPossibleScore} ({$percentage}%)");

        } catch (Exception $e) {
            Log::error('Error calculating score: ' . $e->getMessage());
            // Don't throw exception to avoid breaking the main flow
        }
    }

    /**
     * Show survey result page
     * 
     * @param Request $request
     * @return InertiaResponse|\Illuminate\Http\RedirectResponse
     */
    public function showResult(Request $request)
    {
        try {
            // Check if token is provided as query parameter
            $validator = Validator::make($request->all(), [
                'token' => 'required|string'
            ], [
                'token.required' => 'Token survey wajib diisi.',
                'token.string' => 'Token survey harus berupa teks.'
            ]);

            if ($validator->fails()) {
                return redirect('/entry')
                    ->withErrors(['message' => 'Token survey tidak valid.']);
            }

            $token = $request->query('token');
            
            // If token is provided, use it to find the response
            $response = Response::where('respondent_token', $token)->first();
            
            if (!$response) {
                return redirect('/entry')
                    ->withErrors(['message' => 'Token survey tidak valid.']);
            }

            // Check if response is completed
            if ($response->status !== ResponseStatus::COMPLETED) {
                return redirect('/entry')
                    ->withErrors(['message' => 'Survey belum selesai dikerjakan.']);
            }
            
            $surveyId = $response->survey_id;
            $responseId = $response->id;
            $surveyCode = $response->survey->code;

            // Get response with all necessary relationships
            $response = Response::with([
                'survey.sections',
                'survey.resultCategories' => function ($query) {
                    $query->orderBy('min_score');
                },
                'score.resultCategory',
                'answers.question.section',
                'answers.choice'
            ])->find($responseId);

            if (!$response || $response->survey_id != $surveyId) {
                return redirect('/entry')
                    ->withErrors(['message' => 'Data response tidak ditemukan.']);
            }           

            $survey = $response->survey;
            $responseScore = $response->score;

            // Prepare section scores for display
            $sectionScores = [];
            if ($responseScore && $responseScore->section_scores) {
                // Convert stored section scores to display format with section details
                foreach ($survey->sections as $section) {
                    $storedScore = collect($responseScore->section_scores)->firstWhere('section_id', $section->id);
                    if ($storedScore) {
                        $sectionScores[] = [
                            'id' => $section->id,
                            'title' => $section->title,
                            'description' => $section->description,
                            'score' => $storedScore['score'],
                            'max_score' => $storedScore['max_score'],
                            'percentage' => $storedScore['percentage']
                        ];
                    }
                }
            } else {
                // Calculate section scores manually if not stored
                foreach ($survey->sections as $section) {
                    $sectionAnswers = $response->answers->where('question.section_id', $section->id);
                    $sectionScore = 0;
                    $sectionMaxScore = 0;

                    foreach ($sectionAnswers as $answer) {
                        $questionWeight = $answer->question->score_weight ?? 1;
                        if ($answer->choice) {
                            $sectionScore += $answer->choice->score * $questionWeight;
                        } elseif ($answer->value_number !== null) {
                            $sectionScore += $answer->value_number * $questionWeight;
                        }
                        
                        // Calculate max possible score for this question
                        $maxChoiceScore = $answer->question->choices->max('score') ?? 1;
                        $sectionMaxScore += $maxChoiceScore * $questionWeight;
                    }

                    $sectionScores[] = [
                        'id' => $section->id,
                        'title' => $section->title,
                        'description' => $section->description,
                        'score' => round($sectionScore, 2),
                        'max_score' => round($sectionMaxScore, 2),
                        'percentage' => $sectionMaxScore > 0 ? round(($sectionScore / $sectionMaxScore) * 100, 2) : 0
                    ];
                }
            }

            // Prepare survey result data structure for the Vue component
            $surveyResult = [
                'survey' => [
                    'id' => $survey->id,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'code' => $survey->code
                ],
                'score' => [
                    'total_score' => $responseScore ? $responseScore->total_score : 0,
                    'max_possible_score' => $responseScore ? $responseScore->max_possible_score : 0,
                    'percentage' => $responseScore ? round($responseScore->percentage, 2) : 0,
                    'category' => $responseScore && $responseScore->resultCategory ? [
                        'id' => $responseScore->resultCategory->id,
                        'name' => $responseScore->resultCategory->name,
                        'description' => $responseScore->resultCategory->description,
                        'color' => $responseScore->resultCategory->color
                    ] : null
                ],
                'sections' => $sectionScores
            ];

            return Inertia::render('Survey/Result', [
                'surveyCode' => $surveyCode,
                'surveyResult' => $surveyResult
            ]);

        } catch (Exception $e) {
            Log::error('Error showing survey result: ' . $e->getMessage());
            return redirect('/entry')
                ->withErrors(['message' => 'Terjadi kesalahan saat menampilkan hasil survey.']);
        }
    }
}
