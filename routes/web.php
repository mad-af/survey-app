<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/entry', [SurveyController::class, 'showEntry'])->middleware('guest.survey');

Route::post('/survey/enter', [SurveyController::class, 'enter']);
Route::post('/survey/logout', [SurveyController::class, 'logout']);

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
    Route::get('/', function () {
        return Inertia::render('Dashboard/Index');
    });
    
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
    
    Route::get('/survey/{survey}/response', [SurveyController::class, 'showResponses']);
    
    Route::get('/survey/{survey}/responses', [SurveyController::class, 'showResponses']);
});

Route::prefix('survey')->middleware('survey.token')->group(function () {
    Route::get('/{survey}/respondent-data', [SurveyController::class, 'showRespondentData']);
    
    Route::get('/{survey}/questions', [SurveyController::class, 'showQuestions']);
    
    Route::post('/{survey}/register', [SurveyController::class, 'registerRespondent']);
    
    Route::post('/{survey}/save-section', [SurveyController::class, 'saveSectionAnswers']);
    
    Route::post('/{survey}/submit', [SurveyController::class, 'submitSurveyResponse']);
    
    Route::get('/{survey}/result', [SurveyController::class, 'showResult'])->name("survey.result");
});

