@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Paiement réussi') }}</div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 64px;"></i>
                    </div>

                    <h3 class="mb-4">{{ __('Merci pour votre paiement !') }}</h3>

                    <p class="lead mb-4">
                        {{ __('Votre paiement a été traité avec succès et votre abonnement a été mis à jour.') }}
                    </p>

                    <div class="alert alert-info mb-4">
                        {{ __('Un email de confirmation vous a été envoyé avec les détails de votre transaction.') }}
                    </div>

                    <div class="d-flex justify-content-center">
                        <a href="{{ route('billing.invoices') }}" class="btn btn-primary me-3">
                            <i class="fas fa-file-invoice"></i> {{ __('Voir mes factures') }}
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-home"></i> {{ __('Retour au tableau de bord') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
