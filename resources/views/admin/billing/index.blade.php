@extends('admin.layouts.app')

@section('title', 'Gestion de la facturation')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Gestion de la facturation</h1>
                    <p class="text-muted">Gérez les abonnements et la facturation de vos tenants</p>
                </div>
                <div>
                    <a href="{{ route('admin.billing.plans') }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-cog"></i> Configurer les plans
                    </a>
                    <a href="{{ route('admin.billing.export') }}" class="btn btn-success">
                        <i class="fas fa-download"></i> Exporter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4" id="billing-stats">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Tenants actifs</h6>
                            <h4 class="mb-0 text-primary" id="active-tenants">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-credit-card text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Abonnements actifs</h6>
                            <h4 class="mb-0 text-success" id="active-subscriptions">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-euro-sign text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Revenus ce mois</h6>
                            <h4 class="mb-0 text-info" id="monthly-revenue">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded p-3">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Paiements échoués</h6>
                            <h4 class="mb-0 text-warning" id="failed-payments">-</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Rechercher</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Nom du tenant...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tous</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Essai</option>
                                <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Annulé</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiré</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="plan" class="form-label">Plan</label>
                            <select class="form-select" id="plan" name="plan">
                                <option value="">Tous</option>
                                <option value="basic" {{ request('plan') === 'basic' ? 'selected' : '' }}>Basic</option>
                                <option value="premium" {{ request('plan') === 'premium' ? 'selected' : '' }}>Premium</option>
                                <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="provider" class="form-label">Fournisseur</label>
                            <select class="form-select" id="provider" name="provider">
                                <option value="">Tous</option>
                                <option value="stripe" {{ request('provider') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                                <option value="paypal" {{ request('provider') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                                <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des tenants -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Tenants et abonnements</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tenant</th>
                                    <th>Plan</th>
                                    <th>Statut</th>
                                    <th>Fournisseur</th>
                                    <th>Prochaine facturation</th>
                                    <th>Montant</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tenants-table">
                                <!-- Contenu chargé via AJAX -->
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12">
            <nav id="pagination-container">
                <!-- Pagination chargée via AJAX -->
            </nav>
        </div>
    </div>
</div>

<!-- Modal de création d'abonnement -->
<div class="modal fade" id="createSubscriptionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer un abonnement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSubscriptionForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tenant_id" class="form-label">Tenant</label>
                                <select class="form-select" id="tenant_id" name="tenant_id" required>
                                    <option value="">Sélectionner un tenant</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="plan_id" class="form-label">Plan</label>
                                <select class="form-select" id="plan_id" name="plan_id" required>
                                    <option value="basic">Basic - 9,99€/mois</option>
                                    <option value="premium">Premium - 29,99€/mois</option>
                                    <option value="enterprise">Enterprise - 99,99€/mois</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="provider" class="form-label">Fournisseur de paiement</label>
                                <select class="form-select" id="provider" name="provider" required>
                                    <option value="stripe">Stripe</option>
                                    <option value="paypal">PayPal</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="trial_days" class="form-label">Période d'essai (jours)</label>
                                <input type="number" class="form-control" id="trial_days" name="trial_days" 
                                       value="14" min="0" max="90">
                            </div>
                        </div>
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
$(document).ready(function() {
    // Charger les statistiques
    loadStats();
    
    // Charger la liste des tenants
    loadTenants();
    
    // Gérer la soumission du formulaire de filtres
    $('form').on('submit', function(e) {
        e.preventDefault();
        loadTenants();
    });
    
    // Gérer la création d'abonnement
    $('#createSubscriptionForm').on('submit', function(e) {
        e.preventDefault();
        createSubscription();
    });
});

function loadStats() {
    $.get('{{ route("admin.billing.stats") }}')
        .done(function(data) {
            $('#active-tenants').text(data.active_tenants);
            $('#active-subscriptions').text(data.active_subscriptions);
            $('#monthly-revenue').text(data.monthly_revenue + '€');
            $('#failed-payments').text(data.failed_payments);
        })
        .fail(function() {
            console.error('Erreur lors du chargement des statistiques');
        });
}

function loadTenants() {
    const params = new URLSearchParams(window.location.search);
    const formData = new FormData($('form')[0]);
    
    for (let [key, value] of formData.entries()) {
        if (value) params.set(key, value);
    }
    
    $.get('{{ route("admin.billing.index") }}?' + params.toString())
        .done(function(data) {
            $('#tenants-table').html(data.html);
            $('#pagination-container').html(data.pagination);
        })
        .fail(function() {
            $('#tenants-table').html('<tr><td colspan="7" class="text-center text-danger">Erreur lors du chargement</td></tr>');
        });
}

function createSubscription() {
    const formData = new FormData($('#createSubscriptionForm')[0]);
    
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
    .done(function(data) {
        $('#createSubscriptionModal').modal('hide');
        loadTenants();
        loadStats();
        showAlert('success', 'Abonnement créé avec succès');
    })
    .fail(function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        showFormErrors(errors);
    });
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
        loadTenants();
        loadStats();
        showAlert('success', 'Abonnement annulé');
    })
    .fail(function() {
        showAlert('error', 'Erreur lors de l\'annulation');
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
        loadTenants();
        showAlert('success', 'Tenant suspendu');
    })
    .fail(function() {
        showAlert('error', 'Erreur lors de la suspension');
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
        loadTenants();
        showAlert('success', 'Tenant réactivé');
    })
    .fail(function() {
        showAlert('error', 'Erreur lors de la réactivation');
    });
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alert);
}

function showFormErrors(errors) {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    for (let field in errors) {
        const input = $(`[name="${field}"]`);
        input.addClass('is-invalid');
        input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
    }
}
</script>
@endpush

@push('styles')
<style>
.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

.card {
    transition: all 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endpush