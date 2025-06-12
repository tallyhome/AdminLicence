@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Factures') }}</span>
                </div>

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

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('N°') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Montant') }}</th>
                                    <th>{{ __('Statut') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->number }}</td>
                                        <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $invoice->description }}</td>
                                        <td>{{ number_format($invoice->total, 2) }} {{ strtoupper($invoice->currency) }}</td>
                                        <td>
                                            <span class="badge {{ $invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'refunded' ? 'bg-warning' : 'bg-danger') }}">
                                                {{ $invoice->status === 'paid' ? __('Payée') : ($invoice->status === 'refunded' ? __('Remboursée') : __('En attente')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('billing.invoice', $invoice->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Voir') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('billing.download-invoice', $invoice->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Télécharger') }}">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('Aucune facture disponible.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
