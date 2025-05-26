@extends('admin.layouts.app')

@section('title', 'Gestion de licence')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Gestion de licence</h1>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    <div class="alert-message">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="alert-message">{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations de licence</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Clé de licence d'installation</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $licenseKey ?? 'Non configurée' }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" id="copyLicenseKey" data-bs-toggle="tooltip" title="Copier la clé">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <h6>Statut de la licence</h6>
                            <div class="mb-3">
                                @if($isValid)
                                    <span class="badge bg-success">Valide</span>
                                @else
                                    <span class="badge bg-danger">Non valide</span>
                                @endif
                            </div>
                            
                            @if($expiresAt)
                                <h6>Date d'expiration</h6>
                                <div class="mb-3">
                                    <span class="{{ $expiresAt && $expiresAt->isPast() ? 'text-danger' : '' }}">
                                        {{ $expiresAt->format('d/m/Y') }}
                                    </span>
                                </div>
                            @endif
                            
                            <h6>Dernière vérification</h6>
                            <div class="mb-3">
                                {{ $lastCheck ? \Carbon\Carbon::parse($lastCheck)->format('d/m/Y H:i:s') : 'Jamais' }}
                            </div>
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.settings.license.force-check') }}" class="btn btn-primary">
                                    <i class="fas fa-sync-alt"></i> Vérifier maintenant
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            @if($licenseDetails)
                                <h6>Détails de la licence</h6>
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Projet</th>
                                            <td>{{ $licenseDetails->project->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Créée le</th>
                                            <td>{{ $licenseDetails->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Expire le</th>
                                            <td class="{{ $licenseDetails->expires_at && $licenseDetails->expires_at->isPast() ? 'text-danger' : '' }}">
                                                {{ $licenseDetails->expires_at ? $licenseDetails->expires_at->format('d/m/Y') : 'Jamais' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Domaine</th>
                                            <td>{{ $licenseDetails->domain ?? 'Non défini' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Adresse IP</th>
                                            <td>{{ $licenseDetails->ip_address ?? 'Non définie' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Statut</th>
                                            <td>
                                                @if($licenseDetails->status == 'active')
                                                    <span class="badge bg-success">Actif</span>
                                                @elseif($licenseDetails->status == 'suspended')
                                                    <span class="badge bg-warning">Suspendu</span>
                                                @elseif($licenseDetails->status == 'revoked')
                                                    <span class="badge bg-danger">Révoqué</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $licenseDetails->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Aucune information détaillée disponible pour cette clé de licence.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Configuration de la licence</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.license.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="license_key" class="form-label">Clé de licence d'installation</label>
                            <input type="text" class="form-control" id="license_key" name="license_key" value="{{ $licenseKey }}" placeholder="XXXX-XXXX-XXXX-XXXX">
                            <div class="form-text">
                                @if($envExists)
                                    Cette clé sera enregistrée dans le fichier .env de votre application.
                                @else
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Le fichier .env n'existe pas encore. Il sera créé automatiquement.</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="check_frequency" class="form-label">Fréquence de vérification</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="check_frequency" name="check_frequency" value="{{ $checkFrequency }}" min="1" max="100" required>
                                <span class="input-group-text">visites</span>
                            </div>
                            <div class="form-text">La licence sera vérifiée une fois tous les N visites du tableau de bord.</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Enregistrer les paramètres
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vérification manuelle</h5>
                </div>
                <div class="card-body">
                    <p>Vous pouvez forcer une vérification immédiate de la licence d'installation. Cela mettra à jour le statut de validité et les informations associées.</p>
                    <a href="{{ route('admin.settings.license.force-check') }}" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Vérifier maintenant
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
            this.setAttribute('data-bs-original-title', 'Copié !');
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
