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
        $responseId = session('response_id');

        if (!$surveyToken || !$surveyId || !$responseId) {
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
        if (!$survey || $survey->status !== 'active') {
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
            'response_id' => $responseId,
            'survey' => $survey,
            'survey_response' => $response
        ]);

        return $next($request);
    }
}