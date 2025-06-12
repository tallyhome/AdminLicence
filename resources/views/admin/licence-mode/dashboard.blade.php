@extends('layouts.admin')

@section('title', __('Mode de Licence'))

@section('content')
<div class="container-fluid">
    <!-- En-tête avec informations sur le mode actuel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">
                                <i class="fas fa-cogs text-primary me-2"></i>
                                {{ __('Mode de Licence') }}
                            </h1>
                            <p class="text-muted mb-0">
                                {{ __('Gestion et surveillance du mode de fonctionnement de l\'application') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex align-items-center justify-content-end">
                                <span class="badge badge-lg me-2 
                                    @if($currentMode === App\Services\LicenceModeService::MODE_SAAS) 
                                        bg-success
                                    @else 
                                        bg-info
                                    @endif">
                                    @if($currentMode === App\Services\LicenceModeService::MODE_SAAS)
                                        <i class="fas fa-cloud me-1"></i> {{ __('Mode SaaS') }}
                                    @else
                                        <i class="fas fa-user me-1"></i> {{ __('Mode Mono-compte') }}
                                    @endif
                                </span>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="refreshMode">
                                    <i class="fas fa-sync-alt"></i> {{ __('Actualiser') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['max_serial_keys'] }}</h4>
                    <p class="text-muted mb-0">{{ __('Clés de licence') }}</p>
                    @if(isset($limits['max_serial_keys']) && $limits['max_serial_keys'])
                        <small class="text-muted">/ {{ $limits['max_serial_keys'] }}</small>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="fas fa-project-diagram fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['max_projects'] }}</h4>
                    <p class="text-muted mb-0">{{ __('Projets') }}</p>
                    @if(isset($limits['max_projects']) && $limits['max_projects'])
                        <small class="text-muted">/ {{ $limits['max_projects'] }}</small>
                    @endif
                </div>
            </div>
        </div>
        
        @saas
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['max_tenants'] }}</h4>
                    <p class="text-muted mb-0">{{ __('Tenants') }}</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-2">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4 class="mb-1">{{ $stats['max_clients_per_tenant'] }}</h4>
                    <p class="text-muted mb-0">{{ __('Max clients/tenant') }}</p>
                </div>
            </div>
        </div>
        @endsaas
        
        @singleaccount
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-secondary mb-2">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <h5 class="mb-1">{{ __('Fonctionnalités limitées') }}</h5>
                    <p class="text-muted mb-0">{{ __('Mode mono-compte actif') }}</p>
                </div>
            </div>
        </div>
        @endsingleaccount
    </div>

    <!-- Fonctionnalités disponibles -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        {{ __('Fonctionnalités disponibles') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($features as $feature => $available)
                            @if($available)
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>{{ __(ucfirst(str_replace('_', ' ', $feature))) }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-times-circle text-danger me-2"></i>
                        {{ __('Fonctionnalités non disponibles') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($features as $feature => $available)
                            @if(!$available)
                                <div class="col-md-6 mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-times text-danger me-2"></i>
                                        <span class="text-muted">{{ __(ucfirst(str_replace('_', ' ', $feature))) }}</span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    @singleaccount
                    <div class="mt-3">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('Mettez à niveau vers une licence multi-comptes pour débloquer ces fonctionnalités.') }}
                        </div>
                    </div>
                    @endsingleaccount
                </div>
            </div>
        </div>
    </div>

    <!-- Limites et utilisation -->
    @if($currentMode === App\Services\LicenceModeService::MODE_SINGLE_ACCOUNT)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-warning me-2"></i>
                        {{ __('Utilisation et limites') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($limits as $limitType => $limitValue)
                            @if($limitValue !== null && isset($stats[$limitType]))
                                @php
                                    $current = $stats[$limitType];
                                    $percentage = $limitValue > 0 ? min(100, ($current / $limitValue) * 100) : 0;
                                    $progressClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fw-medium">{{ __(ucfirst(str_replace(['max_', '_'], ['', ' '], $limitType))) }}</span>
                                        <span class="text-muted">{{ $current }} / {{ $limitValue }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $progressClass }}" 
                                             role="progressbar" 
                                             style="width: {{ $percentage }}%" 
                                             aria-valuenow="{{ $percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    @if($percentage > 80)
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ __('Limite bientôt atteinte') }}
                                        </small>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Licences actives -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-key text-primary me-2"></i>
                        {{ __('Licences actives') }}
                    </h5>
                </div>
                <div class="card-body">
                    @if($activeLicences->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ __('Clé de licence') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Projet') }}</th>
                                        <th>{{ __('Domaine') }}</th>
                                        <th>{{ __('Expiration') }}</th>
                                        <th>{{ __('Max comptes') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeLicences as $licence)
                                        <tr>
                                            <td>
                                                <code class="text-primary">{{ $licence->serial_key }}</code>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    @if($licence->licence_type === App\Models\SerialKey::LICENCE_TYPE_MULTI)
                                                        bg-success
                                                    @else
                                                        bg-info
                                                    @endif">
                                                    @if($licence->licence_type === App\Models\SerialKey::LICENCE_TYPE_MULTI)
                                                        {{ __('Multi-comptes') }}
                                                    @else
                                                        {{ __('Mono-compte') }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>{{ $licence->project->name ?? __('N/A') }}</td>
                                            <td>{{ $licence->domain ?? __('Tous domaines') }}</td>
                                            <td>
                                                @if($licence->expires_at)
                                                    {{ $licence->expires_at->format('d/m/Y') }}
                                                @else
                                                    <span class="text-success">{{ __('Illimitée') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($licence->max_accounts)
                                                    {{ $licence->max_accounts }}
                                                @else
                                                    <span class="text-muted">{{ __('Illimité') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-key fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">{{ __('Aucune licence active') }}</h5>
                            <p class="text-muted">{{ __('Aucune licence active n\'a été trouvée dans le système.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(app()->environment('local'))
<!-- Modal de simulation (développement uniquement) -->
<div class="modal fade" id="simulationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Simulation de mode') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ __('Cette fonctionnalité est uniquement disponible en environnement de développement.') }}
                </p>
                <div class="mb-3">
                    <label class="form-label">{{ __('Mode à simuler') }}</label>
                    <select class="form-select" id="simulatedMode">
                        <option value="{{ App\Services\LicenceModeService::MODE_SINGLE_ACCOUNT }}">{{ __('Mono-compte') }}</option>
                        <option value="{{ App\Services\LicenceModeService::MODE_SAAS }}">{{ __('SaaS') }}</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Annuler') }}</button>
                <button type="button" class="btn btn-warning" id="activateSimulation">{{ __('Activer la simulation') }}</button>
                <button type="button" class="btn btn-outline-danger" id="clearSimulation">{{ __('Désactiver') }}</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Actualiser le mode
    $('#refreshMode').click(function() {
        const btn = $(this);
        const originalText = btn.html();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __('Actualisation...') }}');
        
        $.ajax({
            url: '{{ route('admin.licence-mode.refresh') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    if (response.mode_changed) {
                        location.reload();
                    } else {
                        toastr.success(response.message);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('{{ __('Erreur lors de l\'actualisation') }}');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    @if(app()->environment('local'))
    // Simulation de mode (développement uniquement)
    $('#activateSimulation').click(function() {
        const mode = $('#simulatedMode').val();
        
        $.ajax({
            url: '{{ route('admin.licence-mode.simulate') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: { mode: mode },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#simulationModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('{{ __('Erreur lors de la simulation') }}');
            }
        });
    });
    
    $('#clearSimulation').click(function() {
        $.ajax({
            url: '{{ route('admin.licence-mode.clear-simulation') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#simulationModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('{{ __('Erreur lors de la désactivation') }}');
            }
        });
    });
    @endif
});
</script>
@endpush