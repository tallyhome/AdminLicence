<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;
    protected $daysUntilExpiration;
    protected $isTrialExpiring;

    /**
     * Create a new notification instance.
     */
    public function __construct(Subscription $subscription, int $daysUntilExpiration, bool $isTrialExpiring = false)
    {
        $this->subscription = $subscription;
        $this->daysUntilExpiration = $daysUntilExpiration;
        $this->isTrialExpiring = $isTrialExpiring;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tenant = $this->subscription->tenant;
        $expirationDate = $this->isTrialExpiring 
            ? $this->subscription->trial_ends_at 
            : $this->subscription->ends_at;
        
        $subject = $this->isTrialExpiring 
            ? 'Votre période d\'essai expire bientôt'
            : 'Votre abonnement expire bientôt';
            
        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting("Bonjour {$tenant->name},");

        if ($this->isTrialExpiring) {
            $mailMessage->line('Votre période d\'essai gratuite arrive à expiration.')
                ->line("**Détails de votre essai :**")
                ->line("- Plan : {$this->subscription->plan_name}")
                ->line("- Expire le : {$expirationDate->format('d/m/Y à H:i')}")
                ->line("- Jours restants : {$this->daysUntilExpiration}")
                ->line('')
                ->line('**Pour continuer à utiliser nos services :**')
                ->line('1. Choisissez un plan d\'abonnement')
                ->line('2. Configurez votre méthode de paiement')
                ->line('3. Activez votre abonnement avant la fin de l\'essai');
        } else {
            $mailMessage->line('Votre abonnement arrive à expiration.')
                ->line("**Détails de votre abonnement :**")
                ->line("- Plan : {$this->subscription->plan_name}")
                ->line("- Montant : {$this->subscription->amount} {$this->subscription->currency}")
                ->line("- Expire le : {$expirationDate->format('d/m/Y à H:i')}")
                ->line("- Jours restants : {$this->daysUntilExpiration}")
                ->line('')
                ->line('**Pour renouveler votre abonnement :**')
                ->line('1. Vérifiez votre méthode de paiement')
                ->line('2. Assurez-vous que le renouvellement automatique est activé')
                ->line('3. Contactez-nous si vous rencontrez des difficultés');
        }

        // Urgence selon le nombre de jours restants
        if ($this->daysUntilExpiration <= 1) {
            $mailMessage->line('⚠️ **URGENT** : Action requise dans les 24 heures !');
        } elseif ($this->daysUntilExpiration <= 3) {
            $mailMessage->line('⚠️ **IMPORTANT** : Action requise rapidement.');
        }

        // Lien vers le portail de gestion
        $actionText = $this->isTrialExpiring ? 'Choisir un plan' : 'Gérer mon abonnement';
        
        if ($this->subscription->provider === 'stripe') {
            $mailMessage->action($actionText, $this->getStripePortalUrl());
        } elseif ($this->subscription->provider === 'paypal') {
            $mailMessage->action($actionText, $this->getPaypalPortalUrl());
        } else {
            $mailMessage->action($actionText, route('billing.manage'));
        }

        $mailMessage->line('Merci de votre confiance.')
            ->salutation('L\'équipe ' . config('app.name'));

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $expirationDate = $this->isTrialExpiring 
            ? $this->subscription->trial_ends_at 
            : $this->subscription->ends_at;
            
        return [
            'type' => $this->isTrialExpiring ? 'trial_expiring' : 'subscription_expiring',
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan_name,
            'days_until_expiration' => $this->daysUntilExpiration,
            'expiration_date' => $expirationDate->format('Y-m-d H:i:s'),
            'provider' => $this->subscription->provider,
            'tenant_id' => $this->subscription->tenant_id,
            'is_trial' => $this->isTrialExpiring,
            'urgency_level' => $this->getUrgencyLevel(),
            'message' => $this->getNotificationMessage()
        ];
    }

    /**
     * Get the urgency level based on days until expiration
     */
    protected function getUrgencyLevel(): string
    {
        if ($this->daysUntilExpiration <= 1) {
            return 'critical';
        } elseif ($this->daysUntilExpiration <= 3) {
            return 'high';
        } elseif ($this->daysUntilExpiration <= 7) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Get the notification message
     */
    protected function getNotificationMessage(): string
    {
        $type = $this->isTrialExpiring ? 'période d\'essai' : 'abonnement';
        $planName = $this->subscription->plan_name;
        
        if ($this->daysUntilExpiration <= 1) {
            return "Votre {$type} {$planName} expire dans moins de 24 heures !";
        } elseif ($this->daysUntilExpiration == 1) {
            return "Votre {$type} {$planName} expire demain.";
        } else {
            return "Votre {$type} {$planName} expire dans {$this->daysUntilExpiration} jours.";
        }
    }

    /**
     * Get the Stripe portal URL
     */
    protected function getStripePortalUrl(): string
    {
        return route('billing.manage') . '?provider=stripe';
    }

    /**
     * Get the PayPal portal URL
     */
    protected function getPaypalPortalUrl(): string
    {
        return route('billing.manage') . '?provider=paypal';
    }
}