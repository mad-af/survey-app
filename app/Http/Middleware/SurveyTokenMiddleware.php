<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;

class SurveyTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if survey token exists in session
        $surveyToken = session('survey_token');
        $surveyId = session('survey_id');
        $surveyCode = session('survey_code');
        $responseId = session('response_id');
        $currentStep = session('current_step');

        if (!$surveyToken || !$surveyId || !$responseId || !$surveyCode || !$currentStep) {
            return redirect('/entry')->withErrors([
                'survey_code' => 'Sesi survey tidak valid. Silakan masukkan kode survey kembali.'
            ]);
        }

        // Verify token exists in database
        $response = SurveyResponse::where('respondent_token', $surveyToken)
            ->where('survey_id', $surveyId)
            ->where('id', $responseId)
            ->first();

        if (!$response) {
            return redirect('/entry')->withErrors([
                'survey_code' => 'Token survey tidak valid atau sudah kadaluarsa.'
            ]);
        }

        // Verify survey is still active
        $survey = Survey::find($surveyId);
        if (!$survey || $survey->status->value !== 'active') {
            return redirect('/entry')->withErrors([
                'survey_code' => 'Survey tidak aktif atau tidak ditemukan.'
            ]);
        }

        // Check survey date range
        $now = now();
        if ($survey->starts_at && $now->lt($survey->starts_at)) {
            return redirect('/entry')->withErrors([
                'survey_code' => 'Survey belum dimulai.'
            ]);
        }

        if ($survey->ends_at && $now->gt($survey->ends_at)) {
            return redirect('/entry')->withErrors([
                'survey_code' => 'Survey sudah berakhir.'
            ]);
        }

        // Add survey data to request for use in controllers
        $request->merge([
            'survey_token' => $surveyToken,
            'survey_id' => $surveyId,
            'survey_code' => $surveyCode,
            'response_id' => $responseId,
            'current_step' => $currentStep,
            'survey' => $survey,
            'response' => $response
        ]);
        
        $currentStepRoute = '';
        switch ($currentStep) {
        case SurveyResponse::STEP_RESPONDENT_DATA:
            $currentStepRoute = '/survey/respondent-data';
            break;
        case SurveyResponse::STEP_QUESTIONS:
            $currentStepRoute = '/survey/questions';
            break;
        case SurveyResponse::STEP_RESULT:
            $currentStepRoute = '/survey/result';
            break;
        }

        // Only redirect if not already on the correct route and route is defined
        if ($currentStepRoute && $currentStepRoute !== $request->getPathInfo()) {
            return redirect($currentStepRoute);
        }

        return $next($request);
    }
}