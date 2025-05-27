@extends('admin.layouts.app')

@section('title', 'Recherche de clés de licence')

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Recherche de clés de licence</h1>

    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                    <div class="alert-message">{!! session('success') !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <div class="alert-message">{!! session('error') !!}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rechercher une clé de licence</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.license.search') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="query" id="licenseSearchInput" placeholder="Entrez une clé de licence..." value="{{ request('query') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> Rechercher
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('admin.license.search') }}" class="btn btn-secondary w-100">
                                    <i class="fas fa-times"></i> Réinitialiser
                                </a>
                            </div>
                        </div>
                    </form>

                    @if(isset($results))
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Clé de série</th>
                                        <th>Projet</th>
                                        <th>Statut</th>
                                        <th>Date d'expiration</th>
                                        <th>Domaine</th>
                                        <th>IP</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($results as $key)
                                        <tr>
                                            <td>
                                                <span class="copy-text" data-clipboard-text="{{ $key->serial_key }}" title="Cliquer pour copier">
                                                    {{ $key->serial_key }}
                                                </span>
                                            </td>
                                            <td>{{ $key->project ? $key->project->name : 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $statusClass = '';
                                                    $statusText = $key->status;
                                                    
                                                    switch($key->status) {
                                                        case 'active':
                                                            $statusClass = 'bg-success';
                                                            $statusText = 'Active';
                                                            break;
                                                        case 'suspended':
                                                            $statusClass = 'bg-warning';
                                                            $statusText = 'Suspendue';
                                                            break;
                                                        case 'revoked':
                                                            $statusClass = 'bg-danger';
                                                            $statusText = 'Révoquée';
                                                            break;
                                                        default:
                                                            $statusClass = 'bg-secondary';
                                                            $statusText = ucfirst($key->status);
                                                    }
                                                    
                                                    // Vérifier si la clé est expirée
                                                    $isExpired = $key->expires_at && \Carbon\Carbon::parse($key->expires_at)->isPast();
                                                    if($isExpired) {
                                                        $statusClass = 'bg-danger';
                                                        $statusText .= ' (Expirée)';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                            </td>
                                            <td>
                                                @if($key->expires_at)
                                                    <span class="{{ $isExpired ? 'text-danger' : '' }}">
                                                        {{ \Carbon\Carbon::parse($key->expires_at)->format('d/m/Y') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Non définie</span>
                                                @endif
                                            </td>
                                            <td>{{ $key->domain ?: 'Non défini' }}</td>
                                            <td>{{ $key->ip_address ?: 'Non définie' }}</td>
                                            <td class="table-action">
                                                <div class="btn-group">
                                                    <a href="#" class="btn btn-sm btn-primary view-key" data-key-id="{{ $key->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($key->status === 'active')
                                                        <a href="{{ route('admin.license.suspend', $key->id) }}" class="btn btn-sm btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir suspendre cette clé?')">
                                                            <i class="fas fa-pause"></i>
                                                        </a>
                                                        <a href="{{ route('admin.license.revoke', $key->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir révoquer cette clé? Cette action est irréversible.')">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    @elseif($key->status === 'suspended')
                                                        <a href="{{ route('admin.license.activate', $key->id) }}" class="btn btn-sm btn-success" onclick="return confirm('Êtes-vous sûr de vouloir réactiver cette clé?')">
                                                            <i class="fas fa-play"></i>
                                                        </a>
                                                        <a href="{{ route('admin.license.revoke', $key->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir révoquer cette clé? Cette action est irréversible.')">
                                                            <i class="fas fa-ban"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">Aucune clé de licence trouvée pour votre recherche.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($results->count() > 0)
                            <div class="d-flex justify-content-center mt-4">
                                {{ $results->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails de clé -->
<div class="modal fade" id="keyDetailsModal" tabindex="-1" aria-labelledby="keyDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keyDetailsModalLabel">Détails de la clé de licence</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="keyDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fonction pour copier le texte au clic
        document.querySelectorAll('.copy-text').forEach(function(element) {
            element.addEventListener('click', function() {
                var text = this.getAttribute('data-clipboard-text');
                navigator.clipboard.writeText(text).then(function() {
                    // Afficher un message temporaire
                    var originalText = element.innerHTML;
                    element.innerHTML = '<span class="text-success">Copié !</span>';
                    setTimeout(function() {
                        element.innerHTML = originalText;
                    }, 1500);
                });
            });
        });
        
        // Fonction pour afficher les détails d'une clé
        document.querySelectorAll('.view-key').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                var keyId = this.getAttribute('data-key-id');
                var modal = new bootstrap.Modal(document.getElementById('keyDetailsModal'));
                modal.show();
                
                // Charger les détails de la clé via AJAX
                fetch('/admin/license/details/' + keyId)
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('keyDetailsContent').innerHTML = html;
                    })
                    .catch(error => {
                        document.getElementById('keyDetailsContent').innerHTML = '<div class="alert alert-danger">Erreur lors du chargement des détails: ' + error.message + '</div>';
                    });
            });
        });
        
        // Focus sur le champ de recherche si vide
        var searchInput = document.getElementById('licenseSearchInput');
        if (searchInput && searchInput.value === '') {
            searchInput.focus();
        }
    });
</script>
@endsection

@section('styles')
<style>
    .copy-text {
        cursor: pointer;
        padding: 2px 5px;
        border-radius: 3px;
        transition: background-color 0.2s;
    }
    
    .copy-text:hover {
        background-color: #f0f0f0;
    }
    
    .table-action .btn-group {
        white-space: nowrap;
    }
    
    .table-action .btn {
        padding: .25rem .5rem;
        font-size: .75rem;
    }
</style>
@endsection
