<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScoreController;

// Core Score API Routes - Based on README requirements only
Route::prefix('scores')->group(function () {
    Route::post('/search', [ScoreController::class, 'searchByStudentId']);

    Route::get('/statistics', [ScoreController::class, 'getStatisticsReport']);

    Route::get('/top10-group-a', [ScoreController::class, 'getTop10GroupA']);
});

// Requirement: OOP subject management
Route::get('/subjects', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Models\Subject::active()->ordered()->get()
    ]);
});
