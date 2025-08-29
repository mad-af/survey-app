<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/entry', function () {
    return Inertia::render('Entry');
});

Route::post('/survey/enter', [SurveyController::class, 'enter']);

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::post('/api/token', [AuthController::class, 'generateToken'])->name('api.token');

// Sanctum CSRF cookie route
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

Route::prefix('dashboard')->middleware('auth')->group(function () {
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
});

Route::prefix('survey')->group(function () {
    Route::get('/{survey}/respondent-data', [SurveyController::class, 'showRespondentData'])->middleware('survey.token');
    
    Route::get('/{survey}/questions', [SurveyController::class, 'showQuestions'])->middleware('survey.token');
    
    Route::post('/{survey}/register', [SurveyController::class, 'registerRespondent'])->middleware('survey.token');
    
});

