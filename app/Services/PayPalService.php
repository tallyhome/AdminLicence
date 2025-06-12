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
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Subscriptions\SubscriptionsCreateRequest;
use PayPalCheckoutSdk\Subscriptions\SubscriptionsGetRequest;
use PayPalCheckoutSdk\Subscriptions\SubscriptionsCancelRequest;

class PayPalService
{
    protected $client;
    
    /**
     * Create a new PayPal service instance.
     */
    public function __construct()
    {
        // Initialiser PayPal seulement si les classes existent
        if (class_exists('\PayPalCheckoutSdk\Core\PayPalHttpClient')) {
            $environment = config('services.paypal.sandbox') 
                ? new SandboxEnvironment(config('services.paypal.client_id'), config('services.paypal.secret')) 
                : new ProductionEnvironment(config('services.paypal.client_id'), config('services.paypal.secret'));
            
            $this->client = new PayPalHttpClient($environment);
        }
    }
    
    /**
     * Create a new customer in PayPal.
     *
     * @param Tenant $tenant
     * @return string|null The PayPal customer ID
     */
    public function createCustomer(Tenant $tenant)
    {
        // PayPal doesn't have a direct customer creation API like Stripe
        // Instead, we'll use the tenant ID as a reference in metadata
        // when creating subscriptions or orders
        return $tenant->id;
    }
    
