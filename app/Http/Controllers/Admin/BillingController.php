<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use App\Services\LicenceModeService;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BillingController extends Controller
{
    protected $billingService;
    protected $licenceModeService;

    public function __construct(
        BillingService $billingService,
        LicenceModeService $licenceModeService
    ) {
        $this->billingService = $billingService;
        $this->licenceModeService = $licenceModeService;
    }

    /**
     * Afficher la page de gestion de la facturation
     */
    public function index(): View
    {
        // Vérifier que nous sommes en mode SaaS
        if (!$this->licenceModeService->isSaasMode()) {
            abort(404, 'La facturation n\'est disponible qu\'en mode SaaS');
        }

        $tenants = Tenant::with('subscriptions')->paginate(20);
        $totalRevenue = $this->calculateTotalRevenue();
        $activeSubscriptions = Subscription::active()->count();
        $trialSubscriptions = Subscription::trialing()->count();

        return view('admin.billing.index', compact(
            'tenants',
            'totalRevenue',
            'activeSubscriptions',
            'trialSubscriptions'
        ));
    }

    /**
     * Afficher les détails d'un tenant
     */
    public function show(Tenant $tenant): View
    {
        $tenant->load(['subscriptions.invoices']);
        $currentSubscription = $tenant->subscriptions()->valid()->first();
        
        return view('admin.billing.show', compact('tenant', 'currentSubscription'));
    }

    /**
     * Créer un abonnement pour un tenant
     */
    public function createSubscription(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|string',
            'provider' => 'required|in:stripe,paypal'
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        
        $result = $this->billingService->createSubscription(
            $tenant,
            $request->plan_id,
            $request->provider
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Abonnement créé avec succès',
                'subscription' => $result['subscription']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error']
        ], 400);
    }

    /**
     * Annuler un abonnement
     */
    public function cancelSubscription(Subscription $subscription): JsonResponse
    {
        $result = $this->billingService->cancelSubscription($subscription);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Abonnement annulé avec succès'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['error']
        ], 400);
    }

    /**
     * Suspendre un tenant
     */
    public function suspendTenant(Request $request, Tenant $tenant): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $this->billingService->suspendTenant($tenant, $request->reason);

        return response()->json([
            'success' => true,
            'message' => 'Tenant suspendu avec succès'
        ]);
    }

    /**
     * Réactiver un tenant
     */
    public function reactivateTenant(Tenant $tenant): JsonResponse
    {
        $this->billingService->reactivateTenant($tenant);

        return response()->json([
            'success' => true,
            'message' => 'Tenant réactivé avec succès'
        ]);
    }

    /**
     * Obtenir les statistiques de facturation
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_revenue' => $this->calculateTotalRevenue(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'active_subscriptions' => Subscription::active()->count(),
            'trial_subscriptions' => Subscription::trialing()->count(),
            'cancelled_subscriptions' => Subscription::where('status', 'cancelled')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'revenue_by_plan' => $this->getRevenueByPlan(),
            'subscription_trends' => $this->getSubscriptionTrends()
        ];

        return response()->json($stats);
    }

    /**
     * Exporter les données de facturation
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date'
        ]);

        $format = $request->format;
        $startDate = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->subMonth();
        $endDate = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();

        $filename = "billing_export_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.{$format}";

        return response()->streamDownload(function () use ($startDate, $endDate, $format) {
            $this->generateBillingExport($startDate, $endDate, $format);
        }, $filename);
    }

    /**
     * Calculer le chiffre d'affaires total
     */
    protected function calculateTotalRevenue(): float
    {
        // Cette méthode devrait calculer le CA total basé sur les factures payées
        return 0.0; // Placeholder
    }

    /**
     * Calculer le chiffre d'affaires mensuel
     */
    protected function calculateMonthlyRevenue(): float
    {
        // Cette méthode devrait calculer le CA du mois en cours
        return 0.0; // Placeholder
    }

    /**
     * Obtenir le chiffre d'affaires par plan
     */
    protected function getRevenueByPlan(): array
    {
        // Cette méthode devrait retourner le CA par plan
        return []; // Placeholder
    }

    /**
     * Obtenir les tendances d'abonnement
     */
    protected function getSubscriptionTrends(): array
    {
        // Cette méthode devrait retourner les tendances sur les 12 derniers mois
        return []; // Placeholder
    }

    /**
     * Générer l'export de facturation
     */
    protected function generateBillingExport(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, string $format): void
    {
        // Cette méthode devrait générer l'export selon le format demandé
        // Placeholder pour l'implémentation
        echo "Export de facturation du {$startDate->format('d/m/Y')} au {$endDate->format('d/m/Y')}\n";
    }

    /**
     * Afficher la page de configuration des plans
     */
    public function plans(): View
    {
        $plans = [
            'basic' => [
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

        return view('admin.billing.plans', compact('plans'));
    }

    /**
     * Mettre à jour la configuration d'un plan
     */
    public function updatePlan(Request $request, string $planId): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'features' => 'required|array'
        ]);

        // Cette méthode devrait mettre à jour la configuration du plan
        // Placeholder pour l'implémentation

        return response()->json([
            'success' => true,
            'message' => 'Plan mis à jour avec succès'
        ]);
    }
}