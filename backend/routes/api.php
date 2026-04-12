<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GamertagController;
use App\Http\Controllers\PriceCheckerController;

Route::post('/generate-gamertags', [GamertagController::class, 'generate']);
Route::post('/check-price', [PriceCheckerController::class, 'check']);