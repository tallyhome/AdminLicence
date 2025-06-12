<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Subscription;
use App\Models\Invoice;

class PaymentFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subscription;
    protected $invoice;
    protected $attemptNumber;
    protected $nextAttemptDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        Subscription $subscription, 
        ?Invoice $invoice = null, 
        int $attemptNumber = 1,
        ?\DateTime $nextAttemptDate = null
    ) {
        $this->subscription = $subscription;
        $this->invoice = $invoice;
        $this->attemptNumber = $attemptNumber;
        $this->nextAttemptDate = $nextAttemptDate;
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
        $amount = $this->invoice ? $this->invoice->total_amount : $this->subscription->amount;
        $currency = $this->invoice ? $this->invoice->currency : $this->subscription->currency;
        
        $mailMessage = (new MailMessage)
            ->subject('Échec de paiement - Action requise')
            ->greeting("Bonjour {$tenant->name},")
            ->line('Nous n\'avons pas pu traiter votre paiement pour votre abonnement.')
            ->line("**Détails de l'abonnement :**")
            ->line("- Plan : {$this->subscription->plan_name}")
            ->line("- Montant : {$amount} {$currency}")
            ->line("- Tentative : {$this->attemptNumber}");

        if ($this->invoice) {
            $mailMessage->line("- Facture : {$this->invoice->invoice_number}");
        }

        if ($this->nextAttemptDate) {
            $mailMessage->line("- Prochaine tentative : {$this->nextAttemptDate->format('d/m/Y à H:i')}");
        }

        $mailMessage->line('**Actions à effectuer :**')
            ->line('1. Vérifiez que votre méthode de paiement est valide')
            ->line('2. Assurez-vous que votre compte dispose de fonds suffisants')
            ->line('3. Mettez à jour vos informations de paiement si nécessaire');

        // Lien vers le portail de gestion selon le fournisseur
        if ($this->subscription->provider === 'stripe') {
            $mailMessage->action('Gérer mon abonnement', $this->getStripePortalUrl());
        } elseif ($this->subscription->provider === 'paypal') {
            $mailMessage->action('Gérer mon abonnement', $this->getPaypalPortalUrl());
        }

        $mailMessage->line('Si le problème persiste, votre abonnement pourrait être suspendu.')
            ->line('Pour toute question, n\'hésitez pas à nous contacter.')
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
        return [
            'type' => 'payment_failed',
            'subscription_id' => $this->subscription->id,
            'invoice_id' => $this->invoice?->id,
            'plan_name' => $this->subscription->plan_name,
            'amount' => $this->invoice ? $this->invoice->total_amount : $this->subscription->amount,
            'currency' => $this->invoice ? $this->invoice->currency : $this->subscription->currency,
            'attempt_number' => $this->attemptNumber,
            'next_attempt_date' => $this->nextAttemptDate?->format('Y-m-d H:i:s'),
            'provider' => $this->subscription->provider,
            'tenant_id' => $this->subscription->tenant_id,
            'message' => "Échec de paiement pour l'abonnement {$this->subscription->plan_name} (tentative {$this->attemptNumber})"
        ];
    }

    /**
     * Get the Stripe portal URL
     */
    protected function getStripePortalUrl(): string
    {
        // URL vers le portail client Stripe
        // Cette URL devrait être générée dynamiquement via l'API Stripe
        return route('billing.manage') . '?provider=stripe';
    }

    /**
     * Get the PayPal portal URL
     */
    protected function getPaypalPortalUrl(): string
    {
        // URL vers le portail PayPal
        return route('billing.manage') . '?provider=paypal';
    }
}