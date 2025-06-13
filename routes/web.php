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
use App\Http\Controllers\TenantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

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

// Routes de traduction sans middleware pour éviter les conflits
Route::get('/web-translations', [\App\Http\Controllers\TranslationController::class, 'getTranslations'])->name('web.translations');
Route::get('/direct-translations', [\App\Http\Controllers\TranslationController::class, 'getTranslations'])->name('direct.translations');

Route::middleware(['web', 'locale'])->group(function () {

    // Route pour l'installation
    Route::get('/install', function () {
        return redirect('/install/index.php?step=1');
    })->name('install');

    // Route publique pour la page de version
    Route::get('/version', [VersionController::class, 'index'])->name('version');

    // Webhook routes (no auth required)
    Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripeWebhook']);
    Route::post('/webhooks/paypal', [WebhookController::class, 'handlePayPalWebhook']);
    
    // Routes de documentation (publiques)
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation.index');
    Route::get('/documentation/api', [DocumentationController::class, 'apiIntegration'])->name('documentation.api');

    // Routes pour la gestion des tenants
    Route::middleware(['auth'])->group(function () {
        Route::get('/tenant/select', [TenantController::class, 'select'])->name('tenant.select');
        Route::get('/tenant/switch/{tenant}', [TenantController::class, 'switch'])->name('tenant.switch');
        Route::get('/tenant/create', [TenantController::class, 'create'])->name('tenant.create');
        Route::post('/tenant', [TenantController::class, 'store'])->name('tenant.store');
        
        // Routes pour la facturation (accessibles sans tenant actif)
        Route::get('/billing/plans', [BillingController::class, 'plans'])->name('billing.plans');
        Route::get('/billing/checkout/{planId}', [BillingController::class, 'checkout'])->name('billing.checkout');
        Route::post('/billing/process-payment', [BillingController::class, 'processPayment'])->name('billing.process-payment');
    });
    
    // Routes protégées par le middleware tenant
    Route::middleware(['auth', 'tenant'])->group(function () {
        // Dashboard et routes principales nécessitant un contexte tenant
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Routes pour la facturation protégées par le middleware tenant
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/invoices', [BillingController::class, 'invoices'])->name('invoices');
            Route::get('/invoice/{id}', [BillingController::class, 'showInvoice'])->name('invoice');
            Route::get('/invoice/{id}/download', [BillingController::class, 'downloadInvoice'])->name('download-invoice');
            Route::get('/payment-methods', [BillingController::class, 'paymentMethods'])->name('payment-methods');
            Route::get('/payment-methods/add', [BillingController::class, 'addPaymentMethod'])->name('add-payment-method');
            Route::post('/payment-methods', [BillingController::class, 'storePaymentMethod'])->name('store-payment-method');
            Route::delete('/payment-methods/{id}', [BillingController::class, 'deletePaymentMethod'])->name('delete-payment-method');
            Route::get('/success', [BillingController::class, 'success'])->name('success');
        });
    });
    
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
Route::middleware(['web', 'locale'])->group(function () {
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

// Route de fallback pour 'login' requise par Laravel Framework
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');
