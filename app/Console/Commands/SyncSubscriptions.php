<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Services\StripeService;
use App\Services\PaypalService;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;
use Exception;

class SyncSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:sync-subscriptions
                            {--provider= : Synchroniser uniquement un fournisseur (stripe|paypal)}
                            {--subscription= : Synchroniser un abonnement spécifique par ID}
                            {--dry-run : Afficher les actions sans les exécuter}
                            {--force : Forcer la synchronisation même en mode non-SaaS}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les abonnements avec les fournisseurs de paiement';

    protected $stripeService;
    protected $paypalService;
    protected $billingService;

    public function __construct(
        StripeService $stripeService,
        PaypalService $paypalService,
        BillingService $billingService
    ) {
        parent::__construct();
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
        $this->billingService = $billingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Vérifier si le mode SaaS est activé
        if (!config('billing.enabled') && !$this->option('force')) {
            $this->error('La facturation n\'est pas activée. Utilisez --force pour forcer l\'exécution.');
            return 1;
        }

        $dryRun = $this->option('dry-run');
        $provider = $this->option('provider');
        $subscriptionId = $this->option('subscription');
        
        if ($dryRun) {
            $this->info('Mode simulation activé - aucune action ne sera effectuée');
        }

        $this->info('Début de la synchronisation des abonnements...');

        try {
            if ($subscriptionId) {
                $this->syncSpecificSubscription($subscriptionId, $dryRun);
            } else {
                $this->syncAllSubscriptions($provider, $dryRun);
            }

            $this->info('Synchronisation terminée avec succès.');
            return 0;
        } catch (Exception $e) {
            $this->error("Erreur lors de la synchronisation : {$e->getMessage()}");
            Log::error('Erreur synchronisation abonnements', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Synchronise un abonnement spécifique
     */
    protected function syncSpecificSubscription(int $subscriptionId, bool $dryRun): void
    {
        $subscription = Subscription::find($subscriptionId);
        
        if (!$subscription) {
            $this->error("Abonnement avec l'ID {$subscriptionId} introuvable.");
            return;
        }

        $this->info("Synchronisation de l'abonnement {$subscription->id} ({$subscription->provider})");
        $this->syncSubscription($subscription, $dryRun);
    }

    /**
     * Synchronise tous les abonnements
     */
    protected function syncAllSubscriptions(?string $provider, bool $dryRun): void
    {
        $query = Subscription::whereNotIn('status', ['canceled', 'expired'])
            ->whereNotNull('provider_subscription_id');

        if ($provider) {
            $query->where('provider', $provider);
        }

        $subscriptions = $query->get();

        if ($subscriptions->isEmpty()) {
            $this->info('Aucun abonnement à synchroniser.');
            return;
        }

        $this->info("Abonnements à synchroniser : {$subscriptions->count()}");

        $progressBar = $this->output->createProgressBar($subscriptions->count());
        $progressBar->start();

        foreach ($subscriptions as $subscription) {
            $this->syncSubscription($subscription, $dryRun);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Synchronise un abonnement avec son fournisseur
     */
    protected function syncSubscription(Subscription $subscription, bool $dryRun): void
    {
        try {
            $remoteData = null;
            
            // Récupérer les données du fournisseur
            switch ($subscription->provider) {
                case 'stripe':
                    if (config('billing.stripe.enabled')) {
                        $remoteData = $this->stripeService->getSubscription($subscription->provider_subscription_id);
                    }
                    break;
                    
                case 'paypal':
                    if (config('billing.paypal.enabled')) {
                        $remoteData = $this->paypalService->getSubscriptionDetails($subscription->provider_subscription_id);
                    }
                    break;
            }

            if (!$remoteData) {
                $this->warn("Impossible de récupérer les données pour l'abonnement {$subscription->id}");
                return;
            }

            // Comparer et mettre à jour si nécessaire
            $updates = $this->compareSubscriptionData($subscription, $remoteData);
            
            if (empty($updates)) {
                $this->line("✓ Abonnement {$subscription->id} déjà synchronisé");
                return;
            }

            $this->line("⚠ Abonnement {$subscription->id} nécessite une mise à jour :");
            foreach ($updates as $field => $value) {
                $oldValue = $subscription->$field ?? 'null';
                $this->line("  - {$field}: {$oldValue} → {$value}");
            }

            if (!$dryRun) {
                $subscription->update($updates);
                
                // Mettre à jour le tenant si nécessaire
                $this->updateTenantFromSubscription($subscription);
                
                Log::info('Abonnement synchronisé', [
                    'subscription_id' => $subscription->id,
                    'updates' => $updates
                ]);
            }

        } catch (Exception $e) {
            $this->error("Erreur lors de la synchronisation de l'abonnement {$subscription->id} : {$e->getMessage()}");
            Log::error('Erreur synchronisation abonnement', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Compare les données locales avec les données du fournisseur
     */
    protected function compareSubscriptionData(Subscription $subscription, array $remoteData): array
    {
        $updates = [];

        // Mapper les données selon le fournisseur
        $mappedData = $this->mapRemoteData($subscription->provider, $remoteData);

        // Comparer les champs importants
        $fieldsToCheck = ['status', 'next_billing_at', 'ends_at', 'canceled_at'];
        
        foreach ($fieldsToCheck as $field) {
            if (isset($mappedData[$field])) {
                $localValue = $subscription->$field;
                $remoteValue = $mappedData[$field];
                
                // Comparer les dates
                if (in_array($field, ['next_billing_at', 'ends_at', 'canceled_at'])) {
                    $localValue = $localValue ? $localValue->timestamp : null;
                    $remoteValue = $remoteValue ? strtotime($remoteValue) : null;
                }
                
                if ($localValue !== $remoteValue) {
                    $updates[$field] = $mappedData[$field];
                }
            }
        }

        return $updates;
    }

    /**
     * Mappe les données du fournisseur vers le format local
     */
    protected function mapRemoteData(string $provider, array $remoteData): array
    {
        switch ($provider) {
            case 'stripe':
                return [
                    'status' => $this->mapStripeStatus($remoteData['status'] ?? ''),
                    'next_billing_at' => isset($remoteData['current_period_end']) 
                        ? date('Y-m-d H:i:s', $remoteData['current_period_end']) 
                        : null,
                    'ends_at' => isset($remoteData['ended_at']) 
                        ? date('Y-m-d H:i:s', $remoteData['ended_at']) 
                        : null,
                    'canceled_at' => isset($remoteData['canceled_at']) 
                        ? date('Y-m-d H:i:s', $remoteData['canceled_at']) 
                        : null,
                ];
                
            case 'paypal':
                return [
                    'status' => $this->mapPaypalStatus($remoteData['status'] ?? ''),
                    'next_billing_at' => $remoteData['billing_info']['next_billing_time'] ?? null,
                    'ends_at' => null, // PayPal ne fournit pas cette info directement
                    'canceled_at' => isset($remoteData['status_update_time']) && 
                                   in_array($remoteData['status'], ['CANCELLED', 'SUSPENDED']) 
                        ? $remoteData['status_update_time'] 
                        : null,
                ];
                
            default:
                return [];
        }
    }

    /**
     * Mappe le statut Stripe vers le statut local
     */
    protected function mapStripeStatus(string $stripeStatus): string
    {
        return match($stripeStatus) {
            'active' => 'active',
            'canceled' => 'canceled',
            'incomplete' => 'trial',
            'incomplete_expired' => 'expired',
            'past_due' => 'past_due',
            'unpaid' => 'suspended',
            'trialing' => 'trial',
            default => 'suspended'
        };
    }

    /**
     * Mappe le statut PayPal vers le statut local
     */
    protected function mapPaypalStatus(string $paypalStatus): string
    {
        return match($paypalStatus) {
            'ACTIVE' => 'active',
            'CANCELLED' => 'canceled',
            'SUSPENDED' => 'suspended',
            'EXPIRED' => 'expired',
            default => 'suspended'
        };
    }

    /**
     * Met à jour le tenant en fonction de l'abonnement
     */
    protected function updateTenantFromSubscription(Subscription $subscription): void
    {
        $tenant = $subscription->tenant;
        
        if (!$tenant) {
            return;
        }

        $tenantUpdates = [];

        // Mettre à jour le statut de facturation du tenant
        if ($subscription->status === 'active' && $tenant->billing_status !== 'active') {
            $tenantUpdates['billing_status'] = 'active';
        } elseif (in_array($subscription->status, ['canceled', 'expired']) && $tenant->billing_status === 'active') {
            $tenantUpdates['billing_status'] = $subscription->status;
        }

        // Mettre à jour la date de fin d'abonnement
        if ($subscription->ends_at && $tenant->subscription_ends_at !== $subscription->ends_at) {
            $tenantUpdates['subscription_ends_at'] = $subscription->ends_at;
        }

        if (!empty($tenantUpdates)) {
            $tenant->update($tenantUpdates);
        }
    }
}