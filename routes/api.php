<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SurveySectionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SurveyTakeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Protected API Routes - Require Authentication
Route::middleware('auth:sanctum')->group(function () {
    // User Management API Routes
    Route::apiResource('users', UserController::class);
    
    // Survey Management API Routes
    Route::apiResource('surveys', SurveyController::class);
    
    // Survey Statistics Route
    Route::get('surveys/{survey}/statistics', [SurveyController::class, 'statistics']);
    
    // Survey Sections API Routes
    Route::apiResource('surveys.sections', SurveySectionController::class)->parameters([
        'surveys' => 'survey',
        'sections' => 'section'
    ]);
    
    // Questions API Routes (nested under sections)
    Route::apiResource('sections.questions', QuestionController::class)->parameters([
        'sections' => 'section',
        'questions' => 'question'
    ]);
    
    // Survey Take API Routes
    Route::get('surveys/{survey}/data', [SurveyTakeController::class, 'getSurveyData']);
    Route::post('surveys/{survey}/responses', [SurveyTakeController::class, 'submitResponse']);
});

// Public API Routes - No Authentication Required
Route::prefix('public')->group(function () {
    // Get survey by code for public access
    Route::get('surveys/{code}', [SurveyTakeController::class, 'getSurveyByCode']);
    Route::post('surveys/{code}/responses', [SurveyTakeController::class, 'submitResponseByCode']);
    Route::post('surveys/{survey}/responses', [SurveyTakeController::class, 'submitResponse']);
});
