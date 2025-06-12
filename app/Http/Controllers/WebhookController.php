<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Invoice;
use App\Events\NewPayment;
use App\Services\StripeService;
use App\Services\PaypalService;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected $stripeService;
    protected $paypalService;
    protected $billingService;

    public function __construct(
        StripeService $stripeService, 
        PaypalService $paypalService,
        BillingService $billingService
    ) {
        $this->stripeService = $stripeService;
        $this->paypalService = $paypalService;
        $this->billingService = $billingService;
    }

    /**
     * Gérer les webhooks Stripe (alias pour compatibilité avec les routes)
     */
    public function stripe(Request $request): Response
    {
        return $this->handleStripeWebhook($request);
    }

    /**
     * Gérer les webhooks PayPal (alias pour compatibilité avec les routes)
     */
    public function paypal(Request $request): Response
    {
        return $this->handlePayPalWebhook($request);
    }

    /**
     * Gérer les IPN PayPal
     */
    public function paypalIpn(Request $request): Response
    {
        // Les IPN PayPal utilisent un format différent
        try {
            $data = $request->all();
            Log::info('PayPal IPN reçu', $data);
            
            // Vérifier l'IPN avec PayPal
            if ($this->paypalService->verifyIpn($data)) {
                // Traiter l'IPN selon le type de transaction
                if (isset($data['txn_type'])) {
                    switch ($data['txn_type']) {
                        case 'subscr_payment':
                            return $this->handleSuccessfulPayment($data, 'paypal');
                        case 'subscr_cancel':
                        case 'subscr_eot':
                            return $this->handleSubscriptionCanceled($data, 'paypal');
                        case 'subscr_modify':
                            return $this->handleSubscriptionUpdated($data, 'paypal');
                    }
                }
            }
            
            return response('IPN traité', 200);
        } catch (\Exception $e) {
            Log::error('Erreur IPN PayPal: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Page de succès PayPal
     */
    public function paypalSuccess(Request $request)
    {
        $token = $request->get('token');
        $payerId = $request->get('PayerID');
        
        Log::info('Retour PayPal succès', ['token' => $token, 'payer_id' => $payerId]);
        
        return redirect()->route('admin.billing.index')
            ->with('success', 'Paiement PayPal traité avec succès');
    }

    /**
     * Page d\'annulation PayPal
     */
    public function paypalCancel(Request $request)
    {
        $token = $request->get('token');
        
        Log::info('Retour PayPal annulation', ['token' => $token]);
        
        return redirect()->route('admin.billing.index')
            ->with('warning', 'Paiement PayPal annulé');
    }

    /**
     * Endpoint de test pour les webhooks
     */
    public function test(Request $request)
    {
        Log::info('Test webhook reçu', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Test webhook reçu',
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Gérer les webhooks Stripe
     */
    public function handleStripeWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        
        if (empty($sigHeader)) {
            return response()->json(['error' => 'En-tête de signature Stripe manquant'], 400);
        }
        
        try {
            $event = $this->stripeService->constructEvent($payload, $sigHeader);
            
            switch ($event->type) {
                case 'invoice.payment_succeeded':
                    return $this->handleSuccessfulPayment($event->data->object, 'stripe');

                case 'customer.subscription.deleted':
                    return $this->handleSubscriptionCanceled($event->data->object, 'stripe');

                case 'customer.subscription.updated':
                    return $this->handleSubscriptionUpdated($event->data->object, 'stripe');

                default:
                    return response('Webhook traité', 200);
            }
        } catch (\Exception $e) {
            Log::error('Erreur webhook Stripe: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Gérer les webhooks PayPal
     */
    public function handlePayPalWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $headers = $request->headers->all();
        
        try {
            $event = $this->paypalService->verifyWebhookSignature($payload, $headers);
            
            switch ($event['event_type']) {
                case 'PAYMENT.SALE.COMPLETED':
                    return $this->handleSuccessfulPayment($event['resource'], 'paypal');

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                    return $this->handleSubscriptionCanceled($event['resource'], 'paypal');

                case 'BILLING.SUBSCRIPTION.UPDATED':
                    return $this->handleSubscriptionUpdated($event['resource'], 'paypal');

                default:
                    return response('Webhook traité', 200);
            }
        } catch (\Exception $e) {
            Log::error('Erreur webhook PayPal: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gérer un paiement réussi
     */
    protected function handleSuccessfulPayment($paymentData, string $provider): Response
    {
        $subscriptionId = $provider === 'stripe' 
            ? $paymentData['subscription']
            : $paymentData['billing_agreement_id'];

        $subscription = Subscription::where($provider . '_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            // Créer une nouvelle facture
            $invoice = Invoice::create([
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'amount' => $provider === 'stripe' ? $paymentData['amount_paid'] / 100 : $paymentData['amount']['total'],
                'payment_method' => $provider,
                'status' => 'paid',
                'provider_invoice_id' => $provider === 'stripe' ? $paymentData['id'] : $paymentData['id']
            ]);

            // Émettre l'événement de nouveau paiement
            event(new NewPayment($invoice));
        }

        return response('Webhook traité', 200);
    }

    /**
     * Gérer l'annulation d'un abonnement
     */
    protected function handleSubscriptionCanceled($subscriptionData, string $provider): Response
    {
        $subscriptionId = $provider === 'stripe'
            ? $subscriptionData['id']
            : $subscriptionData['id'];

        $subscription = Subscription::where($provider . '_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'auto_renew' => false
            ]);
        }

        return response('Webhook traité', 200);
    }

    /**
     * Gérer la mise à jour d'un abonnement
     */
    protected function handleSubscriptionUpdated($subscriptionData, string $provider): Response
    {
        $subscriptionId = $provider === 'stripe'
            ? $subscriptionData['id']
            : $subscriptionData['id'];

        $subscription = Subscription::where($provider . '_subscription_id', $subscriptionId)->first();

        if ($subscription) {
            $status = $provider === 'stripe'
                ? $subscriptionData['status']
                : $subscriptionData['status'];

            if ($status === 'active' && $subscription->status !== 'active') {
                $subscription->update([
                    'status' => 'active',
                    'canceled_at' => null,
                    'auto_renew' => true
                ]);
            }
        }

        return response('Webhook traité', 200);
    }
}