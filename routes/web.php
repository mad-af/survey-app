<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SurveyProcessController;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

// ================================
// Dashboard's Routes
// ================================

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware(['guest', 'no.cache']);
Route::post('/login', [AuthController::class, 'login'])->middleware(['guest', 'no.cache']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware(['auth', 'no.cache']);
Route::post('/api/token', [AuthController::class, 'generateToken'])->name('api.token')->middleware('no.cache');

// Sanctum CSRF cookie route
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

Route::prefix('dashboard')->middleware(['auth', 'no.cache'])->group(function () {
    Route::get('/', [SurveyController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/user-management', function () {
        return Inertia::render('Dashboard/UserManagement/Index');
    });
    
    Route::get('/survey', function () {
        return Inertia::render('Dashboard/Survey/Index');
    });

    Route::get('/survey/{survey}', function ($survey) {
        return Inertia::render('Dashboard/Survey/Show', [
            'surveyId' => $survey
        ]);
    });
    
    Route::get('/survey/{survey}/manage', function ($survey) {
        return Inertia::render('Dashboard/Survey/Manage', [
            'surveyId' => $survey
        ]);
    });
    
    Route::get('/survey/{survey}/responses', [SurveyController::class, 'showResponses']);

});

// ================================
// Survey's Routes
// ================================

Route::middleware(['guest.survey','no.cache'])->group(function () {
    Route::get('/entry', [SurveyProcessController::class, 'entry']);

    Route::post('/survey/enter', [SurveyProcessController::class, 'enter']);
    
    Route::get('/survey/result', [SurveyProcessController::class, 'showResult']);
});

Route::prefix('survey')->middleware(['survey.token', 'no.cache'])->group(function () {
    
    Route::get('/respondent-data', [SurveyProcessController::class, 'showRespondentData']);

    Route::post('/respondent-data', [SurveyProcessController::class, 'submitRespondentData']);
    
    Route::get('/questions', [SurveyProcessController::class, 'showQuestions']);
    
    Route::post('/question-partials', [SurveyProcessController::class, 'submitQuestionPartials']);

    Route::post('/questions', [SurveyProcessController::class, 'submitQuestions']);

    // Route::post('/{survey}/save-section', [SurveyController::class, 'saveSectionAnswers']);
    
    // Route::post('/{survey}/submit', [SurveyController::class, 'submitSurveyResponse']);
    
});

