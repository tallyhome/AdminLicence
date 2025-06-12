<?php

namespace App\Http\Controllers;

use App\Models\SerialKey;
use App\Models\Tenant;
use App\Models\BillingPlan;
use App\Models\Invoice;
use App\Services\HistoryService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    protected $historyService;
    protected $paymentService;

    public function __construct(HistoryService $historyService, PaymentGatewayService $paymentService)
    {
        $this->historyService = $historyService;
        $this->paymentService = $paymentService;
        $this->middleware('auth');
        $this->middleware('tenant')->except(['plans', 'checkout']);
    }

    /**
     * Afficher les plans de facturation disponibles
     */
    public function plans()
    {
        // Récupérer les plans de facturation disponibles
        $plans = BillingPlan::where('active', true)
            ->orderBy('price', 'asc')
            ->get();
            
        return view('billing.plans', compact('plans'));
    }

    /**
     * Afficher la page de paiement pour un plan spécifique
     */
    public function checkout(Request $request, $planId)
    {
        $plan = BillingPlan::findOrFail($planId);
        
        // Récupérer les licences multi-comptes de l'utilisateur
        $user = Auth::user();
        $licences = SerialKey::where('licence_type', SerialKey::LICENCE_TYPE_MULTI)
            ->where('status', 'active')
            ->whereHas('tenants', function($query) use ($user) {
                $query->whereHas('users', function($q) use ($user) {
                    $q->where('users.id', $user->id)
                      ->where('tenant_user.role', 'admin');
                });
            })
            ->get();
            
        return view('billing.checkout', compact('plan', 'licences'));
    }

    /**
     * Traiter le paiement et mettre à jour l'abonnement
     */
    public function processPayment(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:billing_plans,id',
            'licence_id' => 'required|exists:serial_keys,id',
            'payment_method' => 'required|in:credit_card,paypal',
            'payment_token' => 'required|string',
        ]);
        
        $plan = BillingPlan::findOrFail($validated['plan_id']);
        $licence = SerialKey::findOrFail($validated['licence_id']);
        
        // Vérifier que l'utilisateur a accès à cette licence
        $user = Auth::user();
        $hasTenantAccess = $licence->tenants()
            ->whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id)
                      ->where('tenant_user.role', 'admin');
            })
            ->exists();
            
        if (!$hasTenantAccess) {
            return redirect()->back()
                ->with('error', 'Vous n\'avez pas accès à cette licence.');
        }
        
        try {
            // Traiter le paiement via le service de paiement
            $paymentResult = $this->paymentService->processPayment(
                $validated['payment_method'],
                $validated['payment_token'],
                $plan->price,
                'Abonnement ' . $plan->name . ' pour licence ' . $licence->serial_key
            );
            
            if ($paymentResult['success']) {
                // Mettre à jour les tenants associés à cette licence
                $tenants = $licence->tenants;
                
                foreach ($tenants as $tenant) {
                    $tenant->update([
                        'subscription_id' => $paymentResult['subscription_id'],
                        'subscription_status' => Tenant::SUBSCRIPTION_ACTIVE,
                        'subscription_ends_at' => now()->addMonths($plan->billing_cycle),
                    ]);
                    
                    // Créer une facture
                    Invoice::create([
                        'tenant_id' => $tenant->id,
                        'amount' => $plan->price,
                        'description' => 'Abonnement ' . $plan->name,
                        'payment_id' => $paymentResult['payment_id'],
                        'status' => 'paid',
                        'paid_at' => now(),
                        'due_date' => now()->addMonths($plan->billing_cycle),
                    ]);
                    
                    // Journaliser l'action
                    $this->historyService->logAction(
                        $licence,
                        'subscription_updated',
                        'Mise à jour de l\'abonnement pour le tenant ' . $tenant->name . ' vers le plan ' . $plan->name
                    );
                }
                
                return redirect()->route('billing.success')
                    ->with('success', 'Paiement traité avec succès. Votre abonnement a été mis à jour.');
            } else {
                Log::error('Échec du paiement', $paymentResult);
                return redirect()->back()
                    ->with('error', 'Le paiement a échoué : ' . $paymentResult['error_message']);
            }
        } catch (\Exception $e) {
            Log::error('Exception lors du traitement du paiement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors du traitement du paiement.');
        }
    }

    /**
     * Page de succès après un paiement réussi
     */
    public function success()
    {
        return view('billing.success');
    }

    /**
     * Afficher les factures pour le tenant actuel
     */
    public function invoices()
    {
        $tenant = session('current_tenant');
        
        $invoices = Invoice::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('billing.invoices', compact('invoices'));
    }

    /**
     * Afficher les détails d'une facture
     */
    public function showInvoice($id)
    {
        $tenant = session('current_tenant');
        
        $invoice = Invoice::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();
            
        return view('billing.invoice-details', compact('invoice'));
    }

    /**
     * Télécharger une facture au format PDF
     */
    public function downloadInvoice($id)
    {
        $tenant = session('current_tenant');
        
        $invoice = Invoice::where('tenant_id', $tenant->id)
            ->where('id', $id)
            ->firstOrFail();
            
        // Générer le PDF de la facture
        $pdf = \PDF::loadView('billing.invoice-pdf', compact('invoice'));
        
        return $pdf->download('facture-' . $invoice->id . '.pdf');
    }

    /**
     * Afficher les méthodes de paiement enregistrées
     */
    public function paymentMethods()
    {
        $tenant = session('current_tenant');
        
        $paymentMethods = $this->paymentService->getPaymentMethods($tenant);
        
        return view('billing.payment-methods', compact('paymentMethods'));
    }

    /**
     * Ajouter une nouvelle méthode de paiement
     */
    public function addPaymentMethod()
    {
        return view('billing.add-payment-method');
    }

    /**
     * Enregistrer une nouvelle méthode de paiement
     */
    public function storePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,paypal',
            'payment_token' => 'required|string',
        ]);
        
        $tenant = session('current_tenant');
        
        try {
            $result = $this->paymentService->addPaymentMethod(
                $tenant,
                $validated['payment_method'],
                $validated['payment_token']
            );
            
            if ($result['success']) {
                return redirect()->route('billing.payment-methods')
                    ->with('success', 'Méthode de paiement ajoutée avec succès.');
            } else {
                return redirect()->back()
                    ->with('error', 'Impossible d\'ajouter la méthode de paiement : ' . $result['error_message']);
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'ajout d\'une méthode de paiement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l\'ajout de la méthode de paiement.');
        }
    }

    /**
     * Supprimer une méthode de paiement
     */
    public function deletePaymentMethod(Request $request, $id)
    {
        $tenant = session('current_tenant');
        
        try {
            $result = $this->paymentService->deletePaymentMethod($tenant, $id);
            
            if ($result['success']) {
                return redirect()->route('billing.payment-methods')
                    ->with('success', 'Méthode de paiement supprimée avec succès.');
            } else {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer la méthode de paiement : ' . $result['error_message']);
            }
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'une méthode de paiement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression de la méthode de paiement.');
        }
    }
}
