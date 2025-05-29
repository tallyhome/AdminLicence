<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Admin\VersionController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LanguageSwitchController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// NOTE: Les routes frontend principales ont été déplacées dans le fichier frontend.php
// Ce fichier ne contient plus que les routes qui ne sont pas directement liées au frontend

Route::middleware(['web', 'locale'])->group(function () {

    // Route pour l'installation
    Route::get('/install', function () {
        return redirect('/install.php');
    })->name('install');

    // Route publique pour la page de version
    Route::get('/version', [VersionController::class, 'index'])->name('version');

    // Webhook routes (no auth required)
    Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripeWebhook']);
    Route::post('/webhooks/paypal', [WebhookController::class, 'handlePayPalWebhook']);
    
    // Routes de documentation (publiques)
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');
    Route::get('/documentation/api', [DocumentationController::class, 'apiIntegration'])->name('documentation.api');

    // Subscription routes (auth required)
    Route::middleware(['auth'])->group(function () {
        // Subscription plans
        Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
        Route::get('/subscription/checkout/{planId}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
        Route::post('/subscription/process-stripe', [SubscriptionController::class, 'processStripeSubscription'])->name('subscription.process-stripe');
        Route::post('/subscription/process-paypal', [SubscriptionController::class, 'processPayPalSubscription'])->name('subscription.process-paypal');
        Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
        
        // Payment methods
        Route::get('/subscription/payment-methods', [SubscriptionController::class, 'paymentMethods'])->name('subscription.payment-methods');
        Route::get('/subscription/add-payment-method/{type?}', [SubscriptionController::class, 'addPaymentMethod'])->name('subscription.add-payment-method');
        Route::post('/subscription/store-stripe-payment-method', [SubscriptionController::class, 'storeStripePaymentMethod'])->name('subscription.store-stripe-payment-method');
        Route::post('/subscription/store-paypal-payment-method', [SubscriptionController::class, 'storePayPalPaymentMethod'])->name('subscription.store-paypal-payment-method');
        Route::post('/subscription/set-default-payment-method/{id}', [SubscriptionController::class, 'setDefaultPaymentMethod'])->name('subscription.set-default-payment-method');
        Route::delete('/subscription/delete-payment-method/{id}', [SubscriptionController::class, 'deletePaymentMethod'])->name('subscription.delete-payment-method');
        
        // Invoices
        Route::get('/subscription/invoices', [SubscriptionController::class, 'invoices'])->name('subscription.invoices');
        Route::get('/subscription/invoices/{id}', [SubscriptionController::class, 'showInvoice'])->name('subscription.show-invoice');
        
        // Subscription management
        Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription'])->name('subscription.cancel');
        Route::post('/subscription/resume', [SubscriptionController::class, 'resumeSubscription'])->name('subscription.resume');
    });

    // Le changement de langue est maintenant géré directement dans le contrôleur AdminAuthController
    
    // Ces routes ont été déplacées dans la section publique ci-dessus
});

// Inclure les routes admin
require __DIR__.'/admin.php';

// Routes de redirection de l'ancien dashboard vers le nouveau
Route::middleware(['web'])->group(function () {
    // Redirection générale de l'ancien dashboard
    Route::get('/dashboard', [\App\Http\Controllers\OldDashboardController::class, 'redirect'])->name('old.dashboard');
    
    // Redirection des pages d'optimisation
    Route::get('/optimization', [\App\Http\Controllers\OldDashboardController::class, 'redirectOptimization'])->name('old.optimization');
    Route::post('/optimization/clean-logs', [\App\Http\Controllers\OldDashboardController::class, 'redirectOptimization']);
    Route::post('/optimization/optimize-images', [\App\Http\Controllers\OldDashboardController::class, 'redirectOptimization']);
    
    // Redirection des pages de diagnostic API
    Route::get('/api-diagnostic', [\App\Http\Controllers\OldDashboardController::class, 'redirectApiDiagnostic'])->name('old.api-diagnostic');
    Route::post('/api-diagnostic/test-serial-key', [\App\Http\Controllers\OldDashboardController::class, 'redirectApiDiagnostic']);
    Route::post('/api-diagnostic/test-api-connection', [\App\Http\Controllers\OldDashboardController::class, 'redirectApiDiagnostic']);
    Route::post('/api-diagnostic/test-database', [\App\Http\Controllers\OldDashboardController::class, 'redirectApiDiagnostic']);
    Route::post('/api-diagnostic/check-permissions', [\App\Http\Controllers\OldDashboardController::class, 'redirectApiDiagnostic']);
    Route::post('/api-diagnostic/get-logs', [\App\Http\Controllers\OldDashboardController::class, 'redirectApiDiagnostic']);
});
