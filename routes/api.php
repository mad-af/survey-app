<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SurveySectionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SurveyTakeController;
use App\Http\Controllers\SurveyProcessController;
use App\Http\Controllers\WilayahController;

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

Route::middleware(['auth:sanctum', 'no.cache'])->get('/user', function (Request $request) {
    return $request->user();
});

// Protected API Routes - Require Authentication
Route::middleware(['auth:sanctum', 'no.cache'])->group(function () {
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
    
    // User Password Update API Route
    Route::put('user/password', [UserController::class, 'updatePassword']);
    
});

// Survey Process API Routes - Require survey.token middleware
Route::middleware(['survey.token', 'no.cache'])->group(function () {
    Route::post('/survey/question-partials', [SurveyProcessController::class, 'submitQuestionPartials']);
});

// Wilayah API Routes - Public access for address data
Route::prefix('wilayah')->group(function () {
    Route::get('provinces', [WilayahController::class, 'getProvinces']);
    Route::get('regencies/{provinceCode}', [WilayahController::class, 'getRegencies']);
    Route::get('districts/{regencyCode}', [WilayahController::class, 'getDistricts']);
    Route::get('villages/{districtCode}', [WilayahController::class, 'getVillages']);
});
