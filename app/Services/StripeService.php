<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentMethod;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\WebSocketService;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeService
{
    protected $stripe;
    protected $webSocketService;
    
    /**
     * Create a new Stripe service instance.
     */
    public function __construct(WebSocketService $webSocketService)
    {
        // Initialiser Stripe seulement si la classe existe
        if (class_exists('\Stripe\StripeClient')) {
            $this->stripe = new StripeClient(config('services.stripe.secret'));
        }
        $this->webSocketService = $webSocketService;
    }
    
    /**
     * Create a new customer in Stripe.
     *
     * @param Tenant $tenant
     * @return string|null The Stripe customer ID
     */
    public function createCustomer(Tenant $tenant)
    {
        try {
            $customer = $this->stripe->customers->create([
                'name' => $tenant->name,
                'email' => $tenant->clients()->first()->email ?? null,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                ],
                'invoice_settings' => [
                    'default_payment_method' => $tenant->defaultPaymentMethod?->provider_id
                ]
            ]);
            
            // Créer une configuration de facturation automatique
            $this->stripe->subscriptionSchedules->create([
                'customer' => $customer->id,
                'start_date' => 'now',
                'end_behavior' => 'release',
                'phases' => [
                    [
                        'collection_method' => 'charge_automatically',
                        'invoice_settings' => [
                            'days_until_due' => 30
                        ]
                    ]
                ]
            ]);
            
            return $customer->id;
        } catch (Exception $e) {
            Log::error('Failed to create Stripe customer: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a payment method in Stripe.
     *
     * @param Tenant $tenant
     * @param string $paymentMethodId
     * @return PaymentMethod|null
     */
    public function createPaymentMethod(Tenant $tenant, string $paymentMethodId)
    {
        try {
            // Retrieve the payment method from Stripe
            $stripePaymentMethod = $this->stripe->paymentMethods->retrieve($paymentMethodId);
            
            // Attach the payment method to the customer
            $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $tenant->stripe_customer_id,
            ]);
            
            // Create a payment method record in the database
            $paymentMethod = new PaymentMethod([
                'tenant_id' => $tenant->id,
                'type' => $stripePaymentMethod->type,
                'provider' => PaymentMethod::PROVIDER_STRIPE,
                'provider_id' => $stripePaymentMethod->id,
                'is_default' => true,
            ]);
            
            // If it's a card, add card details
            if ($stripePaymentMethod->type === 'card') {
                $paymentMethod->card_brand = $stripePaymentMethod->card->brand;
                $paymentMethod->card_last_four = $stripePaymentMethod->card->last4;
                $paymentMethod->expires_at = now()->addMonths(
                    ($stripePaymentMethod->card->exp_year - now()->year) * 12 + 
                    $stripePaymentMethod->card->exp_month - now()->month
                );
            }
            
            // Set all other payment methods as non-default
            PaymentMethod::where('tenant_id', $tenant->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
            
            $paymentMethod->save();
            
            return $paymentMethod;
        } catch (Exception $e) {
            Log::error('Failed to create payment method: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a subscription in Stripe.
     *
     * @param Tenant $tenant
     * @param string $priceId
     * @param PaymentMethod $paymentMethod
     * @param int $trialDays
     * @return Subscription|null
     */
    public function createSubscription(Tenant $tenant, string $priceId, PaymentMethod $paymentMethod, int $trialDays = 0)
    {
        try {
            // Create the subscription in Stripe
            $stripeSubscription = $this->stripe->subscriptions->create([
                'customer' => $tenant->stripe_customer_id,
                'items' => [['price' => $priceId]],
                'default_payment_method' => $paymentMethod->provider_id,
                'trial_period_days' => $trialDays,
                'metadata' => [
                    'tenant_id' => $tenant->id,
                ],
            ]);
            
            // Create a subscription record in the database
            $subscription = new Subscription([
                'tenant_id' => $tenant->id,
                'name' => 'default',
                'stripe_id' => $stripeSubscription->id,
                'stripe_status' => $stripeSubscription->status,
                'stripe_price' => $priceId,
                'quantity' => 1,
                'payment_method_id' => $paymentMethod->id,
                'payment_method_type' => PaymentMethod::PROVIDER_STRIPE,
            ]);
            
            if ($trialDays > 0) {
                $subscription->trial_ends_at = now()->addDays($trialDays);
            }
            
            $subscription->save();
            
            // Update tenant subscription information
            $tenant->subscription_plan = $priceId;
            $tenant->subscription_status = Tenant::SUBSCRIPTION_ACTIVE;
            if ($trialDays > 0) {
                $tenant->subscription_status = Tenant::SUBSCRIPTION_TRIAL;
                $tenant->trial_ends_at = now()->addDays($trialDays);
            }
            $tenant->save();
            
            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to create subscription: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cancel a subscription in Stripe.
     *
     * @param Subscription $subscription
     * @param bool $atPeriodEnd
     * @return bool
     */
    public function cancelSubscription(Subscription $subscription, bool $atPeriodEnd = true)
    {
        try {
            if ($atPeriodEnd) {
                $this->stripe->subscriptions->update($subscription->stripe_id, [
                    'cancel_at_period_end' => true,
                ]);
                
                // Set the ends_at date to the end of the current period
                $stripeSubscription = $this->stripe->subscriptions->retrieve($subscription->stripe_id);
                $subscription->ends_at = now()->timestamp($stripeSubscription->current_period_end);
            } else {
                $this->stripe->subscriptions->cancel($subscription->stripe_id);
                $subscription->ends_at = now();
            }
            
            $subscription->save();
            
            // Update tenant subscription information
            $tenant = $subscription->tenant;
            $tenant->subscription_status = Tenant::SUBSCRIPTION_CANCELED;
            $tenant->save();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to cancel subscription: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process a webhook event from Stripe.
     *
     * @param string $payload
     * @param string $sigHeader
     * @return bool
     */
    public function handleWebhook(string $payload, string $sigHeader)
    {
        try {
            $event = $this->stripe->webhooks->constructEvent(
                $payload, $sigHeader, config('services.stripe.webhook_secret')
            );
            
            switch ($event->type) {
                case 'invoice.payment_succeeded':
                    return $this->handleInvoicePaymentSucceeded($event->data->object);
                    
                case 'invoice.payment_failed':
                    return $this->handleInvoicePaymentFailed($event->data->object);
                    
                case 'customer.subscription.updated':
                    return $this->handleSubscriptionUpdated($event->data->object);
                    
                case 'customer.subscription.deleted':
                    return $this->handleSubscriptionDeleted($event->data->object);
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to process Stripe webhook: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the invoice.payment_succeeded event from Stripe.
     *
     * @param object $invoiceData
     * @return bool
     */
    protected function handleInvoicePaymentSucceeded($invoiceData)
    {
        try {
            // Find the subscription
            $subscription = Subscription::where('stripe_id', $invoiceData->subscription)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Create or update the invoice
            $invoice = Invoice::updateOrCreate(
                ['provider_id' => $invoiceData->id, 'provider' => Invoice::PROVIDER_STRIPE],
                [
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'number' => $invoiceData->number,
                    'total' => $invoiceData->total,
                    'currency' => $invoiceData->currency,
                    'status' => Invoice::STATUS_PAID,
                    'billing_reason' => $invoiceData->billing_reason,
                    'payment_method_id' => $subscription->payment_method_id,
                    'payment_method_type' => PaymentMethod::PROVIDER_STRIPE,
                    'paid_at' => now(),
                    'due_at' => now()->timestamp($invoiceData->due_date),
                ]
            );
            
            // Create invoice items
            foreach ($invoiceData->lines->data as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item->description,
                    'amount' => $item->amount,
                    'currency' => $invoiceData->currency,
                    'quantity' => $item->quantity,
                    'period_start' => now()->timestamp($item->period->start),
                    'period_end' => now()->timestamp($item->period->end),
                    'type' => $item->type === 'subscription' ? InvoiceItem::TYPE_SUBSCRIPTION : InvoiceItem::TYPE_INVOICE_ITEM,
                ]);
            }
            
            // Envoyer une notification pour le nouveau paiement
            app(WebSocketService::class)->notifyNewPayment($invoice);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle invoice.payment_succeeded: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the invoice.payment_failed event from Stripe.
     *
     * @param object $invoiceData
     * @return bool
     */
    protected function handleInvoicePaymentFailed($invoiceData)
    {
        try {
            // Find the subscription
            $subscription = Subscription::where('stripe_id', $invoiceData->subscription)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Create or update the invoice
            $invoice = Invoice::updateOrCreate(
                ['provider_id' => $invoiceData->id, 'provider' => Invoice::PROVIDER_STRIPE],
                [
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'number' => $invoiceData->number,
                    'total' => $invoiceData->total,
                    'currency' => $invoiceData->currency,
                    'status' => Invoice::STATUS_OPEN,
                    'billing_reason' => $invoiceData->billing_reason,
                    'payment_method_id' => $subscription->payment_method_id,
                    'payment_method_type' => PaymentMethod::PROVIDER_STRIPE,
                    'due_at' => now()->timestamp($invoiceData->due_date),
                ]
            );
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle invoice.payment_failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the customer.subscription.updated event from Stripe.
     *
     * @param object $subscriptionData
     * @return bool
     */
    protected function handleSubscriptionUpdated($subscriptionData)
    {
        try {
            // Find the subscription
            $subscription = Subscription::where('stripe_id', $subscriptionData->id)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Update the subscription
            $subscription->stripe_status = $subscriptionData->status;
            
            if ($subscriptionData->cancel_at_period_end) {
                $subscription->ends_at = now()->timestamp($subscriptionData->current_period_end);
            } elseif ($subscription->ends_at) {
                $subscription->ends_at = null;
            }
            
            $subscription->save();
            
            // Update tenant subscription information
            $tenant = $subscription->tenant;
            
            if ($subscriptionData->status === 'active') {
                $tenant->subscription_status = Tenant::SUBSCRIPTION_ACTIVE;
            } elseif ($subscriptionData->status === 'canceled') {
                $tenant->subscription_status = Tenant::SUBSCRIPTION_CANCELED;
            } elseif ($subscriptionData->status === 'trialing') {
                $tenant->subscription_status = Tenant::SUBSCRIPTION_TRIAL;
                $tenant->trial_ends_at = now()->timestamp($subscriptionData->trial_end);
            }
            
            $tenant->save();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle customer.subscription.updated: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the customer.subscription.deleted event from Stripe.
     *
     * @param object $subscriptionData
     * @return bool
     */
    protected function handleSubscriptionDeleted($subscriptionData)
    {
        try {
            // Find the subscription
            $subscription = Subscription::where('stripe_id', $subscriptionData->id)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Update the subscription
            $subscription->stripe_status = 'canceled';
            $subscription->ends_at = now();
            $subscription->save();
            
            // Update tenant subscription information
            $tenant = $subscription->tenant;
            $tenant->subscription_status = Tenant::SUBSCRIPTION_EXPIRED;
            $tenant->save();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle customer.subscription.deleted: ' . $e->getMessage());
            return false;
        }
    }
}