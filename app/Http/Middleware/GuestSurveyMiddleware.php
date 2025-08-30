<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Response as SurveyResponse;
use App\Models\Survey;

class GuestSurveyMiddleware
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

        // If all survey session data exists, verify it's valid
        if ($surveyToken && $surveyId && $responseId && $surveyCode && $currentStep) {
            // Verify token exists in database
            $response = SurveyResponse::where('respondent_token', $surveyToken)
                ->where('survey_id', $surveyId)
                ->where('id', $responseId)
                ->first();

            if ($response) {
                // Verify survey is still active
                $survey = Survey::find($surveyId);
                if ($survey && $survey->status->value === 'active') {
                    // Check survey date range
                    $now = now();
                    $surveyActive = true;
                    
                    if ($survey->starts_at && $now->lt($survey->starts_at)) {
                        $surveyActive = false;
                    }
                    
                    if ($survey->ends_at && $now->gt($survey->ends_at)) {
                        $surveyActive = false;
                    }
                    
                    // If survey is active and token is valid, redirect to appropriate step
                    if ($surveyActive) {
                        // Determine the correct route based on current step
                        $targetRoute = '';
                        switch ($currentStep) {
                            case SurveyResponse::STEP_RESPONDENT_DATA:
                                $targetRoute = '/survey/respondent-data';
                                break;
                            case SurveyResponse::STEP_QUESTIONS:
                                $targetRoute = '/survey/questions';
                                break;
                            case SurveyResponse::STEP_RESULT:
                                $targetRoute = '/survey/result';
                                break;
                        }
                        
                        // Only redirect if not already on the target route
                        if ($targetRoute != '' && $request->getPathInfo() !== $targetRoute) {
                            return redirect($targetRoute);
                        }
                    }
                }
            }
            
            // If we reach here, the token/session is invalid, clear it
            session()->forget(['survey_token', 'survey_id', 'survey_code', 'response_id', 'current_step']);
        }

        return $next($request);
    }
}