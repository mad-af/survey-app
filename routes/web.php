<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});


Route::prefix('dashboard')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    });
    
    Route::get('/surveys', function () {
        return Inertia::render('Surveys/Index');
    });
    
    Route::get('/surveys/create', function () {
        return Inertia::render('Surveys/Create');
    });
    
    Route::get('/surveys/{id}', function ($id) {
        return Inertia::render('Surveys/Show');
    });
    
    Route::get('/survey/{slug}', function () {
        return Inertia::render('Survey/Take');
    });
});

