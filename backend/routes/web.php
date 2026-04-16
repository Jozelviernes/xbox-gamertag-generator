<?php

use App\Http\Controllers\Admin\GlossaryController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GamertagController;
use App\Http\Controllers\Api\ProfileController;

Route::prefix('xbox')->group(function () {
    Route::post('gamertag/check',  [GamertagController::class, 'check']);
    Route::get('profile',          [ProfileController::class,  'show']);
});



Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return redirect()->route('admin.glossaries.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('glossaries', GlossaryController::class)->only([
        'index',
        'store',
        'update',
        'destroy',
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';