    /**
     * Create a payment method in PayPal.
     *
     * @param Tenant $tenant
     * @param string $paypalEmail
     * @return PaymentMethod|null
     */
    public function createPaymentMethod(Tenant $tenant, string $paypalEmail)
    {
        try {
            // Create a payment method record in the database
            $paymentMethod = new PaymentMethod([
                'tenant_id' => $tenant->id,
                'type' => PaymentMethod::TYPE_PAYPAL,
                'provider' => PaymentMethod::PROVIDER_PAYPAL,
                'provider_id' => null, // PayPal doesn't provide a specific ID for payment methods
                'paypal_email' => $paypalEmail,
                'is_default' => true,
            ]);
            
            // Set all other payment methods as non-default
            PaymentMethod::where('tenant_id', $tenant->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
            
            $paymentMethod->save();
            
            return $paymentMethod;
        } catch (Exception $e) {
            Log::error('Failed to create PayPal payment method: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a subscription in PayPal.
     *
     * @param Tenant $tenant
     * @param string $planId
     * @param PaymentMethod $paymentMethod
     * @param int $trialDays
     * @return Subscription|null
     */
    public function createSubscription(Tenant $tenant, string $planId, PaymentMethod $paymentMethod, int $trialDays = 0)
    {
        try {
            // Create the subscription request
            $request = new SubscriptionsCreateRequest();
            $request->prefer('return=representation');
            
            // Build the subscription object
            $subscriptionData = [
                'plan_id' => $planId,
                'subscriber' => [
                    'name' => [
                        'given_name' => $tenant->name,
                    ],
                    'email_address' => $paymentMethod->paypal_email,
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'payment_method' => [
                        'payer_selected' => 'PAYPAL',
                        'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                    ],
                ],
                'custom_id' => $tenant->id, // Store tenant ID as reference
            ];
            
            // Add trial period if specified
            if ($trialDays > 0) {
                $subscriptionData['start_time'] = now()->addDays($trialDays)->toIso8601String();
            }
            
            $request->body = $subscriptionData;
            
            // Call PayPal API to create subscription
            $response = $this->client->execute($request);
            
            // Create a subscription record in the database
            $subscription = new Subscription([
                'tenant_id' => $tenant->id,
                'name' => 'default',
                'stripe_id' => null, // Not using Stripe
                'stripe_status' => null, // Not using Stripe
                'stripe_price' => null, // Not using Stripe
                'quantity' => 1,
                'payment_method_id' => $paymentMethod->id,
                'payment_method_type' => PaymentMethod::PROVIDER_PAYPAL,
            ]);
            
            // Set PayPal specific fields in metadata
            $subscription->paypal_id = $response->result->id;
            $subscription->paypal_status = $response->result->status;
            $subscription->paypal_plan = $planId;
            
            if ($trialDays > 0) {
                $subscription->trial_ends_at = now()->addDays($trialDays);
            }
            
            $subscription->save();
            
            // Update tenant subscription information
            $tenant->subscription_plan = $planId;
            $tenant->subscription_status = Tenant::SUBSCRIPTION_ACTIVE;
            if ($trialDays > 0) {
                $tenant->subscription_status = Tenant::SUBSCRIPTION_TRIAL;
                $tenant->trial_ends_at = now()->addDays($trialDays);
            }
            $tenant->save();
            
            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to create PayPal subscription: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cancel a subscription in PayPal.
     *
     * @param Subscription $subscription
     * @param bool $atPeriodEnd
     * @return bool
     */
    public function cancelSubscription(Subscription $subscription, bool $atPeriodEnd = true)
    {
        try {
            // Create the cancel subscription request
            $request = new SubscriptionsCancelRequest($subscription->paypal_id);
            $request->body = [
                'reason' => 'Canceled by customer',
            ];
            
            // Call PayPal API to cancel subscription
            $this->client->execute($request);
            
            // Update subscription in database
            if ($atPeriodEnd) {
                // Get subscription details to find the end of the current period
                $getRequest = new SubscriptionsGetRequest($subscription->paypal_id);
                $response = $this->client->execute($getRequest);
                
                // Set the ends_at date to the end of the current billing period
                $subscription->ends_at = now()->parse($response->result->billing_info->next_billing_time);
            } else {
                $subscription->ends_at = now();
            }
            
            $subscription->paypal_status = 'CANCELLED';
            $subscription->save();
            
            // Update tenant subscription information
            $tenant = $subscription->tenant;
            $tenant->subscription_status = Tenant::SUBSCRIPTION_CANCELED;
            $tenant->save();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to cancel PayPal subscription: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process a webhook event from PayPal.
     *
     * @param string $payload
     * @param array $headers
     * @return bool
     */
    public function handleWebhook(string $payload, array $headers)
    {
        try {
            $data = json_decode($payload, true);
            $eventType = $data['event_type'] ?? null;
            
            if (!$eventType) {
                return false;
            }
            
            // Verify webhook signature (implementation depends on PayPal SDK)
            // This is a simplified version
            
            switch ($eventType) {
                case 'PAYMENT.SALE.COMPLETED':
                    return $this->handlePaymentCompleted($data);
                    
                case 'BILLING.SUBSCRIPTION.CREATED':
                    return $this->handleSubscriptionCreated($data);
                    
                case 'BILLING.SUBSCRIPTION.UPDATED':
                    return $this->handleSubscriptionUpdated($data);
                    
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    return $this->handleSubscriptionCancelled($data);
                    
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    return $this->handleSubscriptionExpired($data);
            }
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to process PayPal webhook: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the PAYMENT.SALE.COMPLETED event from PayPal.
     *
     * @param array $data
     * @return bool
     */
    protected function handlePaymentCompleted($data)
    {
        try {
            $resource = $data['resource'] ?? [];
            $subscriptionId = $resource['billing_agreement_id'] ?? null;
            
            if (!$subscriptionId) {
                return false;
            }
            
            // Find the subscription
            $subscription = Subscription::where('paypal_id', $subscriptionId)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Create or update the invoice
            $invoice = Invoice::updateOrCreate(
                ['provider_id' => $resource['id'], 'provider' => Invoice::PROVIDER_PAYPAL],
                [
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'number' => $resource['id'], // PayPal transaction ID as invoice number
                    'total' => $resource['amount']['total'] * 100, // Convert to cents
                    'currency' => $resource['amount']['currency'],
                    'status' => Invoice::STATUS_PAID,
                    'billing_reason' => 'subscription_cycle',
                    'payment_method_id' => $subscription->payment_method_id,
                    'payment_method_type' => PaymentMethod::PROVIDER_PAYPAL,
                    'paid_at' => now(),
                    'due_at' => now(),
                ]
            );
            
            // Create invoice item
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Subscription payment',
                'amount' => $resource['amount']['total'] * 100, // Convert to cents
                'currency' => $resource['amount']['currency'],
                'quantity' => 1,
                'period_start' => now(),
                'period_end' => now()->addMonth(), // Assuming monthly billing
                'type' => InvoiceItem::TYPE_SUBSCRIPTION,
            ]);
            
            // Envoyer une notification pour le nouveau paiement
            app(WebSocketService::class)->notifyNewPayment($invoice);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle PAYMENT.SALE.COMPLETED: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the BILLING.SUBSCRIPTION.CREATED event from PayPal.
     *
     * @param array $data
     * @return bool
     */
    protected function handleSubscriptionCreated($data)
    {
        // This is typically handled during the createSubscription method
        // But we can use this to verify and update if needed
        return true;
    }
    
    /**
     * Handle the BILLING.SUBSCRIPTION.UPDATED event from PayPal.
     *
     * @param array $data
     * @return bool
     */
    protected function handleSubscriptionUpdated($data)
    {
        try {
            $resource = $data['resource'] ?? [];
            $subscriptionId = $resource['id'] ?? null;
            
            if (!$subscriptionId) {
                return false;
            }
            
            // Find the subscription
            $subscription = Subscription::where('paypal_id', $subscriptionId)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Update the subscription
            $subscription->paypal_status = $resource['status'];
            $subscription->save();
            
            // Update tenant subscription information
            $tenant = $subscription->tenant;
            
            if ($resource['status'] === 'ACTIVE') {
                $tenant->subscription_status = Tenant::SUBSCRIPTION_ACTIVE;
            } elseif ($resource['status'] === 'SUSPENDED') {
                $tenant->subscription_status = Tenant::SUBSCRIPTION_CANCELED;
            }
            
            $tenant->save();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle BILLING.SUBSCRIPTION.UPDATED: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the BILLING.SUBSCRIPTION.CANCELLED event from PayPal.
     *
     * @param array $data
     * @return bool
     */
    protected function handleSubscriptionCancelled($data)
    {
        try {
            $resource = $data['resource'] ?? [];
            $subscriptionId = $resource['id'] ?? null;
            
            if (!$subscriptionId) {
                return false;
            }
            
            // Find the subscription
            $subscription = Subscription::where('paypal_id', $subscriptionId)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Update the subscription
            $subscription->paypal_status = 'CANCELLED';
            $subscription->ends_at = now();
            $subscription->save();
            
            // Update tenant subscription information
            $tenant = $subscription->tenant;
            $tenant->subscription_status = Tenant::SUBSCRIPTION_CANCELED;
            $tenant->save();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle BILLING.SUBSCRIPTION.CANCELLED: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle the BILLING.SUBSCRIPTION.EXPIRED event from PayPal.
     *
     * @param array $data
     * @return bool
     */
    protected function handleSubscriptionExpired($data)
    {
        try {
            $resource = $data['resource'] ?? [];
            $subscriptionId = $resource['id'] ?? null;
            
            if (!$subscriptionId) {
                return false;
            }
            
            // Find the subscription
            $subscription = Subscription::where('paypal_id', $subscriptionId)->first();
            
            if (!$subscription) {
                return false;
            }
            
            // Update the subscription
            $subscription->paypal_status = 'EXPIRED';
            $subscription->ends_at = now();
            $subscription->save();
            
            // Update tenant subscription information
            $tenant = $subscription->tenant;
            $tenant->subscription_status = Tenant::SUBSCRIPTION_EXPIRED;
            $tenant->save();
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to handle BILLING.SUBSCRIPTION.EXPIRED: ' . $e->getMessage());
            return false;
        }
    }
}