@extends('admin.layouts.app')

@section('title', 'Configuration des plans')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Configuration des plans</h1>
                    <p class="text-muted">Gérez les plans d'abonnement et leurs tarifs</p>
                </div>
                <div>
                    <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <button type="button" class="btn btn-success" onclick="saveAllPlans()">
                        <i class="fas fa-save"></i> Sauvegarder tout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans de facturation -->
    <div class="row">
        <!-- Plan Basic -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-star"></i> Plan Basic
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="basic_enabled" checked>
                            <label class="form-check-label text-white" for="basic_enabled">
                                Actif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="basic-plan-form">
                        <input type="hidden" name="plan_id" value="basic">
                        
                        <!-- Tarification -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Tarification</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label for="basic_price" class="form-label">Prix mensuel</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="basic_price" 
                                               name="price" value="9.99" step="0.01" min="0">
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="basic_setup_fee" class="form-label">Frais d'installation</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="basic_setup_fee" 
                                               name="setup_fee" value="0" step="0.01" min="0">
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <label for="basic_trial_days" class="form-label">Période d'essai</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="basic_trial_days" 
                                               name="trial_days" value="14" min="0" max="90">
                                        <span class="input-group-text">jours</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="basic_billing_cycle" class="form-label">Cycle de facturation</label>
                                    <select class="form-select" id="basic_billing_cycle" name="billing_cycle">
                                        <option value="monthly" selected>Mensuel</option>
                                        <option value="yearly">Annuel</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Limites -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Limites</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="basic_projects" class="form-label">Projets</label>
                                    <input type="number" class="form-control" id="basic_projects" 
                                           name="limits[projects]" value="5" min="1">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="basic_serial_keys" class="form-label">Clés de licence</label>
                                    <input type="number" class="form-control" id="basic_serial_keys" 
                                           name="limits[serial_keys]" value="100" min="1">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="basic_api_keys" class="form-label">Clés API</label>
                                    <input type="number" class="form-control" id="basic_api_keys" 
                                           name="limits[api_keys]" value="3" min="1">
                                </div>
                            </div>
                        </div>

                        <!-- Fonctionnalités -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Fonctionnalités</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="basic_analytics" 
                                       name="features[analytics]" checked>
                                <label class="form-check-label" for="basic_analytics">
                                    Analytiques de base
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="basic_support" 
                                       name="features[support]" checked>
                                <label class="form-check-label" for="basic_support">
                                    Support email
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="basic_api" 
                                       name="features[api]" checked>
                                <label class="form-check-label" for="basic_api">
                                    Accès API
                                </label>
                            </div>
                        </div>

                        <!-- IDs des plans externes -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Identifiants externes</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="basic_stripe_price_id" class="form-label">Stripe Price ID</label>
                                    <input type="text" class="form-control" id="basic_stripe_price_id" 
                                           name="stripe_price_id" placeholder="price_xxxxx">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="basic_paypal_plan_id" class="form-label">PayPal Plan ID</label>
                                    <input type="text" class="form-control" id="basic_paypal_plan_id" 
                                           name="paypal_plan_id" placeholder="P-xxxxx">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <button type="button" class="btn btn-primary w-100" onclick="savePlan('basic')">
                        <i class="fas fa-save"></i> Sauvegarder le plan Basic
                    </button>
                </div>
            </div>
        </div>

        <!-- Plan Premium -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-crown"></i> Plan Premium
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="premium_enabled" checked>
                            <label class="form-check-label text-white" for="premium_enabled">
                                Actif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="premium-plan-form">
                        <input type="hidden" name="plan_id" value="premium">
                        
                        <!-- Tarification -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Tarification</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label for="premium_price" class="form-label">Prix mensuel</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="premium_price" 
                                               name="price" value="29.99" step="0.01" min="0">
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="premium_setup_fee" class="form-label">Frais d'installation</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="premium_setup_fee" 
                                               name="setup_fee" value="0" step="0.01" min="0">
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <label for="premium_trial_days" class="form-label">Période d'essai</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="premium_trial_days" 
                                               name="trial_days" value="14" min="0" max="90">
                                        <span class="input-group-text">jours</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="premium_billing_cycle" class="form-label">Cycle de facturation</label>
                                    <select class="form-select" id="premium_billing_cycle" name="billing_cycle">
                                        <option value="monthly" selected>Mensuel</option>
                                        <option value="yearly">Annuel</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Limites -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Limites</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="premium_projects" class="form-label">Projets</label>
                                    <input type="number" class="form-control" id="premium_projects" 
                                           name="limits[projects]" value="25" min="1">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="premium_serial_keys" class="form-label">Clés de licence</label>
                                    <input type="number" class="form-control" id="premium_serial_keys" 
                                           name="limits[serial_keys]" value="1000" min="1">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="premium_api_keys" class="form-label">Clés API</label>
                                    <input type="number" class="form-control" id="premium_api_keys" 
                                           name="limits[api_keys]" value="10" min="1">
                                </div>
                            </div>
                        </div>

                        <!-- Fonctionnalités -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Fonctionnalités</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="premium_analytics" 
                                       name="features[analytics]" checked>
                                <label class="form-check-label" for="premium_analytics">
                                    Analytiques avancées
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="premium_support" 
                                       name="features[support]" checked>
                                <label class="form-check-label" for="premium_support">
                                    Support prioritaire
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="premium_api" 
                                       name="features[api]" checked>
                                <label class="form-check-label" for="premium_api">
                                    Accès API complet
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="premium_webhooks" 
                                       name="features[webhooks]" checked>
                                <label class="form-check-label" for="premium_webhooks">
                                    Webhooks
                                </label>
                            </div>
                        </div>

                        <!-- IDs des plans externes -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Identifiants externes</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="premium_stripe_price_id" class="form-label">Stripe Price ID</label>
                                    <input type="text" class="form-control" id="premium_stripe_price_id" 
                                           name="stripe_price_id" placeholder="price_xxxxx">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="premium_paypal_plan_id" class="form-label">PayPal Plan ID</label>
                                    <input type="text" class="form-control" id="premium_paypal_plan_id" 
                                           name="paypal_plan_id" placeholder="P-xxxxx">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <button type="button" class="btn btn-success w-100" onclick="savePlan('premium')">
                        <i class="fas fa-save"></i> Sauvegarder le plan Premium
                    </button>
                </div>
            </div>
        </div>

        <!-- Plan Enterprise -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-building"></i> Plan Enterprise
                        </h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enterprise_enabled" checked>
                            <label class="form-check-label text-dark" for="enterprise_enabled">
                                Actif
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="enterprise-plan-form">
                        <input type="hidden" name="plan_id" value="enterprise">
                        
                        <!-- Tarification -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Tarification</h6>
                            <div class="row">
                                <div class="col-6">
                                    <label for="enterprise_price" class="form-label">Prix mensuel</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="enterprise_price" 
                                               name="price" value="99.99" step="0.01" min="0">
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="enterprise_setup_fee" class="form-label">Frais d'installation</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="enterprise_setup_fee" 
                                               name="setup_fee" value="0" step="0.01" min="0">
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <label for="enterprise_trial_days" class="form-label">Période d'essai</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="enterprise_trial_days" 
                                               name="trial_days" value="30" min="0" max="90">
                                        <span class="input-group-text">jours</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label for="enterprise_billing_cycle" class="form-label">Cycle de facturation</label>
                                    <select class="form-select" id="enterprise_billing_cycle" name="billing_cycle">
                                        <option value="monthly" selected>Mensuel</option>
                                        <option value="yearly">Annuel</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Limites -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Limites</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Projets</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enterprise_unlimited_projects" 
                                               name="limits[projects_unlimited]" checked>
                                        <label class="form-check-label" for="enterprise_unlimited_projects">
                                            Illimité
                                        </label>
                                    </div>
                                    <input type="number" class="form-control mt-2" id="enterprise_projects" 
                                           name="limits[projects]" value="999999" min="1" disabled>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Clés de licence</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enterprise_unlimited_serial_keys" 
                                               name="limits[serial_keys_unlimited]" checked>
                                        <label class="form-check-label" for="enterprise_unlimited_serial_keys">
                                            Illimité
                                        </label>
                                    </div>
                                    <input type="number" class="form-control mt-2" id="enterprise_serial_keys" 
                                           name="limits[serial_keys]" value="999999" min="1" disabled>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Clés API</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="enterprise_unlimited_api_keys" 
                                               name="limits[api_keys_unlimited]" checked>
                                        <label class="form-check-label" for="enterprise_unlimited_api_keys">
                                            Illimité
                                        </label>
                                    </div>
                                    <input type="number" class="form-control mt-2" id="enterprise_api_keys" 
                                           name="limits[api_keys]" value="999999" min="1" disabled>
                                </div>
                            </div>
                        </div>

                        <!-- Fonctionnalités -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Fonctionnalités</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="enterprise_analytics" 
                                       name="features[analytics]" checked>
                                <label class="form-check-label" for="enterprise_analytics">
                                    Analytiques complètes
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="enterprise_support" 
                                       name="features[support]" checked>
                                <label class="form-check-label" for="enterprise_support">
                                    Support dédié
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="enterprise_api" 
                                       name="features[api]" checked>
                                <label class="form-check-label" for="enterprise_api">
                                    API complète
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="enterprise_webhooks" 
                                       name="features[webhooks]" checked>
                                <label class="form-check-label" for="enterprise_webhooks">
                                    Webhooks avancés
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="enterprise_white_label" 
                                       name="features[white_label]" checked>
                                <label class="form-check-label" for="enterprise_white_label">
                                    White label
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="enterprise_custom_domain" 
                                       name="features[custom_domain]" checked>
                                <label class="form-check-label" for="enterprise_custom_domain">
                                    Domaine personnalisé
                                </label>
                            </div>
                        </div>

                        <!-- IDs des plans externes -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">Identifiants externes</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="enterprise_stripe_price_id" class="form-label">Stripe Price ID</label>
                                    <input type="text" class="form-control" id="enterprise_stripe_price_id" 
                                           name="stripe_price_id" placeholder="price_xxxxx">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="enterprise_paypal_plan_id" class="form-label">PayPal Plan ID</label>
                                    <input type="text" class="form-control" id="enterprise_paypal_plan_id" 
                                           name="paypal_plan_id" placeholder="P-xxxxx">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer bg-light">
                    <button type="button" class="btn btn-warning w-100" onclick="savePlan('enterprise')">
                        <i class="fas fa-save"></i> Sauvegarder le plan Enterprise
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Gérer les checkboxes illimité pour Enterprise
    $('[id$="_unlimited_projects"], [id$="_unlimited_serial_keys"], [id$="_unlimited_api_keys"]').on('change', function() {
        const targetInput = $(this).closest('.mb-3').find('input[type="number"]');
        if ($(this).is(':checked')) {
            targetInput.prop('disabled', true).val(999999);
        } else {
            targetInput.prop('disabled', false);
        }
    });
    
    // Charger la configuration actuelle des plans
    loadPlansConfiguration();
});

