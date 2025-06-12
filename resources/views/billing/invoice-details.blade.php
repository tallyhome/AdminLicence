@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Détails de la facture') }}</span>
                    <div>
                        <a href="{{ route('billing.download-invoice', $invoice->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> {{ __('Télécharger PDF') }}
                        </a>
                        <a href="{{ route('billing.invoices') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Retour aux factures') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>{{ config('app.name') }}</h4>
                            <p>
                                123 Rue de l'Exemple<br>
                                75000 Paris, France<br>
                                contact@example.com<br>
                                +33 1 23 45 67 89
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h4>{{ __('Facture') }} #{{ $invoice->number }}</h4>
                            <p>
                                {{ __('Date d\'émission') }}: {{ $invoice->created_at->format('d/m/Y') }}<br>
                                {{ __('Date d\'échéance') }}: {{ $invoice->due_at ? $invoice->due_at->format('d/m/Y') : 'N/A' }}<br>
                                {{ __('Statut') }}: 
                                <span class="badge {{ $invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'refunded' ? 'bg-warning' : 'bg-danger') }}">
                                    {{ $invoice->status === 'paid' ? __('Payée') : ($invoice->status === 'refunded' ? __('Remboursée') : __('En attente')) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>{{ __('Facturé à') }}</h5>
                            <p>
                                {{ $invoice->tenant->name }}<br>
                                {{ $invoice->billing_details['address'] ?? 'Adresse non spécifiée' }}<br>
                                {{ $invoice->billing_details['email'] ?? auth()->user()->email }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>{{ __('Informations de paiement') }}</h5>
                            <p>
                                {{ __('Méthode') }}: {{ $invoice->payment_method_type === 'credit_card' ? __('Carte de crédit') : ($invoice->payment_method_type === 'paypal' ? 'PayPal' : __('Autre')) }}<br>
                                {{ __('Date de paiement') }}: {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : 'N/A' }}<br>
                                {{ __('ID de transaction') }}: {{ $invoice->provider_id ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Montant') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($invoice->items->count() > 0)
                                    @foreach($invoice->items as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td class="text-end">{{ number_format($item->amount, 2) }} {{ strtoupper($invoice->currency) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ $invoice->description }}</td>
                                        <td class="text-end">{{ number_format($invoice->total, 2) }} {{ strtoupper($invoice->currency) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>{{ __('Total') }}</th>
                                    <th class="text-end">{{ number_format($invoice->total, 2) }} {{ strtoupper($invoice->currency) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5>{{ __('Notes') }}</h5>
                                    <p class="mb-0">
                                        {{ __('Merci pour votre confiance. Pour toute question concernant cette facture, veuillez contacter notre service client.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
