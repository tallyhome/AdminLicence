<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\CheckLicenceMode;
use App\Http\Middleware\CheckLimits;

/*
|--------------------------------------------------------------------------
| Billing Routes
|--------------------------------------------------------------------------
|
| Ces routes gèrent la facturation et les abonnements en mode SaaS.
| Elles sont protégées par le middleware CheckLicenceMode pour s'assurer
| qu'elles ne sont accessibles qu'en mode SaaS.
|
*/

// Routes de webhooks (pas de protection auth car appelées par les fournisseurs)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    // Webhooks Stripe
    Route::post('stripe', [WebhookController::class, 'stripe'])->name('stripe');
    
    // Webhooks PayPal
    Route::post('paypal', [WebhookController::class, 'paypal'])->name('paypal');
    Route::post('paypal/ipn', [WebhookController::class, 'paypalIpn'])->name('paypal.ipn');
    
    // Endpoint de test
    Route::any('test', [WebhookController::class, 'test'])->name('test');
});

// Routes de retour PayPal (pas de protection auth)
Route::prefix('billing/paypal')->name('billing.paypal.')->group(function () {
    Route::get('success', [WebhookController::class, 'paypalSuccess'])->name('success');
    Route::get('cancel', [WebhookController::class, 'paypalCancel'])->name('cancel');
});

// Routes admin de facturation (protégées par auth et mode SaaS)
Route::prefix('admin/billing')
    ->name('admin.billing.')
    ->middleware(['auth:admin', 'check.licence.mode:saas'])
    ->group(function () {
        
        // Dashboard de facturation
        Route::get('/', [BillingController::class, 'index'])->name('index');
        
        // Gestion des plans
        Route::get('plans', [BillingController::class, 'plans'])->name('plans');
        Route::put('plans/{planId}', [BillingController::class, 'updatePlan'])->name('plans.update');
        
        // Gestion des tenants
        Route::get('tenants/{tenant}', [BillingController::class, 'show'])->name('tenants.show');
        
        // Gestion des abonnements
        Route::post('subscriptions', [BillingController::class, 'createSubscription'])
            ->middleware('check.limits:subscriptions')
            ->name('subscriptions.create');
        Route::delete('subscriptions/{subscription}', [BillingController::class, 'cancelSubscription'])
            ->name('subscriptions.cancel');
        
        // Gestion des tenants
        Route::post('tenants/{tenant}/suspend', [BillingController::class, 'suspendTenant'])
            ->name('tenants.suspend');
        Route::post('tenants/{tenant}/reactivate', [BillingController::class, 'reactivateTenant'])
            ->name('tenants.reactivate');
        
        // Statistiques et rapports
        Route::get('stats', [BillingController::class, 'getStats'])->name('stats');
        Route::get('export', [BillingController::class, 'export'])->name('export');
        
        // Portail client Stripe
        Route::get('stripe/portal/{subscription}', function (\App\Models\Subscription $subscription) {
            $stripeService = app(\App\Services\StripeService::class);
            $tenant = $subscription->tenant;
            
            if (!$tenant->stripe_customer_id) {
                return redirect()->back()->with('error', 'Client Stripe non configuré');
            }
            
            $result = $stripeService->createBillingPortalSession(
                $tenant->stripe_customer_id,
                route('admin.billing.tenants.show', $tenant)
            );
            
            if ($result['success']) {
                return redirect($result['url']);
            }
            
            return redirect()->back()->with('error', 'Impossible d\'accéder au portail client');
        })->name('stripe.portal');
        
        // Gestion PayPal
        Route::get('paypal/manage/{subscription}', function (\App\Models\Subscription $subscription) {
            // Rediriger vers l'interface de gestion PayPal
            $paypalManageUrl = 'https://www.paypal.com/myaccount/autopay/';
            return redirect($paypalManageUrl);
        })->name('paypal.manage');
    });

// Routes API pour la facturation (pour les intégrations externes)
Route::prefix('api/billing')
    ->name('api.billing.')
    ->middleware(['auth:sanctum', 'check.licence.mode:saas'])
    ->group(function () {
        
        // Informations sur les plans
        Route::get('plans', function () {
            $plans = [
                'basic' => [
                    'id' => 'basic',
                    'name' => 'Plan Basic',
                    'price' => 9.99,
                    'currency' => 'EUR',
                    'interval' => 'month',
                    'features' => [
                        'projects' => 5,
                        'serial_keys' => 100,
                        'api_keys' => 3
                    ]
                ],
                'premium' => [
                    'id' => 'premium',
                    'name' => 'Plan Premium',
                    'price' => 29.99,
                    'currency' => 'EUR',
                    'interval' => 'month',
                    'features' => [
                        'projects' => 25,
                        'serial_keys' => 1000,
                        'api_keys' => 10
                    ]
                ],
                'enterprise' => [
                    'id' => 'enterprise',
                    'name' => 'Plan Enterprise',
                    'price' => 99.99,
                    'currency' => 'EUR',
                    'interval' => 'month',
                    'features' => [
                        'projects' => 'unlimited',
                        'serial_keys' => 'unlimited',
                        'api_keys' => 'unlimited'
                    ]
                ]
            ];
            
            return response()->json($plans);
        })->name('plans');
        
        // Informations sur l'abonnement actuel
        Route::get('subscription', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            $tenant = $user->tenant;
            
            if (!$tenant) {
                return response()->json(['error' => 'Tenant non trouvé'], 404);
            }
            
            $subscription = $tenant->subscriptions()->valid()->first();
            
            if (!$subscription) {
                return response()->json(['error' => 'Aucun abonnement actif'], 404);
            }
            
            return response()->json([
                'subscription' => $subscription,
                'plan' => $subscription->getPlanDetails(),
                'usage' => [
                    'projects' => $tenant->projects()->count(),
                    'serial_keys' => $tenant->serialKeys()->count(),
                    'api_keys' => $tenant->apiKeys()->count()
                ]
            ]);
        })->name('subscription');
        
        // Utilisation actuelle
        Route::get('usage', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            $tenant = $user->tenant;
            
            if (!$tenant) {
                return response()->json(['error' => 'Tenant non trouvé'], 404);
            }
            
            $limits = $tenant->limits ?? [];
            $usage = [
                'projects' => [
                    'current' => $tenant->projects()->count(),
                    'limit' => $limits['projects'] ?? 'unlimited'
                ],
                'serial_keys' => [
                    'current' => $tenant->serialKeys()->count(),
                    'limit' => $limits['serial_keys'] ?? 'unlimited'
                ],
                'api_keys' => [
                    'current' => $tenant->apiKeys()->count(),
                    'limit' => $limits['api_keys'] ?? 'unlimited'
                ]
            ];
            
            return response()->json($usage);
        })->name('usage');
        
        // Factures
        Route::get('invoices', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            $tenant = $user->tenant;
            
            if (!$tenant) {
                return response()->json(['error' => 'Tenant non trouvé'], 404);
            }
            
            $invoices = $tenant->invoices()
                ->with('items')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            return response()->json($invoices);
        })->name('invoices');
    });