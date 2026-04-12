<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GamertagController;
use App\Http\Controllers\PriceCheckerController;
use Illuminate\Support\Facades\Artisan;

Route::post('/generate-gamertags', [GamertagController::class, 'generate']);
Route::post('/check-price', [PriceCheckerController::class, 'check']);

Route::get('/run-setup', function () {
    Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = Artisan::output();

    Artisan::call('db:seed', ['--class' => 'FullGeneratorSeeder']);
    $seeder1Output = Artisan::output();

    Artisan::call('db:seed', ['--class' => 'GamertagValueSeeder']);
    $seeder2Output = Artisan::output();

    return response()->json([
        'migrate' => $migrateOutput,
        'seeder1' => $seeder1Output,
        'seeder2' => $seeder2Output,
        'done'    => true,
    ]);
});