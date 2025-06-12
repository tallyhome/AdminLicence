<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Notifications\SubscriptionExpiringNotification;
use App\Notifications\PaymentFailedNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ProcessBillingNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Début du traitement des notifications de facturation');

        try {
            // 1. Notifications d'expiration d'abonnements
            $this->processSubscriptionExpiringNotifications();

            // 2. Notifications d'expiration d'essais
            $this->processTrialExpiringNotifications();

            // 3. Notifications de paiements échoués (optionnel, généralement géré par les webhooks)
            // $this->processPaymentFailedNotifications();

            Log::info('Traitement des notifications de facturation terminé');
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement des notifications de facturation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Traite les notifications d'expiration d'abonnements
     */
    protected function processSubscriptionExpiringNotifications(): void
    {
        if (!config('billing.notifications.subscription_ending.enabled', true)) {
            return;
        }

        $daysBefore = config('billing.notifications.subscription_ending.days_before', [30, 7, 1]);
        
        foreach ($daysBefore as $days) {
            $targetDate = now()->addDays($days)->startOfDay();
            
            $subscriptions = Subscription::where('status', 'active')
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [
                    $targetDate,
                    $targetDate->copy()->endOfDay()
                ])
                ->with(['tenant'])
                ->get();

            foreach ($subscriptions as $subscription) {
                $this->sendSubscriptionExpiringNotification($subscription, $days);
            }

            Log::info("Notifications d'expiration d'abonnements envoyées", [
                'days_before' => $days,
                'count' => $subscriptions->count()
            ]);
        }
    }

    /**
     * Traite les notifications d'expiration d'essais
     */
    protected function processTrialExpiringNotifications(): void
    {
        if (!config('billing.notifications.trial_ending.enabled', true)) {
            return;
        }

        $daysBefore = config('billing.notifications.trial_ending.days_before', [7, 3, 1]);
        
        foreach ($daysBefore as $days) {
            $targetDate = now()->addDays($days)->startOfDay();
            
            // Rechercher les tenants avec des essais qui expirent
            $tenants = Tenant::where('billing_status', 'trial')
                ->whereNotNull('trial_ends_at')
                ->whereBetween('trial_ends_at', [
                    $targetDate,
                    $targetDate->copy()->endOfDay()
                ])
                ->get();

            foreach ($tenants as $tenant) {
                $this->sendTrialExpiringNotification($tenant, $days);
            }

            Log::info("Notifications d'expiration d'essais envoyées", [
                'days_before' => $days,
                'count' => $tenants->count()
            ]);
        }
    }

    /**
     * Envoie une notification d'expiration d'abonnement
     */
    protected function sendSubscriptionExpiringNotification(Subscription $subscription, int $days): void
    {
        try {
            $tenant = $subscription->tenant;
            
            if (!$tenant) {
                Log::warning('Tenant introuvable pour l\'abonnement', ['subscription_id' => $subscription->id]);
                return;
            }

            // Vérifier si une notification similaire n'a pas déjà été envoyée récemment
            if ($this->hasRecentNotification($tenant, 'subscription_expiring', $days)) {
                return;
            }

            // Déterminer qui notifier
            $notifiables = $this->getNotifiables($tenant);
            
            foreach ($notifiables as $notifiable) {
                $notifiable->notify(new SubscriptionExpiringNotification($subscription, $days, false));
            }

            Log::info('Notification d\'expiration d\'abonnement envoyée', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'days_before' => $days
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de notification d\'expiration d\'abonnement', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoie une notification d'expiration d'essai
     */
    protected function sendTrialExpiringNotification(Tenant $tenant, int $days): void
    {
        try {
            // Vérifier si une notification similaire n'a pas déjà été envoyée récemment
            if ($this->hasRecentNotification($tenant, 'trial_expiring', $days)) {
                return;
            }

            // Créer un abonnement fictif pour la notification
            $fakeSubscription = new Subscription([
                'tenant_id' => $tenant->id,
                'plan_id' => $tenant->current_plan ?? 'trial',
                'plan_name' => 'Période d\'essai',
                'trial_ends_at' => $tenant->trial_ends_at,
                'provider' => 'trial'
            ]);
            $fakeSubscription->tenant = $tenant;

            // Déterminer qui notifier
            $notifiables = $this->getNotifiables($tenant);
            
            foreach ($notifiables as $notifiable) {
                $notifiable->notify(new SubscriptionExpiringNotification($fakeSubscription, $days, true));
            }

            Log::info('Notification d\'expiration d\'essai envoyée', [
                'tenant_id' => $tenant->id,
                'days_before' => $days
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de notification d\'expiration d\'essai', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Vérifie si une notification similaire a été envoyée récemment
     */
    protected function hasRecentNotification(Tenant $tenant, string $type, int $days): bool
    {
        // Vérifier dans les notifications de la base de données
        $recentNotification = $tenant->notifications()
            ->where('type', 'App\\Notifications\\SubscriptionExpiringNotification')
            ->where('created_at', '>', now()->subHours(12)) // Éviter les doublons dans les 12h
            ->whereJsonContains('data->type', $type)
            ->whereJsonContains('data->days_until_expiration', $days)
            ->exists();

        return $recentNotification;
    }

    /**
     * Détermine qui doit recevoir les notifications
     */
    protected function getNotifiables(Tenant $tenant): array
    {
        $notifiables = [];

        // Ajouter le tenant lui-même (si il implémente Notifiable)
        if (method_exists($tenant, 'notify')) {
            $notifiables[] = $tenant;
        }

        // Ajouter les utilisateurs administrateurs du tenant
        if ($tenant->users) {
            $adminUsers = $tenant->users()->where('role', 'admin')->get();
            $notifiables = array_merge($notifiables, $adminUsers->toArray());
        }

        // Si aucun utilisateur trouvé, utiliser l'email de facturation
        if (empty($notifiables) && $tenant->billing_email) {
            // Créer une notification anonyme vers l'email de facturation
            Notification::route('mail', $tenant->billing_email)
                ->notify(new SubscriptionExpiringNotification($subscription ?? $fakeSubscription, $days, $isTrialExpiring ?? false));
        }

        return $notifiables;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Échec du job de traitement des notifications de facturation', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}