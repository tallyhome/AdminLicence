@extends('admin.layouts.app')

@section('title', 'Détails du tenant - ' . $tenant->name)

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">{{ $tenant->name }}</h1>
                    <p class="text-muted mb-0">{{ $tenant->email }} • Créé le {{ $tenant->created_at->format('d/m/Y') }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    @if($tenant->status === 'active')
                        <button type="button" class="btn btn-warning me-2" onclick="suspendTenant({{ $tenant->id }})">
                            <i class="fas fa-pause"></i> Suspendre
                        </button>
                    @elseif($tenant->status === 'suspended')
                        <button type="button" class="btn btn-success me-2" onclick="reactivateTenant({{ $tenant->id }})">
                            <i class="fas fa-play"></i> Réactiver
                        </button>
                    @endif
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-cog"></i> Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="createSubscription()"><i class="fas fa-plus"></i> Nouvel abonnement</a></li>
                            <li><a class="dropdown-item" href="#" onclick="sendNotification()"><i class="fas fa-envelope"></i> Envoyer notification</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteTenant()"><i class="fas fa-trash"></i> Supprimer tenant</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations générales -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Statut:</strong>
                        </div>
                        <div class="col-sm-8">
                            @php
                                $statusColors = [
                                    'active' => 'success',
                                    'trial' => 'info',
                                    'suspended' => 'warning',
                                    'expired' => 'danger',
                                    'canceled' => 'secondary'
                                ];
                                $statusColor = $statusColors[$tenant->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($tenant->status) }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Domaine:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $tenant->domain ?? 'Non configuré' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Timezone:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $tenant->timezone ?? 'UTC' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Dernière activité:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $tenant->last_activity_at ? $tenant->last_activity_at->diffForHumans() : 'Jamais' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong>Utilisateurs:</strong>
                        </div>
                        <div class="col-sm-8">
                            {{ $tenant->users()->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilisation actuelle -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Utilisation actuelle</h5>
                </div>
                <div class="card-body">
                    @php
                        $limits = $tenant->limits ?? [];
                        $usage = [
                            'projects' => $tenant->projects()->count(),
                            'serial_keys' => $tenant->serialKeys()->count(),
                            'api_keys' => $tenant->apiKeys()->count()
                        ];
                    @endphp
                    
                    <!-- Projets -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Projets</span>
                            <span class="text-muted">
                                {{ $usage['projects'] }} / 
                                {{ isset($limits['projects']) && $limits['projects'] !== 'unlimited' ? $limits['projects'] : '∞' }}
                            </span>
                        </div>
                        @if(isset($limits['projects']) && $limits['projects'] !== 'unlimited')
                            @php
                                $percentage = ($usage['projects'] / $limits['projects']) * 100;
                                $progressColor = $percentage >= 90 ? 'danger' : ($percentage >= 75 ? 'warning' : 'success');
                            @endphp
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        @else
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        @endif
                    </div>

                    <!-- Clés de licence -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Clés de licence</span>
                            <span class="text-muted">
                                {{ $usage['serial_keys'] }} / 
                                {{ isset($limits['serial_keys']) && $limits['serial_keys'] !== 'unlimited' ? $limits['serial_keys'] : '∞' }}
                            </span>
                        </div>
                        @if(isset($limits['serial_keys']) && $limits['serial_keys'] !== 'unlimited')
                            @php
                                $percentage = ($usage['serial_keys'] / $limits['serial_keys']) * 100;
                                $progressColor = $percentage >= 90 ? 'danger' : ($percentage >= 75 ? 'warning' : 'success');
                            @endphp
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        @else
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        @endif
                    </div>

                    <!-- Clés API -->
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Clés API</span>
                            <span class="text-muted">
                                {{ $usage['api_keys'] }} / 
                                {{ isset($limits['api_keys']) && $limits['api_keys'] !== 'unlimited' ? $limits['api_keys'] : '∞' }}
                            </span>
                        </div>
                        @if(isset($limits['api_keys']) && $limits['api_keys'] !== 'unlimited')
                            @php
                                $percentage = ($usage['api_keys'] / $limits['api_keys']) * 100;
                                $progressColor = $percentage >= 90 ? 'danger' : ($percentage >= 75 ? 'warning' : 'success');
                            @endphp
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        @else
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Abonnements -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Abonnements</h5>
                        <button type="button" class="btn btn-sm btn-primary" onclick="createSubscription()">
                            <i class="fas fa-plus"></i> Nouvel abonnement
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($tenant->subscriptions->count() > 0)
                        @foreach($tenant->subscriptions->sortByDesc('created_at') as $subscription)
                            <div class="border rounded p-3 mb-3 {{ $subscription->isValid() ? 'border-success' : 'border-secondary' }}">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            @if($subscription->provider === 'stripe')
                                                <i class="fab fa-stripe text-primary me-2"></i>
                                            @else
                                                <i class="fab fa-paypal text-info me-2"></i>
                                            @endif
                                            <div>
                                                <strong>{{ ucfirst($subscription->plan_id) }}</strong>
                                                <br>
                                                <small class="text-muted">{{ ucfirst($subscription->provider) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        @php
                                            $statusColors = [
                                                'active' => 'success',
                                                'trial' => 'info',
                                                'canceled' => 'secondary',
                                                'suspended' => 'warning',
                                                'past_due' => 'danger'
                                            ];
                                            $statusColor = $statusColors[$subscription->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($subscription->status) }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>{{ number_format($subscription->amount, 2) }}€</strong>
                                        <br>
                                        <small class="text-muted">{{ $subscription->interval }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        @if($subscription->trial_ends_at && $subscription->trial_ends_at->isFuture())
                                            <small class="text-info">
                                                <i class="fas fa-clock"></i> Essai jusqu'au {{ $subscription->trial_ends_at->format('d/m/Y') }}
                                            </small>
                                        @elseif($subscription->next_billing_at)
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> Prochaine facturation: {{ $subscription->next_billing_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                        @if($subscription->canceled_at)
                                            <br>
                                            <small class="text-danger">
                                                <i class="fas fa-times"></i> Annulé le {{ $subscription->canceled_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <div class="btn-group btn-group-sm">
                                            @if($subscription->isActive())
                                                @if($subscription->provider === 'stripe')
                                                    <a href="{{ route('admin.billing.stripe.portal', $subscription) }}" 
                                                       class="btn btn-outline-primary" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('admin.billing.paypal.manage', $subscription) }}" 
                                                       class="btn btn-outline-info" target="_blank">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="cancelSubscription({{ $subscription->id }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun abonnement</h5>
                            <p class="text-muted">Ce tenant n'a pas encore d'abonnement actif.</p>
                            <button type="button" class="btn btn-primary" onclick="createSubscription()">
                                <i class="fas fa-plus"></i> Créer un abonnement
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historique des factures -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Historique des factures</h5>
                </div>
                <div class="card-body">
                    @if($tenant->invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Méthode</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenant->invoices->sortByDesc('created_at')->take(10) as $invoice)
                                        <tr>
                                            <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                            <td>{{ number_format($invoice->amount, 2) }}€</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'paid' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        'refunded' => 'info'
                                                    ];
                                                    $statusColor = $statusColors[$invoice->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($invoice->status) }}</span>
                                            </td>
                                            <td>
                                                @if($invoice->payment_method === 'stripe')
                                                    <i class="fab fa-stripe text-primary"></i> Stripe
                                                @else
                                                    <i class="fab fa-paypal text-info"></i> PayPal
                                                @endif
                                            </td>
                                            <td>
                                                @if($invoice->provider_invoice_id)
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewInvoice('{{ $invoice->provider_invoice_id }}', '{{ $invoice->payment_method }}')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($tenant->invoices->count() > 10)
                            <div class="text-center mt-3">
                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="loadMoreInvoices()">
                                    Voir plus de factures
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Aucune facture trouvée</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de création d'abonnement -->
<div class="modal fade" id="createSubscriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer un abonnement pour {{ $tenant->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSubscriptionForm">
                <div class="modal-body">
                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                    
                    <div class="mb-3">
                        <label for="plan_id" class="form-label">Plan</label>
                        <select class="form-select" id="plan_id" name="plan_id" required>
                            <option value="basic">Basic - 9,99€/mois</option>
                            <option value="premium">Premium - 29,99€/mois</option>
                            <option value="enterprise">Enterprise - 99,99€/mois</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="provider" class="form-label">Fournisseur de paiement</label>
                        <select class="form-select" id="provider" name="provider" required>
                            <option value="stripe">Stripe</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="trial_days" class="form-label">Période d'essai (jours)</label>
                        <input type="number" class="form-control" id="trial_days" name="trial_days" 
                               value="14" min="0" max="90">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Créer l'abonnement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function createSubscription() {
    $('#createSubscriptionModal').modal('show');
}

function cancelSubscription(subscriptionId) {
    if (!confirm('Êtes-vous sûr de vouloir annuler cet abonnement ?')) {
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.billing.subscriptions.cancel", ":id") }}'.replace(':id', subscriptionId),
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function() {
        location.reload();
    })
    .fail(function() {
        alert('Erreur lors de l\'annulation');
    });
}

function suspendTenant(tenantId) {
    if (!confirm('Êtes-vous sûr de vouloir suspendre ce tenant ?')) {
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.billing.tenants.suspend", ":id") }}'.replace(':id', tenantId),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function() {
        location.reload();
    })
    .fail(function() {
        alert('Erreur lors de la suspension');
    });
}

function reactivateTenant(tenantId) {
    $.ajax({
        url: '{{ route("admin.billing.tenants.reactivate", ":id") }}'.replace(':id', tenantId),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function() {
        location.reload();
    })
    .fail(function() {
        alert('Erreur lors de la réactivation');
    });
}

function viewInvoice(invoiceId, provider) {
    // Ouvrir la facture dans un nouvel onglet
    if (provider === 'stripe') {
        window.open(`https://dashboard.stripe.com/invoices/${invoiceId}`, '_blank');
    } else {
        // Pour PayPal, rediriger vers l'interface de gestion
        window.open('https://www.paypal.com/invoice', '_blank');
    }
}

// Gérer la soumission du formulaire de création d'abonnement
$('#createSubscriptionForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: '{{ route("admin.billing.subscriptions.create") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function() {
        $('#createSubscriptionModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        alert('Erreur lors de la création de l\'abonnement');
        console.error(errors);
    });
});
</script>
@endpush

@push('styles')
<style>
.progress {
    background-color: #e9ecef;
}

.border-success {
    border-color: #198754 !important;
}

.border-secondary {
    border-color: #6c757d !important;
}

.card {
    transition: all 0.2s ease-in-out;
}

.badge {
    font-size: 0.75rem;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.btn-group-sm > .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endpush