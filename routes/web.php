<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    Route::get('/niche', [\App\Http\Controllers\Web\Essential\NicheController::class, 'index'])->name('niche.index');
    Route::post('/niche', [\App\Http\Controllers\Web\Essential\NicheController::class, 'store'])->name('niche.store');
    Route::put('/niche/{niche}', [\App\Http\Controllers\Web\Essential\NicheController::class, 'update'])->name('niche.update');
    Route::delete('/niche', [\App\Http\Controllers\Web\Essential\NicheController::class, 'massDestroy'])->name('niche.mass-destroy');
    Route::delete('/niche/{niche}', [\App\Http\Controllers\Web\Essential\NicheController::class, 'destroy'])->name('niche.destroy');

    Route::get('/influencer', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'index'])->name('influencer.index');
    Route::get('/influencer/new', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'create'])->name('influencer.create');
    Route::post('/influencer', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'store'])->name('influencer.store');
    Route::get('/influencer/{influencer}', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'show'])->name('influencer.show');
    Route::get('/influencer/{influencer}/edit', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'edit'])->name('influencer.edit');
    Route::put('/influencer/{influencer}', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'update'])->name('influencer.update');
    Route::delete('/influencer', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'massDestroy'])->name('influencer.mass-destroy');
    Route::delete('/influencer/{influencer}', [\App\Http\Controllers\Web\Influencer\InfluencerController::class, 'destroy'])->name('influencer.destroy');

    Route::delete('/influencer/{influencer}/kol/{keyOpinionLeader}', [\App\Http\Controllers\Web\Influencer\KeyOpinionLeaderController::class, 'destroy'])->name('influencer.key-opinion-leader.destroy');

    Route::get('/campaign', [\App\Http\Controllers\Web\Campaign\CampaignController::class, 'index'])->name('campaign.index');
    Route::post('/campaign', [\App\Http\Controllers\Web\Campaign\CampaignController::class, 'store'])->name('campaign.store');
    Route::get('/campaign/{campaign}', [\App\Http\Controllers\Web\Campaign\CampaignController::class, 'show'])->name('campaign.show');
    Route::put('/campaign/{campaign}', [\App\Http\Controllers\Web\Campaign\CampaignController::class, 'update'])->name('campaign.update');
    Route::delete('/campaign', [\App\Http\Controllers\Web\Campaign\CampaignController::class, 'massDestroy'])->name('campaign.mass-destroy');
    Route::delete('/campaign/{campaign}', [\App\Http\Controllers\Web\Campaign\CampaignController::class, 'destroy'])->name('campaign.destroy');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
