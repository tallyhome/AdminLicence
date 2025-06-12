@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Paiement') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h4 class="mb-4">{{ __('Finaliser votre abonnement') }}</h4>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $plan->name }}</h5>
                                    <p class="card-text">{{ $plan->description }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">{{ $plan->billing_cycle_text }}</span>
                                        <span class="h4">{{ $plan->formatted_price }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('billing.process-payment') }}" id="payment-form">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                        <div class="mb-4">
                            <label for="licence_id" class="form-label">{{ __('Sélectionnez une licence') }} <span class="text-danger">*</span></label>
                            
                            @if(count($licences) > 0)
                                <select class="form-select @error('licence_id') is-invalid @enderror" id="licence_id" name="licence_id" required>
                                    <option value="">{{ __('Choisir une licence') }}</option>
                                    @foreach($licences as $licence)
                                        <option value="{{ $licence->id }}" {{ old('licence_id') == $licence->id ? 'selected' : '' }}>
                                            {{ $licence->serial_key }} 
                                            ({{ $licence->activeTenantsCount() }}/{{ $licence->max_accounts }} {{ __('comptes') }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    {{ t('serial_keys.choose_multi_account_licence') }}
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    {{ t('serial_keys.no_multi_account_licence_available') }}
                                </div>
                            @endif
                            
                            @error('licence_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <h5>{{ __('Mode de paiement') }}</h5>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_method_card" value="credit_card" checked>
                                <label class="form-check-label" for="payment_method_card">
                                    <i class="fab fa-cc-visa me-2"></i>
                                    <i class="fab fa-cc-mastercard me-2"></i>
                                    <i class="fab fa-cc-amex me-2"></i>
                                    {{ __('Carte de crédit') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_method_paypal" value="paypal">
                                <label class="form-check-label" for="payment_method_paypal">
                                    <i class="fab fa-paypal me-2"></i>
                                    {{ __('PayPal') }}
                                </label>
                            </div>
                        </div>

                        <div id="credit-card-form" class="mb-4">
                            <div class="mb-3">
                                <label for="card_number" class="form-label">{{ __('Numéro de carte') }}</label>
                                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_expiry" class="form-label">{{ __('Date d\'expiration') }}</label>
                                    <input type="text" class="form-control" id="card_expiry" placeholder="MM/AA">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_cvc" class="form-label">{{ __('CVC') }}</label>
                                    <input type="text" class="form-control" id="card_cvc" placeholder="123">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="card_name" class="form-label">{{ __('Nom sur la carte') }}</label>
                                <input type="text" class="form-control" id="card_name" placeholder="John Doe">
                            </div>
                        </div>

                        <div id="paypal-form" class="mb-4" style="display: none;">
                            <div class="alert alert-info">
                                {{ __('Vous serez redirigé vers PayPal pour finaliser votre paiement après avoir cliqué sur le bouton "Payer".') }}
                            </div>
                        </div>

                        <input type="hidden" name="payment_token" id="payment_token" value="">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('billing.plans') }}" class="btn btn-secondary">
                                {{ __('Retour aux plans') }}
                            </a>
                            
                            @if(count($licences) > 0)
                                <button type="submit" class="btn btn-primary" id="submit-button">
                                    {{ __('Payer') }} {{ $plan->formatted_price }}
                                </button>
                            @else
                                <button type="button" class="btn btn-primary" disabled>
                                    {{ __('Payer') }} {{ $plan->formatted_price }}
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gestion de l'affichage des formulaires de paiement
        const paymentMethodCard = document.getElementById('payment_method_card');
        const paymentMethodPaypal = document.getElementById('payment_method_paypal');
        const creditCardForm = document.getElementById('credit-card-form');
        const paypalForm = document.getElementById('paypal-form');
        
        paymentMethodCard.addEventListener('change', function() {
            if (this.checked) {
                creditCardForm.style.display = 'block';
                paypalForm.style.display = 'none';
            }
        });
        
        paymentMethodPaypal.addEventListener('change', function() {
            if (this.checked) {
                creditCardForm.style.display = 'none';
                paypalForm.style.display = 'block';
            }
        });
        
        // Simulation de la génération d'un token de paiement
        const paymentForm = document.getElementById('payment-form');
        const paymentToken = document.getElementById('payment_token');
        
        paymentForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Simuler le traitement du paiement
            const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (selectedMethod === 'credit_card') {
                // Simuler la validation des champs de carte
                const cardNumber = document.getElementById('card_number').value;
                const cardExpiry = document.getElementById('card_expiry').value;
                const cardCvc = document.getElementById('card_cvc').value;
                const cardName = document.getElementById('card_name').value;
                
                if (!cardNumber || !cardExpiry || !cardCvc || !cardName) {
                    alert('Veuillez remplir tous les champs de la carte.');
                    return;
                }
                
                // Générer un token fictif pour la carte
                paymentToken.value = 'tok_' + Math.random().toString(36).substring(2, 15);
            } else {
                // Générer un token fictif pour PayPal
                paymentToken.value = 'pp_' + Math.random().toString(36).substring(2, 15);
            }
            
            // Soumettre le formulaire
            paymentForm.submit();
        });
    });
</script>
@endsection
