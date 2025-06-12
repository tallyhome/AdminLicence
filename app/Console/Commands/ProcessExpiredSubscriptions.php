<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Events\SubscriptionExpired;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:process-expired
                            {--dry-run : Afficher les actions sans les exécuter}
                            {--force : Forcer le traitement même en mode non-SaaS}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Traite les abonnements expirés et suspend/supprime les tenants';

    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        parent::__construct();
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
        
        if ($dryRun) {
            $this->info('Mode simulation activé - aucune action ne sera effectuée');
        }

        $this->info('Début du traitement des abonnements expirés...');

        // 1. Traiter les abonnements expirés
        $this->processExpiredSubscriptions($dryRun);

        // 2. Traiter les tenants en période de grâce
        $this->processGracePeriodTenants($dryRun);

        // 3. Traiter les tenants à supprimer
        $this->processTenantsToDelete($dryRun);

        // 4. Traiter les essais expirés
        $this->processExpiredTrials($dryRun);

        $this->info('Traitement terminé.');
        return 0;
    }

    /**
     * Traite les abonnements expirés
     */
    protected function processExpiredSubscriptions(bool $dryRun): void
    {
        $this->info('\n--- Traitement des abonnements expirés ---');

        $expiredSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->with('tenant')
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('Aucun abonnement expiré trouvé.');
            return;
        }

        $this->info("Abonnements expirés trouvés : {$expiredSubscriptions->count()}");

        foreach ($expiredSubscriptions as $subscription) {
            $tenant = $subscription->tenant;
            
            $this->line("- Tenant: {$tenant->name} (ID: {$tenant->id}) - Plan: {$subscription->plan_id}");

            if (!$dryRun) {
                // Marquer l'abonnement comme expiré
                $subscription->update([
                    'status' => 'expired',
                    'canceled_at' => now()
                ]);

                // Mettre le tenant en période de grâce
                $gracePeriodDays = config('billing.grace_period_days', 7);
                $tenant->update([
                    'billing_status' => 'expired',
                    'grace_period_ends_at' => now()->addDays($gracePeriodDays)
                ]);

                // Émettre l'événement
                event(new SubscriptionExpired($subscription));

                Log::info("Abonnement expiré traité", [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                    'grace_period_ends_at' => $tenant->grace_period_ends_at
                ]);
            }
        }
    }

    /**
     * Traite les tenants en fin de période de grâce
     */
    protected function processGracePeriodTenants(bool $dryRun): void
    {
        $this->info('\n--- Traitement des tenants en fin de période de grâce ---');

        $tenantsToSuspend = Tenant::where('billing_status', 'expired')
            ->where('grace_period_ends_at', '<', now())
            ->get();

        if ($tenantsToSuspend->isEmpty()) {
            $this->info('Aucun tenant à suspendre trouvé.');
            return;
        }

        $this->info("Tenants à suspendre : {$tenantsToSuspend->count()}");

        foreach ($tenantsToSuspend as $tenant) {
            $this->line("- Suspension: {$tenant->name} (ID: {$tenant->id})");

            if (!$dryRun) {
                $autoDeleteDays = config('billing.auto_delete_after_days', 30);
                
                $tenant->update([
                    'billing_status' => 'suspended',
                    'status' => 'suspended',
                    'suspended_at' => now(),
                    'grace_period_ends_at' => now()->addDays($autoDeleteDays) // Réutiliser pour la suppression
                ]);

                Log::info("Tenant suspendu", [
                    'tenant_id' => $tenant->id,
                    'suspended_at' => now(),
                    'delete_at' => $tenant->grace_period_ends_at
                ]);
            }
        }
    }

    /**
     * Traite les tenants à supprimer
     */
    protected function processTenantsToDelete(bool $dryRun): void
    {
        $this->info('\n--- Traitement des tenants à supprimer ---');

        $tenantsToDelete = Tenant::where('billing_status', 'suspended')
            ->where('grace_period_ends_at', '<', now())
            ->get();

        if ($tenantsToDelete->isEmpty()) {
            $this->info('Aucun tenant à supprimer trouvé.');
            return;
        }

        $this->info("Tenants à supprimer : {$tenantsToDelete->count()}");

        foreach ($tenantsToDelete as $tenant) {
            $this->line("- Suppression: {$tenant->name} (ID: {$tenant->id})");

            if (!$dryRun) {
                // Marquer comme supprimé au lieu de supprimer physiquement
                $tenant->update([
                    'billing_status' => 'deleted',
                    'status' => 'deleted',
                    'deleted_at' => now()
                ]);

                // Optionnel : supprimer physiquement après confirmation
                // $tenant->delete();

                Log::info("Tenant marqué comme supprimé", [
                    'tenant_id' => $tenant->id,
                    'deleted_at' => now()
                ]);
            }
        }
    }

    /**
     * Traite les essais expirés
     */
    protected function processExpiredTrials(bool $dryRun): void
    {
        $this->info('\n--- Traitement des essais expirés ---');

        $expiredTrials = Tenant::where('billing_status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get();

        if ($expiredTrials->isEmpty()) {
            $this->info('Aucun essai expiré trouvé.');
            return;
        }

        $this->info("Essais expirés : {$expiredTrials->count()}");

        foreach ($expiredTrials as $tenant) {
            $this->line("- Essai expiré: {$tenant->name} (ID: {$tenant->id})");

            if (!$dryRun) {
                $gracePeriodDays = config('billing.grace_period_days', 7);
                
                $tenant->update([
                    'billing_status' => 'expired',
                    'grace_period_ends_at' => now()->addDays($gracePeriodDays)
                ]);

                Log::info("Essai expiré traité", [
                    'tenant_id' => $tenant->id,
                    'grace_period_ends_at' => $tenant->grace_period_ends_at
                ]);
            }
        }
    }
}