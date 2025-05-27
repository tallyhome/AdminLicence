@extends('admin.layouts.app')

@section('title', t('settings_license.license.title'))

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">{{ t('settings_license.license.title') }}</h1>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                    <div class="alert-message">{!! nl2br(e(session('success'))) !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                    <div class="alert-message">{!! nl2br(e(session('error'))) !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('success') || session('error'))
                <script>
                    // {{ t('settings_license.license.auto_dismiss_alerts') }}
                    document.addEventListener('DOMContentLoaded', function() {
                        var alerts = document.querySelectorAll('.alert');
                        if (alerts.length > 0) {
                            setTimeout(function() {
                                alerts.forEach(function(alert) {
                                    var bsAlert = new bootstrap.Alert(alert);
                                    bsAlert.close();
                                });
                            }, 5000);
                        }
                    });
                </script>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ t('settings_license.license.info_title') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>{{ t('settings_license.license.installation_key') }}</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $licenseKey ?? 'Non configurée' }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copyLicenseKey" data-bs-toggle="tooltip" title="{{ t('settings_license.license.copy_key') }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <h6>{{ t('settings_license.license.status') }}</h6>
                            <div class="mb-3">
                                @if($isValid)
                                    <span class="badge bg-success">{{ t('settings_license.license.valid') }}</span>
                                @else
                                    <span class="badge bg-danger">{{ t('settings_license.license.invalid') }}</span>
                                @endif
                            </div>
                            
                            @if($expiresAt)
                                <h6>{{ t('settings_license.license.expiry_date') }}</h6>
                                <div class="mb-3">
                                    <span class="{{ $expiresAt && $expiresAt->isPast() ? 'text-danger' : '' }}">
                                        {{ $expiresAt->format('d/m/Y') }}
                                    </span>
                                </div>
                            @endif
                            
                            <h6>{{ t('settings_license.license.last_check') }}</h6>
                            <div class="mb-3">
                                {{ $lastCheck ? \Carbon\Carbon::parse($lastCheck)->format('d/m/Y H:i:s') : t('settings_license.license.never') }}
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.settings.license.force-check') }}" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> {{ t('settings_license.license.check_now') }}
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            @if(session('license_details') || $licenseDetails)
                                <h6>{{ t('settings_license.license.details') }}</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        @if(session('license_details'))
                                        <tr>
                                            <th>{{ t('settings_license.license.status_label') }}</th>
                                            <td>
                                                @php
                                                    $status = session('license_details')['status'] ?? 'inconnu';
                                                    $statusClass = '';
                                                    $statusText = $status;
                                                    
                                                    switch($status) {
                                                        case 'active':
                                                            $statusClass = 'text-success';
                                                            $statusText = 'Active';
                                                            break;
                                                        case 'suspended':
                                                            $statusClass = 'text-warning';
                                                            $statusText = 'Suspendue';
                                                            break;
                                                        case 'revoked':
                                                            $statusClass = 'text-danger';
                                                            $statusText = 'Révoquée';
                                                            break;
                                                        default:
                                                            $statusText = ucfirst($status);
                                                    }
                                                @endphp
                                                <span class="{{ $statusClass }}">{{ $statusText }}</span>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <th>{{ t('settings_license.license.expiry_date_label') }}</th>
                                            <td>
                                                @php
                                                    $expiryDate = session('license_details')['expiry_date'] ?? null;
                                                    $hasExpiry = false;
                                                    $expiry = null;
                                                    $expired = false;
                                                    
                                                    if (!empty($expiryDate)) {
                                                        try {
                                                            $expiry = new \DateTime($expiryDate);
                                                            $now = new \DateTime();
                                                            $expired = $expiry < $now;
                                                            $hasExpiry = true;
                                                        } catch (\Exception $e) {
                                                            // Si la date n'est pas au bon format, on l'affiche telle quelle
                                                            $hasExpiry = false;
                                                        }
                                                    }
                                                @endphp
                                                
                                                @if($hasExpiry)
                                                    <span class="{{ $expired ? 'text-danger' : 'text-success' }}">
                                                        {{ $expiry->format('d/m/Y') }}
                                                        @if($expired)
                                                            <i class="fas fa-exclamation-triangle" data-bs-toggle="tooltip" title="Licence expirée"></i>
                                                        @else
                                                            <i class="fas fa-check-circle" data-bs-toggle="tooltip" title="Licence valide jusqu'à cette date"></i>
                                                        @endif
                                                    </span>
                                                @else
                                                    @if($expiryDate)
                                                        {{ $expiryDate }} <i class="fas fa-info-circle" data-bs-toggle="tooltip" title="Format de date non reconnu"></i>
                                                    @else
                                                        <span class="text-muted">Non spécifiée</span>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                        
                                        @if(session('license_details')['registered_domain'])
                                        <tr>
                                            <th>Domaine enregistré</th>
                                            <td>{{ session('license_details')['registered_domain'] }}</td>
                                        </tr>
                                        @endif
                                        
                                        @if(session('license_details')['registered_ip'])
                                        <tr>
                                            <th>Adresse IP enregistrée</th>
                                            <td>{{ session('license_details')['registered_ip'] }}</td>
                                        </tr>
                                        @endif
                                        @endif
                                        
                                        @if($licenseDetails)
                                        <tr>
                                            <th>Projet</th>
                                            <td>{{ $licenseDetails->project->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Créée le</th>
                                            <td>{{ $licenseDetails->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('settings_license.license.expires_on') }}</th>
                                            <td class="{{ $licenseDetails->expires_at && $licenseDetails->expires_at->isPast() ? 'text-danger' : '' }}">
                                                {{ $licenseDetails->expires_at ? $licenseDetails->expires_at->format('d/m/Y') : t('settings_license.license.never') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('settings_license.license.domain') }}</th>
                                            <td>{{ $licenseDetails->domain ?? t('settings_license.license.not_defined') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('settings_license.license.ip_address') }}</th>
                                            <td>{{ $licenseDetails->ip_address ?? t('settings_license.license.not_defined') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ t('settings_license.license.status') }}</th>
                                            <td>
                                                @if($licenseDetails->status == 'active')
                                                     <span class="badge bg-success">{{ t('settings_license.license.status_active') }}</span>
                                                @elseif($licenseDetails->status == 'suspended')
                                                     <span class="badge bg-warning">{{ t('settings_license.license.status_suspended') }}</span>
                                                @elseif($licenseDetails->status == 'revoked')
                                                     <span class="badge bg-danger">{{ t('settings_license.license.status_revoked') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $licenseDetails->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> {{ t('settings_license.license.no_details') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ t('settings_license.license.configuration') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.license.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="license_key" class="form-label">{{ t('settings_license.license.installation_key') }}</label>
                            <input type="text" class="form-control" id="license_key" name="license_key" value="{{ $licenseKey }}" placeholder="XXXX-XXXX-XXXX-XXXX">
                            <div class="form-text">
                                @if($envExists)
                                    {{ t('settings_license.license.key_saved_in_env') }}
                                @else
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> {{ t('settings_license.license.env_not_exists') }}</span>
                                @endif
                            </div>
                        </div>
                        

                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ t('settings_license.license.save_settings') }}
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ t('settings_license.license.manual_verification') }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ t('settings_license.license.manual_verification_desc') }}</p>
                    <a href="{{ route('admin.settings.license.force-check') }}" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> {{ t('settings_license.license.check_now') }}
                    </a>
                </div>
            </div>
            
            <!-- Débogage -->
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ t('settings_license.license.debug_info') }}</h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#debugInfo">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="card-body collapse" id="debugInfo">
                    <h6>{{ t('settings_license.license.expiry_date') }}</h6>
                    <div class="mb-3">
                        {{ t('settings_license.license.detected_value') }}: <code>{{ (string) \App\Models\Setting::get('debug_expiry_date', t('settings_license.license.not_found')) }}</code>
                    </div>
                    
                    <h6>{{ t('settings_license.license.status') }}</h6>
                    <div class="mb-3">
                        {{ t('settings_license.license.detected_value') }}: <code>{{ (string) \App\Models\Setting::get('license_status', t('settings_license.license.not_found')) }}</code>
                    </div>
                    
                    <h6>{{ t('settings_license.license.http_code') }}</h6>
                    <div class="mb-3">
                        <code>{{ (string) \App\Models\Setting::get('debug_api_http_code', 'N/A') }}</code>
                    </div>
                    
                    <h6>{{ t('settings_license.license.raw_api_response') }}</h6>
                    <div class="mb-3">
                        @php
                            $apiResponse = \App\Models\Setting::get('debug_api_response', t('settings_license.license.no_response'));
                            if (!is_string($apiResponse)) {
                                $apiResponse = json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: t('settings_license.license.unviewable_format');
                            }
                        @endphp
                        <textarea class="form-control" rows="8" readonly>{{ $apiResponse }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss pour les alertes de succès après 5 secondes
        const successAlerts = document.querySelectorAll('.alert-success');
        if (successAlerts.length > 0) {
            setTimeout(function() {
                successAlerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000); // 5000ms = 5 secondes
        }
        
        // Initialiser les tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
        
        // Fonction de copie de la clé de licence
        document.getElementById('copyLicenseKey').addEventListener('click', function() {
            var licenseInput = this.parentElement.querySelector('input');
            licenseInput.select();
            document.execCommand('copy');
            
            // Changer temporairement le tooltip
            var tooltip = bootstrap.Tooltip.getInstance(this);
            var originalTitle = this.getAttribute('data-bs-original-title');
            tooltip.hide();
            this.setAttribute('data-bs-original-title', '{{ t('settings_license.license.copied') }}');
            tooltip.show();
            
            // Restaurer le titre original après 1.5 secondes
            setTimeout(function() {
                tooltip.hide();
                this.setAttribute('data-bs-original-title', originalTitle);
            }.bind(this), 1500);
        });
    });
</script>
@endsection

@section('styles')
<style>
    .license-info-item {
        margin-bottom: 1rem;
    }
    .license-info-item h5 {
        font-size: 0.9rem;
        font-weight: bold;
        color: #4e73df;
        margin-bottom: 0.5rem;
    }
    .license-info-item p {
        margin-bottom: 0.25rem;
    }
</style>
@endsection
