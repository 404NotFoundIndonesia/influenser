<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    Route::get('/influencer', [\App\Http\Controllers\Influencer\InfluencerController::class, 'index'])->name('influencer.index');
    Route::get('/influencer/new', [\App\Http\Controllers\Influencer\InfluencerController::class, 'create'])->name('influencer.create');
    Route::post('/influencer', [\App\Http\Controllers\Influencer\InfluencerController::class, 'store'])->name('influencer.store');
    Route::get('/influencer/{influencer}', [\App\Http\Controllers\Influencer\InfluencerController::class, 'show'])->name('influencer.show');
    Route::get('/influencer/{influencer}/edit', [\App\Http\Controllers\Influencer\InfluencerController::class, 'edit'])->name('influencer.edit');
    Route::put('/influencer/{influencer}', [\App\Http\Controllers\Influencer\InfluencerController::class, 'update'])->name('influencer.update');
    Route::delete('/influencer', [\App\Http\Controllers\Influencer\InfluencerController::class, 'massDestroy'])->name('influencer.mass-destroy');
    Route::delete('/influencer/{influencer}', [\App\Http\Controllers\Influencer\InfluencerController::class, 'destroy'])->name('influencer.destroy');

    Route::delete('/influencer/{influencer}/kol/{keyOpinionLeader}', [\App\Http\Controllers\KeyOpinionLeaderController::class, 'destroy'])->name('influencer.key-opinion-leader.destroy');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
