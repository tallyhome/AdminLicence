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

// Route de base de l'API
Route::get('/', function () {
    return response()->json(['message' => 'API AdminLicence']);
});

// Route de traductions accessible sans middleware
Route::get('/translations', [\App\Http\Controllers\Api\TranslationApiController::class, 'getTranslations'])->name('api.translations');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route de test pour vérifier que l'API fonctionne (avec rate limiting)
Route::middleware('api.ratelimit:20,1')->get('/test', [LicenceApiController::class, 'test']);

// Routes pour la validation des licences - Version directe (pour compatibilité)
Route::middleware('license.ratelimit:5,1')->post('/check-serial', [LicenceApiController::class, 'checkSerial']);

// Routes pour la validation des licences - Version avec préfixe v1
Route::prefix('v1')->middleware('licence-api')->group(function () {
    // Route de test pour vérifier que l'API fonctionne
    Route::get('/test', [LicenceApiController::class, 'test']);
    
    // Route publique pour vérifier une clé de licence
    Route::post('/check-serial', [LicenceApiController::class, 'checkSerial']);
});

// Routes protégées par JWT pour les opérations sensibles
Route::prefix('v1')->middleware(['licence-api', 'jwt.auth'])->group(function () {
    // Ces routes nécessitent une authentification JWT
    Route::post('/verify-licence', [LicenceApiController::class, 'verifyLicence']);
    Route::post('/refresh-token', [LicenceApiController::class, 'refreshToken']);
});