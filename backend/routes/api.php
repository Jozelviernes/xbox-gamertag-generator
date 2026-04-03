<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GamertagController;

Route::post('/generate-gamertags', [GamertagController::class, 'generate']);

Route::get('/test-api', function () {
    return response()->json([
        'message' => 'API route working'
    ]);
});