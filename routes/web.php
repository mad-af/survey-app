<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('/entry', function () {
    return Inertia::render('Entry');
});

Route::get('/login', function () {
    return Inertia::render('Login');
});

Route::prefix('dashboard')->group(function () {
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
    
    Route::get('/survey/{survey}/edit', function ($survey) {
        return Inertia::render('Dashboard/Survey/Edit', [
            'surveyId' => $survey
        ]);
    });
    
    Route::get('/survey/{survey}/responses', function ($survey) {
        return Inertia::render('Dashboard/Survey/Responses', [
            'surveyId' => $survey
        ]);
    });
});

