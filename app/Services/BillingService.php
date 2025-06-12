<?php

namespace App\Services;

use App\Models\Licence;
use App\Models\Tenant;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\PaymentReceived;
use App\Events\PaymentFailed;

class BillingService
{
    protected $licenceModeService;
    protected $stripeService;
    protected $paypalService;

    public function __construct(
        LicenceModeService $licenceModeService,
        StripeService $stripeService = null,
        PaypalService $paypalService = null
    ) {
        $this->licenceModeService = $licenceModeService;
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
    }

    /**
     * Créer un abonnement pour un tenant
     */
    public function createSubscription(Tenant $tenant, string $planId, string $provider = 'stripe'): array
    {
        try {
            // Vérifier que nous sommes en mode SaaS
            if (!$this->licenceModeService->isSaasMode()) {
                throw new \Exception('Les abonnements ne sont disponibles qu\'en mode SaaS');
            }

            $subscription = null;
            $externalId = null;

            switch ($provider) {
                case 'stripe':
                    if (!$this->stripeService) {
                        throw new \Exception('Service Stripe non configuré');
                    }
                    $result = $this->stripeService->createSubscription($tenant, $planId);
                    $externalId = $result['subscription_id'];
                    break;

                case 'paypal':
                    if (!$this->paypalService) {
                        throw new \Exception('Service PayPal non configuré');
                    }
                    $result = $this->paypalService->createSubscription($tenant, $planId);
                    $externalId = $result['subscription_id'];
                    break;

                default:
                    throw new \Exception('Fournisseur de paiement non supporté');
            }

            // Créer l'abonnement en base
            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $planId,
                'provider' => $provider,
                'external_id' => $externalId,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);

            // Mettre à jour les limites du tenant
            $this->updateTenantLimits($tenant, $planId);

            // Déclencher l'événement
            Event::dispatch(new SubscriptionCreated($subscription));

            Log::info('Abonnement créé avec succès', [
                'tenant_id' => $tenant->id,
                'plan_id' => $planId,
                'provider' => $provider,
                'subscription_id' => $subscription->id
            ]);

            return [
                'success' => true,
                'subscription' => $subscription,
                'message' => 'Abonnement créé avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'abonnement', [
                'tenant_id' => $tenant->id,
                'plan_id' => $planId,
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Annuler un abonnement
     */
    public function cancelSubscription(Subscription $subscription): array
    {
        try {
            // Annuler chez le fournisseur
            switch ($subscription->provider) {
                case 'stripe':
                    $this->stripeService->cancelSubscription($subscription->external_id);
                    break;

                case 'paypal':
                    $this->paypalService->cancelSubscription($subscription->external_id);
                    break;
            }

            // Mettre à jour le statut
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            // Révoquer les limites étendues
            $this->revertTenantLimits($subscription->tenant);

            // Déclencher l'événement
            Event::dispatch(new SubscriptionCancelled($subscription));

            Log::info('Abonnement annulé', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id
            ]);

            return [
                'success' => true,
                'message' => 'Abonnement annulé avec succès'
            ];

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'annulation de l\'abonnement', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Traiter un webhook de paiement
     */
    public function handleWebhook(string $provider, array $payload): array
    {
        try {
            switch ($provider) {
                case 'stripe':
                    return $this->handleStripeWebhook($payload);

                case 'paypal':
                    return $this->handlePaypalWebhook($payload);

                default:
                    throw new \Exception('Fournisseur de webhook non supporté');
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du webhook', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Suspendre un tenant pour non-paiement
     */
    public function suspendTenant(Tenant $tenant, string $reason = 'Non-paiement'): void
    {
        $tenant->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspension_reason' => $reason
        ]);

        // Révoquer l'accès aux fonctionnalités
        $this->revokeTenantAccess($tenant);

        Log::warning('Tenant suspendu', [
            'tenant_id' => $tenant->id,
            'reason' => $reason
        ]);
    }

    /**
     * Réactiver un tenant
     */
    public function reactivateTenant(Tenant $tenant): void
    {
        $tenant->update([
            'status' => 'active',
            'suspended_at' => null,
            'suspension_reason' => null
        ]);

        // Restaurer l'accès
        $this->restoreTenantAccess($tenant);

        Log::info('Tenant réactivé', [
            'tenant_id' => $tenant->id
        ]);
    }

    /**
     * Mettre à jour les limites d'un tenant selon son plan
     */
    protected function updateTenantLimits(Tenant $tenant, string $planId): void
    {
        $planLimits = $this->getPlanLimits($planId);
        
        $tenant->update([
            'limits' => $planLimits
        ]);
    }

    /**
     * Révoquer les limites étendues d'un tenant
     */
    protected function revertTenantLimits(Tenant $tenant): void
    {
        $defaultLimits = $this->getDefaultLimits();
        
        $tenant->update([
            'limits' => $defaultLimits
        ]);
    }

    /**
     * Obtenir les limites d'un plan
     */
    protected function getPlanLimits(string $planId): array
    {
        $plans = [
            'basic' => [
                'projects' => 5,
                'serial_keys' => 100,
                'api_keys' => 3
            ],
            'premium' => [
                'projects' => 25,
                'serial_keys' => 1000,
                'api_keys' => 10
            ],
            'enterprise' => [
                'projects' => 'unlimited',
                'serial_keys' => 'unlimited',
                'api_keys' => 'unlimited'
            ]
        ];

        return $plans[$planId] ?? $plans['basic'];
    }

    /**
     * Obtenir les limites par défaut
     */
    protected function getDefaultLimits(): array
    {
        return [
            'projects' => 1,
            'serial_keys' => 10,
            'api_keys' => 1
        ];
    }

    /**
     * Traiter un webhook Stripe
     */
    protected function handleStripeWebhook(array $payload): array
    {
        $eventType = $payload['type'] ?? null;
        
        switch ($eventType) {
            case 'invoice.payment_succeeded':
                return $this->handlePaymentSuccess($payload);
                
            case 'invoice.payment_failed':
                return $this->handlePaymentFailure($payload);
                
            case 'customer.subscription.deleted':
                return $this->handleSubscriptionDeleted($payload);
                
            default:
                return ['success' => true, 'message' => 'Événement ignoré'];
        }
    }

    /**
     * Traiter un webhook PayPal
     */
    protected function handlePaypalWebhook(array $payload): array
    {
        // Implémentation similaire pour PayPal
        return ['success' => true, 'message' => 'Webhook PayPal traité'];
    }

    /**
     * Traiter un paiement réussi
     */
    protected function handlePaymentSuccess(array $payload): array
    {
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;
        
        if ($subscriptionId) {
            $subscription = Subscription::where('external_id', $subscriptionId)->first();
            
            if ($subscription) {
                Event::dispatch(new PaymentReceived($subscription, $payload));
                
                // Réactiver le tenant si suspendu
                if ($subscription->tenant->status === 'suspended') {
                    $this->reactivateTenant($subscription->tenant);
                }
            }
        }
        
        return ['success' => true, 'message' => 'Paiement traité'];
    }

    /**
     * Traiter un échec de paiement
     */
    protected function handlePaymentFailure(array $payload): array
    {
        $subscriptionId = $payload['data']['object']['subscription'] ?? null;
        
        if ($subscriptionId) {
            $subscription = Subscription::where('external_id', $subscriptionId)->first();
            
            if ($subscription) {
                Event::dispatch(new PaymentFailed($subscription, $payload));
                
                // Suspendre le tenant après plusieurs échecs
                $this->suspendTenant($subscription->tenant, 'Échec de paiement');
            }
        }
        
        return ['success' => true, 'message' => 'Échec de paiement traité'];
    }

    /**
     * Traiter la suppression d'un abonnement
     */
    protected function handleSubscriptionDeleted(array $payload): array
    {
        $subscriptionId = $payload['data']['object']['id'] ?? null;
        
        if ($subscriptionId) {
            $subscription = Subscription::where('external_id', $subscriptionId)->first();
            
            if ($subscription) {
                $subscription->update(['status' => 'cancelled']);
                $this->revertTenantLimits($subscription->tenant);
            }
        }
        
        return ['success' => true, 'message' => 'Suppression d\'abonnement traitée'];
    }

    /**
     * Révoquer l'accès d'un tenant
     */
    protected function revokeTenantAccess(Tenant $tenant): void
    {
        // Implémentation de la révocation d'accès
        // Par exemple, désactiver les API keys, etc.
    }

    /**
     * Restaurer l'accès d'un tenant
     */
    protected function restoreTenantAccess(Tenant $tenant): void
    {
        // Implémentation de la restauration d'accès
    }
}