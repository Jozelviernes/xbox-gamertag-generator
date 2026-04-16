<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GamertagController;
use App\Http\Controllers\Api\GlossaryController;
use App\Http\Controllers\PriceCheckerController;
use App\Http\Controllers\Api\GametagController;
use App\Http\Controllers\Api\ProfileController;

Route::prefix('xbox')->group(function () {
    Route::post('gametag/check',  [GametagController::class, 'check']);
    Route::get('profile',          [ProfileController::class,  'show']);
});


Route::post('/generate-gamertags', [GamertagController::class, 'generate']);
Route::post('/check-price', [PriceCheckerController::class, 'check']);
Route::get('/glossary', [GlossaryController::class, 'index']);

Route::get('/test-cors', function () {
    return response()->json([
        'cors_origins' => env('CORS_ALLOWED_ORIGINS'),
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'fallback')),
        'app_env' => env('APP_ENV'),
    ]);
});