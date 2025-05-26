<?php

use App\Http\Controllers\FrontendController;
use App\Http\Controllers\DocumentationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Frontend
|--------------------------------------------------------------------------
|
| Ces routes sont complètement séparées des routes admin et ne sont soumises
| à aucun middleware d'authentification. Elles sont chargées avant les routes
| web.php pour éviter les conflits.
|
*/

// Groupe de routes frontend sans middleware d'authentification
Route::group(['as' => 'frontend.'], function () {
    // Page d'accueil
    Route::get('/', [FrontendController::class, 'index'])->name('home');

    // Pages statiques
    Route::get('/features', [FrontendController::class, 'features'])->name('features');
    Route::get('/pricing', [FrontendController::class, 'pricing'])->name('pricing');
    Route::get('/faq', [FrontendController::class, 'faq'])->name('faq');
    Route::get('/support', [FrontendController::class, 'support'])->name('support');
    Route::get('/cgv', [FrontendController::class, 'cgv'])->name('cgv');
    Route::get('/privacy', [FrontendController::class, 'privacy'])->name('privacy');

    // Changement de langue
    Route::post('/set-locale', [FrontendController::class, 'setLocale'])->name('set.locale');

    // Documentation
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');
    Route::get('/documentation/api', [DocumentationController::class, 'apiIntegration'])->name('documentation.api');
});
