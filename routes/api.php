<?php

use App\Http\Controllers\Api\ConversionController;
use App\Http\Controllers\Api\PublicConversionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public API routes (no auth required)
Route::prefix('public')->group(function () {
    Route::post('/conversions', [PublicConversionController::class, 'store']);
    Route::get('/conversions/{conversion}', [PublicConversionController::class, 'show']);
    Route::get('/conversions/{conversion}/download', [PublicConversionController::class, 'download']);

    // Debug route
    Route::post('/test-upload', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'has_file' => $request->hasFile('file'),
            'all_files' => array_keys($request->allFiles()),
            'all_input' => array_keys($request->all()),
            'content_type' => $request->header('Content-Type'),
            'method' => $request->method(),
        ]);
    });
});

// User API routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Conversions
    Route::get('/conversions', [ConversionController::class, 'index']);
    Route::post('/conversions', [ConversionController::class, 'store']);
    Route::get('/conversions/{conversion}', [ConversionController::class, 'show']);
    Route::delete('/conversions/{conversion}', [ConversionController::class, 'destroy']);
    Route::get('/conversions/{conversion}/download', [ConversionController::class, 'download']);
});

// Python backend routes (consider adding API key middleware for production)
Route::prefix('backend')->group(function () {
    Route::get('/conversions/pending', [ConversionController::class, 'pending']);
    Route::put('/conversions/{conversion}/processing', [ConversionController::class, 'markProcessing']);
    Route::post('/conversions/{conversion}/complete', [ConversionController::class, 'complete']);
    Route::post('/conversions/{conversion}/fail', [ConversionController::class, 'fail']);
});