function loadPlansConfiguration() {
    // Ici vous pourriez charger la configuration depuis le serveur
    // Pour l'instant, on utilise les valeurs par défaut
}

function savePlan(planId) {
    const form = $(`#${planId}-plan-form`);
    const formData = new FormData(form[0]);
    
    // Ajouter l'état enabled
    const enabled = $(`#${planId}_enabled`).is(':checked');
    formData.append('enabled', enabled ? '1' : '0');
    
    $.ajax({
        url: '{{ route("admin.billing.plans.update", ":planId") }}'.replace(':planId', planId),
        method: 'PUT',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .done(function(data) {
        showAlert('success', `Plan ${planId} sauvegardé avec succès`);
    })
    .fail(function(xhr) {
        const errors = xhr.responseJSON?.errors || {};
        showAlert('error', 'Erreur lors de la sauvegarde');
        console.error('Erreurs:', errors);
    });
}

function saveAllPlans() {
    const plans = ['basic', 'premium', 'enterprise'];
    let completed = 0;
    let errors = 0;
    
    plans.forEach(planId => {
        const form = $(`#${planId}-plan-form`);
        const formData = new FormData(form[0]);
        
        // Ajouter l'état enabled
        const enabled = $(`#${planId}_enabled`).is(':checked');
        formData.append('enabled', enabled ? '1' : '0');
        
        $.ajax({
            url: '{{ route("admin.billing.plans.update", ":planId") }}'.replace(':planId', planId),
            method: 'PUT',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function() {
            completed++;
            checkAllCompleted();
        })
        .fail(function() {
            errors++;
            completed++;
            checkAllCompleted();
        });
    });
    
    function checkAllCompleted() {
        if (completed === plans.length) {
            if (errors === 0) {
                showAlert('success', 'Tous les plans ont été sauvegardés avec succès');
            } else {
                showAlert('warning', `${plans.length - errors} plans sauvegardés, ${errors} erreurs`);
            }
        }
    }
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('.container-fluid').prepend(alert);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
}
</script>
@endpush

@push('styles')
<style>
.card {
    transition: all 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.input-group-text {
    background-color: var(--bs-light);
    border-color: var(--bs-border-color);
}

.card-header {
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
}

h6.text-muted {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.75rem;
}
</style>
@endpush