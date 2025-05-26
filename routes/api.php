<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LicenceApiController;
use App\Http\Controllers\Api\TranslationApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route de test pour vérifier que l'API fonctionne
Route::get('/test', [LicenceApiController::class, 'test']);

// Routes pour la validation des licences - Version directe (pour compatibilité)
Route::post('/check-serial', [LicenceApiController::class, 'checkSerial']);

// Route pour récupérer les traductions
Route::get('/translations', [TranslationApiController::class, 'getTranslations']);

// Routes pour la validation des licences - Version avec préfixe v1
Route::prefix('v1')->group(function () {
    // Route de test pour vérifier que l'API fonctionne
    Route::get('/test', [LicenceApiController::class, 'test']);
    
    // Route publique pour vérifier une clé de licence
    Route::post('/check-serial', [LicenceApiController::class, 'checkSerial']);
});